@extends('layouts.app')

@section('title', 'Connexion Sécurisée')

@section('content')
<div class="container" style="max-width:480px;padding-top:40px;padding-bottom:60px;">
    <div class="card" style="box-shadow:var(--shadow-lg);">
        <div class="card-header" style="text-align:center;padding:24px;border-bottom:none;padding-bottom:0;">
            
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--slate-900);">Connexion à MEKILINK</h1>
            <p style="font-size:0.85rem;color:var(--slate-600);margin-top:4px;">
                Accédez à votre espace donneur, demandeur ou supervision.
            </p>
        </div>

        <div class="card-body" style="padding:24px;">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="votre.email@exemple.com">
                </div>

                <div class="form-group">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label class="form-label" for="password" style="margin-bottom:0;">Mot de passe</label>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:var(--slate-700);cursor:pointer;">
                        <input type="checkbox" name="remember" style="accent-color:var(--blood-primary);">
                        <span>Se souvenir de moi</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-vital btn-lg" style="width:100%;">
                    Se Connecter
                </button>
            </form>

            <!-- Raccourcis Comptes de Démonstration (Spécial Soutenance) -->
            <div style="margin-top:24px;padding:14px;background:var(--slate-50);border:1px dashed var(--slate-300);border-radius:var(--radius-sm);">
                <div style="font-size:0.75rem;font-weight:700;color:var(--slate-600);text-transform:uppercase;margin-bottom:8px;text-align:center;">
                    Comptes de Test (Cliquer pour remplir) :
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <button type="button" class="btn btn-secondary btn-sm" style="font-size:0.75rem;text-align:left;justify-content:flex-start;" onclick="fillLogin('admin@mekilink.org', 'password')">
                        🛡️ <strong>Administrateur :</strong> admin@mekilink.org
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" style="font-size:0.75rem;text-align:left;justify-content:flex-start;" onclick="fillLogin('donneur.o_neg@mekilink.org', 'password')">
                        🩸 <strong>Donneur (O-) :</strong> donneur.o_neg@mekilink.org
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" style="font-size:0.75rem;text-align:left;justify-content:flex-start;" onclick="fillLogin('demandeur.hopital@mekilink.org', 'password')">
                        🏥 <strong>Demandeur (Hôpital) :</strong> demandeur.hopital@mekilink.org
                    </button>
                </div>
            </div>
        </div>

        <div class="card-footer" style="text-align:center;justify-content:center;background:var(--slate-50);font-size:0.88rem;">
            <span>Pas encore de compte ?</span>
            <a href="{{ route('register') }}" style="font-weight:700;margin-left:6px;">Créer un compte donneur</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fillLogin(email, pwd) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pwd;
}
</script>
@endpush
@endsection
