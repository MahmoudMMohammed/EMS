<?php

namespace App\Models;

use Database\Factories\ProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profiles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string, double>
     */

    protected $fillable = [
        'user_id',
        'phone_number',
        'balance',
        'birth_date',
        'profile_picture',
        'preferred_language',
        'about_me',
        'place_of_residence',
        'gender',
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
    public function user()
    {
        return $this->belongsTo(User::class ,'user_id' , 'id');
    }

    public function getProfilePictureAttribute($value)
    {
        return env('APP_URL') . '/' . $value;
    }
    protected static function newFactory(): ProfileFactory
    {
        return ProfileFactory::new();
    }
}
