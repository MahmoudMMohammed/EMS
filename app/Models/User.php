<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'verified',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'google_id',
        'verified',
        'role',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function profile()
    {
        return $this->hasOne(Profile::class , 'user_id' , 'id');
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class , 'user_id' , 'id');
    }
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class , 'user_id' , 'id');
    }
    public function userEvents()
    {
        return $this->hasMany(UserEvent::class , 'user_id' , 'id');
    }
    public function receipts()
    {
        return $this->hasMany(Receipt::class , 'user_id' , 'id');
    }
    public function reservations()
    {
        return $this->hasMany(Reservation::class , 'user_id' , 'id');
    }
    public function activities()
    {
        return $this->hasMany(UserActivity::class , 'user_id' , 'id');
    }
    public function location()
    {
        return $this->hasOne(Location::class , 'user_id' , 'id');
    }
}
