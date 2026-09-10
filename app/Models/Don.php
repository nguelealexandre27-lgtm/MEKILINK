<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modèle Don correspondant à la classe Don du diagramme UML.
 */
class Don extends Model
{
    use HasFactory;

    protected $table = 'dons';

    protected $fillable = [
        'donneur_id',
        'demande_sang_id',
        'centre_don_id',
        'date_don',
        'groupe_sanguin',
        'statut', // planifie, effectue, valide, rejete
        'quantite_ml',
    ];

    protected function casts(): array
    {
        return [
            'date_don' => 'datetime',
            'quantite_ml' => 'integer',
        ];
    }

    public function donneur(): BelongsTo
    {
        return $this->belongsTo(Donneur::class);
    }

    public function demandeSang(): BelongsTo
    {
        return $this->belongsTo(DemandeSang::class);
    }

    public function centreDon(): BelongsTo
    {
        return $this->belongsTo(CentreDon::class);
    }

    public function rapportMedical(): HasOne
    {
        return $this->hasOne(RapportMedical::class);
    }

    // UML Operation
    public static function enregistrer(array $data): self
    {
        $don = self::create($data);

        // Mettre à jour la date de dernier don du donneur si effectué
        if ($don->statut === 'effectue' || $don->statut === 'valide') {
            $donneur = $don->donneur;
            if ($donneur) {
                $donneur->date_dernier_don = $don->date_don->toDateString();
                $donneur->disponibilite = false; // indisponible temporairement pendant 56 jours
                $donneur->save();
            }
        }

        return $don;
    }
}
