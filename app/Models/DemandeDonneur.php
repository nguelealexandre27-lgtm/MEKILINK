<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle Pivot / Sollicitation reliant DemandeSang et Donneur.
 * Stocke le score calculé par l'IA et le statut de réponse.
 */
class DemandeDonneur extends Model
{
    use HasFactory;

    protected $table = 'demande_donneurs';

    protected $fillable = [
        'demande_sang_id',
        'donneur_id',
        'score_compatibilite',
        'distance_km',
        'statut_reponse', // en_attente, accepte, refuse, expire
        'date_reponse',
        'explication_ia',
    ];

    protected function casts(): array
    {
        return [
            'score_compatibilite' => 'integer',
            'distance_km' => 'float',
            'date_reponse' => 'datetime',
        ];
    }

    public function demandeSang(): BelongsTo
    {
        return $this->belongsTo(DemandeSang::class);
    }

    public function donneur(): BelongsTo
    {
        return $this->belongsTo(Donneur::class);
    }
}
