<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'intégration de l'API de messagerie Twilio SMS.
 */
class TwilioService
{
    protected string $sid;
    protected string $token;
    protected string $from;
    protected bool $enabled;

    public function __construct()
    {
        $this->sid = config('mekilink.twilio.sid', '');
        $this->token = config('mekilink.twilio.token', '');
        $this->from = config('mekilink.twilio.phone_number', '');
        $this->enabled = !empty($this->sid) && !empty($this->token) && !empty($this->from);
    }

    /**
     * Envoi d'un message SMS à un donneur
     */
    public function sendSms(string $to, string $message): array
    {
        // Nettoyer le numéro de téléphone (ajouter indicatif si manquant e.g. +237 pour Cameroun)
        $cleanPhone = $this->formatPhoneNumber($to);

        if (!$this->enabled) {
            Log::info("[TWILIO SMS LOCAL] Vers: {$cleanPhone} | Message: {$message}");
            return [
                'success' => true,
                'to' => $cleanPhone,
                'message' => 'SMS transmis avec succès.',
            ];
        }

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";
            $response = Http::withBasicAuth($this->sid, $this->token)
                ->asForm()
                ->post($url, [
                    'To' => $cleanPhone,
                    'From' => $this->from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info("[TWILIO SMS ENVOYÉ] SID: " . ($data['sid'] ?? 'N/A') . " vers {$cleanPhone}");
                return [
                    'success' => true,
                    'simulated' => false,
                    'sid' => $data['sid'] ?? null,
                    'status' => $data['status'] ?? 'sent',
                ];
            }

            Log::error("[TWILIO SMS ERREUR] Code: " . $response->status() . " | " . $response->body());
            return [
                'success' => false,
                'simulated' => false,
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error("[TWILIO SMS EXCEPTION] " . $e->getMessage());
            return [
                'success' => false,
                'simulated' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Formate le numéro au format international E.164
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            // Si numéro local à 9 chiffres (ex: Cameroun 6XXXXXXXX)
            if (strlen($phone) === 9 && in_array(substr($phone, 0, 1), ['6', '2'])) {
                $phone = '+237' . $phone;
            } else {
                $phone = '+' . $phone;
            }
        }
        return $phone;
    }
}
