@extends('layouts.app')

@section('title', $centre->nom . ' - Fiche Centre de Don')

@section('content')
<div class="container" style="max-width:900px;padding-top:20px;">
    <div style="margin-bottom:16px;">
        <a href="{{ route('centres.index') }}" class="btn btn-secondary btn-sm">
            &larr; Retour à la liste des centres
        </a>
    </div>

    <div class="card" style="margin-bottom:28px;">
        <div class="card-header" style="background:#f8fafc;padding:24px;">
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="width:52px;height:52px;border-radius:12px;background:var(--blood-light);color:var(--blood-primary);display:flex;align-items:center;justify-content:center;font-size:1.6rem;">
                    🏥
                </div>
                <div>
                    <h1 style="font-size:1.5rem;font-weight:800;color:var(--slate-900);margin:0;">
                        {{ $centre->nom }}
                    </h1>
                    <p style="font-size:0.88rem;color:var(--slate-600);margin:2px 0 0;">
                        {{ $centre->ville }} • Point de collecte et banque de sang agréée
                    </p>
                </div>
            </div>
        </div>

        <div class="card-body" style="padding:28px;">
            <div class="grid-2" style="margin-bottom:24px;">
                <div>
                    <h4 style="font-size:0.95rem;font-weight:700;color:var(--slate-900);margin-bottom:10px;">Coordonnées & Accès</h4>
                    <p style="font-size:0.9rem;color:var(--slate-700);line-height:1.7;margin:0;">
                        📍 <strong>Adresse physique :</strong> {{ $centre->adresse }}<br>
                        🌍 <strong>Ville / Région :</strong> {{ $centre->ville }}, Cameroun<br>
                        ⏰ <strong>Horaires de prélèvement :</strong> {{ $centre->horaires }}<br>
                        📞 <strong>Contact téléphonique :</strong> {{ $centre->telephone ?? 'Standard central' }}<br>
                        ✉️ <strong>Email médical :</strong> {{ $centre->email ?? 'transfusion@mekilink.org' }}
                    </p>
                </div>

                <div>
                    <h4 style="font-size:0.95rem;font-weight:700;color:var(--slate-900);margin-bottom:10px;">Localisation GPS</h4>
                    <p style="font-size:0.85rem;color:var(--slate-600);margin-bottom:12px;">
                        Latitude : {{ $centre->latitude }} | Longitude : {{ $centre->longitude }}
                    </p>
                    <a href="https://www.openstreetmap.org/?mlat={{ $centre->latitude }}&mlon={{ $centre->longitude }}#map=17/{{ $centre->latitude }}/{{ $centre->longitude }}" 
                       target="_blank" 
                       class="btn btn-vital btn-sm" style="width:100%;">
                        Ouvrir l'Itinéraire sur OpenStreetMap ↗
                    </a>
                </div>
            </div>

            <!-- Mini carte de localisation -->
            <div id="osm-map" style="height:280px;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--slate-200);margin-bottom:24px;" 
                 data-lat="{{ $centre->latitude }}" data-lng="{{ $centre->longitude }}" data-zoom="15">
            </div>

            <script id="centres-data" type="application/json">
                {!! json_encode([$centre]) !!}
            </script>

            <h3 style="font-size:1.15rem;font-weight:700;color:var(--slate-900);margin-bottom:14px;">
                Derniers Dons Effectués à ce Centre ({{ $centre->dons->count() }})
            </h3>

            @if($centre->dons->count() > 0)
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.88rem;">
                        <thead>
                            <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);">
                                <th style="padding:10px 14px;">Réf Don</th>
                                <th style="padding:10px 14px;">Date</th>
                                <th style="padding:10px 14px;">Groupe</th>
                                <th style="padding:10px 14px;">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($centre->dons->take(5) as $d)
                                <tr style="border-bottom:1px solid var(--slate-100);">
                                    <td style="padding:10px 14px;font-weight:700;">#DON-{{ $d->id }}</td>
                                    <td style="padding:10px 14px;">{{ $d->date_don->format('d/m/Y') }}</td>
                                    <td style="padding:10px 14px;"><span class="blood-badge sm filled">{{ $d->groupe_sanguin }}</span></td>
                                    <td style="padding:10px 14px;"><span class="badge-success">{{ ucfirst($d->statut) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="font-size:0.88rem;color:var(--slate-500);margin:0;">
                    Aucun don historique enregistré à ce jour dans ce centre.
                </p>
            @endif
        </div>
    </div>
</div>
@endsection
