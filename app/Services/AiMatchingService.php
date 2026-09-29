<?php

namespace App\Services;

use App\Models\DemandeSang;
use App\Models\DemandeDonneur;
use App\Models\Donneur;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Moteur d'intelligence artificielle de mise en relation et de scoring des donneurs de sang.
 * Combine un algorithme médical déterministe (ABO/Rh, disponibilité, délai 56j, Haversine, urgence)
 * et une analyse via l'API Google Gemini (Gemini 3.6 Flash / 1.5 Flash).
 */
class AiMatchingService
{
    protected bool $aiAvailable = true;
    protected ?string $aiErrorMessage = null;

    public function isAiAvailable(): bool
    {
        return $this->aiAvailable;
    }

    public function getAiErrorMessage(): ?string
    {
        return $this->aiErrorMessage;
    }

    /**
     * Recherche et classe les donneurs compatibles pour une demande donnée.
     * Enregistre les scores dans la table associative `demande_donneurs`.
     *
     * @param DemandeSang $demande
     * @param int $limiteNombre Nombre max de donneurs à retenir (défaut 10)
     * @return Collection
     */
    public function matcherDonneurs(DemandeSang $demande, int $limiteNombre = 10): Collection
    {
        @set_time_limit(60);
        $this->aiAvailable = true;
        $this->aiErrorMessage = null;
        $groupeRecherche = $demande->groupe_sanguin_recherche;
        $tousLesDonneurs = Donneur::with('user')->get();

        $candidats = collect();

        foreach ($tousLesDonneurs as $donneur) {
            // Vérifier que l'utilisateur est actif
            if ($donneur->user && $donneur->user->statut !== 'actif') {
                continue;
            }

            // 1. FILTRAGE IMMUNOLOGIQUE STRICT (ABO / RHÉSUS)
            if (!BloodCompatibilityService::isCompatible($donneur->groupe_sanguin, $groupeRecherche)) {
                continue; // Exclusion immédiate des profils incompatibles
            }

            // 2. VÉRIFICATION DU DÉLAI MÉDICAL POST-DON (56 jours minimum)
            if ($donneur->date_dernier_don) {
                $joursDepuisDon = Carbon::parse($donneur->date_dernier_don)->diffInDays(now());
                $delaiRequis = config('mekilink.delai_don_jours', 56);
                if ($joursDepuisDon < $delaiRequis) {
                    continue; // Médicalement inéligible au don de sang total pour sa propre sécurité
                }
            }

            // 3. CALCUL DE LA DISTANCE GÉOGRAPHIQUE (Formule de Haversine)
            $distanceKm = $this->calculerDistance(
                $demande->latitude,
                $demande->longitude,
                $donneur->latitude,
                $donneur->longitude
            );

            // 4. CALCUL DU SCORE DE PERTINENCE (0 à 100)
            $score = $this->calculerScorePertinence($donneur, $demande, $distanceKm);

            $candidats->push([
                'donneur' => $donneur,
                'score' => $score,
                'distance_km' => $distanceKm,
                'is_isogroupe' => ($donneur->groupe_sanguin === $groupeRecherche),
            ]);
        }

        // Tri par score décroissant, puis par donneurs isogroupes, puis par disponibilité déclarée
        $classes = $candidats->sort(function ($a, $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }
            if ($a['is_isogroupe'] !== $b['is_isogroupe']) {
                return $b['is_isogroupe'] <=> $a['is_isogroupe'];
            }
            return ($b['donneur']->disponibilite ? 1 : 0) <=> ($a['donneur']->disponibilite ? 1 : 0);
        })->values()->take($limiteNombre);

        $geminiCallCount = 0;
        $geminiSuccessCount = 0;

        // Enregistrement des résultats dans `demande_donneurs`
        foreach ($classes as $index => $candidat) {
            $donneur = $candidat['donneur'];
            $score = $candidat['score'];
            $distance = $candidat['distance_km'];

            // Appel ciblé à Gemini pour le donneur prioritaire (ou repli si le premier échoue) pour une latence web optimale
            $shouldCallGemini = ($geminiSuccessCount === 0 && $index < 2);

            $explication = $this->genererExplicationIA(
                $donneur,
                $demande,
                $score,
                $distance,
                $shouldCallGemini,
                $geminiCallCount,
                $geminiSuccessCount
            );

            DemandeDonneur::updateOrCreate(
                [
                    'demande_sang_id' => $demande->id,
                    'donneur_id' => $donneur->id,
                ],
                [
                    'score_compatibilite' => $score,
                    'distance_km' => $distance,
                    'explication_ia' => $explication,
                ]
            );
        }

        // Si des appels Gemini ont été tentés et qu'aucun n'a réussi
        if ($geminiCallCount > 0 && $geminiSuccessCount === 0) {
            $this->aiAvailable = false;
            $this->aiErrorMessage = "L'IA n'est pas disponible pour le moment, veuillez réessayer plus tard.";
        } else {
            $this->aiAvailable = true;
        }

