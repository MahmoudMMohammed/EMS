<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string, double>
     */

    protected $fillable = [
        'quantity',
        'cart_id',
        'itemable_id',
        'itemable_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
    protected static function newFactory(): CartItemFactory
    {
        return CartItemFactory::new();
    }
}
