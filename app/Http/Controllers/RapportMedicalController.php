<?php

namespace App\Http\Controllers;

use App\Models\RapportMedical;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RapportMedicalController extends Controller
{
    public function index(): View
    {
        $rapportsEnAttente = RapportMedical::with(['don.donneur.user', 'don.centreDon'])
            ->where('statut', 'en_attente')
            ->latest()
            ->get();

        $rapportsTraites = RapportMedical::with(['don.donneur.user', 'don.centreDon', 'administrateur'])
            ->where('statut', '!=', 'en_attente')
            ->latest()
            ->paginate(15);

        return view('admin.rapports', compact('rapportsEnAttente', 'rapportsTraites'));
    }

    public function show(int $id): View
    {
        $rapport = RapportMedical::with(['don.donneur.user', 'don.centreDon', 'administrateur'])->findOrFail($id);
        return view('admin.rapport-detail', compact('rapport'));
    }

    public function valider(Request $request, int $id): RedirectResponse
    {
        $rapport = RapportMedical::findOrFail($id);

        $validated = $request->validate([
            'resultat' => ['required', 'in:apte,inapte,conforme,non_conforme'],
            'taux_hemoglobine' => ['nullable', 'string', 'max:50'],
            'serologie_conforme' => ['required', 'boolean'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $rapport->serologie_conforme = (bool)$validated['serologie_conforme'];
        $rapport->taux_hemoglobine = $validated['taux_hemoglobine'] ?? $rapport->taux_hemoglobine;
        $rapport->valider(
            Auth::id(),
            $validated['resultat'],
            $validated['commentaire'] ?? null,
            $validated['taux_hemoglobine'] ?? null
        );

        $statutTexte = ($rapport->statut === 'valide') ? 'VALIDÉ avec succès' : 'REJETÉ pour non-conformité';

        return redirect()->route('rapports.index')->with('success', "Le rapport médical #RAP-{$rapport->id} a été {$statutTexte}.");
    }
}
