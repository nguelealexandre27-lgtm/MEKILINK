@extends('layouts.app')

@section('title', 'Mon Historique de Dons - MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Mon Historique de Dons</h1>
            <p class="page-subtitle">Retrouvez l'ensemble de vos dons enregistrés et vos validations médicales.</p>
        </div>
        <a href="{{ route('dons.create') }}" class="btn btn-vital">
            <span>🩸</span>
            <span>Déclarer un Don Effectué</span>
        </a>
    </div>

    <div class="card">
        <div class="card-body" style="padding:0;">
            @if($dons->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.92rem;">
                        <thead>
                            <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                                <th style="padding:14px 20px;">Réf Don</th>
                                <th style="padding:14px 20px;">Date</th>
                                <th style="padding:14px 20px;">Groupe</th>
                                <th style="padding:14px 20px;">Volume</th>
                                <th style="padding:14px 20px;">Centre de Collecte</th>
                                <th style="padding:14px 20px;">Statut Don</th>
                                <th style="padding:14px 20px;">Rapport Médical</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dons as $don)
                                <tr style="border-bottom:1px solid var(--slate-100);">
                                    <td style="padding:14px 20px;font-weight:700;font-family:var(--font-mono);">
                                        #DON-{{ $don->id }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        {{ $don->date_don->format('d/m/Y') }}
                                    </td>
                                    <td style="padding:14px 20px;">
                                        <span class="blood-badge sm filled">{{ $don->groupe_sanguin }}</span>
                                    </td>
                                    <td style="padding:14px 20px;">
                                        {{ $don->quantite_ml }} mL
                                    </td>
                                    <td style="padding:14px 20px;">
                                        {{ $don->centreDon->nom ?? 'Centre non précisé' }}
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
                                            <span class="badge-success">Conforme (Sérologie OK)</span>
                                        @else
                                            <span class="badge-warning">En cours d'analyse</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding:50px;text-align:center;color:var(--slate-500);">
                    <div style="font-size:3rem;margin-bottom:14px;">🩸</div>
                    <h3 style="color:var(--slate-800);margin-bottom:6px;">Aucun don encore enregistré</h3>
                    <p style="font-size:0.9rem;max-width:400px;margin:0 auto 20px;">
                        Votre engagement commence ici. Chaque don permet de sauver jusqu'à 3 personnes hospitalisées.
                    </p>
                    <a href="{{ route('centres.index') }}" class="btn btn-secondary">
                        Trouver un centre de don à proximité
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
