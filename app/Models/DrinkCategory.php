<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrinkCategory extends Model
{
    use HasFactory;
    protected $table = 'drink_categories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'category',
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

    public function drinks()
    {
        return $this->hasMany(Drink::class, 'drink_category_id', 'id');
    }
    public function hostDrinkCategories()
    {
        return $this->hasMany(HostDrinkCategory::class, 'drink_category_id', 'id');
    }
}
