<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomOrderController;
use App\Http\Controllers\FabricRollController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryTrackingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitController;
// use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    // Dashboard
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', CategoryController::class);

    // Units
    Route::resource('units', UnitController::class);

    // Taxes
    Route::resource('taxes', TaxController::class);

    // Products
    Route::resource('products', ProductController::class);
    Route::get('/products/import-export', [ProductController::class, 'importExport'])->name('products.import-export');
    Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/products/fabric', [ProductController::class, 'fabricProducts'])->name('products.fabric');
    Route::get('/products/{product}/price', [ProductController::class, 'getPrice'])->name('products.price');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/details', [CustomerController::class, 'getDetails'])->name('customers.details');

    // Invoices
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/print/{id}', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/pdf/{id}', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/invoices/email/{id}', [InvoiceController::class, 'email'])->name('invoices.email');
    Route::get('/find-price', [InvoiceController::class, 'findPrice'])->name('findPrice');

    // Sales
    Route::resource('sales', SalesController::class);

    // Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::get('/find-price-purchase', [PurchaseController::class, 'findPricePurchase'])->name('findPricePurchase');
    Route::get('/purchases/{purchase}/details/{detail}/fabric-rolls', [PurchaseController::class, 'getFabricRolls'])->name('purchases.details.fabric-rolls');

    // Custom Orders
    Route::resource('custom-orders', CustomOrderController::class);
    Route::get('/custom-orders/status-summary', [CustomOrderController::class, 'statusSummary'])->name('custom-orders.status-summary');
    Route::get('/custom-orders/flag/create', [CustomOrderController::class, 'createFlagOrder'])->name('custom-orders.create-flag');
    Route::patch('/custom-orders/{custom_order}/status', [CustomOrderController::class, 'updateStatus'])->name('custom-orders.update-status');
    Route::post('/custom-orders/{custom_order}/create-invoice', [CustomOrderController::class, 'createInvoice'])->name('custom-orders.create-invoice');
    
    // Flag Details
    // Route::resource('flag-details', FlagDetailController::class);
    
    // Inventory Tracking
    Route::get('/inventory/tracking', [InventoryTrackingController::class, 'index'])->name('inventory.tracking.index');
    Route::get('/inventory/tracking/fabric', [InventoryTrackingController::class, 'fabricInventory'])->name('inventory.tracking.fabric');
    Route::get('/inventory/tracking/quantity', [InventoryTrackingController::class, 'quantityInventory'])->name('inventory.tracking.quantity');
    Route::get('/inventory/tracking/fabric/{id}', [InventoryTrackingController::class, 'fabricDetail'])->name('inventory.tracking.fabric.detail');
    Route::get('/inventory/tracking/quantity/{id}', [InventoryTrackingController::class, 'quantityDetail'])->name('inventory.tracking.quantity.detail');
    
    // Fabric Rolls
    Route::resource('fabric-rolls', FabricRollController::class);
    Route::post('/fabric-rolls/add-to-product/{product}', [FabricRollController::class, 'addToProduct'])->name('fabric-rolls.add-to-product');
    Route::post('/fabric-rolls/use-fabric/{product}', [FabricRollController::class, 'useFabric'])->name('fabric-rolls.use-fabric');
    Route::post('/fabric-rolls/mark-damaged/{roll}', [FabricRollController::class, 'markDamaged'])->name('fabric-rolls.mark-damaged');
    
    // Simplified Inventory Management
    Route::get('/inventory', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory/adjust/{product}', [InventoryController::class, 'adjustForm'])->name('inventory.adjust');
    Route::post('/inventory/adjust/{product}', [InventoryController::class, 'adjust'])->name('inventory.adjust.post');
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::get('/inventory/out-of-stock', [InventoryController::class, 'outOfStock'])->name('inventory.out-of-stock');
    Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
    
    // Fabric roll management
    Route::get('/inventory/add-fabric-roll/{product}', [InventoryController::class, 'addFabricRollForm'])->name('inventory.add-fabric-roll');
    Route::post('/inventory/add-fabric-roll/{product}', [InventoryController::class, 'addFabricRoll'])->name('inventory.add-fabric-roll.post');
    Route::get('/inventory/use-fabric/{roll}', [InventoryController::class, 'useFabricForm'])->name('inventory.use-fabric');
    Route::post('/inventory/use-fabric/{roll}', [InventoryController::class, 'useFabric'])->name('inventory.use-fabric.post');
    
    // Warehouses and Locations
    // Route::resource('warehouses', WarehouseController::class);
    // Route::resource('inventory-locations', InventoryLocationController::class);
    
    // Reports
    // Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    // Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    // Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
    // Route::get('/reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
    // Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    // Route::get('/reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
    
    // Settings
    // Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    // Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    
    // User Profile
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    
    // Users (Admin only)
    Route::group(['middleware' => ['admin']], function () {
        // Route::resource('users', UserController::class);
    });
    
    // Fabric Roll API
    Route::get('/fabric-roll/product/{product}', function (App\Product $product) {
        $rolls = $product->fabricRolls()->orderBy('status')->orderBy('received_date', 'desc')->get();
        
        $summary = [
            'total_square_feet' => $product->total_square_feet,
            'active_rolls' => $rolls->where('status', 'active')->count(),
            'depleted_rolls' => $rolls->where('status', 'depleted')->count(),
            'damaged_rolls' => $rolls->where('status', 'damaged')->count(),
        ];
        
        return response()->json([
            'product' => $summary,
            'rolls' => $rolls
        ]);
    })->name('fabric-roll.product');
    
    // API Routes
    Route::prefix('api')->group(function () {
        Route::get('/products/{product}/price', [App\Http\Controllers\API\ProductController::class, 'getPrice'])->name('api.products.price');
        Route::get('/products/{product}', [App\Http\Controllers\API\ProductController::class, 'show'])->name('api.products.show');
        
        Route::get('/customers/{customer}', function (App\Customer $customer) {
            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        })->name('api.customers.show');
    });
});
