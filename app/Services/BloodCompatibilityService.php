<?php

namespace App\Services;

/**
 * Service de compatibilité sanguine ABO / Rhésus.
 * Respecte les lois universelles de transfusion des globules rouges (érythrocytes).
 */
class BloodCompatibilityService
{
    /**
     * Matrice de compatibilité pour les globules rouges concentrés (CGR) :
     * Clé = Groupe du Receveur (Demandeur)
     * Valeur = Liste des Groupes Donneurs autorisés
     */
    protected static array $compatibilityMatrix = [
        'O-' => ['O-'],
        'O+' => ['O-', 'O+'],
        'A-' => ['O-', 'A-'],
        'A+' => ['O-', 'O+', 'A-', 'A+'],
        'B-' => ['O-', 'B-'],
        'B+' => ['O-', 'O+', 'B-', 'B+'],
        'AB-' => ['O-', 'A-', 'B-', 'AB-'],
        'AB+' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'], // Receveur universel
    ];

    /**
     * Matrice inverse : Qui peut recevoir le sang de ce Donneur ?
     * Clé = Groupe du Donneur
     * Valeur = Liste des Groupes Receveurs pouvant accepter ce sang
     */
    protected static array $canDonateToMatrix = [
        'O-' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'], // Donneur universel
        'O+' => ['O+', 'A+', 'B+', 'AB+'],
        'A-' => ['A-', 'A+', 'AB-', 'AB+'],
        'A+' => ['A+', 'AB+'],
        'B-' => ['B-', 'B+', 'AB-', 'AB+'],
        'B+' => ['B+', 'AB+'],
        'AB-' => ['AB-', 'AB+'],
        'AB+' => ['AB+'],
    ];

    /**
     * Vérifie si un donneur est compatible avec un receveur
     */
    public static function isCompatible(string $donorGroup, string $recipientGroup): bool
    {
        $allowed = self::$compatibilityMatrix[$recipientGroup] ?? [];
        return in_array($donorGroup, $allowed, true);
    }

    /**
     * Retourne la liste des groupes de donneurs compatibles pour un receveur
     */
    public static function getCompatibleDonorsFor(string $recipientGroup): array
    {
        return self::$compatibilityMatrix[$recipientGroup] ?? [];
    }

    /**
     * Retourne la liste des groupes de receveurs pouvant recevoir ce sang
     */
    public static function getCanDonateTo(string $donorGroup): array
    {
        return self::$canDonateToMatrix[$donorGroup] ?? [];
    }

    /**
     * Retourne la matrice complète pour affichage interactif
     */
    public static function getMatrix(): array
    {
        return self::$compatibilityMatrix;
    }

    /**
     * Fournit une explication médicale de la compatibilité
     */
    public static function getExplanation(string $donorGroup, string $recipientGroup): string
    {
        if ($donorGroup === $recipientGroup) {
            return "Compatibilité isogroupe parfaite ({$donorGroup} vers {$recipientGroup}). Aucun risque d'allo-immunisation majeure.";
        }

        if ($donorGroup === 'O-') {
            return "Compatibilité universelle : Le groupe O- ne porte ni antigènes A, ni antigènes B, ni antigène Rh(D). Il peut être transfusé en toute sécurité au groupe {$recipientGroup}.";
        }

        if (self::isCompatible($donorGroup, $recipientGroup)) {
            return "Compatibilité hétérogroupe validée : Les globules rouges du donneur ({$donorGroup}) ne possèdent pas d'antigènes contre lesquels le receveur ({$recipientGroup}) a des anticorps naturels.";
        }

        return "Incompatibilité immunologique majeure : Le sang {$donorGroup} entraînerait une réaction hémolytique aiguë chez un receveur {$recipientGroup}. Transfusion strictement interdite.";
    }
}
