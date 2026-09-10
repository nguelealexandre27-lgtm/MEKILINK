<?php

namespace Database\Seeders;

use App\Models\CentreDon;
use App\Models\DemandeDonneur;
use App\Models\DemandeSang;
use App\Models\Don;
use App\Models\Demandeur;
use App\Models\Donneur;
use App\Models\Notification;
use App\Models\RapportMedical;
use App\Models\User;
use App\Services\AiMatchingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MekilinkSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CRÉATION DES CENTRES DE DON DE SANG (Référence Cameroun / Yaoundé & Douala)
        $cnts = CentreDon::create([
            'nom' => 'Centre National de Transfusion Sanguine (CNTS)',
            'adresse' => 'Quartier Messa, face Camp SIC',
            'ville' => 'Yaoundé',
            'latitude' => 3.8732,
            'longitude' => 11.5042,
            'horaires' => '24h/24 - 7j/7 pour les urgences | 08h00 - 18h00 pour les dons',
            'telephone' => '+237 222 23 45 67',
            'email' => 'contact@cnts.cm',
        ]);

        $hcy = CentreDon::create([
            'nom' => 'Hôpital Central de Yaoundé (HCY) - Banque de sang',
            'adresse' => 'Rue Henri Dunant, Centre-ville',
            'ville' => 'Yaoundé',
            'latitude' => 3.8695,
            'longitude' => 11.5121,
            'horaires' => '07h30 - 20h00',
            'telephone' => '+237 222 22 14 33',
            'email' => 'urgences@hcy.cm',
        ]);

        $hgy = CentreDon::create([
            'nom' => 'Hôpital Général de Yaoundé (HGY)',
            'adresse' => 'Quartier Ngousso',
            'ville' => 'Yaoundé',
            'latitude' => 3.8967,
            'longitude' => 11.5458,
            'horaires' => '08h00 - 17h30',
            'telephone' => '+237 222 21 34 56',
            'email' => 'banquedesang@hgy.cm',
        ]);

        $biyem = CentreDon::create([
            'nom' => 'Hôpital de District de Biyem-Assi',
            'adresse' => 'Rond-point Express, Biyem-Assi',
            'ville' => 'Yaoundé',
            'latitude' => 3.8341,
            'longitude' => 11.4920,
            'horaires' => '08h00 - 18h00',
            'telephone' => '+237 222 31 09 88',
            'email' => 'contact@hdbiyemassi.cm',
        ]);

        $laquintinie = CentreDon::create([
            'nom' => 'Hôpital Laquintinie de Douala',
            'adresse' => 'Rue de l\'Hôpital, Akwa',
            'ville' => 'Douala',
            'latitude' => 4.0511,
            'longitude' => 9.7042,
            'horaires' => '24h/24',
            'telephone' => '+237 233 42 11 11',
            'email' => 'transfusion@laquintinie.cm',
        ]);

        // 2. CRÉATION DES UTILISATEURS

        // A. Administrateur de la plateforme (Étudiant concepteur)
        $admin = User::create([
            'name' => 'Alexandre NGUELE',
            'nom' => 'NGUELE NTOLO',
            'prenom' => 'Alexandre Briand',
            'email' => 'admin@mekilink.org',
            'password' => Hash::make('password'),
            'telephone' => '+237699112233',
            'age' => 24,
            'role' => 'administrateur',
            'statut' => 'actif',
        ]);

        // B. Demandeurs
        $userDemandeur1 = User::create([
            'name' => 'Dr. KAMGA Paul (HCY Urgences)',
            'nom' => 'KAMGA',
            'prenom' => 'Paul',
            'email' => 'demandeur.hopital@mekilink.org',
            'password' => Hash::make('password'),
            'telephone' => '+237677445566',
            'age' => 42,
            'role' => 'demandeur',
            'statut' => 'actif',
        ]);
        $demandeur1 = Demandeur::create([
            'user_id' => $userDemandeur1->id,
            'type_demandeur' => 'Hôpital',
            'nom_etablissement' => 'Hôpital Central de Yaoundé - Urgences Traumatologie',
        ]);

        $userDemandeur2 = User::create([
            'name' => 'Mme ESSOMBA Marie',
            'nom' => 'ESSOMBA',
            'prenom' => 'Marie',
            'email' => 'demandeur.particulier@mekilink.org',
            'password' => Hash::make('password'),
            'telephone' => '+237655889900',
            'age' => 31,
            'role' => 'demandeur',
            'statut' => 'actif',
        ]);
        $demandeur2 = Demandeur::create([
            'user_id' => $userDemandeur2->id,
            'type_demandeur' => 'Particulier',
            'nom_etablissement' => 'Clinique Maternité Sainte Anne',
        ]);

        // C. Donneurs (Variété de groupes sanguins, disponibilités et positions)
        $donneursData = [
            [
                'nom' => 'MBARGA', 'prenom' => 'Samuel', 'email' => 'donneur.o_neg@mekilink.org',
                'tel' => '+237690102030', 'age' => 26, 'groupe' => 'O-', 'dispo' => true,
                'loc' => 'Yaoundé, Mokolo', 'lat' => 3.8710, 'lng' => 11.4980, 'dernier_don' => now()->subMonths(4),
                'centre_id' => $cnts->id,
            ],
            [
                'nom' => 'FOTSO', 'prenom' => 'Christian', 'email' => 'donneur.o_pos@mekilink.org',
                'tel' => '+237670203040', 'age' => 29, 'groupe' => 'O+', 'dispo' => true,
                'loc' => 'Yaoundé, Melen', 'lat' => 3.8620, 'lng' => 11.5010, 'dernier_don' => now()->subMonths(5),
                'centre_id' => $hcy->id,
            ],
            [
                'nom' => 'BEKONO', 'prenom' => 'Serge', 'email' => 'donneur.a_pos@mekilink.org',
                'tel' => '+237695304050', 'age' => 34, 'groupe' => 'A+', 'dispo' => true,
                'loc' => 'Yaoundé, Bastos', 'lat' => 3.8850, 'lng' => 11.5150, 'dernier_don' => now()->subMonths(3),
                'centre_id' => $cnts->id,
            ],
            [
                'nom' => 'ATANGANA', 'prenom' => 'Nadine', 'email' => 'donneur.b_pos@mekilink.org',
                'tel' => '+237678405060', 'age' => 27, 'groupe' => 'B+', 'dispo' => true,
                'loc' => 'Yaoundé, Nlongkak', 'lat' => 3.8820, 'lng' => 11.5220, 'dernier_don' => now()->subMonths(6),
                'centre_id' => $hgy->id,
            ],
            [
                'nom' => 'TCHOUA', 'prenom' => 'Alain', 'email' => 'donneur.ab_pos@mekilink.org',
                'tel' => '+237699506070', 'age' => 38, 'groupe' => 'AB+', 'dispo' => true,
                'loc' => 'Yaoundé, Omnisports', 'lat' => 3.8890, 'lng' => 11.5380, 'dernier_don' => null,
                'centre_id' => $hgy->id,
            ],
            [
                'nom' => 'NDONGO', 'prenom' => 'Clarisse', 'email' => 'donneur.a_neg@mekilink.org',
                'tel' => '+237671607080', 'age' => 23, 'groupe' => 'A-', 'dispo' => true,
                'loc' => 'Yaoundé, Biyem-Assi', 'lat' => 3.8350, 'lng' => 11.4910, 'dernier_don' => now()->subMonths(7),
                'centre_id' => $biyem->id,
            ],
            [
                'nom' => 'ONANA', 'prenom' => 'Jean-Pierre', 'email' => 'donneur.b_neg@mekilink.org',
                'tel' => '+237692708090', 'age' => 45, 'groupe' => 'B-', 'dispo' => false, // Temporairement indisponible
                'loc' => 'Yaoundé, Mendong', 'lat' => 3.8290, 'lng' => 11.4780, 'dernier_don' => now()->subDays(20), // Inéligible (récent)
                'centre_id' => $biyem->id,
            ],
            [
                'nom' => 'BILOUNGA', 'prenom' => 'Esther', 'email' => 'donneur.o_neg2@mekilink.org',
                'tel' => '+237673809010', 'age' => 22, 'groupe' => 'O-', 'dispo' => true,
                'loc' => 'Yaoundé, Ngoa-Ekellé', 'lat' => 3.8550, 'lng' => 11.5050, 'dernier_don' => now()->subMonths(8),
                'centre_id' => $hcy->id,
            ],
        ];

        $donneursModels = [];

        foreach ($donneursData as $d) {
            $u = User::create([
                'name' => "{$d['prenom']} {$d['nom']}",
                'nom' => $d['nom'],
                'prenom' => $d['prenom'],
                'email' => $d['email'],
                'password' => Hash::make('password'),
                'telephone' => $d['tel'],
                'age' => $d['age'],
                'role' => 'donneur',
                'statut' => 'actif',
            ]);

            $donneursModels[] = Donneur::create([
                'user_id' => $u->id,
                'groupe_sanguin' => $d['groupe'],
                'disponibilite' => $d['dispo'],
                'date_dernier_don' => $d['dernier_don'],
                'localisation' => $d['loc'],
                'latitude' => $d['lat'],
                'longitude' => $d['lng'],
                'centre_don_id' => $d['centre_id'],
            ]);
        }

        // 3. CRÉATION DES DEMANDES DE SANG ACTIVES & EN COURS
        $aiService = app(AiMatchingService::class);

        // Demande 1: Urgence Vitale O- pour Polytraumatisé à l'Hôpital Central
        $demandeVitale = DemandeSang::create([
            'demandeur_id' => $demandeur1->id,
            'centre_don_id' => $hcy->id,
            'groupe_sanguin_recherche' => 'O-',
            'quantite' => 3,
            'urgence' => 'vitale',
            'statut' => 'en_attente',
            'localisation' => 'Hôpital Central de Yaoundé - Salle d\'Opération Bloc A',
            'latitude' => 3.8695,
            'longitude' => 11.5121,
            'motif' => 'Victime d\'accident de la voie publique avec choc hémorragique sévère. Besoin immédiat de sang O- avant laparotomie.',
            'date_besoin' => now()->addHours(2),
        ]);

        // Exécuter l'IA de matching pour cette demande vitale
        $aiService->matcherDonneurs($demandeVitale);

        // Notifier les meilleurs donneurs O-
        $sollicitationsVitale = DemandeDonneur::where('demande_sang_id', $demandeVitale->id)->get();
        foreach ($sollicitationsVitale->take(2) as $s) {
            Notification::create([
                'user_id' => $s->donneur->user_id,
                'demande_sang_id' => $demandeVitale->id,
                'contenu' => "ALERTE VITALE MEKILINK : Besoin d'extrême urgence en sang O- à l'Hôpital Central de Yaoundé. Score IA : {$s->score_compatibilite}%. Merci de confirmer d'urgence sur l'application.",
                'type' => 'SMS_TWILIO',
                'statut' => 'envoye',
                'date_envoi' => now(),
            ]);
        }

        // Demande 2: Urgence B+ pour Maternité Sainte-Anne (En cours avec donneur ayant accepté !)
        $demandeEnCours = DemandeSang::create([
            'demandeur_id' => $demandeur2->id,
            'centre_don_id' => $biyem->id,
            'groupe_sanguin_recherche' => 'B+',
            'quantite' => 2,
            'urgence' => 'urgente',
            'statut' => 'en_cours',
            'localisation' => 'Clinique Maternité Sainte-Anne, Biyem-Assi',
            'latitude' => 3.8345,
            'longitude' => 11.4930,
            'motif' => 'Hémorragie de la délivrance chez une jeune mère. Transfusion requise dans les 4 heures.',
            'date_besoin' => now()->addHours(4),
        ]);

        $aiService->matcherDonneurs($demandeEnCours);

        // Marquer le donneur B+ comme ayant accepté
        $donneurB = Donneur::where('groupe_sanguin', 'B+')->first();
        if ($donneurB) {
            DemandeDonneur::where('demande_sang_id', $demandeEnCours->id)
                ->where('donneur_id', $donneurB->id)
                ->update([
                    'statut_reponse' => 'accepte',
                    'date_reponse' => now()->subMinutes(15),
                ]);
        }

        // 4. CRÉATION DE DONS EFFECTUÉS & RAPPORTS MÉDICAUX HISTORIQUES
        // Don 1: Effectué et Validé par l'Admin avec Rapport Médical conforme
        $donneurA = Donneur::where('groupe_sanguin', 'A+')->first();
        if ($donneurA) {
            $donValide = Don::create([
                'donneur_id' => $donneurA->id,
                'centre_don_id' => $cnts->id,
                'date_don' => now()->subMonths(3),
                'groupe_sanguin' => 'A+',
                'statut' => 'valide',
                'quantite_ml' => 450,
            ]);

            RapportMedical::create([
                'don_id' => $donValide->id,
                'administrateur_id' => $admin->id,
                'date_valorisation' => now()->subMonths(3)->addDay(),
                'resultat' => 'conforme',
                'taux_hemoglobine' => '14.2 g/dL',
                'serologie_conforme' => true,
                'commentaire' => 'Poche de sang CGR validée. Bilan sérologique complet négatif (VIH, VHB, VHC, Syphilis). Intégrée au stock d\'urgence du CNTS.',
                'statut' => 'valide',
            ]);
        }

        // Don 2: Effectué récemment, EN ATTENTE de validation médicale (pour tester l'écran admin !)
        $donneurO = Donneur::where('groupe_sanguin', 'O+')->first();
        if ($donneurO) {
            $donEnAttente = Don::create([
                'donneur_id' => $donneurO->id,
                'centre_don_id' => $hcy->id,
                'date_don' => now()->subHours(8),
                'groupe_sanguin' => 'O+',
                'statut' => 'effectue',
                'quantite_ml' => 450,
            ]);

            RapportMedical::create([
                'don_id' => $donEnAttente->id,
                'administrateur_id' => null,
                'date_valorisation' => now()->toDateString(),
                'resultat' => 'apte',
                'taux_hemoglobine' => '13.9 g/dL',
                'serologie_conforme' => true,
                'commentaire' => 'Prélèvement effectué ce matin. Tests de laboratoire terminés conformes. En attente de signature et validation médicale administrative.',
                'statut' => 'en_attente',
            ]);
        }
    }
}
