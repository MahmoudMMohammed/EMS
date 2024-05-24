<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationPicture extends Model
{
    use HasFactory;
    protected $table = 'location_pictures';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'location_id',
        'picture',
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

    public function location()
    {
        return $this->belongsTo(Location::class , 'location_id' , 'id');
    }

    public function getPictureAttribute($value)
    {
        return url($value);
    }
}
