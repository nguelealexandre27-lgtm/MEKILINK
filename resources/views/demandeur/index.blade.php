@extends('layouts.app')

@section('title', 'Mes Demandes de Sang - MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Mes Demandes Transfusionnelles</h1>
            <p class="page-subtitle">Suivi en temps réel des besoins de sang et des donneurs identifiés par l'IA.</p>
        </div>
        <a href="{{ route('demandes.create') }}" class="btn btn-vital btn-lg">
            <span>🚨</span>
            <span>Exprimer un Nouveau Besoin</span>
        </a>
    </div>

    <div class="card">
        <div class="card-body" style="padding:0;">
            @if($demandes->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.92rem;">
                        <thead>
                            <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                                <th style="padding:14px 20px;">Groupe Requis</th>
                                <th style="padding:14px 20px;">Établissement / Lieu</th>
                                <th style="padding:14px 20px;">Urgence</th>
                                <th style="padding:14px 20px;">Quantité</th>
                                <th style="padding:14px 20px;">Statut</th>
                                <th style="padding:14px 20px;">Donneurs IA Identifiés</th>
                                <th style="padding:14px 20px;text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demandes as $d)
                                <tr style="border-bottom:1px solid var(--slate-100);">
                                    <td style="padding:14px 20px;">
                                        <span class="blood-badge filled">{{ $d->groupe_sanguin_recherche }}</span>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <div style="font-weight:700;color:var(--slate-900);">{{ $d->localisation }}</div>
                                        <div style="font-size:0.75rem;color:var(--slate-500);">Créé {{ $d->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span class="{{ $d->urgence_badge['class'] }}">
                                            {{ $d->urgence_badge['label'] }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 20px;font-weight:700;">
                                        {{ $d->quantite }} poche(s)
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span class="{{ $d->statut_badge['class'] }}">
                                            {{ $d->statut_badge['label'] }}
                                        </span>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span style="font-weight:700;color:var(--blood-primary);">
                                            {{ $d->sollicitations->count() }} donneur(s)
                                        </span>
                                        @php
                                            $acceptes = $d->sollicitations->where('statut_reponse', 'accepte')->count();
                                        @endphp
                                        @if($acceptes > 0)
                                            <span class="badge-success" style="font-size:0.7rem;margin-left:6px;">
                                                {{ $acceptes }} confirmé(s) !
                                            </span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px;text-align:right;">
                                        <a href="{{ route('demandes.show', $d->id) }}" class="btn btn-secondary btn-sm">
                                            Voir Matching IA &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding:16px 20px;">
                    {{ $demandes->links() }}
                </div>
            @else
                <div style="padding:50px;text-align:center;color:var(--slate-500);">
                    <div style="font-size:3rem;margin-bottom:14px;">🏥</div>
                    <h3 style="color:var(--slate-800);margin-bottom:6px;">Aucune demande enregistrée</h3>
                    <p style="font-size:0.9rem;max-width:420px;margin:0 auto 20px;">
                        En cas d'urgence ou d'intervention programmée, créez votre demande de sang. L'IA sélectionnera les donneurs les plus compatibles en quelques secondes.
                    </p>
                    <a href="{{ route('demandes.create') }}" class="btn btn-vital">
                        Créer une Demande de Sang
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
