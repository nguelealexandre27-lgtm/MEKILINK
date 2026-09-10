@extends('layouts.app')

@section('title', 'Validation des Rapports Médicaux - MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Validation des Rapports Médicaux</h1>
            <p class="page-subtitle">Contrôle biologique post-don, vérification sérologique et intégration aux réserves transfusionnelles.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            &larr; Retour à la supervision
        </a>
    </div>

    <!-- Section 1 : Rapports en Attente Prioritaire -->
    <div class="card" style="margin-bottom:30px;border-top:4px solid var(--blood-arterial);">
        <div class="card-header" style="background:#fffaf8;">
            <div class="card-title">
                <span class="pulse-dot"></span>
                Rapports en Attente de Validation Médicale Administrative ({{ $rapportsEnAttente->count() }})
            </div>
            <span class="badge-vitale">CONTRÔLE DE SÉCURITÉ REQUIS</span>
        </div>

        <div class="card-body" style="padding:0;">
            @if($rapportsEnAttente->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.92rem;">
                        <thead>
                            <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                                <th style="padding:14px 20px;">Réf Rapport</th>
                                <th style="padding:14px 20px;">Donneur</th>
                                <th style="padding:14px 20px;">Groupe Sanguin</th>
                                <th style="padding:14px 20px;">Centre de Prélèvement</th>
                                <th style="padding:14px 20px;">Date Prélèvement</th>
                                <th style="padding:14px 20px;">Sérologie</th>
                                <th style="padding:14px 20px;text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rapportsEnAttente as $r)
                                <tr style="border-bottom:1px solid var(--slate-100);background:#fffdfc;">
                                    <td style="padding:14px 20px;font-weight:700;font-family:var(--font-mono);">
                                        #RAP-{{ $r->id }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <div style="font-weight:700;color:var(--slate-900);">
                                            {{ $r->don->donneur->user->nom_complet ?? 'Donneur' }}
                                        </div>
                                        <div style="font-size:0.75rem;color:var(--slate-500);">
                                            Poche de {{ $r->don->quantite_ml }} mL
                                        </div>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span class="blood-badge sm filled">{{ $r->don->groupe_sanguin }}</span>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        {{ $r->don->centreDon->nom ?? 'Centre de collecte' }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        {{ $r->don->date_don->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span class="badge-success">Tests négatifs</span>
                                    </td>
                                    <td style="padding:14px 20px;text-align:right;">
                                        <a href="{{ route('rapports.show', $r->id) }}" class="btn btn-vital btn-sm">
                                            Valider le Rapport &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding:36px;text-align:center;color:var(--slate-500);">
                    <p style="margin:0;">✅ Tous les rapports médicaux soumis ont été examinés et validés.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Section 2 : Historique des Rapports Déjà Traités -->
    <div class="card">
        <div class="card-header">
            <div class="card-title" style="font-size:1rem;">
                <span>📋</span>
                Historique des Rapports Validés ou Rejetés
            </div>
        </div>

        <div class="card-body" style="padding:0;">
            @if($rapportsTraites->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.9rem;">
                        <thead>
                            <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);font-size:0.78rem;text-transform:uppercase;">
                                <th style="padding:12px 20px;">Réf</th>
                                <th style="padding:12px 20px;">Donneur</th>
                                <th style="padding:12px 20px;">Groupe</th>
                                <th style="padding:12px 20px;">Date Validation</th>
                                <th style="padding:12px 20px;">Décision</th>
                                <th style="padding:12px 20px;">Validateur</th>
                                <th style="padding:12px 20px;text-align:right;">Consulter</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rapportsTraites as $rt)
                                <tr style="border-bottom:1px solid var(--slate-100);">
                                    <td style="padding:12px 20px;font-family:var(--font-mono);font-weight:700;">#RAP-{{ $rt->id }}</td>
                                    <td style="padding:12px 20px;">{{ $rt->don->donneur->user->nom_complet ?? 'Donneur' }}</td>
                                    <td style="padding:12px 20px;"><span class="blood-badge sm filled">{{ $rt->don->groupe_sanguin }}</span></td>
                                    <td style="padding:12px 20px;">{{ $rt->date_valorisation ? $rt->date_valorisation->format('d/m/Y') : '-' }}</td>
                                    <td style="padding:12px 20px;">
                                        @if($rt->statut === 'valide')
                                            <span class="badge-success">Conforme (Apte)</span>
                                        @else
                                            <span class="badge-danger">Rejeté</span>
                                        @endif
                                    </td>
                                    <td style="padding:12px 20px;color:var(--slate-600);">
                                        {{ $rt->administrateur->nom_complet ?? 'Admin' }}
                                    </td>
                                    <td style="padding:12px 20px;text-align:right;">
                                        <a href="{{ route('rapports.show', $rt->id) }}" class="btn btn-secondary btn-sm" style="font-size:0.75rem;">
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding:16px 20px;">
                    {{ $rapportsTraites->links() }}
                </div>
            @else
                <div style="padding:30px;text-align:center;color:var(--slate-400);">Aucun historique de rapport.</div>
            @endif
        </div>
    </div>
</div>
@endsection
