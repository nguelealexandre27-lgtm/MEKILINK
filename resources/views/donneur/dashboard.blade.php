@extends('layouts.app')

@section('title', 'Espace Donneur - Sauver des Vies')

@section('content')
<div class="container" style="padding-top:20px;">
    <!-- En-tête Donneur -->
    <div class="page-header">
        <div>
            <div style="display:flex;align-items:center;gap:12px;">
                <span class="blood-badge filled lg">{{ $donneur->groupe_sanguin }}</span>
                <div>
                    <h1 class="page-title">{{ Auth::user()->nom_complet }}</h1>
                    <p class="page-subtitle">
                        Donneur de Sang Bénévole • {{ $donneur->localisation ?? 'Yaoundé' }}
                    </p>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px;align-items:center;">
            <a href="{{ route('donneur.historique') }}" class="btn btn-secondary">
                <span>📜</span>
                <span>Mon Historique ({{ $totalDons }} don(s))</span>
            </a>
            <a href="{{ route('dons.create') }}" class="btn btn-vital">
                <span>🩸</span>
                <span>Déclarer un Nouveau Don</span>
            </a>
        </div>
    </div>

    <!-- Toggle Tactile de Disponibilité (1-Clic Asynchrone) -->
    <div class="disponibilite-card {{ $donneur->disponibilite ? 'active' : '' }}" id="dispo-card" style="margin-bottom:28px;">
        <div class="disponibilite-info">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                <span style="font-size:1.4rem;">🔔</span>
                <h3 style="margin:0;">Disponibilité pour les Urgences Transfusionnelles</h3>
            </div>
            <p id="dispo-label" style="margin:0;">
                @if($donneur->disponibilite)
                    Vous êtes actuellement <strong>DISPONIBLE</strong>. Vous pouvez recevoir des alertes SMS Twilio lors d'urgences vitales compatibles.
                @else
                    Vous êtes actuellement <strong>INDISPONIBLE</strong>. Activez votre statut dès que vous êtes prêt à répondre à une alerte.
                @endif
            </p>
        </div>

        <div>
            <button type="button" 
                    id="btn-toggle-dispo"
                    data-url="{{ route('donneur.toggle-disponibilite') }}"
                    class="toggle-switch-btn {{ $donneur->disponibilite ? 'is-active' : 'is-inactive' }}">
                @if($donneur->disponibilite)
                    <span class="pulse-dot" style="background:#fff;"></span>
                    <span>DISPONIBLE</span>
                @else
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#64748b;"></span>
                    <span>INDISPONIBLE</span>
                @endif
            </button>
        </div>
    </div>

    <div class="grid-2-1">
        <!-- Colonne Principale : Sollicitations & Demandes Urgentes -->
        <div>
            <!-- Sollicitations Ciblées par l'IA MEKILINK -->
            <div class="card" style="margin-bottom:28px;">
                <div class="card-header">
                    <div class="card-title">
                        <span class="pulse-dot"></span>
                        Sollicitations Prioritaires Ciblées par l'IA ({{ $sollicitations->count() }})
                    </div>
                    <span class="badge-vitale">ACTION REQUISE</span>
                </div>

                <div class="card-body" style="padding:0;">
                    @forelse($sollicitations as $s)
                        @php $demande = $s->demandeSang; @endphp
                        <div style="padding:20px;border-bottom:1px solid var(--slate-200);display:flex;flex-direction:column;gap:12px;background:{{ $s->statut_reponse === 'en_attente' ? '#fffaf8' : '#ffffff' }};">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <span class="blood-badge filled lg">{{ $demande->groupe_sanguin_recherche }}</span>
                                    <div>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <h4 style="margin:0;font-size:1.05rem;font-weight:700;color:var(--slate-900);">
                                                {{ $demande->localisation }}
                                            </h4>
                                            <span class="{{ $demande->urgence_badge['class'] }}">
                                                {{ $demande->urgence_badge['label'] }}
                                            </span>
                                        </div>
                                        <p style="font-size:0.85rem;color:var(--slate-600);margin:4px 0 0;">
                                            Demandeur : <strong>{{ $demande->demandeur->user->nom_complet ?? 'Établissement' }}</strong> • Besoin de {{ $demande->quantite }} poche(s)
                                        </p>
                                    </div>
                                </div>

                                <div style="text-align:right;">
                                    <span class="ai-score-pill {{ $s->score_compatibilite >= 85 ? 'ai-score-high' : 'ai-score-medium' }}">
                                        ⚡ Match IA : {{ $s->score_compatibilite }}%
                                    </span>
                                    @if($s->distance_km !== null)
                                        <div style="font-size:0.75rem;color:var(--slate-500);margin-top:4px;">
                                            📍 À environ {{ $s->distance_km }} km
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if($s->explication_ia)
                                <div style="font-size:0.82rem;background:var(--slate-50);padding:10px 14px;border-radius:var(--radius-sm);color:var(--slate-700);border-left:3px solid var(--blood-primary);">
                                    <strong>Analyse IA :</strong> {{ $s->explication_ia }}
                                </div>
                            @endif

                            @if($demande->motif)
                                <p style="font-size:0.88rem;color:var(--slate-700);margin:0;">
                                    <strong>Motif médical :</strong> {{ $demande->motif }}
                                </p>
                            @endif

                            <!-- Boutons de Réponse Rapide -->
                            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;padding-top:8px;">
                                <div>
                                    Statut : 
                                    @if($s->statut_reponse === 'accepte')
                                        <span class="badge-success">VOUS AVEZ ACCEPTÉ</span>
                                    @elseif($s->statut_reponse === 'refuse')
                                        <span class="badge-danger">VOUS AVEZ DÉCLINÉ</span>
                                    @else
                                        <span class="badge-warning">EN ATTENTE DE VOTRE RÉPONSE</span>
                                    @endif
                                </div>

                                @if($s->statut_reponse === 'en_attente')
                                    <div style="display:flex;gap:10px;">
                                        <form method="POST" action="{{ route('donneur.repondre', $demande->id) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="reponse" value="refuse">
                                            <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Confirmez-vous le refus ? Le système transmettra l\'alerte au donneur compatible suivant.');">
                                                Je ne peux pas
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('donneur.repondre', $demande->id) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="reponse" value="accepte">
                                            <button type="submit" class="btn btn-vital btn-sm">
                                                ❤️ J'Accepte de Donner
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a href="{{ route('donneur.demande', $demande->id) }}" class="btn btn-secondary btn-sm">
                                        Voir les détails de la demande
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="padding:40px;text-align:center;color:var(--slate-500);">
                            <div style="font-size:2.5rem;margin-bottom:12px;">🩺</div>
                            <h4 style="color:var(--slate-800);margin-bottom:6px;">Aucune sollicitation directe en attente</h4>
                            <p style="font-size:0.88rem;max-width:420px;margin:0 auto;">
                                Dès qu'une demande urgente correspondant à votre groupe sanguin ({{ $donneur->groupe_sanguin }}) sera créée dans votre secteur, l'IA vous alertera automatiquement par SMS.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Autres Urgences Publiques de la Région -->
            @if($urgencesPubliques->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <span>🚨</span>
                            Autres Urgences Vitales Compatibles avec votre Sang
                        </div>
                    </div>
                    <div class="card-body" style="padding:0;">
                        @foreach($urgencesPubliques as $u)
                            <div style="padding:14px 20px;border-bottom:1px solid var(--slate-200);display:flex;align-items:center;justify-content:space-between;gap:12px;">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span class="blood-badge sm filled">{{ $u->groupe_sanguin_recherche }}</span>
                                    <div>
                                        <div style="font-weight:700;font-size:0.9rem;color:var(--slate-900);">{{ $u->localisation }}</div>
                                        <div style="font-size:0.75rem;color:var(--slate-500);">{{ $u->quantite }} poche(s) demandée(s)</div>
                                    </div>
                                </div>
                                <a href="{{ route('demandes.show', $u->id) }}" class="btn btn-secondary btn-sm">Consulter</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Colonne Latérale : Éligibilité & Centres -->
        <div>
            <!-- Carte Éligibilité Médicale -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <div class="card-title" style="font-size:1rem;">
                        <span>🩺</span>
                        Statut Médical & Éligibilité
                    </div>
                </div>
                <div class="card-body">
                    @if($donneur->est_eligible)
                        <div style="background:#ecfdf5;border:1px solid #a7f3d0;padding:14px;border-radius:var(--radius-sm);margin-bottom:14px;display:flex;align-items:center;gap:10px;">
                            <span style="font-size:1.5rem;">✅</span>
                            <div>
                                <strong style="color:#065f46;font-size:0.92rem;display:block;">Vous êtes éligible au don !</strong>
                                <span style="font-size:0.78rem;color:#047857;">Délai de sécurité de 56 jours respecté.</span>
                            </div>
                        </div>
                    @else
                        <div style="background:#fff7ed;border:1px solid #ffedd5;padding:14px;border-radius:var(--radius-sm);margin-bottom:14px;display:flex;align-items:center;gap:10px;">
                            <span style="font-size:1.5rem;">⏳</span>
                            <div>
                                <strong style="color:#9a3412;font-size:0.92rem;display:block;">Période de repos physiologique</strong>
                                <span style="font-size:0.78rem;color:#c2410c;">
                                    Prochain don possible dans {{ $donneur->jours_avant_prochain_don }} jour(s).
                                </span>
                            </div>
                        </div>
                    @endif

                    <ul style="list-style:none;font-size:0.85rem;line-height:1.9;color:var(--slate-700);">
                        <li><strong>Groupe :</strong> {{ $donneur->groupe_sanguin }}</li>
                        <li><strong>Dernier don :</strong> {{ $donneur->date_dernier_don ? $donneur->date_dernier_don->format('d/m/Y') : 'Aucun don enregistré' }}</li>
                        <li><strong>Prochain don possible :</strong> {{ $donneur->date_prochain_don_possible ? $donneur->date_prochain_don_possible->format('d/m/Y') : 'Immédiat' }}</li>
                        <li><strong>Total de dons :</strong> {{ $totalDons }} poche(s) ({{ $totalDons * 3 }} vies potentielles sauvées)</li>
                    </ul>
                </div>
            </div>

            <!-- Centres Proches -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title" style="font-size:1rem;">
                        <span>🏥</span>
                        Centres de Collecte Proches
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    @foreach($centres as $c)
                        <div style="padding:14px 18px;border-bottom:1px solid var(--slate-100);">
                            <h5 style="margin:0 0 4px;font-size:0.88rem;color:var(--slate-900);font-weight:700;">{{ $c->nom }}</h5>
                            <p style="margin:0;font-size:0.78rem;color:var(--slate-500);">📍 {{ $c->adresse }}</p>
                            <a href="{{ route('centres.show', $c->id) }}" style="font-size:0.75rem;color:var(--blood-primary);font-weight:700;display:inline-block;margin-top:6px;">
                                Itinéraire & Horaires &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
