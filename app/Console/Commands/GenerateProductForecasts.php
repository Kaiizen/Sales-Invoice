<?php

namespace App\Console\Commands;

use App\Services\ForecastingService;
use Illuminate\Console\Command;

class GenerateProductForecasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:generate-forecasts {--product_id= : Generate forecast for a specific product}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI-based demand forecasts for products';

    /**
     * The forecasting service.
     *
     * @var ForecastingService
     */
    protected $forecastingService;

    /**
     * Create a new command instance.
     *
     * @param ForecastingService $forecastingService
     * @return void
     */
    public function __construct(ForecastingService $forecastingService)
    {
        parent::__construct();
        $this->forecastingService = $forecastingService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting forecast generation...');

        $productId = $this->option('product_id');

        if ($productId) {
            $this->generateForecastForProduct($productId);
        } else {
            $this->generateForecastsForAllProducts();
        }

        return 0;
    }

    /**
     * Generate forecast for a specific product
     *
     * @param int $productId
     * @return void
     */
    protected function generateForecastForProduct($productId)
    {
        $this->info("Generating forecast for product #{$productId}...");

        try {
            $product = \App\Product::findOrFail($productId);
            $forecast = $this->forecastingService->generateForecastForProduct($product);

            if ($forecast) {
                $this->info("Forecast generated successfully:");
                $this->table(
                    ['ID', 'Product', 'Date', 'Predicted Demand', 'Confidence', 'Recommended Stock'],
                    [[
                        $forecast->id,
                        $product->name,
                        $forecast->forecast_date->format('Y-m-d'),
                        $forecast->predicted_demand,
                        $forecast->confidence_level . '%',
                        $forecast->recommended_stock_level
                    ]]
                );
            } else {
                $this->warn("Could not generate forecast for product #{$productId}. Not enough historical data.");
            }
        } catch (\Exception $e) {
            $this->error("Error generating forecast for product #{$productId}: " . $e->getMessage());
        }
    }

    /**
     * Generate forecasts for all active products
     *
     * @return void
     */
    protected function generateForecastsForAllProducts()
    {
        $this->info("Generating forecasts for all active products...");

        $startTime = microtime(true);
        $forecasts = $this->forecastingService->generateForecasts();
        $endTime = microtime(true);

        $this->info("Generated " . count($forecasts) . " forecasts in " . round($endTime - $startTime, 2) . " seconds.");

        if (count($forecasts) > 0) {
            $tableData = [];

            foreach ($forecasts as $forecast) {
                $tableData[] = [
                    $forecast->id,
                    $forecast->product->name ?? "Product #{$forecast->product_id}",
                    $forecast->forecast_date->format('Y-m-d'),
                    $forecast->predicted_demand,
                    $forecast->confidence_level . '%',
                    $forecast->recommended_stock_level
                ];
            }

            $this->table(
                ['ID', 'Product', 'Date', 'Predicted Demand', 'Confidence', 'Recommended Stock'],
                $tableData
            );
        }
    }
}