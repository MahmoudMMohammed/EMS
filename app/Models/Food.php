<?php

namespace App\Models;

use App\Helpers\CurrencyConverterScraper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends Model
{
    use HasFactory,SoftDeletes;
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
        'deleted_at'

    ];
    public function category()
    {
        return $this->belongsTo(FoodCategory::class ,'food_category_id' , 'id');
    }
    public function cartItems()
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }
    public function favoriteItems()
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function getPictureAttribute($value): string
    {
        return env('APP_URL') . '/' . $value;
    }

    public function getPriceAttribute($value)
    {
        $userPreferredCurrency = auth()->user()->profile->preferred_currency;

        $convertedPrice = CurrencyConverterScraper::convert($value, $userPreferredCurrency);

        return number_format($convertedPrice, 2) . ' ' . $userPreferredCurrency;
    }

    public function getRawPriceAttribute(): float
    {
        return (float) str_replace(',', '', $this->attributes['price']);
    }

}
