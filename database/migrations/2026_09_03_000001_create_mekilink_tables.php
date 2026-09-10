<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Centres de Don (Banques de sang / Hôpitaux)
        Schema::create('centres_don', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('adresse');
            $table->string('ville')->default('Yaoundé');
            $table->decimal('latitude', 10, 7)->default(3.8667);
            $table->decimal('longitude', 10, 7)->default(11.5167);
            $table->string('horaires')->default('08:00 - 18:00');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // 2. Profils Donneurs (Spécialisation d'Utilisateur)
        Schema::create('donneurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('groupe_sanguin', 5); // A+, A-, B+, B-, AB+, AB-, O+, O-
            $table->boolean('disponibilite')->default(true);
            $table->date('date_dernier_don')->nullable();
            $table->string('localisation')->nullable(); // Quartier / Ville
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('centre_don_id')->nullable()->constrained('centres_don')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Profils Demandeurs (Spécialisation d'Utilisateur)
        Schema::create('demandeurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type_demandeur')->default('Particulier'); // Particulier, Hôpital, Clinique, Banque de sang
            $table->string('nom_etablissement')->nullable();
            $table->timestamps();
        });

        // 4. Demandes de Sang
        Schema::create('demandes_sang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demandeur_id')->constrained('demandeurs')->onDelete('cascade');
            $table->foreignId('centre_don_id')->nullable()->constrained('centres_don')->nullOnDelete();
            $table->string('groupe_sanguin_recherche', 5);
            $table->integer('quantite')->default(1); // Poches / Unités
            $table->string('urgence')->default('urgente'); // vitale, urgente, moyenne, faible
            $table->string('statut')->default('en_attente'); // en_attente, en_cours, satisfaite, annulee, expiree
            $table->string('localisation'); // Établissement médical / Hôpital demandeur
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('motif')->nullable();
            $table->dateTime('date_besoin')->nullable();
            $table->timestamps();
        });

        // 5. Association DemandeSang <-> Donneur (Sollicitations et Scoring IA)
        Schema::create('demande_donneurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_sang_id')->constrained('demandes_sang')->onDelete('cascade');
            $table->foreignId('donneur_id')->constrained('donneurs')->onDelete('cascade');
            $table->integer('score_compatibilite')->default(0); // 0 à 100%
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->string('statut_reponse')->default('en_attente'); // en_attente, accepte, refuse, expire
            $table->dateTime('date_reponse')->nullable();
            $table->text('explication_ia')->nullable();
            $table->timestamps();
        });

        // 6. Dons effectués
        Schema::create('dons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donneur_id')->constrained('donneurs')->onDelete('cascade');
            $table->foreignId('demande_sang_id')->nullable()->constrained('demandes_sang')->nullOnDelete();
            $table->foreignId('centre_don_id')->constrained('centres_don')->onDelete('cascade');
            $table->dateTime('date_don');
            $table->string('groupe_sanguin', 5);
            $table->string('statut')->default('planifie'); // planifie, effectue, valide, rejete
            $table->integer('quantite_ml')->default(450);
            $table->timestamps();
        });

        // 7. Rapports Médicaux associés aux Dons
        Schema::create('rapports_medicaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('don_id')->unique()->constrained('dons')->onDelete('cascade');
            $table->foreignId('administrateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date_valorisation');
            $table->string('resultat')->default('apte'); // apte, inapte, conforme, non_conforme
            $table->string('taux_hemoglobine')->nullable(); // e.g. 13.5 g/dL
            $table->boolean('serologie_conforme')->default(true);
            $table->text('commentaire')->nullable();
            $table->string('statut')->default('en_attente'); // en_attente, valide, rejete
            $table->timestamps();
        });

        // 8. Notifications (Twilio SMS & In-app)
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('demande_sang_id')->nullable()->constrained('demandes_sang')->nullOnDelete();
            $table->text('contenu');
            $table->string('type')->default('SMS_TWILIO'); // SMS_TWILIO, IN_APP, EMAIL
            $table->string('statut')->default('envoye'); // en_attente, envoye, echoue, lu
            $table->dateTime('date_envoi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('rapports_medicaux');
        Schema::dropIfExists('dons');
        Schema::dropIfExists('demande_donneurs');
        Schema::dropIfExists('demandes_sang');
        Schema::dropIfExists('demandeurs');
        Schema::dropIfExists('donneurs');
        Schema::dropIfExists('centres_don');
    }
};
