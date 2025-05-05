<?php

namespace App\Notifications;

use App\SupplierOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRequiresApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     *
     * @param SupplierOrder $order
     * @return void
     */
    public function __construct(SupplierOrder $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/supplier-orders/' . $this->order->id);

        return (new MailMessage)
            ->subject('Order #' . $this->order->id . ' Requires Approval')
            ->line('A new supplier order requires your approval.')
            ->line('Order #: ' . $this->order->id)
            ->line('Supplier: ' . $this->order->supplier->name)
            ->line('Total Amount: $' . number_format($this->order->total_amount, 2))
            ->line('Items: ' . $this->order->items->count())
            ->action('View Order', $url)
            ->line('Please review and approve or reject this order.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'supplier_id' => $this->order->supplier_id,
            'supplier_name' => $this->order->supplier->name,
            'total_amount' => $this->order->total_amount,
            'items_count' => $this->order->items->count(),
            'is_auto_generated' => $this->order->is_auto_generated,
            'message' => 'Order #' . $this->order->id . ' requires your approval',
            'url' => '/supplier-orders/' . $this->order->id
        ];
    }
}