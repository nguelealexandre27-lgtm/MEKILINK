@extends('layouts.app')

@section('title', 'Accueil - Sauvez des Vies en Temps Réel')

@section('content')

<!-- Hero Section Médicale & Humaine -->
<section style="background:linear-gradient(135deg, #090d16 0%, #1e1b2e 50%, #450a0a 100%);color:#fff;padding:60px 0;position:relative;overflow:hidden;">
    <div class="container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:40px;align-items:center;">
            <div>
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.4);color:#fca5a5;padding:6px 14px;border-radius:999px;font-size:0.82rem;font-weight:700;margin-bottom:20px;">
                    <span class="pulse-dot"></span>
                    RÉSEAU D'URGENCE TRANSFUSIONNELLE
                </div>

                <h1 style="font-size:clamp(2rem, 4vw, 3.2rem);font-weight:900;line-height:1.15;letter-spacing:-0.03em;margin-bottom:20px;">
                    Connecter une <span style="color:#f87171;">urgence vitale</span> au bon donneur en quelques minutes.
                </h1>

                <p style="font-size:1.05rem;line-height:1.6;color:#cbd5e1;margin-bottom:30px;max-width:540px;">
                    Fini les appels désespérés et le bouche-à-oreille informel. MEKILINK mobilise l'intelligence artificielle pour identifier instantanément les donneurs compatibles les plus proches et les alerter par SMS.
                </p>

                <div style="display:flex;flex-wrap:wrap;gap:14px;margin-bottom:30px;">
                    <a href="{{ route('register') }}" class="btn btn-vital btn-lg">
                        <span>🩸</span>
                        <span>Je m'engage comme Donneur</span>
                    </a>
                    <a href="{{ route('demandes.create') }}" class="btn btn-secondary btn-lg" style="background:rgba(255,255,255,0.1);color:#fff;border-color:rgba(255,255,255,0.2);">
                        <span>🚨</span>
                        <span>Créer un Besoin Urgent</span>
                    </a>
                </div>

                <!-- Indicateurs clés -->
                <div style="display:flex;flex-wrap:wrap;gap:24px;border-top:1px solid rgba(255,255,255,0.1);padding-top:20px;">
                    <div>
                        <div style="font-size:1.75rem;font-weight:800;color:#fff;">{{ $totalDonneurs }}</div>
                        <div style="font-size:0.8rem;color:#94a3b8;text-transform:uppercase;">Donneurs Enregistrés</div>
                    </div>
                    <div>
                        <div style="font-size:1.75rem;font-weight:800;color:#34d399;">{{ $totalDons }}</div>
                        <div style="font-size:0.8rem;color:#94a3b8;text-transform:uppercase;">Dons Effectués</div>
                    </div>
                    <div>
                        <div style="font-size:1.75rem;font-weight:800;color:#f87171;">< 15 min</div>
                        <div style="font-size:0.8rem;color:#94a3b8;text-transform:uppercase;">Délai Réponse Moyen</div>
                    </div>
                </div>
            </div>

            <!-- Carte Interactive Hématologique de droite -->
            <div>
                <div class="card" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.15);backdrop-filter:blur(16px);color:#fff;">
                    <div class="card-header" style="background:rgba(0,0,0,0.2);border-bottom:1px solid rgba(255,255,255,0.1);">
                        <div class="card-title" style="color:#fff;font-size:1rem;">
                            <span class="pulse-dot"></span>
                            Urgences Transfusionnelles en Direct
                        </div>
                        <span class="badge-vitale" style="background:#450a0a;color:#fca5a5;border-color:#7f1d1d;">TEMPS RÉEL</span>
                    </div>

                    <div class="card-body">
                        @if($urgencesActives->count() > 0)
                            <div style="display:flex;flex-direction:column;gap:12px;">
                                @foreach($urgencesActives as $u)
                                    <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                                        <div style="display:flex;align-items:center;gap:12px;">
                                            <span class="blood-badge filled" style="font-size:1.1rem;padding:4px 10px;">
                                                {{ $u->groupe_sanguin_recherche }}
                                            </span>
                                            <div>
                                                <div style="font-weight:700;font-size:0.92rem;color:#fff;">{{ $u->localisation }}</div>
                                                <div style="font-size:0.75rem;color:#94a3b8;">
                                                    Besoin de {{ $u->quantite }} poche(s) • {{ $u->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                        <a href="{{ route('demandes.show', $u->id) }}" class="btn btn-vital btn-sm" style="font-size:0.75rem;">
                                            Voir le besoin
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div style="text-align:center;padding:20px;color:#94a3b8;">
                                Aucune urgence vitale critique actuellement enregistrée.
                            </div>
                        @endif

                        <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:space-between;font-size:0.8rem;color:#cbd5e1;">
                            <span>Besoin immédiat dans votre établissement ?</span>
                            <a href="{{ route('demandes.create') }}" style="color:#f87171;font-weight:700;text-decoration:underline;">
                                Soumettre une alerte &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Simulateur Interactif de Compatibilité Sanguine (Natif & Éducatif) -->
<section style="padding:60px 0;background:#ffffff;border-bottom:1px solid var(--slate-200);">
    <div class="container">
        <div style="text-align:center;max-width:700px;margin:0 auto 40px;">
            <span class="badge-info" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">
                Outil Interactif Médical
            </span>
            <h2 style="font-size:2rem;font-weight:800;color:var(--slate-900);margin-top:8px;">
                Simulateur de Compatibilité Sanguine ABO/Rh
            </h2>
            <p style="color:var(--slate-600);margin-top:8px;">
                Vérifiez instantanément les règles immunologiques strictes appliquées par le moteur de calcul d'intelligence artificielle de MEKILINK.
            </p>
        </div>

        <div style="max-width:850px;margin:0 auto;" class="card">
            <div class="card-body" style="padding:30px;">
                <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:20px;align-items:center;margin-bottom:24px;">
                    <div>
                        <label class="form-label" style="text-align:center;">🩸 Groupe du Donneur</label>
                        <select id="calc-donneur" class="form-select" style="font-size:1.1rem;font-weight:700;text-align:center;">
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

                    <div style="font-size:1.8rem;color:var(--slate-400);text-align:center;">
                        ➔
                    </div>

                    <div>
                        <label class="form-label" style="text-align:center;">🏥 Groupe du Receveur (Demandeur)</label>
                        <select id="calc-receveur" class="form-select" style="font-size:1.1rem;font-weight:700;text-align:center;">
                            <option value="O-">O-</option>
                            <option value="O+">O+</option>
                            <option value="A-">A-</option>
                            <option value="A+" selected>A+</option>
                            <option value="B-">B-</option>
                            <option value="B+">B+</option>
                            <option value="AB-">AB-</option>
                            <option value="AB+">AB+</option>
                        </select>
                    </div>
                </div>

                <!-- Boîte de Résultat Dynamique -->
                <div id="calc-result" style="padding:20px;border-radius:var(--radius-md);border:2px solid #86efac;background:#f0fdf4;transition:var(--transition);">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-weight:800;font-size:1rem;color:var(--slate-900);">Diagnostic Immunologique :</span>
                        <span id="calc-badge" class="badge-success">COMPATIBLE</span>
                    </div>
                    <p id="calc-text" style="color:var(--slate-700);font-size:0.95rem;margin:0;">
                        Transfusion isogroupe parfaite (A+ vers A+). Tolérance immunologique optimale.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Comment Fonctionne le Moteur Intelligent MEKILINK -->
<section style="padding:60px 0;background:var(--slate-50);">
    <div class="container">
        <div style="text-align:center;max-width:700px;margin:0 auto 50px;">
            <h2 style="font-size:2rem;font-weight:800;color:var(--slate-900);">
                Le Cycle de Vie d'un Don Intelligent
            </h2>
            <p style="color:var(--slate-600);margin-top:8px;">
                Un processus automatisé et sécurisé pour éliminer l'attente fatale lors d'un besoin de sang.
            </p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:24px;">
            <!-- Étape 1 -->
            <div class="card" style="padding:24px;text-align:center;">
                <div style="width:48px;height:48px;border-radius:12px;background:#fee2e2;color:#dc2626;font-size:1.4rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-weight:800;">
                    1
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">Expression du Besoin</h3>
                <p style="font-size:0.88rem;color:var(--slate-600);line-height:1.5;">
                    L'hôpital ou le demandeur crée une demande en précisant le groupe sanguin, le nombre de poches et le niveau d'urgence.
                </p>
            </div>

            <!-- Étape 2 -->
            <div class="card" style="padding:24px;text-align:center;">
                <div style="width:48px;height:48px;border-radius:12px;background:#fef3c7;color:#d97706;font-size:1.4rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-weight:800;">
                    2
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">Scoring IA & Proximité</h3>
                <p style="font-size:0.88rem;color:var(--slate-600);line-height:1.5;">
                    L'algorithme IA croise compatibilité immunologique, délai médical (>=56j), géolocalisation et calcule le score de pertinence.
                </p>
            </div>

            <!-- Étape 3 -->
            <div class="card" style="padding:24px;text-align:center;">
                <div style="width:48px;height:48px;border-radius:12px;background:#dbeafe;color:#2563eb;font-size:1.4rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-weight:800;">
                    3
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">Alerte SMS Twilio</h3>
                <p style="font-size:0.88rem;color:var(--slate-600);line-height:1.5;">
                    Les donneurs les plus pertinents reçoivent une notification SMS instantanée avec lien pour accepter ou décliner en 1 clic.
                </p>
            </div>

            <!-- Étape 4 -->
            <div class="card" style="padding:24px;text-align:center;">
                <div style="width:48px;height:48px;border-radius:12px;background:#d1fae5;color:#059669;font-size:1.4rem;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-weight:800;">
                    4
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px;">Validation Médicale</h3>
                <p style="font-size:0.88rem;color:var(--slate-600);line-height:1.5;">
                    Le don est effectué au centre de transfusion le plus proche, suivi par l'administrateur qui valide le rapport médical.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Aperçu Cartographique des Centres de Don -->
<section style="padding:60px 0;background:#ffffff;">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
            <div>
                <h2 style="font-size:1.75rem;font-weight:800;color:var(--slate-900);">
                    Centres de Don & Banques de Sang Référencés
                </h2>
                <p style="color:var(--slate-600);font-size:0.92rem;margin-top:4px;">
                    Localisez le centre de collecte le plus proche pour effectuer votre don ou orienter un receveur.
                </p>
            </div>
            <a href="{{ route('centres.index') }}" class="btn btn-secondary">
                <span>🗺️</span>
                <span>Ouvrir la Carte Interactive Plein Écran</span>
            </a>
        </div>

        <div class="grid-4">
            @foreach($centres as $c)
                <div class="card" style="padding:18px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;">
                        <span style="font-size:1.3rem;">🏥</span>
                        <h4 style="font-size:0.95rem;font-weight:700;color:var(--slate-900);line-height:1.3;">
                            {{ $c->nom }}
                        </h4>
                    </div>
                    <p style="font-size:0.82rem;color:var(--slate-600);margin-bottom:6px;">
                        📍 {{ $c->adresse }}, {{ $c->ville }}
                    </p>
                    <p style="font-size:0.8rem;color:var(--slate-500);margin-bottom:12px;">
                        ⏰ {{ $c->horaires }}
                    </p>
                    <a href="{{ route('centres.show', $c->id) }}" class="btn btn-secondary btn-sm" style="width:100%;">
                        Détails & Itinéraire
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
