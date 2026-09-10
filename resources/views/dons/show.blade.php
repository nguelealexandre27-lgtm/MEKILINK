@extends('layouts.app')

@section('title', 'Détails du Don #DON-' . $don->id . ' - MEKILINK')

@section('content')
<div class="container" style="max-width:760px;padding-top:20px;">
    <div style="margin-bottom:16px;">
        <a href="{{ route('dons.index') }}" class="btn btn-secondary btn-sm">
            &larr; Retour au registre des dons
        </a>
    </div>

    <div class="card">
        <div class="card-header" style="background:#f8fafc;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <span class="blood-badge filled lg">{{ $don->groupe_sanguin }}</span>
                    <div>
                        <h1 style="font-size:1.4rem;font-weight:800;color:var(--slate-900);margin:0;">
                            Don Réf #DON-{{ $don->id }}
                        </h1>
                        <p style="font-size:0.85rem;color:var(--slate-600);margin:2px 0 0;">
                            Prélevé le {{ $don->date_don->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                </div>

                <div>
                    @if($don->statut === 'valide')
                        <span class="badge-success" style="font-size:0.9rem;padding:6px 14px;">✓ Validé</span>
                    @else
                        <span class="badge-info" style="font-size:0.9rem;padding:6px 14px;">{{ ucfirst($don->statut) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body" style="padding:26px;">
            <div class="grid-2" style="margin-bottom:24px;">
                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Donneur</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--slate-900);margin-top:2px;">
                        {{ $don->donneur->user->nom_complet ?? 'Donneur' }}
                    </div>
                    <div style="font-size:0.85rem;color:var(--slate-600);">
                        Groupe {{ $don->groupe_sanguin }} • {{ $don->donneur->localisation ?? 'Yaoundé' }}
                    </div>
                </div>

                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Centre de Transfusion</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--slate-900);margin-top:2px;">
                        {{ $don->centreDon->nom ?? 'Centre' }}
                    </div>
                    <div style="font-size:0.85rem;color:var(--slate-600);">
                        Volume : <strong>{{ $don->quantite_ml }} mL</strong>
                    </div>
                </div>
            </div>

            @if($don->demandeSang)
                <div style="background:var(--slate-50);border:1px solid var(--slate-200);border-radius:var(--radius-sm);padding:14px;margin-bottom:24px;">
                    <div style="font-size:0.8rem;font-weight:700;color:var(--slate-600);text-transform:uppercase;margin-bottom:4px;">
                        Attribué à la Demande :
                    </div>
                    <a href="{{ route('demandes.show', $don->demandeSang->id) }}" style="font-weight:700;color:var(--blood-primary);">
                        #DEM-{{ $don->demandeSang->id }} — {{ $don->demandeSang->localisation }} ({{ $don->demandeSang->groupe_sanguin_recherche }})
                    </a>
                </div>
            @endif

            <!-- Rapport Médical Associé -->
            <div style="border-top:1px solid var(--slate-200);padding-top:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <h3 style="font-size:1.05rem;font-weight:700;color:var(--slate-900);margin:0;">
                        Rapport Médical Associé
                    </h3>
                    @if($don->rapportMedical)
                        <span class="{{ $don->rapportMedical->statut === 'valide' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($don->rapportMedical->statut) }}
                        </span>
                    @endif
                </div>

                @if($don->rapportMedical)
                    <div style="background:#f8fafc;border:1px solid var(--slate-200);border-radius:var(--radius-sm);padding:16px;">
                        <p style="margin:0 0 6px;font-size:0.88rem;color:var(--slate-700);">
                            🧬 <strong>Sérologie :</strong> {{ $don->rapportMedical->serologie_conforme ? 'Conforme (Négative aux agents pathogènes majeurs)' : 'Non-conforme' }}
                        </p>
                        <p style="margin:0 0 6px;font-size:0.88rem;color:var(--slate-700);">
                            🩸 <strong>Taux d'hémoglobine :</strong> {{ $don->rapportMedical->taux_hemoglobine ?? 'Non renseigné' }}
                        </p>
                        @if($don->rapportMedical->commentaire)
                            <p style="margin:0 0 6px;font-size:0.88rem;color:var(--slate-700);">
                                📝 <strong>Observations :</strong> {{ $don->rapportMedical->commentaire }}
                            </p>
                        @endif
                        @if($don->rapportMedical->administrateur)
                            <p style="margin:0;font-size:0.8rem;color:var(--slate-500);">
                                Validé par : <strong>{{ $don->rapportMedical->administrateur->nom_complet }}</strong> le {{ $don->rapportMedical->date_valorisation->format('d/m/Y') }}
                            </p>
                        @endif
                    </div>
                @else
                    <p style="color:var(--slate-500);font-size:0.88rem;margin:0;">
                        Aucun rapport médical n'a encore été associé à ce don.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
