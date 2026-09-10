@extends('layouts.app')

@section('title', 'Gestion des Comptes Utilisateurs - MEKILINK')

@section('content')
<div class="container" style="padding-top:20px;">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Comptes Utilisateurs</h1>
            <p class="page-subtitle">Supervisez l'ensemble des donneurs, demandeurs et administrateurs enregistrés.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            &larr; Retour à la supervision
        </a>
    </div>

    <!-- Barre de Recherche & Filtres -->
    <div class="card" style="margin-bottom:24px;">
        <div class="card-body" style="padding:16px 20px;">
            <form method="GET" action="{{ route('admin.users') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                <div style="flex:1;min-width:240px;">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, email, téléphone..." value="{{ request('search') }}">
                </div>

                <div style="min-width:160px;">
                    <select name="role" class="form-select">
                        <option value="">Tous les rôles</option>
                        <option value="donneur" {{ request('role') === 'donneur' ? 'selected' : '' }}>Donneurs</option>
                        <option value="demandeur" {{ request('role') === 'demandeur' ? 'selected' : '' }}>Demandeurs</option>
                        <option value="administrateur" {{ request('role') === 'administrateur' ? 'selected' : '' }}>Administrateurs</option>
                    </select>
                </div>

                <div style="min-width:140px;">
                    <select name="statut" class="form-select">
                        <option value="">Tous statuts</option>
                        <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actifs</option>
                        <option value="suspendu" {{ request('statut') === 'suspendu' ? 'selected' : '' }}>Suspendus</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                @if(request()->hasAny(['search', 'role', 'statut']))
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Table des Utilisateurs -->
    <div class="card">
        <div class="card-body" style="padding:0;">
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;text-align:left;font-size:0.92rem;">
                    <thead>
                        <tr style="background:var(--slate-50);border-bottom:1px solid var(--slate-200);color:var(--slate-600);font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                            <th style="padding:14px 20px;">Utilisateur</th>
                            <th style="padding:14px 20px;">Rôle</th>
                            <th style="padding:14px 20px;">Spécificités</th>
                            <th style="padding:14px 20px;">Téléphone (Twilio)</th>
                            <th style="padding:14px 20px;">Statut</th>
                            <th style="padding:14px 20px;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr style="border-bottom:1px solid var(--slate-100);">
                                <td style="padding:14px 20px;">
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <span style="width:34px;height:34px;border-radius:50%;background:var(--blood-primary);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;">
                                            {{ $u->initiales }}
                                        </span>
                                        <div>
                                            <div style="font-weight:700;color:var(--slate-900);">{{ $u->nom_complet }}</div>
                                            <div style="font-size:0.75rem;color:var(--slate-500);">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:14px 20px;">
                                    @if($u->role === 'administrateur')
                                        <span class="badge-vitale" style="background:#0f172a;color:#fff;">Admin</span>
                                    @elseif($u->role === 'donneur')
                                        <span class="badge-success">Donneur</span>
                                    @else
                                        <span class="badge-info">Demandeur</span>
                                    @endif
                                </td>
                                <td style="padding:14px 20px;">
                                    @if($u->isDonneur() && $u->donneur)
                                        <span class="blood-badge sm filled">{{ $u->donneur->groupe_sanguin }}</span>
                                        <span style="font-size:0.75rem;color:var(--slate-500);margin-left:4px;">
                                            {{ $u->donneur->disponibilite ? '● Disponible' : '○ Indispo' }}
                                        </span>
                                    @elseif($u->isDemandeur() && $u->demandeur)
                                        <span style="font-size:0.82rem;color:var(--slate-700);">
                                            {{ $u->demandeur->type_demandeur }} {{ $u->demandeur->nom_etablissement ? '(' . $u->demandeur->nom_etablissement . ')' : '' }}
                                        </span>
                                    @else
                                        <span style="color:var(--slate-400);font-size:0.8rem;">Superviseur Système</span>
                                    @endif
                                </td>
                                <td style="padding:14px 20px;font-family:var(--font-mono);font-size:0.85rem;">
                                    {{ $u->telephone ?? 'Non renseigné' }}
                                </td>
                                <td style="padding:14px 20px;">
                                    @if($u->statut === 'actif')
                                        <span class="badge-success">Actif</span>
                                    @else
                                        <span class="badge-danger">Suspendu</span>
                                    @endif
                                </td>
                                <td style="padding:14px 20px;text-align:right;">
                                    @if($u->id !== Auth::id())
                                        <div style="display:inline-flex;gap:6px;">
                                            <form method="POST" action="{{ route('admin.users.toggle', $u->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm" style="font-size:0.75rem;">
                                                    {{ $u->statut === 'actif' ? 'Suspendre' : 'Réactiver' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.users.delete', $u->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" style="font-size:0.75rem;" onclick="return confirm('Confirmez-vous la suppression définitive du compte de {{ $u->nom_complet }} ?');">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span style="font-size:0.75rem;color:var(--slate-400);font-weight:600;">Votre compte</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding:16px 20px;">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
