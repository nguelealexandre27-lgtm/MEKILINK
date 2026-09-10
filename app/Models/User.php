<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

/**
 * Modèle Utilisateur correspondant à la classe Utilisateur du diagramme UML.
 * Entité parente pour Donneur, Demandeur, Administrateur.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nom',
        'prenom',
        'age',
        'telephone',
        'email',
        'password',
        'role',   // donneur, demandeur, administrateur
        'statut', // actif, inactif, suspendu
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'age' => 'integer',
        ];
    }

    // Nom complet
    public function getNomCompletAttribute(): string
    {
        if (!empty($this->prenom) && !empty($this->nom)) {
            return "{$this->prenom} {$this->nom}";
        }
        return $this->name ?? $this->email;
    }

    // Initiales
    public function getInitialesAttribute(): string
    {
        $p = !empty($this->prenom) ? mb_substr($this->prenom, 0, 1) : '';
        $n = !empty($this->nom) ? mb_substr($this->nom, 0, 1) : (!empty($this->name) ? mb_substr($this->name, 0, 1) : 'U');
        return strtoupper("{$p}{$n}");
    }

    // Role checks
    public function isDonneur(): bool
    {
        return $this->role === 'donneur';
    }

    public function isDemandeur(): bool
    {
        return $this->role === 'demandeur';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'administrateur';
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif';
    }

    // Relations
    public function donneur(): HasOne
    {
        return $this->hasOne(Donneur::class);
    }

    public function demandeur(): HasOne
    {
        return $this->hasOne(Demandeur::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function rapportsValides(): HasMany
    {
        return $this->hasMany(RapportMedical::class, 'administrateur_id');
    }

    // UML Operations
    public static function creerCompte(array $data): self
    {
        return self::create($data);
    }

    public function seConnecter(): void
    {
        Auth::login($this);
    }

    public function seDeconnecter(): void
    {
        Auth::logout();
    }

    public function modifierProfil(array $data): bool
    {
        return $this->update($data);
    }

    public function consulterHistorique()
    {
        if ($this->isDonneur()) {
            return $this->donneur ? $this->donneur->consulterHistoriqueDons() : collect();
        }
        if ($this->isDemandeur()) {
            return $this->demandeur ? $this->demandeur->demandes()->latest()->get() : collect();
        }
        return collect();
    }
}
