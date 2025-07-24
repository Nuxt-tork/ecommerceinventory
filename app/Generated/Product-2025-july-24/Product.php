<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [];

    // Add your blueprint logic here

    // Relationships
    
    public function productCategory()
    {
        return $this->belongsTo(\App\Models\ProductCategory::class, 'product_category_id', 'id');
    }

    public function scopeWithProductCategory($query)
    {
        return $query->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id');
    }

    public function productImage()
    {
        return $this->hasMany(\App\Models\ProductImage::class, 'product_id', 'id');
    }

    public function scopeWithProductImage($query)
    {
        return $query->leftJoin('product_images', 'products.id', '=', 'product_images.product_id');
    }

}
