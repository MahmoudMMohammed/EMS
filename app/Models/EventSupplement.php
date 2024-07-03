<?php

namespace App\Models;

use Database\Factories\EventSupplementsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSupplement extends Model
{
    use HasFactory;
    protected $table = 'event_supplements';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, double>
     */

    protected $fillable = [
        'user_event_id',
        'warehouse_id',
        'food_details',
        'drinks_details',
        'accessories_details',
        'total_price',
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

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'event_supplement_id', 'id');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }
    public function event()
    {
        return $this->belongsTo(UserEvent::class, 'user_event_id', 'id');
    }

    public function getTotalPriceAttribute ($value)
    {
        return number_format($value , 2 , '.' , ',');
    }
    public function getFoodDetailsAttribute ($value)
    {
        return json_decode($value,true);
    }
    public function getDrinksDetailsAttribute ($value)
    {
        return json_decode($value, true);
    }
    public function getAccessoriesDetailsAttribute ($value)
    {
        return json_decode($value, true);
    }
    protected static function newFactory(): EventSupplementsFactory
    {
        return EventSupplementsFactory::new();
    }
}
