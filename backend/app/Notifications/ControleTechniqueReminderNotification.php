<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class ControleTechniqueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $vehicle;
    public string $prochainControle;
    public ?string $userEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $vehicle, string $prochainControle, ?string $userEmail = null)
    {
        $this->vehicle = $vehicle;
        $this->prochainControle = $prochainControle;
        $this->userEmail = $userEmail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail']; // Ajouter 'nexmo', 'sms' ou autre canal si besoin
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Contrôle technique à venir')
            ->greeting('Bonjour,')
            ->line("Votre véhicule {$this->vehicle} doit passer le contrôle technique avant le :")
            ->line(Carbon::parse($this->prochainControle)->locale('fr')->isoFormat('LL'))
            ->line('Merci de prendre vos dispositions pour éviter toute sanction.')
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
