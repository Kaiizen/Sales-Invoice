<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['customer_id', 'invoice_number', 'date', 'due_date', 'status', 'custom_order_id', 'total', 'product_type'];
    
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function sale()
    {
        return $this->hasMany('App\Sales');
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'invoice_items')
                    ->using(InvoiceItem::class)
                    ->withPivot(['quantity', 'price', 'discount'])
                    ->withTimestamps();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function customOrder()
    {
        return $this->belongsTo(CustomOrder::class);
    }
}
