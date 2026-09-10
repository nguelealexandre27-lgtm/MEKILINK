@extends('layouts.app')

@section('title', 'Exprimer un Besoin de Sang - MEKILINK')

@section('content')
<div class="container" style="max-width:800px;padding-top:20px;padding-bottom:60px;">
    <div style="margin-bottom:16px;">
        <a href="{{ route('demandeur.dashboard') }}" class="btn btn-secondary btn-sm">
            &larr; Retour à mes demandes
        </a>
    </div>

    <div class="card" style="box-shadow:var(--shadow-lg);">
        <div class="card-header" style="background:linear-gradient(90deg, #1e293b 0%, #450a0a 100%);color:#fff;border-bottom:none;padding:24px;">
            <div>
                <span class="badge-vitale" style="background:rgba(220,38,38,0.25);color:#fca5a5;border-color:rgba(220,38,38,0.5);">
                    <span class="pulse-dot"></span>
                    ALGORITHME D'APPARIEMENT IA
                </span>
                <h1 style="font-size:1.6rem;font-weight:800;color:#fff;margin:8px 0 4px;">
                    Déclarer un Besoin Urgent de Sang
                </h1>
                <p style="font-size:0.88rem;color:#cbd5e1;margin:0;">
                    L'IA calculera instantanément le score immunologique et géographique des donneurs inscrits.
                </p>
            </div>
        </div>

        <div class="card-body" style="padding:28px;">
            <form method="POST" action="{{ route('demandes.store') }}" id="demande-form">
                @csrf

                <!-- Sélection Tactile du Groupe Sanguin Requis -->
                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" style="font-size:1rem;">1. Groupe Sanguin Requis <span style="color:var(--blood-arterial);">*</span></label>
                    <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:10px;">
                        @foreach(config('mekilink.groupes_sanguins') as $grp)
                            <label style="border:2px solid var(--slate-300);background:var(--white);border-radius:var(--radius-sm);padding:12px 6px;display:flex;flex-direction:column;align-items:center;cursor:pointer;transition:var(--transition);" class="grp-radio-label">
                                <input type="radio" name="groupe_sanguin_recherche" value="{{ $grp }}" {{ $loop->first ? 'checked' : '' }} style="display:none;" onchange="highlightGroupSelection(this)">
                                <span class="blood-badge filled sm" style="margin-bottom:4px;">{{ $grp }}</span>
                                <span style="font-size:0.7rem;color:var(--slate-600);text-align:center;">
                                    {{ $grp === 'O-' ? 'Universel' : ($grp === 'AB+' ? 'Receveur Univ.' : 'Groupe ' . $grp) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Niveau d'Urgence Médicale -->
                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" style="font-size:1rem;">2. Degré d'Urgence Médicale <span style="color:var(--blood-arterial);">*</span></label>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:10px;">
                        <label style="border:2px solid #dc2626;background:#fef2f2;border-radius:var(--radius-sm);padding:14px 10px;cursor:pointer;display:flex;flex-direction:column;gap:4px;" class="urg-radio-label">
                            <input type="radio" name="urgence" value="vitale" checked style="accent-color:#dc2626;">
                            <strong style="color:#b91c1c;font-size:0.9rem;">⚠️ Vitale Immédiate</strong>
                            <span style="font-size:0.75rem;color:#7f1d1d;">Pronostic vital engagé (< 2h)</span>
                        </label>

                        <label style="border:2px solid var(--slate-300);background:var(--white);border-radius:var(--radius-sm);padding:14px 10px;cursor:pointer;display:flex;flex-direction:column;gap:4px;" class="urg-radio-label">
                            <input type="radio" name="urgence" value="urgente" style="accent-color:#ea580c;">
                            <strong style="color:#c2410c;font-size:0.9rem;">Urgente (< 6h)</strong>
                            <span style="font-size:0.75rem;color:var(--slate-600);">Intervention chirurgicale proche</span>
                        </label>

                        <label style="border:2px solid var(--slate-300);background:var(--white);border-radius:var(--radius-sm);padding:14px 10px;cursor:pointer;display:flex;flex-direction:column;gap:4px;" class="urg-radio-label">
                            <input type="radio" name="urgence" value="moyenne" style="accent-color:#d97706;">
                            <strong style="color:#d97706;font-size:0.9rem;">Moyenne (< 24h)</strong>
                            <span style="font-size:0.75rem;color:var(--slate-600);">Transfusion programmée</span>
                        </label>

                        <label style="border:2px solid var(--slate-300);background:var(--white);border-radius:var(--radius-sm);padding:14px 10px;cursor:pointer;display:flex;flex-direction:column;gap:4px;" class="urg-radio-label">
                            <input type="radio" name="urgence" value="faible" style="accent-color:#2563eb;">
                            <strong style="color:#2563eb;font-size:0.9rem;">Programmée / Stock</strong>
                            <span style="font-size:0.75rem;color:var(--slate-600);">Renouvellement de réserve</span>
                        </label>
                    </div>
                </div>

                <!-- Quantité & Centre Recommandé -->
                <div class="grid-2" style="margin-bottom:20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="quantite">Quantité (Poches de sang) <span style="color:var(--blood-arterial);">*</span></label>
                        <input type="number" id="quantite" name="quantite" class="form-control" value="{{ old('quantite', 2) }}" min="1" max="20" required>
                        <span class="form-hint">1 poche correspond à environ 450 mL de sang total.</span>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" for="centre_don_id">Centre de Prélèvement Préféré</label>
                        <select id="centre_don_id" name="centre_don_id" class="form-select">
                            <option value="">Sélectionner un centre de don...</option>
                            @foreach($centres as $c)
                                <option value="{{ $c->id }}" {{ $loop->first ? 'selected' : '' }}>
                                    {{ $c->nom }} ({{ $c->ville }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Localisation & Contexte Médical -->
                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label" for="localisation">Établissement / Service Demandeur <span style="color:var(--blood-arterial);">*</span></label>
                    <input type="text" id="localisation" name="localisation" class="form-control" value="{{ old('localisation', 'Hôpital Central de Yaoundé - Urgences Bloc A') }}" required placeholder="Ex: Hôpital Général de Yaoundé, Pavillon Chirurgie">
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" for="motif">Motif / Contexte Clinique (Confidentiel)</label>
                    <textarea id="motif" name="motif" rows="3" class="form-control" placeholder="Précisez le contexte (ex: Choc hémorragique, césarienne compliquée, anémie aiguë...)">{{ old('motif') }}</textarea>
                </div>

                <!-- Coordonnées GPS (par défaut Yaoundé centre) -->
                <input type="hidden" name="latitude" value="3.8667">
                <input type="hidden" name="longitude" value="11.5167">

                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius-sm);padding:14px;margin-bottom:24px;display:flex;align-items:center;gap:12px;">
                    <span style="font-size:1.5rem;">📱</span>
                    <p style="margin:0;font-size:0.85rem;color:#7f1d1d;line-height:1.4;">
                        <strong>Alerte automatique Twilio :</strong> Dès la validation, le système déclenchera le moteur d'IA et transmettra un SMS d'alerte aux donneurs prioritaires identifiés.
                    </p>
                </div>

                <button type="submit" class="btn btn-vital btn-lg" style="width:100%;">
                    🚨 Lancer la Recherche Intelligente & Alerter les Donneurs
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function highlightGroupSelection(radio) {
    document.querySelectorAll('.grp-radio-label').forEach(lbl => {
        lbl.style.borderColor = 'var(--slate-300)';
        lbl.style.background = 'var(--white)';
    });
    if (radio.checked) {
        radio.parentElement.style.borderColor = 'var(--blood-primary)';
        radio.parentElement.style.background = 'var(--blood-light)';
    }
}
</script>
@endpush
@endsection
