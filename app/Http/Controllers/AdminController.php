<?php

namespace App\Http\Controllers;

use App\Models\CentreDon;
use App\Models\DemandeSang;
use App\Models\Don;
use App\Models\Donneur;
use App\Models\RapportMedical;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        // Indicateurs clés du système
        $totalDonneurs = Donneur::count();
        $donneursDisponibles = Donneur::where('disponibilite', true)->count();
        $totalDemandes = DemandeSang::count();
        $demandesEnAttente = DemandeSang::where('statut', 'en_attente')->count();
        $totalDons = Don::count();
        $rapportsEnAttente = RapportMedical::where('statut', 'en_attente')->count();

        // Répartition des donneurs par groupe sanguin (Supervision des stocks & profils)
        $repartitionGroupes = Donneur::select('groupe_sanguin', DB::raw('count(*) as total'))
            ->groupBy('groupe_sanguin')
            ->pluck('total', 'groupe_sanguin')
            ->toArray();

        // Répartition des dons effectués par groupe sanguin
        $donsParGroupe = Don::where('statut', 'valide')
            ->select('groupe_sanguin', DB::raw('count(*) as total'))
            ->groupBy('groupe_sanguin')
            ->pluck('total', 'groupe_sanguin')
            ->toArray();

        // Demandes récentes
        $demandesRecentes = DemandeSang::with(['demandeur.user', 'centreDon'])->latest()->take(6)->get();

        // Derniers dons
        $donsRecents = Don::with(['donneur.user', 'centreDon', 'rapportMedical'])->latest()->take(6)->get();

        // Centres actifs
        $centres = CentreDon::withCount('dons')->get();

        return view('admin.dashboard', compact(
            'totalDonneurs',
            'donneursDisponibles',
            'totalDemandes',
            'demandesEnAttente',
            'totalDons',
            'rapportsEnAttente',
            'repartitionGroupes',
            'donsParGroupe',
            'demandesRecentes',
            'donsRecents',
            'centres'
        ));
    }

    public function users(Request $request): View
    {
        $query = User::with(['donneur', 'demandeur']);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('nom', 'like', "%{$s}%")
                  ->orWhere('prenom', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('telephone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function toggleStatut(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier le statut de votre propre compte administrateur.');
        }

        $user->statut = ($user->statut === 'actif') ? 'suspendu' : 'actif';
        $user->save();

        $action = ($user->statut === 'actif') ? 'réactivé' : 'suspendu';
        return back()->with('success', "Le compte de {$user->nom_complet} a été {$action}.");
    }

    public function destroyUser(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Action interdite : vous ne pouvez pas supprimer votre propre compte.');
        }

        $nom = $user->nom_complet;
        $user->delete();

        return back()->with('success', "L'utilisateur {$nom} a été supprimé du système.");
    }
}
