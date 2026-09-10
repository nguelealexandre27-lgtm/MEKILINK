<?php

namespace App\Models;

use App\Services\TwilioService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle Notification correspondant à la classe Notification du diagramme UML.
 */
class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'demande_sang_id',
        'contenu',
        'type',   // SMS_TWILIO, IN_APP, EMAIL
        'statut', // en_attente, envoye, echoue, lu
        'date_envoi',
    ];

    protected function casts(): array
    {
        return [
            'date_envoi' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function demandeSang(): BelongsTo
    {
        return $this->belongsTo(DemandeSang::class);
    }

    // UML Operation: envoyer
    public function envoyer(): bool
    {
        if ($this->type === 'SMS_TWILIO') {
            $user = $this->user;
            if ($user && $user->telephone) {
                $twilio = app(TwilioService::class);
                $result = $twilio->sendSms($user->telephone, $this->contenu);
                $this->statut = $result['success'] ? 'envoye' : 'echoue';
            } else {
                $this->statut = 'echoue';
            }
        } else {
            $this->statut = 'envoye';
        }

        $this->date_envoi = now();
        return $this->save();
    }
}
