<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function getConnectionName()
    {
        // Always use tenant connection for products
        return 'tenant';
    }

    protected static function booted()
    {
        static::creating(function ($product) {
            // Ensure we're using the tenant connection
            $product->setConnection('tenant');
        });
        
        static::updating(function ($product) {
            // Ensure we're using the tenant connection
            $product->setConnection('tenant');
        });
    }
}
