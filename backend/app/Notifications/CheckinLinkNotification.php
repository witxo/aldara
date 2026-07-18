<?php

namespace App\Notifications;

use App\Domains\Reservation\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CheckinLinkNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Reservation $reservation,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('public.checkin.show', ['token' => $this->reservation->checkin_token], true);

        return (new MailMessage)
            ->subject('Check-in online - ' . config('app.name'))
            ->greeting('Hola ' . $this->reservation->guest_name)
            ->line('Su reserva está confirmada. Complete el check-in online antes de su llegada.')
            ->line("Alojamiento: {$this->reservation->property->name}")
            ->line("Entrada: {$this->reservation->checkin_date->format('d/m/Y')}")
            ->line("Salida: {$this->reservation->checkout_date->format('d/m/Y')}")
            ->action('Hacer check-in', $url)
            ->line('Este enlace expirará en ' . config('checkin.token_expiry_hours') . ' horas.')
            ->salutation('Atentamente, ' . config('app.name'));
    }
}
