@extends('layouts.app')

@section('title', 'Validation du Rapport Médical #RAP-' . $rapport->id . ' - MEKILINK')

@section('content')
<div class="container" style="max-width:800px;padding-top:20px;padding-bottom:60px;">
    <div style="margin-bottom:16px;">
        <a href="{{ route('rapports.index') }}" class="btn btn-secondary btn-sm">
            &larr; Retour aux rapports médicaux
        </a>
    </div>

    <div class="card" style="box-shadow:var(--shadow-lg);">
        <div class="card-header" style="background:#f8fafc;padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;width:100%;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <span class="blood-badge filled lg">{{ $rapport->don->groupe_sanguin }}</span>
                    <div>
                        <h1 style="font-size:1.4rem;font-weight:800;color:var(--slate-900);margin:0;">
                            Rapport Médical Transfusionnel #RAP-{{ $rapport->id }}
                        </h1>
                        <p style="font-size:0.85rem;color:var(--slate-600);margin:2px 0 0;">
                            Rattaché au Don #DON-{{ $rapport->don->id }} • Prélevé le {{ $rapport->don->date_don->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                </div>

                <div>
                    @if($rapport->statut === 'valide')
                        <span class="badge-success" style="font-size:0.9rem;padding:6px 12px;">Validé Conforme</span>
                    @elseif($rapport->statut === 'rejete')
                        <span class="badge-danger" style="font-size:0.9rem;padding:6px 12px;">Rejeté</span>
                    @else
                        <span class="badge-warning" style="font-size:0.9rem;padding:6px 12px;">En attente de validation</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body" style="padding:28px;">
            <!-- Fiche d'identification Donneur & Prélèvement -->
            <div class="grid-2" style="margin-bottom:24px;background:var(--slate-50);padding:18px;border-radius:var(--radius-sm);border:1px solid var(--slate-200);">
                <div>
                    <div style="font-size:0.75rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Donneur Associé</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--slate-900);margin-top:2px;">
                        {{ $rapport->don->donneur->user->nom_complet ?? 'Donneur' }}
                    </div>
                    <div style="font-size:0.82rem;color:var(--slate-600);">
                        Groupe {{ $rapport->don->groupe_sanguin }} • Âge : {{ $rapport->don->donneur->user->age ?? 'N/A' }} ans
                    </div>
                </div>

                <div>
                    <div style="font-size:0.75rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Lieu & Volume</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--slate-900);margin-top:2px;">
                        {{ $rapport->don->centreDon->nom ?? 'Centre' }}
                    </div>
                    <div style="font-size:0.82rem;color:var(--slate-600);">
                        Poche CGR : <strong>{{ $rapport->don->quantite_ml }} mL</strong>
                    </div>
                </div>
            </div>

            <!-- Formulaire de Validation par l'Administrateur -->
            <form method="POST" action="{{ route('rapports.valider', $rapport->id) }}">
                @csrf

                <div style="margin-bottom:20px;">
                    <label class="form-label" style="font-size:1rem;">1. Contrôle Sérologique & Virologique Obligatoire</label>
                    <div style="background:#fff;border:1px solid var(--slate-200);border-radius:var(--radius-sm);padding:14px;">
                        <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;">
                            <input type="checkbox" name="serologie_conforme" value="1" {{ $rapport->serologie_conforme ? 'checked' : '' }} style="margin-top:4px;accent-color:#059669;">
                            <div>
                                <strong style="color:var(--slate-900);font-size:0.92rem;display:block;">
                                    Sérologie Négative et Conforme aux Normes Sanitaires
                                </strong>
                                <span style="font-size:0.8rem;color:var(--slate-600);">
                                    Dépistage négatif certifié pour : VIH 1 & 2, Virus Hépatite B (Ag HBs), Virus Hépatite C (Ac anti-VHC), et Syphilis (TPHA/VDRL).
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid-2" style="margin-bottom:20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="taux_hemoglobine">2. Taux d'Hémoglobine Pré-Don</label>
                        <input type="text" id="taux_hemoglobine" name="taux_hemoglobine" class="form-control" value="{{ old('taux_hemoglobine', $rapport->taux_hemoglobine ?? '13.8 g/dL') }}" placeholder="Ex: 13.5 g/dL">
                        <span class="form-hint">Norme minimale : 12.5 g/dL (femme) ou 13.0 g/dL (homme).</span>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="resultat">3. Décision d'Aptitude Médicale</label>
                        <select id="resultat" name="resultat" class="form-select" style="font-weight:700;">
                            <option value="conforme" {{ $rapport->resultat === 'conforme' ? 'selected' : '' }}>
                                ✓ Conforme & Apte (Sang validé pour distribution)
                            </option>
                            <option value="apte" {{ $rapport->resultat === 'apte' ? 'selected' : '' }}>
                                ✓ Apte
                            </option>
                            <option value="inapte" {{ $rapport->resultat === 'inapte' ? 'selected' : '' }}>
                                ✗ Inapte (Non distribution)
                            </option>
                            <option value="non_conforme" {{ $rapport->resultat === 'non_conforme' ? 'selected' : '' }}>
                                ✗ Non Conforme (Rejet biologique)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" for="commentaire">Observations Médicales & Traçabilité</label>
                    <textarea id="commentaire" name="commentaire" rows="3" class="form-control" placeholder="Mentionnez les éventuelles observations de laboratoire ou remarques de conformité...">{{ old('commentaire', $rapport->commentaire) }}</textarea>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--slate-200);padding-top:20px;">
                    <div>
                        @if($rapport->administrateur)
                            <span style="font-size:0.8rem;color:var(--slate-500);">
                                Dernier examen par : <strong>{{ $rapport->administrateur->nom_complet }}</strong>
                            </span>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-vital btn-lg">
                        Enregistrer la Décision Médicale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
