<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Demandeur correspondant à la classe Demandeur du diagramme UML.
 * Spécialisation / extension d'Utilisateur.
 */
class Demandeur extends Model
{
    use HasFactory;

    protected $table = 'demandeurs';

    protected $fillable = [
        'user_id',
        'type_demandeur', // Particulier, Hôpital, Clinique, Banque de sang
        'nom_etablissement',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function demandes(): HasMany
    {
        return $this->hasMany(DemandeSang::class)->orderBy('created_at', 'desc');
    }

    // UML Operations
    public function creerDemandeSang(array $data): DemandeSang
    {
        return $this->demandes()->create($data);
    }

    public function annulerDemande(int $demandeId): bool
    {
        $demande = $this->demandes()->where('id', $demandeId)->first();
        if ($demande) {
            return $demande->annuler();
        }
        return false;
    }

    public function suivreStatutDemande(int $demandeId)
    {
        return $this->demandes()
            ->with(['centreDon', 'donneurs', 'sollicitations.donneur.user'])
            ->find($demandeId);
    }
}
