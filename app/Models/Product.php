<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity',
        'price',
        'total_value'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total_value' => 'decimal:2'
    ];

    public function calculateTotalValue(): float
    {
        return $this->quantity * $this->price;
    }

    public function scopeOrderedByDate($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
