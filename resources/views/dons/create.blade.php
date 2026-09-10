@extends('layouts.app')

@section('title', 'Enregistrer un Don de Sang - MEKILINK')

@section('content')
<div class="container" style="max-width:740px;padding-top:20px;padding-bottom:60px;">
    <div style="margin-bottom:16px;">
        <a href="{{ route('dons.index') }}" class="btn btn-secondary btn-sm">
            &larr; Retour au registre des dons
        </a>
    </div>

    <div class="card" style="box-shadow:var(--shadow-lg);">
        <div class="card-header" style="background:#f8fafc;padding:22px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:1.6rem;">🩸</span>
                <div>
                    <h1 style="font-size:1.4rem;font-weight:800;color:var(--slate-900);margin:0;">
                        Enregistrement d'un Prélèvement de Sang
                    </h1>
                    <p style="font-size:0.85rem;color:var(--slate-600);margin:2px 0 0;">
                        Consigne le don, met à jour l'éligibilité du donneur et génère le rapport médical à valider.
                    </p>
                </div>
            </div>
        </div>

        <div class="card-body" style="padding:26px;">
            <form method="POST" action="{{ route('dons.store') }}">
                @csrf

                <!-- Sélection Donneur -->
                <div class="form-group">
                    <label class="form-label" for="donneur_id">Donneur de Sang <span style="color:var(--blood-arterial);">*</span></label>
                    <select id="donneur_id" name="donneur_id" class="form-select" required onchange="updateDonorBloodType(this)">
                        <option value="">Sélectionner un donneur...</option>
                        @foreach($donneurs as $d)
                            <option value="{{ $d->id }}" 
                                    data-groupe="{{ $d->groupe_sanguin }}"
                                    {{ (old('donneur_id', $selectedDonneurId) == $d->id) ? 'selected' : '' }}>
                                {{ $d->user ? $d->user->nom_complet : 'Donneur' }} — Groupe {{ $d->groupe_sanguin }} ({{ $d->localisation ?? 'Yaoundé' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="groupe_sanguin">Groupe Sanguin Confirmé <span style="color:var(--blood-arterial);">*</span></label>
                        <select id="groupe_sanguin" name="groupe_sanguin" class="form-select" required style="font-weight:700;">
                            @foreach(config('mekilink.groupes_sanguins') as $grp)
                                <option value="{{ $grp }}">{{ $grp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="quantite_ml">Volume Prélevé (mL) <span style="color:var(--blood-arterial);">*</span></label>
                        <input type="number" id="quantite_ml" name="quantite_ml" class="form-control" value="{{ old('quantite_ml', 450) }}" min="200" max="600" required>
                        <span class="form-hint">Standard adulte : 450 mL de sang total.</span>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="centre_don_id">Centre de Collecte <span style="color:var(--blood-arterial);">*</span></label>
                        <select id="centre_don_id" name="centre_don_id" class="form-select" required>
                            @foreach($centres as $c)
                                <option value="{{ $c->id }}">{{ $c->nom }} ({{ $c->ville }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="date_don">Date & Heure du Don <span style="color:var(--blood-arterial);">*</span></label>
                        <input type="datetime-local" id="date_don" name="date_don" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>

                <!-- Demande Associée (Optionnel) -->
                <div class="form-group">
                    <label class="form-label" for="demande_sang_id">Rattacher à une Demande Transfusionnelle (Optionnel)</label>
                    <select id="demande_sang_id" name="demande_sang_id" class="form-select">
                        <option value="">Aucune (Don bénévole pour la réserve générale)</option>
                        @foreach($demandes as $dem)
                            <option value="{{ $dem->id }}" {{ (old('demande_sang_id', $selectedDemandeId) == $dem->id) ? 'selected' : '' }}>
                                #DEM-{{ $dem->id }} : {{ $dem->groupe_sanguin_recherche }} pour {{ $dem->localisation }} (Urgence {{ $dem->urgence }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="statut">Statut du Prélèvement <span style="color:var(--blood-arterial);">*</span></label>
                    <select id="statut" name="statut" class="form-select">
                        <option value="effectue" selected>Effectué (Transmis pour validation médicale)</option>
                        <option value="planifie">Planifié (Rendez-vous futur)</option>
                        <option value="valide">Validé directement</option>
                    </select>
                </div>

                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:var(--radius-sm);padding:14px;margin-bottom:20px;font-size:0.85rem;color:#166534;">
                    ℹ️ L'enregistrement du don déclenche automatiquement la création d'un <strong>Rapport Médical</strong> (sérologie, hémoglobine) soumis à la validation de l'administrateur.
                </div>

                <button type="submit" class="btn btn-vital btn-lg" style="width:100%;">
                    Enregistrer le Don & Mettre à Jour le Dossier
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateDonorBloodType(select) {
    const selectedOption = select.options[select.selectedIndex];
    const grp = selectedOption.getAttribute('data-groupe');
    if (grp) {
        document.getElementById('groupe_sanguin').value = grp;
    }
}
</script>
@endpush
@endsection
