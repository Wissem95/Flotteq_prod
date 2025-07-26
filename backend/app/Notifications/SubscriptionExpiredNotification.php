<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        // Peut ajouter des infos si besoin (nom abonnement, date, etc.)
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre abonnement FlotteQ a expiré')
            ->greeting('Bonjour,')
            ->line('Votre abonnement à FlotteQ est arrivé à expiration.')
            ->line('Merci de le renouveler pour continuer à profiter de nos services.')
            ->salutation("L'équipe FlotteQ");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    // Pour SMS plus tard :
    // public function toNexmo($notifiable) { ... }
}
