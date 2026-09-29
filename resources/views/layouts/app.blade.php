<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MEKILINK') | Plateforme Intelligente de Recherche de Donneurs de Sang</title>

    <!-- Feuille de style CSS3 personnalisée & innovante -->
    <link rel="stylesheet" href="{{ asset('css/mekilink.css') }}">

    <!-- Leaflet.js pour cartographie libre OpenStreetMap (sans clé API) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    @stack('styles')
</head>
<body>

    <!-- Barre d'Urgence Supérieure -->
    <div class="emergency-ticker">
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="ticker-badge">
                <span class="ticker-pulse"></span>
                Urgence Vitale
            </span>
            <span style="opacity:0.9;">Chaque seconde compte : 1 don de sang sauve jusqu'à 3 vies humaines.</span>
        </div>
        <div style="display:flex;align-items:center;gap:16px;">
            <a href="{{ route('centres.index') }}" style="color:rgba(255,255,255,0.85);font-size:0.8rem;text-decoration:underline;">
                Centres de transfusion proches
            </a>
            @auth
                @if(Auth::user()->isDonneur() && Auth::user()->donneur)
                    <span style="font-size:0.75rem;padding:2px 8px;border-radius:4px;background:{{ Auth::user()->donneur->disponibilite ? '#059669' : '#64748b' }};color:#fff;font-weight:700;">
                        {{ Auth::user()->donneur->disponibilite ? '● DISPONIBLE' : '○ INDISPONIBLE' }}
                    </span>
                @endif
            @endauth
        </div>
    </div>

    <!-- Navigation Principale -->
    <header class="site-header">
        <div class="header-container">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="logo-link">
                <div class="logo-drop">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <span class="logo-title">MEKI<span>LINK</span></span>
                    <span class="logo-subtitle">Réseau Intelligent de Don de Sang</span>
                </div>
            </a>

            <!-- Liens de navigation -->
            <nav>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('centres.index') }}" class="{{ request()->routeIs('centres.*') ? 'active' : '' }}">Centres de Don</a>
                    </li>

                    @auth
                        @if(Auth::user()->isDonneur())
                            <li class="nav-item">
                                <a href="{{ route('donneur.dashboard') }}" class="{{ request()->routeIs('donneur.dashboard') ? 'active' : '' }}">Espace Donneur</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('donneur.historique') }}" class="{{ request()->routeIs('donneur.historique') ? 'active' : '' }}">Mes Dons</a>
                            </li>
                        @elseif(Auth::user()->isDemandeur())
                            <li class="nav-item">
                                <a href="{{ route('demandeur.dashboard') }}" class="{{ request()->routeIs('demandeur.*') ? 'active' : '' }}">Mes Demandes</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('demandes.create') }}" class="{{ request()->routeIs('demandes.create') ? 'active' : '' }}">Nouvelle Demande</a>
                            </li>
                        @elseif(Auth::user()->isAdmin())
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Supervision</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">Utilisateurs</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('rapports.index') }}" class="{{ request()->routeIs('rapports.*') ? 'active' : '' }}">Validation Médicale</a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a href="{{ route('dons.index') }}" class="{{ request()->routeIs('dons.*') ? 'active' : '' }}">Dons</a>
                        </li>
                    @endauth
                </ul>
            </nav>

            <!-- Actions Utilisateur -->
            <div class="header-actions">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">Connexion</a>
                    <a href="{{ route('register') }}" class="btn btn-vital btn-sm">Devenir Donneur</a>
                @else
                    <div style="display:flex;align-items:center;gap:10px;">
                        <a href="{{ route('profil') }}" style="display:flex;align-items:center;gap:8px;padding:4px 10px;border-radius:6px;background:var(--slate-100);font-size:0.85rem;color:var(--slate-800);font-weight:600;">
                            <span style="width:28px;height:28px;border-radius:50%;background:var(--blood-primary);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:0.75rem;">
                                {{ Auth::user()->initiales }}
                            </span>
                            <span>{{ Auth::user()->prenom ?? Auth::user()->name }}</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm" title="Déconnexion">
                                Quitter
                            </button>
                        </form>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- Messages Flash -->
    <main style="flex:1;">
        <div class="container" style="padding-top:16px;padding-bottom:16px;">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <!-- Pied de page Professionnel -->
    <footer style="background:var(--slate-900);color:var(--slate-400);padding:40px 0 20px;border-top:1px solid var(--slate-800);margin-top:50px;">
        <div class="container">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:30px;margin-bottom:30px;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;color:#fff;font-weight:800;font-size:1.2rem;margin-bottom:12px;">
                        <span style="color:var(--blood-arterial);">●</span> MEKILINK
                    </div>
                    <p style="font-size:0.88rem;line-height:1.6;color:var(--slate-400);">
                        Plateforme médicale intelligente de mise en relation et de recherche rapide de donneurs de sang compatibles pour répondre efficacement aux urgences vitales.
                    </p>
                </div>

                <div>
                    <h4 style="color:#fff;font-size:0.95rem;margin-bottom:12px;font-weight:700;">Navigation Rapide</h4>
                    <ul style="list-style:none;font-size:0.85rem;line-height:2;">
                        <li><a href="{{ route('home') }}" style="color:var(--slate-400);">Accueil & Compatibilité</a></li>
                        <li><a href="{{ route('centres.index') }}" style="color:var(--slate-400);">Trouver un centre de don</a></li>
                        <li><a href="{{ route('login') }}" style="color:var(--slate-400);">Espace Connexion</a></li>
                        <li><a href="{{ route('register') }}" style="color:var(--slate-400);">S'inscrire comme Donneur</a></li>
                    </ul>
                </div>
            </div>

            <div style="border-top:1px solid rgba(255,255,255,0.08);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;font-size:0.8rem;">
                <p>&copy; {{ date('Y') }} MEKILINK. Tous droits réservés.</p>
                <p>Sauver une vie commence par une décision.</p>
            </div>
        </div>
    </footer>

    <!-- Barre de Navigation Mobile Inférieure -->
    <div class="mobile-bottom-nav">
        <a href="{{ route('home') }}" class="mobile-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <span>🏠</span>
            <span>Accueil</span>
        </a>
        <a href="{{ route('centres.index') }}" class="mobile-nav-item {{ request()->routeIs('centres.*') ? 'active' : '' }}">
            <span>🏥</span>
            <span>Centres</span>
        </a>
        @auth
            <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('profil') }}" class="mobile-nav-item {{ request()->routeIs('profil') ? 'active' : '' }}">
                <span>👤</span>
                <span>Profil</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="mobile-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
                <span>🔑</span>
                <span>Connexion</span>
            </a>
            <a href="{{ route('register') }}" class="mobile-nav-item {{ request()->routeIs('register') ? 'active' : '' }}">
                <span>🩸</span>
                <span>Donner</span>
            </a>
        @endauth
    </div>

    <!-- Modal Pop-up IA Indisponible -->
    <div id="ai-unavailable-modal" class="ai-modal-overlay" style="display: {{ session('ai_unavailable') ? 'flex' : 'none' }};">
        <div class="ai-modal-box">
            <div class="ai-modal-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h3 class="ai-modal-title">Service IA</h3>
            <p class="ai-modal-text">{{ session('ai_message', "L'IA n'est pas disponible pour le moment, veuillez réessayer plus tard.") }}</p>
            <button type="button" class="btn btn-primary" onclick="closeAiUnavailableModal()" style="width:100%;margin-top:18px;justify-content:center;">
                Compris
            </button>
        </div>
    </div>

    <style>
        .ai-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .ai-modal-box {
            background: #ffffff;
            border-radius: 16px;
            max-width: 420px;
            width: 100%;
            padding: 28px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
            animation: modalPopIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes modalPopIn {
            from { opacity: 0; transform: scale(0.92) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .ai-modal-icon {
            width: 54px;
            height: 54px;
            background: #fef2f2;
            color: #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .ai-modal-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 8px 0;
        }
        .ai-modal-text {
            font-size: 0.95rem;
            color: #475569;
            line-height: 1.5;
            margin: 0;
        }
    </style>

    <!-- Script JavaScript Natif Pur -->
    <script src="{{ asset('js/mekilink.js') }}"></script>
    @stack('scripts')
</body>
</html>
