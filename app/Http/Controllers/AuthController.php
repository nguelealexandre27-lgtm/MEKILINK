<?php

namespace App\Http\Controllers;

use App\Models\Demandeur;
use App\Models\Donneur;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->isActif()) {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte a été suspendu ou désactivé par l\'administrateur.']);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', "Bienvenue, {$user->nom_complet} !");
        }

        return back()->withErrors([
            'email' => 'Identifiants invalides. Veuillez vérifier vos informations.',
        ])->onlyInput('email');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $role = $request->input('role', 'donneur');

        $rules = [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'telephone' => ['required', 'string', 'max:25'],
            'role' => ['required', 'in:donneur,demandeur'],
        ];

        if ($role === 'donneur') {
            $rules['groupe_sanguin'] = ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'];
            $rules['localisation'] = ['nullable', 'string', 'max:150'];
            $rules['age'] = ['nullable', 'integer', 'min:18', 'max:70'];
        } else {
            $rules['type_demandeur'] = ['required', 'string'];
            $rules['nom_etablissement'] = ['nullable', 'string', 'max:150'];
        }

        $validated = $request->validate($rules);

        $user = User::create([
            'name' => "{$validated['prenom']} {$validated['nom']}",
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'],
            'age' => $validated['age'] ?? null,
            'role' => $role,
            'statut' => 'actif',
        ]);

        if ($role === 'donneur') {
            Donneur::create([
                'user_id' => $user->id,
                'groupe_sanguin' => $validated['groupe_sanguin'],
                'disponibilite' => true,
                'localisation' => $validated['localisation'] ?? 'Yaoundé',
                'latitude' => 3.8480,
                'longitude' => 11.5021,
            ]);
        } else {
            Demandeur::create([
                'user_id' => $user->id,
                'type_demandeur' => $validated['type_demandeur'] ?? 'Particulier',
                'nom_etablissement' => $validated['nom_etablissement'] ?? null,
            ]);
        }

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Votre compte a été créé avec succès. Bienvenue sur MEKILINK !');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Vous avez été déconnecté.');
    }

    public function profil(): View
    {
        $user = Auth::user();
        return view('profil', compact('user'));
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:25'],
            'age' => ['nullable', 'integer', 'min:18', 'max:70'],
        ];

        if ($user->isDonneur() && $user->donneur) {
            $rules['groupe_sanguin'] = ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'];
            $rules['localisation'] = ['nullable', 'string', 'max:150'];
        } elseif ($user->isDemandeur() && $user->demandeur) {
            $rules['type_demandeur'] = ['required', 'string'];
            $rules['nom_etablissement'] = ['nullable', 'string', 'max:150'];
        }

        if ($request->filled('password')) {
            $rules['password'] = ['min:6', 'confirmed'];
        }

        $validated = $request->validate($rules);

        $user->nom = $validated['nom'];
        $user->prenom = $validated['prenom'];
        $user->name = "{$validated['prenom']} {$validated['nom']}";
        $user->telephone = $validated['telephone'];
        $user->age = $validated['age'] ?? $user->age;

        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        if ($user->isDonneur() && $user->donneur) {
            $user->donneur->groupe_sanguin = $validated['groupe_sanguin'];
            $user->donneur->localisation = $validated['localisation'] ?? $user->donneur->localisation;
            $user->donneur->save();
        } elseif ($user->isDemandeur() && $user->demandeur) {
            $user->demandeur->type_demandeur = $validated['type_demandeur'];
            $user->demandeur->nom_etablissement = $validated['nom_etablissement'] ?? null;
            $user->demandeur->save();
        }

        return back()->with('success', 'Votre profil a été mis à jour.');
    }
}
