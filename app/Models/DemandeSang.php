<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle DemandeSang correspondant à la classe DemandeSang du diagramme UML.
 */
class DemandeSang extends Model
{
    use HasFactory;

    protected $table = 'demandes_sang';

    protected $fillable = [
        'demandeur_id',
        'centre_don_id',
        'groupe_sanguin_recherche',
        'quantite',
        'urgence', // vitale, urgente, moyenne, faible
        'statut',  // en_attente, en_cours, satisfaite, annulee, expiree
        'localisation',
        'latitude',
        'longitude',
        'motif',
        'date_besoin',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'date_besoin' => 'datetime',
        ];
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(Demandeur::class);
    }

    public function centreDon(): BelongsTo
    {
        return $this->belongsTo(CentreDon::class);
    }

    public function sollicitations(): HasMany
    {
        return $this->hasMany(DemandeDonneur::class)->orderBy('score_compatibilite', 'desc');
    }

    public function donneurs(): BelongsToMany
    {
        return $this->belongsToMany(Donneur::class, 'demande_donneurs')
            ->withPivot(['id', 'score_compatibilite', 'distance_km', 'statut_reponse', 'date_reponse', 'explication_ia'])
            ->withTimestamps();
    }

    public function dons(): HasMany
    {
        return $this->hasMany(Don::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Urgence Badge Info
    public function getUrgenceBadgeAttribute(): array
    {
        return match ($this->urgence) {
            'vitale' => ['label' => 'Vitale Immédiate', 'class' => 'badge-vitale', 'pulse' => true],
            'urgente' => ['label' => 'Urgente (< 6h)', 'class' => 'badge-urgente', 'pulse' => false],
            'moyenne' => ['label' => 'Moyenne (< 24h)', 'class' => 'badge-moyenne', 'pulse' => false],
            default => ['label' => 'Programmée', 'class' => 'badge-faible', 'pulse' => false],
        };
    }

    // Statut Badge Info
    public function getStatutBadgeAttribute(): array
    {
        return match ($this->statut) {
            'en_attente' => ['label' => 'En attente', 'class' => 'badge-warning'],
            'en_cours' => ['label' => 'Donneur trouvé / En cours', 'class' => 'badge-info'],
            'satisfaite' => ['label' => 'Satisfaite', 'class' => 'badge-success'],
            'annulee' => ['label' => 'Annulée', 'class' => 'badge-danger'],
            default => ['label' => ucfirst($this->statut), 'class' => 'badge-secondary'],
        };
    }

    // UML Operations
    public static function creer(array $data): self
    {
        return self::create($data);
    }

    public function annuler(): bool
    {
        $this->statut = 'annulee';
        return $this->save();
    }

    public function mettreAJourStatut(string $statut): bool
    {
        $this->statut = $statut;
        return $this->save();
    }
}
