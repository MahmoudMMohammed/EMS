<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = 'locations';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string, double>
     */

    protected $fillable = [
        'user_id',
        'name',
        'governorate',
        'address',
        'host_id',
        'capacity',
        'open_time',
        'close_time',
        'reservation_price',
        'x_position',
        'y_position',
        'logo',
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

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
    public function host()
    {
        return $this->belongsTo(Host::class, 'host_id','id');
    }
    public function userEvents()
    {
        return $this->hasMany(UserEvent::class, 'location_id','id');
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'location_id','id');
    }
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'location_id','id');
    }
    public function pictures()
    {
        return $this->hasMany(LocationPicture::class, 'location_id','id');
    }

    public function getLogoAttribute($value)
    {
        return env('APP_URL') . '/' . $value;
    }
}
