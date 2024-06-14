<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drink extends Model
{
    use HasFactory;
    protected $table = 'drinks';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string, double>
     */

    protected $fillable = [
        'name',
        'price',
        'drink_category_id',
        'picture',
        'description',
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
    public function category()
    {
        return $this->belongsTo(DrinkCategory::class ,'drink_category_id' , 'id');
    }
    public function cartItems()
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }
    public function favoriteItems()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function getPictureAttribute($value)
    {
        return env('APP_URL') . '/' . $value;
    }

    public function getPriceAttribute ($value)
    {
        return number_format($value , 2 , '.' , ',') . "S.P" ;
    }
}
