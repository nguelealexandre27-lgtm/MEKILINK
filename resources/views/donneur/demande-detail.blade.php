@extends('layouts.app')

@section('title', 'Détails de la Sollicitation - MEKILINK')

@section('content')
<div class="container" style="max-width:800px;padding-top:20px;">
    <div style="margin-bottom:16px;">
        <a href="{{ route('donneur.dashboard') }}" class="btn btn-secondary btn-sm">
            &larr; Retour au tableau de bord donneur
        </a>
    </div>

    <div class="card" style="margin-bottom:24px;">
        <div class="card-header" style="background:var(--blood-light);border-bottom:1px solid #fecaca;">
            <div style="display:flex;align-items:center;gap:12px;">
                <span class="blood-badge filled lg">{{ $demande->groupe_sanguin_recherche }}</span>
                <div>
                    <h1 style="font-size:1.4rem;font-weight:800;color:var(--blood-dark);margin:0;">
                        Besoin Transfusionnel : {{ $demande->localisation }}
                    </h1>
                    <span class="{{ $demande->urgence_badge['class'] }}" style="margin-top:4px;">
                        {{ $demande->urgence_badge['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <div class="card-body" style="padding:28px;">
            <div class="grid-2" style="margin-bottom:24px;">
                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Demandeur</div>
                    <div style="font-size:1rem;font-weight:700;color:var(--slate-900);margin-top:2px;">
                        {{ $demande->demandeur->user->nom_complet ?? 'Établissement' }}
                    </div>
                    <div style="font-size:0.85rem;color:var(--slate-600);">
                        Type : {{ $demande->demandeur->type_demandeur }}
                    </div>
                </div>

                <div>
                    <div style="font-size:0.8rem;color:var(--slate-500);text-transform:uppercase;font-weight:700;">Quantité requise</div>
                    <div style="font-size:1.2rem;font-weight:800;color:var(--blood-primary);margin-top:2px;">
                        {{ $demande->quantite }} poche(s) de sang
                    </div>
                </div>
            </div>

            @if($demande->motif)
                <div style="background:var(--slate-50);padding:16px;border-radius:var(--radius-sm);margin-bottom:24px;border-left:4px solid var(--blood-primary);">
                    <div style="font-size:0.8rem;font-weight:700;color:var(--slate-700);text-transform:uppercase;margin-bottom:4px;">
                        Raison / Contexte Médical :
                    </div>
                    <p style="margin:0;font-size:0.95rem;color:var(--slate-800);line-height:1.5;">
                        {{ $demande->motif }}
                    </p>
                </div>
            @endif

            @if($demande->centreDon)
                <div style="border:1px solid var(--slate-200);border-radius:var(--radius-md);padding:18px;margin-bottom:24px;">
                    <h4 style="font-size:1rem;font-weight:700;color:var(--slate-900);margin:0 0 6px;">
                        🏥 Centre de prélèvement recommandé :
                    </h4>
                    <p style="margin:0;font-size:0.9rem;color:var(--slate-700);">
                        <strong>{{ $demande->centreDon->nom }}</strong><br>
                        📍 {{ $demande->centreDon->adresse }}, {{ $demande->centreDon->ville }}<br>
                        ⏰ Horaires : {{ $demande->centreDon->horaires }}<br>
                        📞 Contact : {{ $demande->centreDon->telephone }}
                    </p>
                </div>
            @endif

            @if($sollicitation && $sollicitation->explication_ia)
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;padding:16px;border-radius:var(--radius-sm);margin-bottom:24px;">
                    <div style="display:flex;align-items:center;gap:8px;font-weight:700;color:#166534;font-size:0.85rem;margin-bottom:4px;">
                        <span>🧠</span>
                        <span>Synthèse de l'Intelligence Artificielle (Score : {{ $sollicitation->score_compatibilite }}%) :</span>
                    </div>
                    <p style="margin:0;font-size:0.9rem;color:#14532d;">
                        {{ $sollicitation->explication_ia }}
                    </p>
                </div>
            @endif

            <!-- Choix d'action -->
            <div style="border-top:1px solid var(--slate-200);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
                <div>
                    Statut actuel :
                    @if($sollicitation && $sollicitation->statut_reponse === 'accepte')
                        <span class="badge-success">VOUS AVEZ CONFIRMÉ VOTRE DISPONIBILITÉ</span>
                    @elseif($sollicitation && $sollicitation->statut_reponse === 'refuse')
                        <span class="badge-danger">VOUS AVEZ DÉCLINÉ CETTE SOLLICITATION</span>
                    @else
                        <span class="badge-warning">EN ATTENTE DE VOTRE RÉPONSE</span>
                    @endif
                </div>

                <div style="display:flex;gap:12px;">
                    <form method="POST" action="{{ route('donneur.repondre', $demande->id) }}">
                        @csrf
                        <input type="hidden" name="reponse" value="refuse">
                        <button type="submit" class="btn btn-secondary" onclick="return confirm('Confirmez-vous le refus ? Le système transmettra l\'alerte au donneur suivant.');">
                            Je ne peux pas participer
                        </button>
                    </form>

                    <form method="POST" action="{{ route('donneur.repondre', $demande->id) }}">
                        @csrf
                        <input type="hidden" name="reponse" value="accepte">
                        <button type="submit" class="btn btn-vital btn-lg">
                            ❤️ J'Accepte de Sauver cette Vie
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
