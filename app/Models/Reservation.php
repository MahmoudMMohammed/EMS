<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $table = 'reservations';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, boolean>
     */

    protected $fillable = [
        'user_id',
        'user_event_id',
        'verified',
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

    public function userActivity()
    {
        return $this->hasOne(UserActivity::class, 'reservation_id', 'id');
    }
    public function userEvent()
    {
        return $this->belongsTo(UserEvent::class, 'user_event_id', 'id');
    }
}
