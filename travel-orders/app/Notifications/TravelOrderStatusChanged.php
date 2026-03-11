<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TravelOrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public TravelOrder $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'order_id'    => $this->order->id,
            'destination' => $this->order->destination,
            'status'      => $this->order->status,
            'message'     => "Seu pedido para {$this->order->destination} foi {$this->order->status}.",
        ];
    }
}
