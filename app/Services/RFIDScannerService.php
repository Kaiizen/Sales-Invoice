<?php

namespace App\Services;

use App\RFIDTag;
use App\InventoryLocation;
use App\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RFIDScannerService
{
    /**
     * Process a single RFID tag scan
     *
     * @param string $tagId
     * @param int $locationId
     * @param int|null $userId
     * @return array|null
     */
    public function processScan($tagId, $locationId, $userId = null)
    {
        try {
            $tag = RFIDTag::where('tag_id', $tagId)->first();
            
            if (!$tag) {
                Log::warning("Unknown RFID tag scanned: {$tagId}");
                return null;
            }
            
            // Update tag location
            $tag->updateLocation($locationId, $userId);
            
            // Get product details
            $product = $tag->product;
            
            if (!$product) {
                Log::warning("RFID tag {$tagId} has no associated product");
                return null;
            }
            
            // Return scan result
            return [
                'tag_id' => $tag->tag_id,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $product->current_stock,
                    'price' => $product->price
                ],
                'location' => [
                    'id' => $locationId,
                    'name' => InventoryLocation::find($locationId)->name ?? 'Unknown'
                ],
                'batch' => $tag->batch ? [
                    'id' => $tag->batch->id,
                    'batch_number' => $tag->batch->batch_number,
                    'expiry_date' => $tag->batch->expiry_date ? $tag->batch->expiry_date->format('Y-m-d') : null
                ] : null,
                'last_scanned_at' => $tag->last_scanned_at->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            Log::error("Error processing RFID scan: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Process multiple RFID tags at once
     *
     * @param array $tagIds
     * @param int $locationId
     * @param int|null $userId
     * @return array
     */
    public function bulkScan(array $tagIds, $locationId, $userId = null)
    {
        $results = [];
        $errors = [];
        
        foreach ($tagIds as $tagId) {
            $result = $this->processScan($tagId, $locationId, $userId);
            
            if ($result) {
                $results[] = $result;
            } else {
                $errors[] = $tagId;
            }
        }
        
        return [
            'success' => count($results),
            'errors' => count($errors),
            'results' => $results,
            'error_tags' => $errors
        ];
    }
    
    /**
     * Perform inventory count using RFID
     *
     * @param array $tagIds
     * @param int $locationId
     * @param int|null $userId
     * @return array
     */
    public function performInventoryCount(array $tagIds, $locationId, $userId = null)
    {
        // Get the location
        $location = InventoryLocation::find($locationId);
        
        if (!$location) {
            return [
                'success' => false,
                'message' => 'Invalid location'
            ];
        }
        
        // Get all tags that should be at this location
        $expectedTags = RFIDTag::where('last_location_id', $locationId)
            ->where('status', RFIDTag::STATUS_ACTIVE)
            ->pluck('tag_id')
            ->toArray();
            
        // Find tags that are missing (expected but not scanned)
        $missingTags = array_diff($expectedTags, $tagIds);
        
        // Find tags that are unexpected (scanned but not expected)
        $unexpectedTags = array_diff($tagIds, $expectedTags);
        
        // Update all scanned tags to this location
        $scannedTags = RFIDTag::whereIn('tag_id', $tagIds)->get();
        
        foreach ($scannedTags as $tag) {
            $tag->updateLocation($locationId, $userId);
        }
        
        // Get product counts
        $productCounts = [];
        
        foreach ($scannedTags as $tag) {
            $productId = $tag->product_id;
            
            if (!isset($productCounts[$productId])) {
                $productCounts[$productId] = [
                    'product_id' => $productId,
                    'product_name' => $tag->product->name ?? 'Unknown',
                    'count' => 0
                ];
            }
            
            $productCounts[$productId]['count']++;
        }
        
        // Compare with expected inventory
        $inventoryDiscrepancies = [];
        
        foreach ($productCounts as $productId => $data) {
            $expectedCount = DB::table('product_inventories')
                ->where('product_id', $productId)
                ->where('location_id', $locationId)
                ->value('quantity') ?? 0;
                
            $difference = $data['count'] - $expectedCount;
            
            if ($difference != 0) {
                $inventoryDiscrepancies[] = [
                    'product_id' => $productId,
                    'product_name' => $data['product_name'],
                    'expected' => $expectedCount,
                    'actual' => $data['count'],
                    'difference' => $difference
                ];
            }
        }
        
        // Return the results
        return [
            'success' => true,
            'location_id' => $locationId,
            'location_name' => $location->name,
            'scanned_tags_count' => count($tagIds),
            'expected_tags_count' => count($expectedTags),
            'missing_tags_count' => count($missingTags),
            'unexpected_tags_count' => count($unexpectedTags),
            'missing_tags' => $missingTags,
            'unexpected_tags' => $unexpectedTags,
            'product_counts' => array_values($productCounts),
            'inventory_discrepancies' => $inventoryDiscrepancies
        ];
    }
    
    /**
     * Register a new RFID tag for a product
     *
     * @param string $tagId
     * @param int $productId
     * @param int|null $batchId
     * @param int|null $locationId
     * @param int|null $userId
     * @return RFIDTag
     */
    public function registerTag($tagId, $productId, $batchId = null, $locationId = null, $userId = null)
    {
        // Check if tag already exists
        $existingTag = RFIDTag::where('tag_id', $tagId)->first();
        
        if ($existingTag) {
            throw new \Exception("RFID tag {$tagId} is already registered");
        }
        
        // Check if product exists
        $product = Product::find($productId);
        
        if (!$product) {
            throw new \Exception("Product with ID {$productId} not found");
        }
        
        // Create the tag
        $tag = RFIDTag::create([
            'tag_id' => $tagId,
            'product_id' => $productId,
            'batch_id' => $batchId,
            'last_location_id' => $locationId,
            'last_scanned_at' => now(),
            'status' => RFIDTag::STATUS_ACTIVE
        ]);
        
        // Log the registration
        if ($userId) {
            // In a real implementation, you would log this activity
        }
        
        return $tag;
    }
    
    /**
     * Find products by RFID tag
     *
     * @param string $tagId
     * @return array|null
     */
    public function findProductByTag($tagId)
    {
        $tag = RFIDTag::where('tag_id', $tagId)->first();
        
        if (!$tag || !$tag->product) {
            return null;
        }
        
        $product = $tag->product;
        
        return [
            'tag_id' => $tag->tag_id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->price,
            'current_stock' => $product->current_stock,
            'location' => $tag->location ? [
                'id' => $tag->location->id,
                'name' => $tag->location->name
            ] : null,
            'batch' => $tag->batch ? [
                'id' => $tag->batch->id,
                'batch_number' => $tag->batch->batch_number,
                'expiry_date' => $tag->batch->expiry_date ? $tag->batch->expiry_date->format('Y-m-d') : null
            ] : null,
            'last_scanned_at' => $tag->last_scanned_at ? $tag->last_scanned_at->format('Y-m-d H:i:s') : null
        ];
    }
}