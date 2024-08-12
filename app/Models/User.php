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
        'fcm_token',
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
    public function events()
    {
        return $this->hasMany(UserEvent::class , 'user_id' , 'id');
    }
    public function receipt()
    {
        return $this->hasMany(Receipt::class , 'user_id' , 'id');
    }
    public function location()
    {
        return $this->hasOne(Location::class , 'user_id' , 'id');
    }
    public function cart()
    {
        return $this->hasOne(Cart::class , 'user_id' , 'id');
    }

    public function searches()
    {
        return $this->hasMany(Search::class , 'user_id' , 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class , 'notifiable_id' , 'id');
    }
}
