<?php

namespace App\Http\Controllers;

use App\Models\CentreDon;
use App\Models\DemandeSang;
use App\Models\Don;
use App\Models\Donneur;
use App\Services\BloodCompatibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $totalDonneurs = Donneur::count();
        $totalDons = Don::count();
        $urgencesActives = DemandeSang::with(['centreDon', 'demandeur.user'])
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->where('urgence', 'vitale')
            ->latest()
            ->take(4)
            ->get();

        $centres = CentreDon::take(4)->get();
        $matrix = BloodCompatibilityService::getMatrix();

        return view('welcome', compact('totalDonneurs', 'totalDons', 'urgencesActives', 'centres', 'matrix'));
    }

    public function checkCompatibility(Request $request, \App\Services\AiMatchingService $aiService): JsonResponse
    {
        $donneur = $request->query('donneur', 'O-');
        $receveur = $request->query('receveur', 'A+');
        $useAi = $request->boolean('use_ai', false);

        $isCompatible = BloodCompatibilityService::isCompatible($donneur, $receveur);
        $explanation = BloodCompatibilityService::getExplanation($donneur, $receveur);
        $aiExplanation = null;
        $aiAvailable = true;

        if ($useAi) {
            $aiExplanation = $aiService->expliquerCompatibiliteGemini($donneur, $receveur, $isCompatible);
            $aiAvailable = $aiService->isAiAvailable();
            if ($aiExplanation) {
                $explanation = $aiExplanation;
            }
        }

        return response()->json([
            'donneur' => $donneur,
            'receveur' => $receveur,
            'compatible' => $isCompatible,
            'explication' => $explanation,
            'ai_used' => !empty($aiExplanation),
            'ai_available' => $aiAvailable,
            'ai_unavailable_message' => "L'IA n'est pas disponible pour le moment, veuillez réessayer plus tard.",
            'can_donate_to' => BloodCompatibilityService::getCanDonateTo($donneur),
            'can_receive_from' => BloodCompatibilityService::getCompatibleDonorsFor($receveur),
        ]);
    }
}
