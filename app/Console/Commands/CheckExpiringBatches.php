<?php

namespace App\Console\Commands;

use App\ProductBatch;
use App\Notifications\BatchExpiryNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckExpiringBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-expiring-batches 
                            {--days=30 : Number of days to check for expiring batches}
                            {--notify : Send notifications to admins}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for product batches that are expiring soon';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $shouldNotify = $this->option('notify');
        
        $this->info("Checking for batches expiring in the next {$days} days...");
        
        $today = Carbon::today();
        $expiryDate = $today->copy()->addDays($days);
        
        $expiringBatches = ProductBatch::where('expiry_date', '<=', $expiryDate)
            ->where('expiry_date', '>=', $today)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();
            
        $this->info("Found " . $expiringBatches->count() . " batches expiring soon.");
        
        if ($expiringBatches->count() > 0) {
            $tableData = [];
            
            foreach ($expiringBatches as $batch) {
                $daysUntilExpiry = $today->diffInDays($batch->expiry_date);
                
                $tableData[] = [
                    $batch->id,
                    $batch->product->name ?? "Product #{$batch->product_id}",
                    $batch->batch_number,
                    $batch->expiry_date->format('Y-m-d'),
                    $daysUntilExpiry,
                    $batch->quantity,
                    $batch->location ? $batch->location->name : 'Unknown'
                ];
            }
            
            $this->table(
                ['ID', 'Product', 'Batch Number', 'Expiry Date', 'Days Left', 'Quantity', 'Location'],
                $tableData
            );
            
            // Send notifications if requested
            if ($shouldNotify) {
                $this->sendNotifications($expiringBatches);
            }
        }
        
        // Check for already expired batches
        $expiredBatches = ProductBatch::where('expiry_date', '<', $today)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'desc')
            ->get();
            
        if ($expiredBatches->count() > 0) {
            $this->warn("Found " . $expiredBatches->count() . " batches that have already expired!");
            
            $tableData = [];
            
            foreach ($expiredBatches as $batch) {
                $daysExpired = $today->diffInDays($batch->expiry_date);
                
                $tableData[] = [
                    $batch->id,
                    $batch->product->name ?? "Product #{$batch->product_id}",
                    $batch->batch_number,
                    $batch->expiry_date->format('Y-m-d'),
                    $daysExpired,
                    $batch->quantity,
                    $batch->location ? $batch->location->name : 'Unknown'
                ];
            }
            
            $this->table(
                ['ID', 'Product', 'Batch Number', 'Expiry Date', 'Days Expired', 'Quantity', 'Location'],
                $tableData
            );
            
            // Send notifications for expired batches if requested
            if ($shouldNotify) {
                $this->sendExpiredNotifications($expiredBatches);
            }
        }
        
        return 0;
    }
    
    /**
     * Send notifications for expiring batches
     *
     * @param \Illuminate\Database\Eloquent\Collection $batches
     * @return void
     */
    protected function sendNotifications($batches)
    {
        $this->info("Sending notifications for expiring batches...");
        
        // In a real implementation, you would use roles to find inventory managers
        // For now, we'll notify all admin users
        $admins = User::where('is_admin', true)->get();
        
        if ($admins->isEmpty()) {
            $this->warn("No admin users found to notify.");
            return;
        }
        
        // Group batches by expiry date for better notification organization
        $batchesByDate = $batches->groupBy(function ($batch) {
            return $batch->expiry_date->format('Y-m-d');
        });
        
        foreach ($batchesByDate as $date => $dateBatches) {
            $notification = new BatchExpiryNotification($dateBatches, $date);
            Notification::send($admins, $notification);
        }
        
        $this->info("Sent " . $batchesByDate->count() . " notifications to " . $admins->count() . " admins.");
    }
    
    /**
     * Send notifications for expired batches
     *
     * @param \Illuminate\Database\Eloquent\Collection $batches
     * @return void
     */
    protected function sendExpiredNotifications($batches)
    {
        $this->info("Sending notifications for expired batches...");
        
        // In a real implementation, you would use roles to find inventory managers
        // For now, we'll notify all admin users
        $admins = User::where('is_admin', true)->get();
        
        if ($admins->isEmpty()) {
            $this->warn("No admin users found to notify.");
            return;
        }
        
        // Create an urgent notification for expired batches
        $notification = new BatchExpiryNotification($batches, null, true);
        Notification::send($admins, $notification);
        
        $this->info("Sent expired batch notification to " . $admins->count() . " admins.");
    }
}