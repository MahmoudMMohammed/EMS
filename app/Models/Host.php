<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    use HasFactory;
    protected $table = 'hosts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'picture',
        'recommended_for',
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
    public function locations()
    {
        return $this->hasMany(Location::class , 'host_id' , 'id');
    }

    public function mainEventHosts()
    {
        return $this->hasMany(MainEventHost::class, 'host_id', 'id');
    }
    public function hostFoodCategories()
    {
        return $this->hasMany(HostFoodCategory::class, 'host_id', 'id');
    }
    public function hostDrinkCategories()
    {
        return $this->hasMany(HostDrinkCategory::class, 'host_id', 'id');
    }

    public function getPictureAttribute($value)
    {
        return url($value);
    }
}
