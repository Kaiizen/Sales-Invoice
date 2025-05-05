<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\InvoiceItem;

class CustomOrder extends Model
{
    // Product type constants
    public const PRODUCT_BANNER = 'Banner';
    public const PRODUCT_SIGNAGE = 'Signage';
    public const PRODUCT_VEHICLE_WRAP = 'Vehicle Wrap';
    public const PRODUCT_STICKER = 'Sticker';
    public const PRODUCT_OTHER = 'Other';

    public const PRODUCT_TYPES = [
        self::PRODUCT_BANNER,
        self::PRODUCT_SIGNAGE,
        self::PRODUCT_VEHICLE_WRAP,
        self::PRODUCT_STICKER,
        self::PRODUCT_OTHER
    ];

    protected $fillable = [
        'flag_type',
        'size',
        'height',
        'breadth',
        'square_feet',
        'price_per_square_feet',
        'total_price',
        'quantity',
        'stitching',
        'special_instructions',
        'design_file',
        'status',
        'user_id',
        'customer_id',
        'job_type',
        'fabric_type',
        'contact_through',
        'received_by'
    ];

    // Flag to prevent infinite loops when creating invoices
    protected $isCreatingInvoice = false;
    
    // Attributes that should not be saved to the database
    protected $guarded = ['isCreatingInvoice'];
    
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->height && $model->breadth) {
                $model->size = $model->height . ' X ' . $model->breadth;
            }
            
            // Only create invoice when status changes to ready
            if ($model->isDirty('status') && $model->status === self::STATUS_READY) {
                if (!$model->isCreatingInvoice) {
                    // Use the property without saving it to the database
                    $model->isCreatingInvoice = true;
                    $model->createInvoice();
                }
            }
        });
    }

    public function calculateSquareFeet()
    {
        if ($this->height && $this->breadth) {
            $this->square_feet = $this->height * $this->breadth;
            return $this->square_feet;
        }
        return 0;
    }

    public function calculateTotalPrice()
    {
        if ($this->square_feet && $this->price_per_square_feet) {
            $this->total_price = $this->square_feet * $this->price_per_square_feet * $this->quantity;
            return $this->total_price;
        }
        return 0;
    }

    public const STATUS_PENDING = 'Pending';
    public const STATUS_IN_PRODUCTION = 'In Production';
    public const STATUS_READY = 'Ready';
    public const STATUS_DELIVERED = 'Delivered';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PRODUCTION,
        self::STATUS_READY,
        self::STATUS_DELIVERED
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createInvoice()
    {
        return $this->invoice()->create([
            'customer_id' => $this->customer_id,
            'product_type' => $this->flag_type,
            'total' => $this->total_price,
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'custom_order_id' => $this->id
        ]);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function flagDetails()
    {
        return $this->hasMany(FlagDetail::class);
    }
    
    public function invoiceItems()
    {
        return $this->hasManyThrough(InvoiceItem::class, Invoice::class);
    }
}