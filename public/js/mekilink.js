/**
 * MEKILINK - JavaScript Natif Pur & Ergonomie Réactive
 * Conçu pour des interactions médicales d'urgence rapides et fiables.
 */

document.addEventListener('DOMContentLoaded', () => {
    initDisponibiliteToggle();
    initCompatibiliteTester();
    initUrgentCountdowns();
    initLeafletMaps();
    initMobileNav();
});

/**
 * 1. Toggle Tactile de Disponibilité Donneur (Asynchrone 1-Clic)
 */
function initDisponibiliteToggle() {
    const toggleBtn = document.getElementById('btn-toggle-dispo');
    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const url = toggleBtn.getAttribute('data-url');
        const statusCard = document.getElementById('dispo-card');
        const statusLabel = document.getElementById('dispo-label');

        toggleBtn.disabled = true;
        toggleBtn.style.opacity = '0.6';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({}),
            });

            const data = await response.json();

            if (data.success) {
                if (data.disponibilite) {
                    toggleBtn.className = 'toggle-switch-btn is-active';
                    toggleBtn.innerHTML = `
                        <span class="pulse-dot" style="background:#fff;"></span>
                        <span>DISPONIBLE</span>
                    `;
                    if (statusCard) statusCard.classList.add('active');
                    if (statusLabel) statusLabel.textContent = 'Vous êtes actuellement DISPONIBLE pour répondre aux urgences.';
                } else {
                    toggleBtn.className = 'toggle-switch-btn is-inactive';
                    toggleBtn.innerHTML = `
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#64748b;"></span>
                        <span>INDISPONIBLE</span>
                    `;
                    if (statusCard) statusCard.classList.remove('active');
                    if (statusLabel) statusLabel.textContent = 'Vous êtes actuellement INDISPONIBLE. Activez votre statut dès que possible.';
                }

                // Toast notification
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Erreur lors de la mise à jour.', 'error');
            }
        } catch (err) {
            console.error('Erreur toggle disponibilité:', err);
            showToast('Erreur de communication avec le serveur.', 'error');
        } finally {
            toggleBtn.disabled = false;
            toggleBtn.style.opacity = '1';
        }
    });
}

/**
 * 2. Simulateur Interactif de Compatibilité Sanguine (Sans rechargement)
 */
function initCompatibiliteTester() {
    const donorSelect = document.getElementById('calc-donneur');
    const recipientSelect = document.getElementById('calc-receveur');
    const resultBox = document.getElementById('calc-result');
    const resultText = document.getElementById('calc-text');
    const resultBadge = document.getElementById('calc-badge');

    if (!donorSelect || !recipientSelect) return;

    // Matrice de compatibilité locale pour réponse instantanée à 0ms
    const matrix = {
        'O-': ['O-'],
        'O+': ['O-', 'O+'],
        'A-': ['O-', 'A-'],
        'A+': ['O-', 'O+', 'A-', 'A+'],
        'B-': ['O-', 'B-'],
        'B+': ['O-', 'O+', 'B-', 'B+'],
        'AB-': ['O-', 'A-', 'B-', 'AB-'],
        'AB+': ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
    };

    function updateCheck() {
        const d = donorSelect.value;
        const r = recipientSelect.value;
        const isCompatible = (matrix[r] || []).includes(d);

        if (isCompatible) {
            resultBox.style.background = '#f0fdf4';
            resultBox.style.borderColor = '#86efac';
            resultBadge.className = 'badge-success';
            resultBadge.textContent = 'COMPATIBLE';

            if (d === r) {
                resultText.textContent = `Transfusion isogroupe parfaite (${d} vers ${r}). Tolérance immunologique optimale.`;
            } else if (d === 'O-') {
                resultText.textContent = `Donneur universel : Le sang O- ne possède aucun antigène A, B ou Rhésus. Compatible d'urgence pour le receveur ${r}.`;
            } else {
                resultText.textContent = `Compatibilité hétérogroupe validée : Le receveur ${r} tolère les globules rouges du donneur ${d}.`;
            }
        } else {
            resultBox.style.background = '#fff1f2';
            resultBox.style.borderColor = '#fecdd3';
            resultBadge.className = 'badge-danger';
            resultBadge.textContent = 'INCOMPATIBLE';
            resultText.textContent = `Incompatibilité immunologique majeure : Le receveur ${r} possède des anticorps destructeurs contre les antigènes du sang ${d}. Risque d'accident transfusionnel grave.`;
        }
    }

    donorSelect.addEventListener('change', updateCheck);
    recipientSelect.addEventListener('change', updateCheck);
}

