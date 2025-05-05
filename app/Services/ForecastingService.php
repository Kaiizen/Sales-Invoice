<?php

namespace App\Services;

use App\Product;
use App\ProductForecast;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForecastingService
{
    /**
     * Generate forecasts for all active products
     *
     * @return array Array of created forecasts
     */
    public function generateForecasts()
    {
        $products = Product::where('is_active', true)->get();
        $forecasts = [];
        
        foreach ($products as $product) {
            try {
                $forecast = $this->generateForecastForProduct($product);
                
                if ($forecast) {
                    $forecasts[] = $forecast;
                }
            } catch (\Exception $e) {
                Log::error("Error generating forecast for product {$product->id}: " . $e->getMessage());
            }
        }
        
        return $forecasts;
    }
    
    /**
     * Generate a forecast for a specific product
     *
     * @param Product $product
     * @return ProductForecast|null
     */
    public function generateForecastForProduct(Product $product)
    {
        // Get historical sales data for the past year
        $salesData = $this->getHistoricalSalesData($product);
        
        if (empty($salesData)) {
            // Not enough data to generate a forecast
            return null;
        }
        
        // In a real implementation, this would use a machine learning model
        // For this example, we'll use a simple moving average
        $predictedDemand = $this->calculatePredictedDemand($salesData);
        $confidenceLevel = $this->calculateConfidenceLevel($salesData);
        $recommendedStockLevel = $this->calculateRecommendedStockLevel($predictedDemand, $confidenceLevel, $product);
        
        // Create the forecast
        return ProductForecast::create([
            'product_id' => $product->id,
            'forecast_date' => Carbon::tomorrow(),
            'predicted_demand' => $predictedDemand,
            'confidence_level' => $confidenceLevel,
            'factors_considered' => json_encode([
                'historical_sales' => true,
                'seasonality' => false,
                'promotions' => false,
                'market_trends' => false
            ]),
            'recommended_stock_level' => $recommendedStockLevel
        ]);
    }
    
    /**
     * Get historical sales data for a product
     *
     * @param Product $product
     * @return array
     */
    protected function getHistoricalSalesData(Product $product)
    {
        // Get daily sales for the past year
        $startDate = Carbon::now()->subYear();
        
        $dailySales = Sale::where('product_id', $product->id)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();
            
        return $dailySales;
    }
    
    /**
     * Calculate predicted demand based on historical data
     *
     * @param array $salesData
     * @return int
     */
    protected function calculatePredictedDemand(array $salesData)
    {
        // In a real implementation, this would use a more sophisticated algorithm
        // For this example, we'll use a simple average of the last 30 days
        
        $recentSales = array_slice($salesData, -30, 30, true);
        
        if (empty($recentSales)) {
            return 0;
        }
        
        $totalQuantity = array_sum(array_column($recentSales, 'total_quantity'));
        $averageDailyDemand = $totalQuantity / count($recentSales);
        
        // Predict for the next 7 days
        return ceil($averageDailyDemand * 7);
    }
    
    /**
     * Calculate confidence level for the prediction
     *
     * @param array $salesData
     * @return float
     */
    protected function calculateConfidenceLevel(array $salesData)
    {
        // In a real implementation, this would be based on statistical measures
        // For this example, we'll use a simple heuristic based on data volume
        
        $dataPoints = count($salesData);
        
        if ($dataPoints < 30) {
            return 50.0; // Low confidence with limited data
        } elseif ($dataPoints < 90) {
            return 70.0; // Medium confidence
        } else {
            return 85.0; // High confidence with lots of data
        }
    }
    
    /**
     * Calculate recommended stock level based on predicted demand
     *
     * @param int $predictedDemand
     * @param float $confidenceLevel
     * @param Product $product
     * @return int
     */
    protected function calculateRecommendedStockLevel($predictedDemand, $confidenceLevel, Product $product)
    {
        // Add safety stock based on confidence level
        // Lower confidence = higher safety stock
        $safetyFactor = (100 - $confidenceLevel) / 100 + 1;
        
        // Base recommendation on predicted demand plus safety stock
        $recommendedLevel = ceil($predictedDemand * $safetyFactor);
        
        // Ensure it's at least the minimum stock level
        return max($recommendedLevel, $product->minimum_stock);
    }
    
    /**
     * Get the recommended stock level for a product
     *
     * @param Product $product
     * @return int
     */
    public function getRecommendedStockLevel(Product $product)
    {
        $forecast = ProductForecast::where('product_id', $product->id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($forecast) {
            return $forecast->recommended_stock_level;
        }
        
        // Default to current minimum stock if no forecast
        return $product->minimum_stock;
    }
    
    /**
     * Evaluate forecast accuracy by comparing with actual sales
     *
     * @param ProductForecast $forecast
     * @return float|null
     */
    public function evaluateForecastAccuracy(ProductForecast $forecast)
    {
        // Only evaluate past forecasts
        if (!$forecast->forecast_date->isPast()) {
            return null;
        }
        
        // Get actual sales for the forecast date
        $actualSales = Sale::where('product_id', $forecast->product_id)
            ->whereDate('created_at', $forecast->forecast_date)
            ->sum('quantity');
            
        // Calculate accuracy as a percentage (100% - error percentage)
        if ($forecast->predicted_demand == 0) {
            return $actualSales == 0 ? 100 : 0;
        }
        
        $errorPercentage = abs($actualSales - $forecast->predicted_demand) / $forecast->predicted_demand * 100;
        return max(0, 100 - $errorPercentage);
    }
}