<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accessory extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'accessories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string, double>
     */

    protected $fillable = [
        'name',
        'price',
        'picture',
        'accessory_category_id',
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
        return $this->belongsTo(AccessoryCategory::class ,'accessory_category_id' , 'id');
    }
    public function warehouseAccessories()
    {
        return $this->hasMany(WarehouseAccessory::class ,'accessory_id' , 'id');
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

    public function getPriceAttribute($value)
    {
        return number_format($value , 2 , '.' , ',') . ' S.P';
    }

    public function getRawPriceAttribute(): float
    {
        return (float) str_replace(',', '', $this->attributes['price']);
    }


}
