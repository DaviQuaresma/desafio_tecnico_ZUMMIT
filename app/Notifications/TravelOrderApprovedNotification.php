<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TravelOrder $travelOrder
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pedido de Viagem Aprovado')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Seu pedido de viagem foi aprovado.')
            ->line("**Destino:** {$this->travelOrder->destination}")
            ->line("**Data de Ida:** {$this->travelOrder->departure_date->format('d/m/Y')}")
            ->line("**Data de Volta:** {$this->travelOrder->return_date->format('d/m/Y')}")
            ->line('Obrigado por usar nosso sistema de viagens corporativas!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'destination' => $this->travelOrder->destination,
            'departure_date' => $this->travelOrder->departure_date->format('Y-m-d'),
            'return_date' => $this->travelOrder->return_date->format('Y-m-d'),
            'status' => 'approved',
            'message' => 'Seu pedido de viagem foi aprovado.',
        ];
    }
}
