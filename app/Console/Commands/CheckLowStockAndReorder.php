<?php

namespace App\Console\Commands;

use App\Services\AutoOrderService;
use Illuminate\Console\Command;

class CheckLowStockAndReorder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-and-reorder 
                            {--product_id= : Check and reorder a specific product}
                            {--consolidated : Create consolidated orders by supplier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock products and create auto-reorder requests';

    /**
     * The auto order service.
     *
     * @var AutoOrderService
     */
    protected $autoOrderService;

    /**
     * Create a new command instance.
     *
     * @param AutoOrderService $autoOrderService
     * @return void
     */
    public function __construct(AutoOrderService $autoOrderService)
    {
        parent::__construct();
        $this->autoOrderService = $autoOrderService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking for low stock products...');

        $productId = $this->option('product_id');
        $consolidated = $this->option('consolidated');

        if ($productId) {
            $this->checkAndReorderProduct($productId);
        } elseif ($consolidated) {
            $this->createConsolidatedOrders();
        } else {
            $this->checkAllProducts();
        }

        return 0;
    }

    /**
     * Check and reorder a specific product
     *
     * @param int $productId
     * @return void
     */
    protected function checkAndReorderProduct($productId)
    {
        $this->info("Checking product #{$productId}...");

        try {
            $product = \App\Product::findOrFail($productId);
            
            if (!$product->auto_reorder) {
                $this->warn("Auto-reordering is disabled for product #{$productId}.");
                return;
            }
            
            if (!$product->needsReordering()) {
                $this->info("Product #{$productId} does not need reordering.");
                return;
            }
            
            $order = $this->autoOrderService->createOrder($product);
            
            if ($order) {
                $this->info("Order created successfully:");
                $this->table(
                    ['Order ID', 'Supplier', 'Status', 'Total Amount', 'Items'],
                    [[
                        $order->id,
                        $order->supplier->name ?? "Supplier #{$order->supplier_id}",
                        $order->status,
                        '$' . number_format($order->total_amount, 2),
                        $order->items->count()
                    ]]
                );
                
                $this->table(
                    ['Product', 'Quantity', 'Unit Price', 'Total Price'],
                    $order->items->map(function ($item) {
                        return [
                            $item->product->name ?? "Product #{$item->product_id}",
                            $item->quantity,
                            '$' . number_format($item->unit_price, 2),
                            '$' . number_format($item->total_price, 2)
                        ];
                    })->toArray()
                );
            } else {
                $this->warn("Could not create order for product #{$productId}.");
            }
        } catch (\Exception $e) {
            $this->error("Error checking product #{$productId}: " . $e->getMessage());
        }
    }

    /**
     * Check all products and create orders for those that need reordering
     *
     * @return void
     */
    protected function checkAllProducts()
    {
        $this->info("Checking all products with auto-reordering enabled...");

        $startTime = microtime(true);
        $createdOrders = $this->autoOrderService->checkAllProducts();
        $endTime = microtime(true);

        $this->info("Created " . count($createdOrders) . " orders in " . round($endTime - $startTime, 2) . " seconds.");

        if (count($createdOrders) > 0) {
            $tableData = [];

            foreach ($createdOrders as $order) {
                $tableData[] = [
                    $order->id,
                    $order->supplier->name ?? "Supplier #{$order->supplier_id}",
                    $order->status,
                    '$' . number_format($order->total_amount, 2),
                    $order->items->count()
                ];
            }

            $this->table(
                ['Order ID', 'Supplier', 'Status', 'Total Amount', 'Items'],
                $tableData
            );
        }
    }

    /**
     * Create consolidated orders by supplier
     *
     * @return void
     */
    protected function createConsolidatedOrders()
    {
        $this->info("Creating consolidated orders by supplier...");

        $startTime = microtime(true);
        $createdOrders = $this->autoOrderService->createConsolidatedOrders();
        $endTime = microtime(true);

        $this->info("Created " . count($createdOrders) . " consolidated orders in " . round($endTime - $startTime, 2) . " seconds.");

        if (count($createdOrders) > 0) {
            $tableData = [];

            foreach ($createdOrders as $order) {
                $tableData[] = [
                    $order->id,
                    $order->supplier->name ?? "Supplier #{$order->supplier_id}",
                    $order->status,
                    '$' . number_format($order->total_amount, 2),
                    $order->items->count()
                ];
            }

            $this->table(
                ['Order ID', 'Supplier', 'Status', 'Total Amount', 'Items'],
                $tableData
            );
            
            // Show details of the first order as an example
            if (count($createdOrders) > 0) {
                $firstOrder = $createdOrders[0];
                $this->info("Items in order #{$firstOrder->id}:");
                
                $this->table(
                    ['Product', 'Quantity', 'Unit Price', 'Total Price'],
                    $firstOrder->items->map(function ($item) {
                        return [
                            $item->product->name ?? "Product #{$item->product_id}",
                            $item->quantity,
                            '$' . number_format($item->unit_price, 2),
                            '$' . number_format($item->total_price, 2)
                        ];
                    })->toArray()
                );
            }
        }
    }
}