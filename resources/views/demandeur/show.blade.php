@extends('layouts.app')

@section('title', 'Suivi de Demande & Matching IA - MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <!-- En-tête et Navigation -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
        <a href="{{ route('demandeur.dashboard') }}" class="btn btn-secondary btn-sm">
            &larr; Retour aux demandes
        </a>

        <div style="display:flex;gap:10px;">
            <form method="POST" action="{{ route('demandes.relancer-ia', $demande->id) }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-sm">
                    🔄 Réactualiser Matching IA
                </button>
            </form>

            @if($demande->statut !== 'satisfaite')
                <form method="POST" action="{{ route('demandes.satisfaire', $demande->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm">
                        ✓ Marquer Satisfaite
                    </button>
                </form>
            @endif

            @if($demande->statut !== 'annulee' && $demande->statut !== 'satisfaite')
                <form method="POST" action="{{ route('demandes.annuler', $demande->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Confirmez-vous l\'annulation de cette demande ?');">
                        Annuler
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Fiche Récapitulative du Besoin -->
    <div class="card" style="margin-bottom:28px;border-left:5px solid var(--blood-primary);">
        <div class="card-body" style="padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
                <div style="display:flex;align-items:center;gap:16px;">
                    <span class="blood-badge filled lg" style="font-size:1.8rem;padding:10px 20px;">
                        {{ $demande->groupe_sanguin_recherche }}
                    </span>
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                            <h1 style="font-size:1.5rem;font-weight:800;color:var(--slate-900);margin:0;">
                                {{ $demande->localisation }}
                            </h1>
                            <span class="{{ $demande->urgence_badge['class'] }}">
                                {{ $demande->urgence_badge['label'] }}
                            </span>
                            <span class="{{ $demande->statut_badge['class'] }}">
                                {{ $demande->statut_badge['label'] }}
                            </span>
                        </div>
                        <p style="font-size:0.88rem;color:var(--slate-600);margin:0;">
                            Demande Réf #DEM-{{ $demande->id }} • Émise {{ $demande->created_at->format('d/m/Y à H:i') }} par <strong>{{ $demande->demandeur->user->nom_complet ?? 'Établissement' }}</strong>
                        </p>
                    </div>
                </div>

                <div style="text-align:right;">
                    <div style="font-size:1.6rem;font-weight:900;color:var(--blood-primary);">
                        {{ $demande->quantite }} poche(s)
                    </div>
                    <div style="font-size:0.75rem;color:var(--slate-500);text-transform:uppercase;">Volume requis</div>
                </div>
            </div>

            @if($demande->motif)
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--slate-100);font-size:0.9rem;color:var(--slate-700);">
                    <strong>Contexte clinique :</strong> {{ $demande->motif }}
                </div>
            @endif

            @if($demande->centreDon)
                <div style="margin-top:10px;font-size:0.85rem;color:var(--slate-600);">
                    🏥 Centre de transfusion rattaché : <strong>{{ $demande->centreDon->nom }}</strong> ({{ $demande->centreDon->telephone }})
                </div>
            @endif
        </div>
    </div>

    <!-- Moteur d'Intelligence Artificielle : Résultats du Matching -->
    <div class="card" style="margin-bottom:28px;">
        <div class="card-header" style="background:#fdfefe;">
            <div class="card-title">
                <span style="font-size:1.3rem;">🧠</span>
                Donneurs Compatibles Identifiés par l'IA ({{ $demande->sollicitations->count() }})
            </div>
            <span class="badge-success">Trié par Score de Pertinence Décroissant</span>
        </div>

        <div class="card-body" style="padding:0;">
            @if($demande->sollicitations->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.92rem;">
                        <thead>
                            <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                                <th style="padding:14px 20px;">Rang IA</th>
                                <th style="padding:14px 20px;">Donneur</th>
                                <th style="padding:14px 20px;">Groupe</th>
                                <th style="padding:14px 20px;">Score de Pertinence IA</th>
                                <th style="padding:14px 20px;">Proximité</th>
                                <th style="padding:14px 20px;">Statut Réponse</th>
                                <th style="padding:14px 20px;text-align:right;">Alerte SMS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demande->sollicitations as $index => $sollicitation)
                                @php
                                    $donneur = $sollicitation->donneur;
                                    $u = $donneur ? $donneur->user : null;
                                @endphp
                                <tr style="border-bottom:1px solid var(--slate-100);background:{{ $sollicitation->statut_reponse === 'accepte' ? '#f0fdf4' : ($index === 0 ? '#fffaf8' : '#ffffff') }};">
                                    <td style="padding:14px 20px;font-weight:800;color:var(--slate-700);font-family:var(--font-mono);">
                                        #{{ $index + 1 }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <div style="font-weight:700;color:var(--slate-900);">
                                            {{ $u ? $u->nom_complet : 'Donneur Anonyme' }}
                                        </div>
                                        <div style="font-size:0.75rem;color:var(--slate-500);">
                                            📞 {{ $u->telephone ?? 'Non renseigné' }} • {{ $donneur->localisation ?? 'Yaoundé' }}
                                        </div>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span class="blood-badge sm filled">{{ $donneur->groupe_sanguin }}</span>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <span class="ai-score-pill {{ $sollicitation->score_compatibilite >= 85 ? 'ai-score-high' : 'ai-score-medium' }}">
                                                {{ $sollicitation->score_compatibilite }}%
                                            </span>
                                        </div>
                                        <div class="ai-score-bar-bg" style="max-width:140px;">
                                            <div class="ai-score-bar-fill" style="width:{{ $sollicitation->score_compatibilite }}%;"></div>
                                        </div>
                                        @if($sollicitation->explication_ia)
                                            <div style="font-size:0.75rem;color:var(--slate-600);margin-top:4px;max-width:320px;line-height:1.3;">
                                                {{ $sollicitation->explication_ia }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px;">
                                        @if($sollicitation->distance_km !== null)
                                            <span style="font-weight:700;color:var(--slate-800);">{{ $sollicitation->distance_km }} km</span>
                                        @else
                                            <span style="color:var(--slate-400);">Non géolocalisé</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px;">
                                        @if($sollicitation->statut_reponse === 'accepte')
                                            <span class="badge-success">❤️ ACCEPTÉ</span>
                                        @elseif($sollicitation->statut_reponse === 'refuse')
                                            <span class="badge-danger">REFUSÉ</span>
                                        @else
                                            <span class="badge-warning">EN ATTENTE</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px;text-align:right;">
                                        <form method="POST" action="{{ route('demandes.notifier-donneur', [$demande->id, $donneur->id]) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm" title="Alerter ce donneur par SMS">
                                                📱 SMS Twilio
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding:40px;text-align:center;color:var(--slate-500);">
                    <p>Aucun donneur compatible enregistré pour le moment. Cliquez sur "Réactualiser Matching IA" pour relancer l'algorithme.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Journal des Alertes Twilio SMS Transmises -->
    <div class="card">
        <div class="card-header">
            <div class="card-title" style="font-size:1rem;">
                <span>📱</span>
                Historique des Notifications SMS (API Twilio)
            </div>
        </div>
        <div class="card-body" style="padding:0;">
            @if($demande->notifications->count() > 0)
                <div style="padding:12px 20px;">
                    @foreach($demande->notifications as $notif)
                        <div style="padding:12px 0;border-bottom:1px solid var(--slate-100);display:flex;justify-content:space-between;align-items:center;gap:12px;font-size:0.85rem;">
                            <div>
                                <span style="font-weight:700;color:var(--slate-900);">Vers {{ $notif->user->nom_complet ?? 'Destinataire' }} :</span>
                                <span style="color:var(--slate-700);margin-left:6px;">"{{ $notif->contenu }}"</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:10px;white-space:nowrap;">
                                <span class="badge-success">{{ $notif->statut }}</span>
                                <span style="color:var(--slate-400);font-size:0.75rem;">{{ $notif->created_at->format('H:i:s') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="padding:20px;text-align:center;color:var(--slate-400);font-size:0.85rem;">
                    Aucun SMS encore transmis pour cette demande.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
