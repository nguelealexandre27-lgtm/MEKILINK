<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle CentreDon correspondant à la classe CentreDon du diagramme UML.
 */
class CentreDon extends Model
{
    use HasFactory;

    protected $table = 'centres_don';

    protected $fillable = [
        'nom',
        'adresse',
        'ville',
        'latitude',
        'longitude',
        'horaires',
        'telephone',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function dons(): HasMany
    {
        return $this->hasMany(Don::class);
    }

    public function demandesSang(): HasMany
    {
        return $this->hasMany(DemandeSang::class);
    }

    public function donneurs(): HasMany
    {
        return $this->hasMany(Donneur::class);
    }

    // UML Operation: localiser
    public function localiser(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'horaires' => $this->horaires,
            'telephone' => $this->telephone,
            'osm_url' => "https://www.openstreetmap.org/?mlat={$this->latitude}&mlon={$this->longitude}#map=16/{$this->latitude}/{$this->longitude}",
        ];
    }
}
