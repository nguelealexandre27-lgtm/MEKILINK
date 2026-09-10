<?php

namespace App\Http\Controllers;

use App\Models\CentreDon;
use App\Models\DemandeDonneur;
use App\Models\DemandeSang;
use App\Models\Donneur;
use App\Models\Notification;
use App\Services\AiMatchingService;
use App\Services\BloodCompatibilityService;
use App\Services\TwilioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DemandeSangController extends Controller
{
    protected AiMatchingService $aiService;
    protected TwilioService $twilioService;

    public function __construct(AiMatchingService $aiService, TwilioService $twilioService)
    {
        $this->aiService = $aiService;
        $this->twilioService = $twilioService;
    }

    public function index(): View
    {
        $user = Auth::user();

        if ($user->isDemandeur()) {
            $demandes = $user->demandeur->demandes()->with(['centreDon', 'sollicitations'])->paginate(10);
        } else {
            $demandes = DemandeSang::with(['demandeur.user', 'centreDon'])->latest()->paginate(15);
        }

        return view('demandeur.index', compact('demandes'));
    }

    public function create(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isDemandeur() && !$user->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Seuls les demandeurs peuvent soumettre un besoin de sang.');
        }

        $centres = CentreDon::orderBy('nom')->get();
        $groupes = config('mekilink.groupes_sanguins');
        $urgences = config('mekilink.urgences');

        return view('demandeur.create', compact('centres', 'groupes', 'urgences'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isDemandeur() && !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'groupe_sanguin_recherche' => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'quantite' => ['required', 'integer', 'min:1', 'max:20'],
            'urgence' => ['required', 'in:vitale,urgente,moyenne,faible'],
            'localisation' => ['required', 'string', 'max:200'],
            'centre_don_id' => ['nullable', 'exists:centres_don,id'],
            'date_besoin' => ['nullable', 'date'],
            'motif' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $demandeurId = $user->demandeur ? $user->demandeur->id : null;
        if (!$demandeurId) {
            $demandeur = $user->demandeur()->create([
                'type_demandeur' => 'Particulier',
            ]);
            $demandeurId = $demandeur->id;
        }

        // Si coordonnées non renseignées, utiliser coordonnées par défaut (ex: Yaoundé Centre)
        $lat = $validated['latitude'] ?? 3.8667;
        $lng = $validated['longitude'] ?? 11.5167;

        $demande = DemandeSang::create([
            'demandeur_id' => $demandeurId,
            'centre_don_id' => $validated['centre_don_id'] ?? null,
            'groupe_sanguin_recherche' => $validated['groupe_sanguin_recherche'],
            'quantite' => $validated['quantite'],
            'urgence' => $validated['urgence'],
            'statut' => 'en_attente',
            'localisation' => $validated['localisation'],
            'latitude' => $lat,
            'longitude' => $lng,
            'motif' => $validated['motif'] ?? null,
            'date_besoin' => $validated['date_besoin'] ?? now()->addHours(6),
        ]);

        // 1. DÉCLENCHEMENT DE L'IA DE MATCHING
        $candidats = $this->aiService->matcherDonneurs($demande);

        // 2. ENVOI DES NOTIFICATIONS SMS AUTOMATIQUES AUX MEILLEURS CANDIDATS (Top 3)
        $topCandidats = $candidats->take(3);
        $notifiesCount = 0;

        foreach ($topCandidats as $candidat) {
            $donneur = $candidat['donneur'];
            if ($donneur->user && $donneur->user->telephone) {
                $smsText = "URGENCE MEKILINK: Besoin de sang {$demande->groupe_sanguin_recherche} à {$demande->localisation}. Votre profil est compatible (Score IA: {$candidat['score']}%). Répondez sur l'application : " . url("/donneur/demande/{$demande->id}");

                // Notification en base
                $notif = Notification::create([
                    'user_id' => $donneur->user->id,
                    'demande_sang_id' => $demande->id,
                    'contenu' => $smsText,
                    'type' => 'SMS_TWILIO',
                    'statut' => 'envoye',
                    'date_envoi' => now(),
                ]);

                // Appel Twilio
                $this->twilioService->sendSms($donneur->user->telephone, $smsText);
                $notifiesCount++;
            }
        }

        $message = "Demande enregistrée avec succès ! L'IA a analysé et identifié " . count($candidats) . " donneur(s) compatible(s). {$notifiesCount} donneur(s) prioritaires ont été notifiés par SMS.";

        return redirect()->route('demandes.show', $demande->id)->with('success', $message);
    }

    public function show(int $id): View
    {
        $demande = DemandeSang::with([
            'demandeur.user',
            'centreDon',
            'sollicitations.donneur.user',
            'notifications',
        ])->findOrFail($id);

        $donneursCompatibles = BloodCompatibilityService::getCompatibleDonorsFor($demande->groupe_sanguin_recherche);

        return view('demandeur.show', compact('demande', 'donneursCompatibles'));
    }

    public function relancerMatching(int $id): RedirectResponse
    {
        $demande = DemandeSang::findOrFail($id);
        $candidats = $this->aiService->matcherDonneurs($demande);

        return back()->with('success', "L'algorithme IA a réactualisé les correspondances : " . count($candidats) . " donneurs analysés.");
    }

    public function notifierDonneur(Request $request, int $demandeId, int $donneurId): RedirectResponse
    {
        $demande = DemandeSang::findOrFail($demandeId);
        $donneur = Donneur::with('user')->findOrFail($donneurId);

        if (!$donneur->user || empty($donneur->user->telephone)) {
            return back()->with('error', 'Numéro de téléphone du donneur indisponible.');
        }

        $pivot = DemandeDonneur::where('demande_sang_id', $demande->id)
            ->where('donneur_id', $donneur->id)
            ->first();

        $score = $pivot ? $pivot->score_compatibilite : 80;

        $smsText = "URGENCE MEKILINK : Relance pour besoin vital de sang {$demande->groupe_sanguin_recherche} à {$demande->localisation}. Votre compatibilité est confirmée par IA ({$score}%). Merci de confirmer : " . url("/donneur/demande/{$demande->id}");

        Notification::create([
            'user_id' => $donneur->user->id,
            'demande_sang_id' => $demande->id,
            'contenu' => $smsText,
            'type' => 'SMS_TWILIO',
            'statut' => 'envoye',
            'date_envoi' => now(),
        ]);

        $res = $this->twilioService->sendSms($donneur->user->telephone, $smsText);

        $statusMsg = $res['simulated'] ? " (Simulation en local enregistrée)" : "";

        return back()->with('success', "Alerte SMS transmise à {$donneur->user->nom_complet}{$statusMsg}.");
    }

    public function annuler(int $id): RedirectResponse
    {
        $demande = DemandeSang::findOrFail($id);
        $demande->annuler();

        return back()->with('info', 'La demande de sang a été annulée.');
    }

    public function satisfaire(int $id): RedirectResponse
    {
        $demande = DemandeSang::findOrFail($id);
        $demande->mettreAJourStatut('satisfaite');

        return back()->with('success', 'La demande a été marquée comme satisfaite.');
    }
}
