<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodCategory extends Model
{
    use HasFactory;
    protected $table = 'food_categories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'category',
        'logo'
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

    public function food()
    {
        return $this->hasMany(Food::class, 'food_category_id', 'id');
    }
    public function hostFoodCategories()
    {
        return $this->hasMany(HostFoodCategory::class, 'food_category_id', 'id');
    }

    public function getLogoAttribute($value)
    {
        return env('APP_URL') . '/' . $value;
    }
}
