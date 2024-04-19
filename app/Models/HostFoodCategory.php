<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostFoodCategory extends Model
{
    use HasFactory;
    protected $table = 'host_food_categories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int>
     */

    protected $fillable = [
        'food_category_id',
        'host_id',
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
        return $this->belongsTo(FoodCategory::class, 'food_category_id', 'id');
    }
    public function host()
    {
        return $this->belongsTo(Host::class, 'host_id', 'id');
    }
}
