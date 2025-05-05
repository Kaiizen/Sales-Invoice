<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['invoice_id', 'product_id', 'qty', 'price', 'dis', 'amount'];
    
    protected static function boot()
    {
        parent::boot();
    }
    public function invoice(){
        return $this->belongsTo('App\Invoice');
    }

    public function product(){
        return $this->belongsTo('App\Product');
    }


}
