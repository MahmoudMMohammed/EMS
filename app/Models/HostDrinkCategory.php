<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostDrinkCategory extends Model
{
    use HasFactory;
    protected $table = 'host_drink_categories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int>
     */

    protected $fillable = [
        'drink_category_id',
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

    public function drinkCategory()
    {
        return $this->belongsTo(DrinkCategory::class, 'drink_category_id', 'id');
    }
    public function host()
    {
        return $this->belongsTo(Host::class, 'host_id', 'id');
    }
}
