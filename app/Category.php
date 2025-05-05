<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'stock_type'
    ];
    
    const STOCK_TYPE_QUANTITY = 'quantity';
    const STOCK_TYPE_SQUARE_FEET = 'square_feet';
    
    public function product(){
        return $this->hasMany('App\Product');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    
    /**
     * Check if this category tracks stock by square feet
     *
     * @return bool
     */
    public function tracksBySquareFeet()
    {
        return $this->stock_type === self::STOCK_TYPE_SQUARE_FEET;
    }
    
    /**
     * Check if this category tracks stock by quantity
     *
     * @return bool
     */
    public function tracksByQuantity()
    {
        return $this->stock_type === self::STOCK_TYPE_QUANTITY;
    }
}
