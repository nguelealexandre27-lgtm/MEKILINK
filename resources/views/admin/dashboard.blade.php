@extends('layouts.app')

@section('title', 'Supervision Globale - Administration MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <!-- En-tête Supervision -->
    <div class="page-header">
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="badge-vitale" style="background:#0f172a;color:#fff;border-color:#334155;">
                    ADMINISTRATION
                </span>
                <h1 class="page-title" style="margin:0;">Console de Supervision MEKILINK</h1>
            </div>
            <p class="page-subtitle">Vue d'ensemble du réseau transfusionnel, des stocks et de la conformité médicale.</p>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                <span>👥</span>
                <span>Gérer les Utilisateurs</span>
            </a>
            <a href="{{ route('rapports.index') }}" class="btn btn-vital">
                <span>📋</span>
                <span>Rapports Médicaux ({{ $rapportsEnAttente }})</span>
            </a>
        </div>
    </div>

    <!-- 4 Cartes d'Indicateurs Clés -->
    <div class="grid-4" style="margin-bottom:28px;">
        <div class="card" style="padding:20px;border-top:4px solid var(--blood-primary);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Donneurs Inscrits</div>
                    <div style="font-size:2rem;font-weight:900;color:var(--slate-900);margin-top:4px;">{{ $totalDonneurs }}</div>
                </div>
                <span style="font-size:1.6rem;">🩸</span>
            </div>
            <div style="font-size:0.8rem;color:#059669;font-weight:600;margin-top:8px;">
                ● {{ $donneursDisponibles }} disponibles immédiatement
            </div>
        </div>

        <div class="card" style="padding:20px;border-top:4px solid var(--urgency-vital);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Demandes de Sang</div>
                    <div style="font-size:2rem;font-weight:900;color:var(--slate-900);margin-top:4px;">{{ $totalDemandes }}</div>
                </div>
                <span style="font-size:1.6rem;">🚨</span>
            </div>
            <div style="font-size:0.8rem;color:#dc2626;font-weight:600;margin-top:8px;">
                ● {{ $demandesEnAttente }} en attente de donneur
            </div>
        </div>

        <div class="card" style="padding:20px;border-top:4px solid #059669;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Dons Réalisés</div>
                    <div style="font-size:2rem;font-weight:900;color:var(--slate-900);margin-top:4px;">{{ $totalDons }}</div>
                </div>
                <span style="font-size:1.6rem;">💉</span>
            </div>
            <div style="font-size:0.8rem;color:var(--slate-600);margin-top:8px;">
                {{ $totalDons * 450 }} mL collectés au total
            </div>
        </div>

        <div class="card" style="padding:20px;border-top:4px solid var(--urgency-medium);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Validation Médicale</div>
                    <div style="font-size:2rem;font-weight:900;color:var(--slate-900);margin-top:4px;">{{ $rapportsEnAttente }}</div>
                </div>
                <span style="font-size:1.6rem;">🔬</span>
            </div>
            <div style="font-size:0.8rem;color:#b45309;font-weight:600;margin-top:8px;">
                Rapports en attente de visa
            </div>
        </div>
    </div>

    <!-- Répartition des Donneurs par Groupe Sanguin (Supervision des stocks) -->
    <div class="card" style="margin-bottom:28px;">
        <div class="card-header">
            <div class="card-title">
                <span>🩸</span>
                Cartographie des Donneurs Actifs par Groupe Sanguin
            </div>
            <span class="badge-info">Indicateur de Disponibilité Transfusionnelle</span>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(110px, 1fr));gap:14px;">
                @foreach(config('mekilink.groupes_sanguins') as $grp)
                    @php $count = $repartitionGroupes[$grp] ?? 0; @endphp
                    <div style="border:1px solid var(--slate-200);border-radius:var(--radius-sm);padding:14px;text-align:center;background:{{ $grp === 'O-' ? '#fff5f5' : '#ffffff' }};border-top:3px solid {{ $grp === 'O-' ? 'var(--blood-arterial)' : 'var(--slate-300)' }};">
                        <span class="blood-badge filled sm" style="margin-bottom:6px;">{{ $grp }}</span>
                        <div style="font-size:1.5rem;font-weight:900;color:var(--slate-900);">{{ $count }}</div>
                        <div style="font-size:0.75rem;color:var(--slate-500);">donneur(s)</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Grille 2 Colonnes : Demandes Récentes & Derniers Dons -->
    <div class="grid-2">
        <!-- Demandes Récentes -->
        <div class="card">
            <div class="card-header">
                <div class="card-title" style="font-size:1rem;">
                    <span>🚨</span>
                    Dernières Demandes Enregistrées
                </div>
                <a href="{{ route('demandes.index') }}" style="font-size:0.8rem;font-weight:700;">Voir tout &rarr;</a>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($demandesRecentes as $dem)
                    <div style="padding:14px 18px;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;justify-content:space-between;gap:12px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="blood-badge sm filled">{{ $dem->groupe_sanguin_recherche }}</span>
                            <div>
                                <div style="font-weight:700;font-size:0.88rem;color:var(--slate-900);">
                                    {{ $dem->localisation }}
                                </div>
                                <div style="font-size:0.75rem;color:var(--slate-500);">
                                    {{ $dem->quantite }} poche(s) • {{ $dem->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <span class="{{ $dem->statut_badge['class'] }}" style="font-size:0.72rem;">
                                {{ $dem->statut_badge['label'] }}
                            </span>
                            <div>
                                <a href="{{ route('demandes.show', $dem->id) }}" style="font-size:0.75rem;color:var(--blood-primary);font-weight:700;">
                                    Matching IA &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="padding:30px;text-align:center;color:var(--slate-400);">Aucune demande récente.</div>
                @endforelse
            </div>
        </div>

        <!-- Derniers Dons Collectés -->
        <div class="card">
            <div class="card-header">
                <div class="card-title" style="font-size:1rem;">
                    <span>💉</span>
                    Derniers Prélèvements Collectés
                </div>
                <a href="{{ route('dons.index') }}" style="font-size:0.8rem;font-weight:700;">Voir tout &rarr;</a>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($donsRecents as $don)
                    <div style="padding:14px 18px;border-bottom:1px solid var(--slate-100);display:flex;align-items:center;justify-content:space-between;gap:12px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="blood-badge sm filled">{{ $don->groupe_sanguin }}</span>
                            <div>
                                <div style="font-weight:700;font-size:0.88rem;color:var(--slate-900);">
                                    {{ $don->donneur->user->nom_complet ?? 'Donneur' }}
                                </div>
                                <div style="font-size:0.75rem;color:var(--slate-500);">
                                    {{ $don->centreDon->nom ?? 'Centre' }} • {{ $don->date_don->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($don->rapportMedical)
                                @if($don->rapportMedical->statut === 'valide')
                                    <span class="badge-success" style="font-size:0.72rem;">Validé</span>
                                @else
                                    <a href="{{ route('rapports.show', $don->rapportMedical->id) }}" class="btn btn-vital btn-sm" style="font-size:0.72rem;padding:3px 8px;">
                                        Valider Médical
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:30px;text-align:center;color:var(--slate-400);">Aucun don récent.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
