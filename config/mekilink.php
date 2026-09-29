<?php

return [
    'twilio' => [
        'sid' => env('TWILIO_SID', ''),
        'token' => env('TWILIO_AUTH_TOKEN', ''),
        'phone_number' => env('TWILIO_PHONE_NUMBER', ''),
        'enabled' => !empty(env('TWILIO_SID')) && !empty(env('TWILIO_AUTH_TOKEN')),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
        'enabled' => !empty(env('GEMINI_API_KEY')),
    ],

    // Blood groups supported
    'groupes_sanguins' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],

    // Urgency levels
    'urgences' => [
        'vitale' => ['label' => 'Urgence Vitale Immédiate', 'color' => '#dc2626', 'priority' => 1],
        'urgente' => ['label' => 'Urgente (< 6h)', 'color' => '#ea580c', 'priority' => 2],
        'moyenne' => ['label' => 'Moyenne (< 24h)', 'color' => '#d97706', 'priority' => 3],
        'faible' => ['label' => 'Faible / Programmée', 'color' => '#2563eb', 'priority' => 4],
    ],

    // Minimum delay between donations in days (medical standard)
    'delai_don_jours' => 56,
];