/**
 * 3. Compte à Rebours d'Urgence Médicale
 */
function initUrgentCountdowns() {
    const countdownEls = document.querySelectorAll('.urgent-countdown');

    countdownEls.forEach(el => {
        const targetDate = new Date(el.getAttribute('data-target')).getTime();
        if (isNaN(targetDate)) return;

        function update() {
            const now = new Date().getTime();
            const diff = targetDate - now;

            if (diff <= 0) {
                el.innerHTML = '<span style="color:#be123c;font-weight:700;">DÉLAI EXPIRÉ</span>';
                return;
            }

            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            el.innerHTML = `<span>${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s</span>`;
        }

        update();
        setInterval(update, 1000);
    });
}

/**
 * 4. Cartographie Leaflet & OpenStreetMap (Centres de don)
 */
function initLeafletMaps() {
    const mapEl = document.getElementById('osm-map');
    if (!mapEl || typeof L === 'undefined') return;

    // Coordonnées par défaut : Yaoundé, Cameroun
    const defaultLat = parseFloat(mapEl.getAttribute('data-lat')) || 3.8667;
    const defaultLng = parseFloat(mapEl.getAttribute('data-lng')) || 11.5167;
    const zoomLevel = parseInt(mapEl.getAttribute('data-zoom')) || 13;

    const map = L.map('osm-map').setView([defaultLat, defaultLng], zoomLevel);

    // Tuiles OpenStreetMap libres
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributeurs | MEKILINK',
        maxZoom: 19,
    }).addTo(map);

    // Icône personnalisée Centre de Don (Croix / Goutte de sang)
    const centerIcon = L.divIcon({
        className: 'custom-map-pin',
        html: `
            <div style="background:#991b1b;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.3);border:2px solid #fff;font-size:16px;">
                🏥
            </div>
        `,
        iconSize: [34, 34],
        iconAnchor: [17, 17],
    });

    // Charger les centres depuis l'API interne ou un tableau injecté
    const centresJsonEl = document.getElementById('centres-data');
    if (centresJsonEl) {
        try {
            const centres = JSON.parse(centresJsonEl.textContent);
            const markers = [];

            centres.forEach(c => {
                if (c.latitude && c.longitude) {
                    const marker = L.marker([c.latitude, c.longitude], { icon: centerIcon }).addTo(map);
                    marker.bindPopup(`
                        <div style="padding:6px 2px;min-width:200px;">
                            <h4 style="margin:0 0 6px 0;color:#991b1b;font-size:14px;font-weight:700;">${c.nom}</h4>
                            <p style="margin:0 0 4px 0;font-size:12px;color:#334155;">📍 ${c.adresse}, ${c.ville}</p>
                            <p style="margin:0 0 4px 0;font-size:12px;color:#475569;">⏰ ${c.horaires}</p>
                            ${c.telephone ? `<p style="margin:0 0 8px 0;font-size:12px;font-weight:600;color:#0f172a;">📞 ${c.telephone}</p>` : ''}
                            <a href="/centres/${c.id}" style="display:inline-block;padding:4px 8px;background:#991b1b;color:#fff;font-size:11px;font-weight:600;border-radius:4px;text-decoration:none;">Voir fiche centre</a>
                        </div>
                    `);
                    markers.push(marker);
                }
            });

            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        } catch (e) {
            console.error('Erreur parsing centres data:', e);
        }
    }
}

/**
 * 5. Menu Mobile & Navigation
 */
function initMobileNav() {
    const toggle = document.getElementById('mobile-menu-toggle');
    const menu = document.getElementById('mobile-nav-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            menu.classList.toggle('is-open');
        });
    }
}

/**
 * Système de Notification Toast Léger & Natif
 */
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bg = (type === 'success') ? '#059669' : ((type === 'error') ? '#dc2626' : '#1e293b');
    toast.style.cssText = `background:${bg};color:#fff;padding:12px 18px;border-radius:8px;font-size:14px;font-weight:600;box-shadow:0 10px 25px rgba(0,0,0,0.15);pointer-events:auto;transition:all 0.3s cubic-bezier(0.16,1,0.3,1);opacity:0;transform:translateY(10px);max-width:360px;`;
    toast.textContent = message;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 4500);
}
