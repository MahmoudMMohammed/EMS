<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;
    protected $table = 'food';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string, double>
     */

    protected $fillable = [
        'name',
        'price',
        'food_category_id',
        'picture',
        'description',
        'country_of_origin',
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
    public function foodCategory()
    {
        return $this->belongsTo(FoodCategory::class ,'food_category_id' , 'id');
    }
    public function cartItems()
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }
}
