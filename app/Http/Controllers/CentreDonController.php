<?php

namespace App\Http\Controllers;

use App\Models\CentreDon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CentreDonController extends Controller
{
    public function index(): View
    {
        $centres = CentreDon::withCount('dons')->orderBy('nom')->get();
        return view('centres.index', compact('centres'));
    }

    public function show(int $id): View
    {
        $centre = CentreDon::with(['dons.donneur.user'])->findOrFail($id);
        return view('centres.show', compact('centre'));
    }

    public function geojson(): JsonResponse
    {
        $centres = CentreDon::all()->map(function ($centre) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$centre->longitude, $centre->latitude],
                ],
                'properties' => [
                    'id' => $centre->id,
                    'nom' => $centre->nom,
                    'adresse' => $centre->adresse,
                    'ville' => $centre->ville,
                    'horaires' => $centre->horaires,
                    'telephone' => $centre->telephone,
                    'url' => route('centres.show', $centre->id),
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $centres,
        ]);
    }
}
