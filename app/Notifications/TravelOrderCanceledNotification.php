<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderCanceledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TravelOrder $travelOrder,
        public bool $canceledByOwner = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->canceledByOwner
            ? 'Você cancelou seu pedido de viagem.'
            : 'Seu pedido de viagem foi cancelado.';

        return (new MailMessage)
            ->subject('Pedido de Viagem Cancelado')
            ->greeting("Olá, {$notifiable->name}!")
            ->line($message)
            ->line("**Destino:** {$this->travelOrder->destination}")
            ->line("**Data de Ida:** {$this->travelOrder->departure_date->format('d/m/Y')}")
            ->line("**Data de Volta:** {$this->travelOrder->return_date->format('d/m/Y')}")
            ->line('Se você não solicitou este cancelamento, entre em contato conosco.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'travel_order_id' => $this->travelOrder->id,
            'destination' => $this->travelOrder->destination,
            'departure_date' => $this->travelOrder->departure_date->format('Y-m-d'),
            'return_date' => $this->travelOrder->return_date->format('Y-m-d'),
            'status' => 'canceled',
            'canceled_by_owner' => $this->canceledByOwner,
            'message' => $this->canceledByOwner
                ? 'Você cancelou seu pedido de viagem.'
                : 'Seu pedido de viagem foi cancelado.',
        ];
    }
}
