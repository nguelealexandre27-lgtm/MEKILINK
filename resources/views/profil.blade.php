@extends('layouts.app')

@section('title', 'Mon Profil Utilisateur')

@section('content')
<div class="container" style="max-width:760px;padding-top:20px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Mon Profil Utilisateur</h1>
            <p class="page-subtitle">Gérez vos coordonnées, votre statut et vos préférences d'alerte.</p>
        </div>
        <div>
            <span class="badge-info" style="font-size:0.85rem;padding:6px 12px;">
                Rôle : {{ ucfirst($user->role) }}
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form method="POST" action="{{ route('profil.update') }}">
                @csrf

                <div style="display:flex;align-items:center;gap:18px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--slate-200);">
                    <div style="width:64px;height:64px;border-radius:50%;background:var(--blood-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;box-shadow:var(--shadow-md);">
                        {{ $user->initiales }}
                    </div>
                    <div>
                        <h3 style="font-size:1.2rem;font-weight:800;color:var(--slate-900);margin:0;">
                            {{ $user->nom_complet }}
                        </h3>
                        <p style="font-size:0.85rem;color:var(--slate-600);margin:2px 0 0;">
                            Membre depuis {{ $user->created_at->format('d/m/Y') }} • Statut : <strong style="color:var(--status-success);">{{ ucfirst($user->statut) }}</strong>
                        </p>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="{{ old('prenom', $user->prenom) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="nom">Nom de famille</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="{{ old('nom', $user->nom) }}" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" class="form-control" value="{{ $user->email }}" disabled style="background:var(--slate-100);">
                        <span class="form-hint">L'email sert d'identifiant unique.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="telephone">Téléphone (SMS Twilio)</label>
                        <input type="text" id="telephone" name="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}" required>
                    </div>
                </div>

                @if($user->isDonneur() && $user->donneur)
                    <div style="background:var(--slate-50);border:1px solid var(--slate-200);border-radius:var(--radius-md);padding:18px;margin-bottom:20px;">
                        <div style="font-size:0.9rem;font-weight:700;color:var(--blood-primary);margin-bottom:12px;">
                            🩸 Données Médicales de Donneur
                        </div>
                        <div class="grid-2">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="groupe_sanguin">Groupe Sanguin</label>
                                <select id="groupe_sanguin" name="groupe_sanguin" class="form-select" style="font-weight:700;">
                                    @foreach(config('mekilink.groupes_sanguins') as $grp)
                                        <option value="{{ $grp }}" {{ $user->donneur->groupe_sanguin === $grp ? 'selected' : '' }}>
                                            {{ $grp }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="localisation">Localisation (Quartier / Ville)</label>
                                <input type="text" id="localisation" name="localisation" class="form-control" value="{{ old('localisation', $user->donneur->localisation) }}">
                            </div>
                        </div>
                    </div>
                @endif

                @if($user->isDemandeur() && $user->demandeur)
                    <div style="background:var(--slate-50);border:1px solid var(--slate-200);border-radius:var(--radius-md);padding:18px;margin-bottom:20px;">
                        <div style="font-size:0.9rem;font-weight:700;color:var(--slate-800);margin-bottom:12px;">
                            🏥 Données d'Établissement Demandeur
                        </div>
                        <div class="grid-2">
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="type_demandeur">Type d'entité</label>
                                <select id="type_demandeur" name="type_demandeur" class="form-select">
                                    @foreach(['Particulier', 'Hôpital', 'Clinique', 'Banque de sang'] as $t)
                                        <option value="{{ $t }}" {{ $user->demandeur->type_demandeur === $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label class="form-label" for="nom_etablissement">Nom de l'établissement</label>
                                <input type="text" id="nom_etablissement" name="nom_etablissement" class="form-control" value="{{ old('nom_etablissement', $user->demandeur->nom_etablissement) }}">
                            </div>
                        </div>
                    </div>
                @endif

                <div style="border-top:1px solid var(--slate-200);padding-top:18px;margin-top:10px;">
                    <div style="font-size:0.9rem;font-weight:700;color:var(--slate-800);margin-bottom:10px;">
                        Modifier le mot de passe (optionnel)
                    </div>
                    <div class="grid-2">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="password">Nouveau mot de passe</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Laisser vide pour conserver">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label" for="password_confirmation">Confirmer</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Répéter nouveau mot de passe">
                        </div>
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Enregistrer les Modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
