@extends('layouts.app')

@section('title', 'Centres de Don de Sang & Banques de Sang - MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Centres de Transfusion & Banques de Sang</h1>
            <p class="page-subtitle">
                Cartographie interactive des points de prélèvement et de conservation du sang (OpenStreetMap).
            </p>
        </div>
        <a href="{{ route('dons.create') }}" class="btn btn-vital">
            <span>🩸</span>
            <span>Déclarer un Don Effectué</span>
        </a>
    </div>

    <!-- Carte Interactive OpenStreetMap (Leaflet.js) -->
    <div class="card" style="margin-bottom:28px;overflow:hidden;">
        <div class="card-header" style="background:#f8fafc;">
            <div class="card-title" style="font-size:1rem;">
                <span>🗺️</span>
                Localisation en Direct des Banques de Sang Référencées
            </div>
            <span class="badge-info">Tuiles Libres OpenStreetMap</span>
        </div>
        <div id="osm-map" style="height:420px;width:100%;" data-lat="3.8667" data-lng="11.5167" data-zoom="13"></div>
    </div>

    <!-- Données JSON des centres pour initialisation Leaflet par mekilink.js -->
    <script id="centres-data" type="application/json">
        {!! json_encode($centres) !!}
    </script>

    <!-- Liste des Établissements -->
    <h2 style="font-size:1.3rem;font-weight:800;color:var(--slate-900);margin-bottom:16px;">
        Établissements Référencés ({{ $centres->count() }})
    </h2>

    <div class="grid-3">
        @foreach($centres as $centre)
            <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--blood-light);color:var(--blood-primary);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                            🏥
                        </div>
                        <div>
                            <h3 style="font-size:1rem;font-weight:800;color:var(--slate-900);margin:0;line-height:1.2;">
                                {{ $centre->nom }}
                            </h3>
                            <span style="font-size:0.75rem;color:var(--slate-500);font-weight:600;">
                                {{ $centre->ville }}
                            </span>
                        </div>
                    </div>

                    <div style="font-size:0.85rem;color:var(--slate-700);line-height:1.6;margin-bottom:16px;">
                        <p style="margin:0 0 4px;">📍 <strong>Adresse :</strong> {{ $centre->adresse }}</p>
                        <p style="margin:0 0 4px;">⏰ <strong>Horaires :</strong> {{ $centre->horaires }}</p>
                        @if($centre->telephone)
                            <p style="margin:0 0 4px;">📞 <strong>Téléphone :</strong> {{ $centre->telephone }}</p>
                        @endif
                        <p style="margin:0;">🩸 <strong>Dons collectés :</strong> {{ $centre->dons_count }} don(s)</p>
                    </div>
                </div>

                <div class="card-footer" style="background:#fff;border-top:1px solid var(--slate-100);">
                    <a href="https://www.openstreetmap.org/?mlat={{ $centre->latitude }}&mlon={{ $centre->longitude }}#map=16/{{ $centre->latitude }}/{{ $centre->longitude }}" 
                       target="_blank" 
                       class="btn btn-secondary btn-sm" style="width:48%;">
                        Ouvrir OSM ↗
                    </a>
                    <a href="{{ route('centres.show', $centre->id) }}" class="btn btn-primary btn-sm" style="width:48%;">
                        Fiche Centre &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
