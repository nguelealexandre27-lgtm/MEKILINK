@extends('layouts.app')

@section('title', 'Inscription - Rejoindre le Réseau')

@section('content')
<div class="container" style="max-width:620px;padding-top:30px;padding-bottom:60px;">
    <div class="card" style="box-shadow:var(--shadow-lg);">
        <div class="card-header" style="text-align:center;padding:24px 24px 10px;border-bottom:none;">
            <div style="width:50px;height:50px;margin:0 auto 12px;background:var(--blood-light);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--blood-primary);font-size:1.5rem;">
                🩸
            </div>
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--slate-900);">Créer un Compte sur MEKILINK</h1>
            <p style="font-size:0.85rem;color:var(--slate-600);margin-top:4px;">
                Chaque inscription contribue à bâtir un maillage de sauvetage fiable et réactif.
            </p>
        </div>

        <div class="card-body" style="padding:24px;">
            <form method="POST" action="{{ route('register') }}" id="register-form">
                @csrf

                <!-- Sélection tactile du Rôle -->
                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" style="text-align:center;margin-bottom:10px;">Je m'inscris en tant que :</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <label id="role-tab-donneur" style="border:2px solid var(--blood-primary);background:var(--blood-light);border-radius:var(--radius-md);padding:14px;display:flex;flex-direction:column;align-items:center;gap:6px;cursor:pointer;transition:var(--transition);">
                            <input type="radio" name="role" value="donneur" checked style="display:none;" onchange="toggleRoleFields('donneur')">
                            <span style="font-size:1.4rem;">❤️</span>
                            <span style="font-weight:700;color:var(--slate-900);">Donneur de Sang</span>
                            <span style="font-size:0.75rem;color:var(--slate-600);text-align:center;">Prêt à être alerté pour sauver des vies</span>
                        </label>

                        <label id="role-tab-demandeur" style="border:2px solid var(--slate-200);background:var(--white);border-radius:var(--radius-md);padding:14px;display:flex;flex-direction:column;align-items:center;gap:6px;cursor:pointer;transition:var(--transition);">
                            <input type="radio" name="role" value="demandeur" style="display:none;" onchange="toggleRoleFields('demandeur')">
                            <span style="font-size:1.4rem;">🏥</span>
                            <span style="font-weight:700;color:var(--slate-900);">Demandeur / Hôpital</span>
                            <span style="font-size:0.75rem;color:var(--slate-600);text-align:center;">Hôpital, clinique ou particulier en besoin</span>
                        </label>
                    </div>
                </div>

                <!-- Informations Personnelles Générales -->
                <div class="grid-2" style="margin-bottom:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="nom">Nom de famille</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="{{ old('nom') }}" required placeholder="Ex: MBARGA">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="{{ old('prenom') }}" required placeholder="Ex: Samuel">
                    </div>
                </div>

                <div class="grid-2" style="margin-bottom:16px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="email">Adresse Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="samuel@exemple.com">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="telephone">Téléphone (SMS Twilio)</label>
                        <input type="text" id="telephone" name="telephone" class="form-control" value="{{ old('telephone') }}" required placeholder="Ex: 690102030 ou +237...">
                        <span class="form-hint">Utilisé pour vous alerter par SMS en cas d'urgence vitale.</span>
                    </div>
                </div>

                <!-- Champs Spécifiques Donneur -->
                <div id="fields-donneur" style="background:var(--slate-50);padding:18px;border-radius:var(--radius-md);margin-bottom:20px;border:1px solid var(--slate-200);">
                    <div style="font-size:0.85rem;font-weight:700;color:var(--blood-primary);text-transform:uppercase;margin-bottom:12px;">
                        🩸 Profil Médical du Donneur
                    </div>
                    <div class="grid-2">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="groupe_sanguin">Groupe Sanguin</label>
                            <select id="groupe_sanguin" name="groupe_sanguin" class="form-select" style="font-weight:700;">
                                <option value="O-">O- (Donneur Universel)</option>
                                <option value="O+">O+</option>
                                <option value="A-">A-</option>
                                <option value="A+" selected>A+</option>
                                <option value="B-">B-</option>
                                <option value="B+">B+</option>
                                <option value="AB-">AB-</option>
                                <option value="AB+">AB+ (Receveur Universel)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="age">Âge (18 - 70 ans)</label>
                            <input type="number" id="age" name="age" class="form-control" min="18" max="70" value="{{ old('age', 25) }}" placeholder="25">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:14px;margin-bottom:0;">
                        <label class="form-label" for="localisation">Ville & Quartier</label>
                        <input type="text" id="localisation" name="localisation" class="form-control" value="{{ old('localisation', 'Yaoundé, Melen') }}" placeholder="Ex: Yaoundé, Mokolo">
                    </div>
                </div>

                <!-- Champs Spécifiques Demandeur -->
                <div id="fields-demandeur" style="display:none;background:var(--slate-50);padding:18px;border-radius:var(--radius-md);margin-bottom:20px;border:1px solid var(--slate-200);">
                    <div style="font-size:0.85rem;font-weight:700;color:var(--slate-800);text-transform:uppercase;margin-bottom:12px;">
                        🏥 Profil du Demandeur
                    </div>
                    <div class="grid-2">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="type_demandeur">Type d'entité</label>
                            <select id="type_demandeur" name="type_demandeur" class="form-select">
                                <option value="Hôpital">Hôpital Public</option>
                                <option value="Clinique">Clinique Privée</option>
                                <option value="Banque de sang">Banque de sang / Laboratoire</option>
                                <option value="Particulier">Particulier (Patient / Famille)</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="nom_etablissement">Nom de l'établissement</label>
                            <input type="text" id="nom_etablissement" name="nom_etablissement" class="form-control" placeholder="Ex: Hôpital Général, Clinique Ste-Anne...">
                        </div>
                    </div>
                </div>

                <!-- Mots de passe -->
                <div class="grid-2" style="margin-bottom:24px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="Minimum 6 caractères">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Répétez le mot de passe">
                    </div>
                </div>

                <button type="submit" class="btn btn-vital btn-lg" style="width:100%;">
                    Finaliser mon Inscription & Rejoindre MEKILINK
                </button>
            </form>
        </div>

        <div class="card-footer" style="text-align:center;justify-content:center;background:var(--slate-50);font-size:0.88rem;">
            <span>Déjà inscrit ?</span>
            <a href="{{ route('login') }}" style="font-weight:700;margin-left:6px;">Se connecter</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleRoleFields(role) {
    const tabDonneur = document.getElementById('role-tab-donneur');
    const tabDemandeur = document.getElementById('role-tab-demandeur');
    const fieldsDonneur = document.getElementById('fields-donneur');
    const fieldsDemandeur = document.getElementById('fields-demandeur');

    if (role === 'donneur') {
        tabDonneur.style.borderColor = 'var(--blood-primary)';
        tabDonneur.style.background = 'var(--blood-light)';
        tabDemandeur.style.borderColor = 'var(--slate-200)';
        tabDemandeur.style.background = 'var(--white)';
        fieldsDonneur.style.display = 'block';
        fieldsDemandeur.style.display = 'none';
    } else {
        tabDemandeur.style.borderColor = 'var(--blood-primary)';
        tabDemandeur.style.background = 'var(--blood-light)';
        tabDonneur.style.borderColor = 'var(--slate-200)';
        tabDonneur.style.background = 'var(--white)';
        fieldsDemandeur.style.display = 'block';
        fieldsDonneur.style.display = 'none';
    }
}
</script>
@endpush
@endsection
