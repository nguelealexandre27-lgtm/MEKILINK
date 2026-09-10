@extends('layouts.app')

@section('title', 'Gestion des Dons de Sang - MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Registre des Dons de Sang</h1>
            <p class="page-subtitle">Suivi des prélèvements effectués, volumes collectés et rapports d'analyse associés.</p>
        </div>
        <a href="{{ route('dons.create') }}" class="btn btn-vital">
            <span>🩸</span>
            <span>Enregistrer un Nouveau Don</span>
        </a>
    </div>

    <div class="card">
        <div class="card-body" style="padding:0;">
            @if($dons->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.92rem;">
                        <thead>
                            <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                                <th style="padding:14px 20px;">Réf</th>
                                <th style="padding:14px 20px;">Donneur</th>
                                <th style="padding:14px 20px;">Groupe</th>
                                <th style="padding:14px 20px;">Centre de Collecte</th>
                                <th style="padding:14px 20px;">Date & Heure</th>
                                <th style="padding:14px 20px;">Statut Don</th>
                                <th style="padding:14px 20px;">Rapport Médical</th>
                                <th style="padding:14px 20px;text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dons as $don)
                                <tr style="border-bottom:1px solid var(--slate-100);">
                                    <td style="padding:14px 20px;font-weight:700;font-family:var(--font-mono);">
                                        #DON-{{ $don->id }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <div style="font-weight:700;color:var(--slate-900);">
                                            {{ $don->donneur->user->nom_complet ?? 'Donneur Anonyme' }}
                                        </div>
                                        <div style="font-size:0.75rem;color:var(--slate-500);">
                                            {{ $don->donneur->localisation ?? 'Yaoundé' }}
                                        </div>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span class="blood-badge sm filled">{{ $don->groupe_sanguin }}</span>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        {{ $don->centreDon->nom ?? 'Centre non précisé' }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        {{ $don->date_don->format('d/m/Y H:i') }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        @if($don->statut === 'valide')
                                            <span class="badge-success">Validé</span>
                                        @elseif($don->statut === 'effectue')
                                            <span class="badge-info">Effectué</span>
                                        @else
                                            <span class="badge-warning">{{ ucfirst($don->statut) }}</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px;">
                                        @if($don->rapportMedical)
                                            @if($don->rapportMedical->statut === 'valide')
                                                <span class="badge-success">Conforme (Apte)</span>
                                            @elseif($don->rapportMedical->statut === 'rejete')
                                                <span class="badge-danger">Rejeté</span>
                                            @else
                                                <span class="badge-warning">En attente validation</span>
                                            @endif
                                        @else
                                            <span style="color:var(--slate-400);font-size:0.8rem;">Non généré</span>
                                        @endif
                                    </td>
                                    <td style="padding:14px 20px;text-align:right;">
                                        <a href="{{ route('dons.show', $don->id) }}" class="btn btn-secondary btn-sm">
                                            Détails &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding:16px 20px;">
                    {{ $dons->links() }}
                </div>
            @else
                <div style="padding:50px;text-align:center;color:var(--slate-500);">
                    <p>Aucun don enregistré pour l'instant.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
