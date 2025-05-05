<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';
    public $timestamps = false;
    
    protected $fillable = ['invoice_id', 'product_id', 'quantity', 'price', 'discount'];
    
    protected $casts = [
        'quantity' => 'decimal:8,2',
        'price' => 'decimal:8,2',
        'discount' => 'decimal:5,2'
    ];
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}