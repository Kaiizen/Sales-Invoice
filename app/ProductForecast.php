<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductForecast extends Model
{
    protected $fillable = [
        'product_id',
        'forecast_date',
        'predicted_demand',
        'confidence_level',
        'factors_considered',
        'recommended_stock_level'
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'predicted_demand' => 'integer',
        'confidence_level' => 'decimal:2',
        'factors_considered' => 'json',
        'recommended_stock_level' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Get forecasts for a specific date range
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('forecast_date', [$startDate, $endDate]);
    }

    // Get forecasts with high confidence (above 70% by default)
    public function scopeHighConfidence($query, $threshold = 70)
    {
        return $query->where('confidence_level', '>=', $threshold);
    }

    // Get forecasts with low confidence (below 50% by default)
    public function scopeLowConfidence($query, $threshold = 50)
    {
        return $query->where('confidence_level', '<', $threshold);
    }

    // Get the most recent forecast for a product
    public static function getLatestForProduct($productId)
    {
        return self::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    // Check if the forecast suggests restocking
    public function needsRestocking()
    {
        if (!$this->product) {
            return false;
        }

        return $this->product->current_stock < $this->recommended_stock_level;
    }

    // Calculate the quantity to reorder
    public function getReorderQuantityAttribute()
    {
        if (!$this->product) {
            return 0;
        }

        $difference = $this->recommended_stock_level - $this->product->current_stock;
        return max(0, $difference);
    }

    // Get the forecast accuracy (can be calculated after the forecast date has passed)
    public function getAccuracyAttribute()
    {
        // Only calculate accuracy for past forecasts
        if (!$this->forecast_date->isPast()) {
            return null;
        }

        // Get actual sales for the forecast date
        $actualSales = Sale::where('product_id', $this->product_id)
            ->whereDate('created_at', $this->forecast_date)
            ->sum('quantity');

        // Calculate accuracy as a percentage (100% - error percentage)
        if ($this->predicted_demand == 0) {
            return $actualSales == 0 ? 100 : 0;
        }

        $errorPercentage = abs($actualSales - $this->predicted_demand) / $this->predicted_demand * 100;
        return max(0, 100 - $errorPercentage);
    }

    // Get a human-readable summary of the forecast
    public function getSummaryAttribute()
    {
        $product = $this->product ? $this->product->name : "Product #{$this->product_id}";
        $date = $this->forecast_date->format('Y-m-d');
        $confidence = number_format($this->confidence_level, 0);

        return "Forecast for {$product} on {$date}: {$this->predicted_demand} units (Confidence: {$confidence}%)";
    }
}