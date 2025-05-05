<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class BatchExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The batches that are expiring.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $batches;

    /**
     * The expiry date.
     *
     * @var string|null
     */
    protected $expiryDate;

    /**
     * Whether the batches have already expired.
     *
     * @var bool
     */
    protected $isExpired;

    /**
     * Create a new notification instance.
     *
     * @param \Illuminate\Support\Collection|array $batches
     * @param string|null $expiryDate
     * @param bool $isExpired
     * @return void
     */
    public function __construct($batches, $expiryDate = null, $isExpired = false)
    {
        $this->batches = $batches instanceof Collection ? $batches : collect($batches);
        $this->expiryDate = $expiryDate;
        $this->isExpired = $isExpired;
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
        $mailMessage = new MailMessage;
        
        if ($this->isExpired) {
            $mailMessage->subject('URGENT: Product Batches Have Expired')
                        ->line('The following product batches have expired and should be removed from inventory:');
        } else {
            $mailMessage->subject('Product Batches Expiring Soon: ' . $this->expiryDate)
                        ->line('The following product batches will expire on ' . $this->expiryDate . ':');
        }
        
        // Add each batch to the email
        foreach ($this->batches as $batch) {
            $product = $batch->product;
            $location = $batch->location;
            
            $mailMessage->line('- ' . ($product ? $product->name : 'Unknown Product') . 
                ' (Batch: ' . $batch->batch_number . 
                ', Quantity: ' . $batch->quantity . 
                ', Location: ' . ($location ? $location->name : 'Unknown') . ')');
        }
        
        if ($this->isExpired) {
            $mailMessage->action('View Expired Batches', url('/inventory/expired-batches'))
                        ->line('Please take immediate action to remove these expired items from inventory.');
        } else {
            $mailMessage->action('View Expiring Batches', url('/inventory/expiring-batches'))
                        ->line('Please take appropriate action before these items expire.');
        }
        
        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $batchData = $this->batches->map(function ($batch) {
            return [
                'id' => $batch->id,
                'product_id' => $batch->product_id,
                'product_name' => $batch->product ? $batch->product->name : null,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : null,
                'quantity' => $batch->quantity,
                'location_id' => $batch->location_id,
                'location_name' => $batch->location ? $batch->location->name : null
            ];
        })->toArray();
        
        return [
            'expiry_date' => $this->expiryDate,
            'is_expired' => $this->isExpired,
            'batches' => $batchData,
            'batch_count' => count($batchData),
            'message' => $this->isExpired 
                ? 'URGENT: ' . count($batchData) . ' product batches have expired' 
                : count($batchData) . ' product batches expiring on ' . $this->expiryDate,
            'url' => $this->isExpired ? '/inventory/expired-batches' : '/inventory/expiring-batches'
        ];
    }
}