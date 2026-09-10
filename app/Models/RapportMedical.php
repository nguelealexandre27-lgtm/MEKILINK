<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle RapportMedical correspondant à la classe RapportMedical du diagramme UML.
 */
class RapportMedical extends Model
{
    use HasFactory;

    protected $table = 'rapports_medicaux';

    protected $fillable = [
        'don_id',
        'administrateur_id',
        'date_valorisation',
        'resultat', // apte, inapte, conforme, non_conforme
        'taux_hemoglobine',
        'serologie_conforme',
        'commentaire',
        'statut', // en_attente, valide, rejete
    ];

    protected function casts(): array
    {
        return [
            'date_valorisation' => 'date',
            'serologie_conforme' => 'boolean',
        ];
    }

    public function don(): BelongsTo
    {
        return $this->belongsTo(Don::class);
    }

    public function administrateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrateur_id');
    }

    // UML Operation: valider
    public function valider(int $adminId, string $resultat, ?string $commentaire = null, ?string $hemoglobine = null): bool
    {
        $this->administrateur_id = $adminId;
        $this->date_valorisation = now();
        $this->resultat = $resultat;
        $this->commentaire = $commentaire;
        if ($hemoglobine) {
            $this->taux_hemoglobine = $hemoglobine;
        }
        $this->statut = ($resultat === 'apte' || $resultat === 'conforme') ? 'valide' : 'rejete';
        
        $saved = $this->save();

        if ($saved && $this->don) {
            $this->don->statut = ($this->statut === 'valide') ? 'valide' : 'rejete';
            $this->don->save();
        }

        return $saved;
    }
}
