<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Donneur correspondant à la classe Donneur du diagramme UML.
 * Spécialisation / extension d'Utilisateur.
 */
class Donneur extends Model
{
    use HasFactory;

    protected $table = 'donneurs';

    protected $fillable = [
        'user_id',
        'groupe_sanguin',
        'disponibilite',
        'date_dernier_don',
        'localisation',
        'latitude',
        'longitude',
        'centre_don_id',
    ];

    protected function casts(): array
    {
        return [
            'disponibilite' => 'boolean',
            'date_dernier_don' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function centreDon(): BelongsTo
    {
        return $this->belongsTo(CentreDon::class);
    }

    public function dons(): HasMany
    {
        return $this->hasMany(Don::class)->orderBy('date_don', 'desc');
    }

    public function sollicitations(): HasMany
    {
        return $this->hasMany(DemandeDonneur::class)->orderBy('created_at', 'desc');
    }

    public function demandesSang(): BelongsToMany
    {
        return $this->belongsToMany(DemandeSang::class, 'demande_donneurs')
            ->withPivot(['id', 'score_compatibilite', 'distance_km', 'statut_reponse', 'date_reponse', 'explication_ia'])
            ->withTimestamps();
    }

    // Éligibilité médicale basée sur le délai de 56 jours (standard OMS/Transfusion)
    public function getEstEligibleAttribute(): bool
    {
        if (!$this->disponibilite) {
            return false;
        }
        if (!$this->date_dernier_don) {
            return true;
        }
        $delai = config('mekilink.delai_don_jours', 56);
        return Carbon::parse($this->date_dernier_don)->diffInDays(now()) >= $delai;
    }

    public function getJoursAvantProchainDonAttribute(): int
    {
        if (!$this->date_dernier_don) {
            return 0;
        }
        $delai = config('mekilink.delai_don_jours', 56);
        $joursEcoules = Carbon::parse($this->date_dernier_don)->diffInDays(now());
        return max(0, $delai - $joursEcoules);
    }

    public function getDateProchainDonPossibleAttribute(): ?Carbon
    {
        if (!$this->date_dernier_don) {
            return now();
        }
        $delai = config('mekilink.delai_don_jours', 56);
        return Carbon::parse($this->date_dernier_don)->addDays($delai);
    }

    // UML Operations
    public function mettreAJourDisponibilite(bool $dispo): bool
    {
        $this->disponibilite = $dispo;
        return $this->save();
    }

    public function consulterHistoriqueDons()
    {
        return $this->dons()->with(['centreDon', 'rapportMedical'])->get();
    }

    public function repondreADemande(int $demandeId, string $reponse): bool
    {
        $pivot = DemandeDonneur::where('demande_sang_id', $demandeId)
            ->where('donneur_id', $this->id)
            ->first();

        if ($pivot) {
            $pivot->statut_reponse = $reponse; // 'accepte', 'refuse'
            $pivot->date_reponse = now();
            $saved = $pivot->save();

            // Si accepté, mettre à jour le statut de la demande en 'en_cours'
            if ($saved && $reponse === 'accepte') {
                $demande = DemandeSang::find($demandeId);
                if ($demande && $demande->statut === 'en_attente') {
                    $demande->statut = 'en_cours';
                    $demande->save();
                }
            }

            return $saved;
        }

        return false;
    }
}