        return $classes;
    }

    /**
     * Calcule le score de pertinence multi-critères
     */
    protected function calculerScorePertinence(Donneur $donneur, DemandeSang $demande, ?float $distanceKm): int
    {
        $score = 0;
        $groupeRecherche = $demande->groupe_sanguin_recherche;

        // A. Score de base compatibilité sanguine (Max: 50 points)
        if ($donneur->groupe_sanguin === $groupeRecherche) {
            $score += 50; // Isogroupe parfait
        } else {
            $score += 38; // Compatible universel ou alternatif
        }

        // B. Disponibilité déclarée par le donneur (Max: 25 points)
        if ($donneur->disponibilite) {
            $score += 25;
        } else {
            $score += 5; // Enregistré mais non explicitement disponible
        }

        // C. Proximité géographique (Max: 15 points)
        if ($distanceKm !== null) {
            if ($distanceKm <= 5.0) {
                $score += 15;
            } elseif ($distanceKm <= 12.0) {
                $score += 10;
            } elseif ($distanceKm <= 25.0) {
                $score += 6;
            } elseif ($distanceKm <= 40.0) {
                $score += 2;
            }
        } else {
            $score += 8; // Score moyen si coordonnées non géolocalisées
        }

        // D. Facteur d'urgence (Max: 10 points)
        switch ($demande->urgence) {
            case 'vitale':
                // En urgence vitale, bonus pour les donneurs très proches et disponibles
                if ($donneur->disponibilite && ($distanceKm === null || $distanceKm <= 10.0)) {
                    $score += 10;
                } else {
                    $score += 5;
                }
                break;
            case 'urgente':
                $score += 8;
                break;
            case 'moyenne':
                $score += 6;
                break;
            default:
                $score += 4;
                break;
        }

        return min(100, max(0, $score));
    }

    /**
     * Calcul de la distance entre deux coordonnées géographiques (Haversine en km)
     */
    public function calculerDistance(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        $earthRadius = 6371; // Rayon moyen de la Terre en kilomètres

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 1);
    }

    /**
     * Génération de l'explication IA (priorité absolue à Gemini)
     */
    protected function genererExplicationIA(
        Donneur $donneur,
        DemandeSang $demande,
        int $score,
        ?float $distance,
        bool $shouldCallGemini = true,
        int &$callCount = 0,
        int &$successCount = 0
    ): string {
        $geminiKey = config('mekilink.gemini.api_key');

        if ($shouldCallGemini && !empty($geminiKey)) {
            $callCount++;
            $explicationGemini = $this->appelerGeminiPourExplication($donneur, $demande, $score, $distance);
            if ($explicationGemini) {
                $successCount++;
                return $explicationGemini;
            }
        }

        // Synthèse clinique de haute précision
        $nomDonneur = $donneur->user ? $donneur->user->nom_complet : 'Donneur';
        $compatibilite = ($donneur->groupe_sanguin === $demande->groupe_sanguin_recherche)
            ? "isogroupe parfait ({$donneur->groupe_sanguin})"
            : "compatible immunologique ({$donneur->groupe_sanguin} vers {$demande->groupe_sanguin_recherche})";
        $dispo = $donneur->disponibilite ? 'immédiatement disponible' : 'disponibilité à confirmer';
        $distStr = $distance !== null ? "localisé à environ {$distance} km" : "secteur {$donneur->localisation}";

        return "Score IA: {$score}%. {$nomDonneur} présente un profil {$compatibilite}, {$dispo}, {$distStr}. Priorité adaptée au degré d'urgence {$demande->urgence}.";
    }

    /**
     * Appel à l'API Google Gemini pour formuler une analyse clinique intelligente
     */
    public function appelerGeminiPourExplication(Donneur $donneur, DemandeSang $demande, int $score, ?float $distance): ?string
    {
        try {
            $apiKey = config('mekilink.gemini.api_key');
            if (empty($apiKey)) {
                return null;
            }

            $model = config('mekilink.gemini.model', 'gemini-3.6-flash');
            if (!str_starts_with($model, 'gemini-')) {
                $model = 'gemini-' . $model;
            }
            $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";

            $prompt = "Tu es le module IA de la plateforme médicale MEKILINK (KIROVA DIGITAL). " .
                "Rédige une brève synthèse clinique de 2 phrases justifiant le choix de ce donneur : " .
                "Demande: groupe {$demande->groupe_sanguin_recherche}, urgence {$demande->urgence}, lieu: {$demande->localisation}. " .
                "Donneur: groupe {$donneur->groupe_sanguin}, disponible: " . ($donneur->disponibilite ? 'Oui' : 'Non') . ", distance: {$distance} km, Score calculé: {$score}%.";

            $response = Http::timeout(35)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 250,
                    'thinkingConfig' => [
                        'thinkingBudget' => 0
                    ]
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $texte = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!empty($texte)) {
                    return trim($texte);
                }
            } else {
                Log::warning("[GEMINI AI] Erreur API status " . $response->status() . " : " . $response->body());
            }
        } catch (\Exception $e) {
            Log::warning("[GEMINI AI] Impossible de joindre l'API : " . $e->getMessage());
        }

        return null;
    }

    /**
     * Analyse clinique instantanée via Gemini d'une compatibilité de groupes sanguins
     */
    public function expliquerCompatibiliteGemini(string $donneur, string $receveur, bool $isCompatible): ?string
    {
        try {
            $apiKey = config('mekilink.gemini.api_key');
            if (empty($apiKey)) {
                return null;
            }

            $model = config('mekilink.gemini.model', 'gemini-3.6-flash');
            if (!str_starts_with($model, 'gemini-')) {
                $model = 'gemini-' . $model;
            }
            $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";

            $statut = $isCompatible ? 'COMPATIBLE' : 'INCOMPATIBLE';
            $prompt = "Tu es l'IA médicale de MEKILINK (KIROVA DIGITAL). " .
                "En une ou deux phrases claires et médicalement rigoureuses, analyse la compatibilité transfusionnelle entre un donneur de sang {$donneur} et un receveur {$receveur} (résultat : {$statut}).";

            $response = Http::timeout(35)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 200,
                    'thinkingConfig' => [
                        'thinkingBudget' => 0
                    ]
                ]
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $texte = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!empty($texte)) {
                    return trim($texte);
                }
            }
        } catch (\Exception $e) {
            Log::warning("[GEMINI AI] Erreur analyse compatibilité : " . $e->getMessage());
        }

        return null;
    }
}
