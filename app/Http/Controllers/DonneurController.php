<?php

namespace App\Http\Controllers;

use App\Models\CentreDon;
use App\Models\DemandeDonneur;
use App\Models\DemandeSang;
use App\Models\Notification;
use App\Services\BloodCompatibilityService;
use App\Services\TwilioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DonneurController extends Controller
{
    protected TwilioService $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function dashboard(): View|RedirectResponse
    {
        $user = Auth::user();
        $donneur = $user->donneur;

        if (!$donneur) {
            return redirect()->route('profil')->with('warning', 'Veuillez compléter vos informations de donneur.');
        }

        // Sollicitations adressées à ce donneur via l'IA
        $sollicitations = DemandeDonneur::with(['demandeSang.demandeur.user', 'demandeSang.centreDon'])
            ->where('donneur_id', $donneur->id)
            ->whereHas('demandeSang', function ($q) {
                $q->whereIn('statut', ['en_attente', 'en_cours']);
            })
            ->latest()
            ->get();

        // Demandes publiques compatibles urgentes dans la région
        $groupesCompatibles = BloodCompatibilityService::getCanDonateTo($donneur->groupe_sanguin);
        $urgencesPubliques = DemandeSang::with(['demandeur.user', 'centreDon'])
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->whereIn('groupe_sanguin_recherche', $groupesCompatibles)
            ->where('urgence', 'vitale')
            ->latest()
            ->take(5)
            ->get();

        // Historique récent
        $historiqueDons = $donneur->dons()->with(['centreDon', 'rapportMedical'])->latest()->take(5)->get();
        $totalDons = $donneur->dons()->count();

        // Centres de don proches
        $centres = CentreDon::take(3)->get();

        return view('donneur.dashboard', compact(
            'donneur',
            'sollicitations',
            'urgencesPubliques',
            'historiqueDons',
            'totalDons',
            'centres'
        ));
    }

    public function toggleDisponibilite(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $donneur = $user->donneur;

        if (!$donneur) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Profil donneur introuvable'], 404);
            }
            return back()->with('error', 'Profil donneur introuvable.');
        }

        $nouvelleDispo = !$donneur->disponibilite;
        $donneur->mettreAJourDisponibilite($nouvelleDispo);

        $etatTexte = $nouvelleDispo ? 'DISPONIBLE pour sauver des vies' : 'INDISPONIBLE pour le moment';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'disponibilite' => $donneur->disponibilite,
                'message' => "Statut mis à jour : vous êtes maintenant {$etatTexte}.",
            ]);
        }

        return back()->with('success', "Votre disponibilité a été modifiée : vous êtes désormais {$etatTexte}.");
    }

    public function showDemande(int $id): View
    {
        $user = Auth::user();
        $donneur = $user->donneur;
        $demande = DemandeSang::with(['demandeur.user', 'centreDon'])->findOrFail($id);

        $sollicitation = DemandeDonneur::where('demande_sang_id', $demande->id)
            ->where('donneur_id', $donneur->id)
            ->first();

        return view('donneur.demande-detail', compact('demande', 'donneur', 'sollicitation'));
    }

    public function repondre(Request $request, int $demandeId): RedirectResponse
    {
        $user = Auth::user();
        $donneur = $user->donneur;

        $validated = $request->validate([
            'reponse' => ['required', 'in:accepte,refuse'],
        ]);

        $demande = DemandeSang::with(['demandeur.user', 'centreDon'])->findOrFail($demandeId);
        $reponse = $validated['reponse'];

        $donneur->repondreADemande($demandeId, $reponse);

        if ($reponse === 'accepte') {
            // Notifier le demandeur
            if ($demande->demandeur && $demande->demandeur->user) {
                $demandeurUser = $demande->demandeur->user;
                $msg = "EXCELLENTE NOUVELLE : Le donneur {$user->nom_complet} ({$donneur->groupe_sanguin}) a ACCEPTÉ votre demande de sang pour {$demande->localisation}. Contactez-le au {$user->telephone}.";

                Notification::create([
                    'user_id' => $demandeurUser->id,
                    'demande_sang_id' => $demande->id,
                    'contenu' => $msg,
                    'type' => 'SMS_TWILIO',
                    'statut' => 'envoye',
                    'date_envoi' => now(),
                ]);

                if ($demandeurUser->telephone) {
                    $this->twilioService->sendSms($demandeurUser->telephone, $msg);
                }
            }

            return redirect()->route('donneur.dashboard')->with('success', "Merci infiniment ! Votre acceptation a été transmise au demandeur. Vous allez sauver une vie !");
        } else {
            // En cas de refus, informer élégamment et chercher le candidat suivant
            $prochain = DemandeDonneur::where('demande_sang_id', $demande->id)
                ->where('statut_reponse', 'en_attente')
                ->where('donneur_id', '!=', $donneur->id)
                ->orderBy('score_compatibilite', 'desc')
                ->first();

            if ($prochain && $prochain->donneur && $prochain->donneur->user) {
                $suivUser = $prochain->donneur->user;
                $relanceSms = "URGENCE MEKILINK : Besoin urgent de sang {$demande->groupe_sanguin_recherche} à {$demande->localisation}. Vous êtes le donneur compatible prioritaire. Confirmez ici : " . url("/donneur/demande/{$demande->id}");
                
                Notification::create([
                    'user_id' => $suivUser->id,
                    'demande_sang_id' => $demande->id,
                    'contenu' => $relanceSms,
                    'type' => 'SMS_TWILIO',
                    'statut' => 'envoye',
                    'date_envoi' => now(),
                ]);

                $this->twilioService->sendSms($suivUser->telephone, $relanceSms);
            }

            return redirect()->route('donneur.dashboard')->with('info', "Votre réponse a été enregistrée. Le système a réorienté la demande vers le candidat suivant.");
        }
    }

    public function historique(): View
    {
        $user = Auth::user();
        $donneur = $user->donneur;
        $dons = $donneur->consulterHistoriqueDons();

        return view('donneur.historique', compact('donneur', 'dons'));
    }
}
