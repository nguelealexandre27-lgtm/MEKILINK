<?php

namespace App\Http\Controllers;

use App\Models\CentreDon;
use App\Models\DemandeSang;
use App\Models\Don;
use App\Models\Donneur;
use App\Models\RapportMedical;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DonController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isDonneur()) {
            $dons = $user->donneur->dons()->with(['centreDon', 'rapportMedical', 'demandeSang'])->paginate(15);
        } elseif ($user->isAdmin()) {
            $dons = Don::with(['donneur.user', 'centreDon', 'rapportMedical', 'demandeSang'])->latest()->paginate(20);
        } else {
            $dons = Don::whereHas('demandeSang', function ($q) use ($user) {
                $q->where('demandeur_id', $user->demandeur->id ?? 0);
            })->with(['donneur.user', 'centreDon'])->latest()->paginate(15);
        }

        return view('dons.index', compact('dons'));
    }

    public function create(Request $request): View
    {
        $donneurs = Donneur::with('user')->get();
        $centres = CentreDon::orderBy('nom')->get();
        $demandes = DemandeSang::whereIn('statut', ['en_attente', 'en_cours'])->get();

        $selectedDemandeId = $request->query('demande_id');
        $selectedDonneurId = $request->query('donneur_id');

        return view('dons.create', compact('donneurs', 'centres', 'demandes', 'selectedDemandeId', 'selectedDonneurId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'donneur_id' => ['required', 'exists:donneurs,id'],
            'centre_don_id' => ['required', 'exists:centres_don,id'],
            'demande_sang_id' => ['nullable', 'exists:demandes_sang,id'],
            'date_don' => ['required', 'date'],
            'groupe_sanguin' => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'quantite_ml' => ['required', 'integer', 'min:200', 'max:600'],
            'statut' => ['required', 'in:planifie,effectue,valide'],
        ]);

        $don = Don::enregistrer($validated);

        // Créer automatiquement un rapport médical en attente si le don est effectué
        if ($don->statut === 'effectue') {
            RapportMedical::create([
                'don_id' => $don->id,
                'administrateur_id' => Auth::user()->isAdmin() ? Auth::id() : null,
                'date_valorisation' => now()->toDateString(),
                'resultat' => 'apte',
                'statut' => 'en_attente',
                'taux_hemoglobine' => '13.8 g/dL',
                'serologie_conforme' => true,
                'commentaire' => 'Don prélevé avec succès. En attente de validation administrative finale.',
            ]);

            // Si rattaché à une demande, marquer la demande satisfaite
            if ($don->demande_sang_id) {
                $demande = DemandeSang::find($don->demande_sang_id);
                if ($demande) {
                    $demande->mettreAJourStatut('satisfaite');
                }
            }
        }

        return redirect()->route('dons.index')->with('success', "Le don a été enregistré avec succès (Réf #DON-{$don->id}).");
    }

    public function show(int $id): View
    {
        $don = Don::with(['donneur.user', 'centreDon', 'demandeSang.demandeur.user', 'rapportMedical.administrateur'])->findOrFail($id);
        return view('dons.show', compact('don'));
    }
}
