<?php

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'carts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string, double>
     */

    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class ,'user_id' , 'id');
    }
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    protected static function newFactory(): CartFactory
    {
        return CartFactory::new();
    }

}
