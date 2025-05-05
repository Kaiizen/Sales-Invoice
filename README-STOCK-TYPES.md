# Stock Type System

This system allows for different stock management approaches based on product categories:

- **Flag Fabric Parent Category**: Stock is tracked based on rolls of different square feet
- **All Other Categories**: Stock is tracked based on quantity

## Setup

Run the following command to set up the stock type system:

```bash
php artisan setup:stock-type
```

This will:
1. Add a `stock_type` field to the categories table
2. Create a `fabric_rolls` table for tracking individual fabric rolls
3. Set the Flag Fabric parent category to track by square feet
4. Set all other categories to track by quantity

## Usage

### Managing Flag Fabric Products

Flag fabric products can now be managed by rolls with specific dimensions:

#### Adding a New Fabric Roll

```php
$product = Product::find($productId);
$fabricRoll = $product->addFabricRoll(
    $width,          // Width in inches
    $length,         // Length in inches
    $reference,      // Optional: Purchase reference
    $notes,          // Optional: Notes
    $locationId,     // Optional: Inventory location ID
    $supplierId      // Optional: Supplier ID
);
```

This will:
- Create a new fabric roll record with dimensions and calculated square feet
- Update the product's total square feet
- Increment the product's roll count
- Record an inventory movement

#### Using Fabric from Rolls

```php
$product = Product::find($productId);
$product->useFabricFromRolls(
    $squareFeet,     // Square feet to use
    $reference,      // Optional: Reference (e.g., order)
    $notes           // Optional: Notes
);
```

This will:
- Use fabric from rolls in FIFO order (oldest rolls first)
- Update each roll's remaining square feet
- Mark rolls as depleted when fully used
- Update the product's total square feet
- Record an inventory movement

#### Flag Details

When creating flag details, the system will automatically deduct fabric from rolls:

```php
$flagDetail = FlagDetail::create([
    'product_id' => $productId,
    'height' => $height,
    'breadth' => $breadth,
    // Other fields...
]);

// This will automatically calculate square feet and deduct from fabric rolls
$flagDetail->deductFabricInventory();
```

### Managing Regular Products

Regular products (in categories other than Flag Fabric) continue to be managed by quantity as before.

## Stock Level Calculation

The system calculates stock levels differently based on the category:

- **Flag Fabric Products**: Based on total square feet and alert threshold percentage
- **Regular Products**: Based on current stock and minimum stock

## Inventory Reports

You can get inventory reports for both types of products:

```php
// For flag fabric products
$totalSquareFeet = $product->total_square_feet;
$activeRolls = $product->fabricRolls()->active()->count();
$remainingPercentage = $product->getRemainingSquareFeetPercentageAttribute();

// For regular products
$currentStock = $product->current_stock;
$minimumStock = $product->minimum_stock;
```

## Category Management

You can check a category's stock type:

```php
$category = Category::find($categoryId);

if ($category->tracksBySquareFeet()) {
    // Handle square feet tracking
} else {
    // Handle quantity tracking
}
```

You can also change a category's stock type:

```php
$category->update(['stock_type' => Category::STOCK_TYPE_SQUARE_FEET]);
// or
$category->update(['stock_type' => Category::STOCK_TYPE_QUANTITY]);