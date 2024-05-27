<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserEvent extends Model
{
    use HasFactory;
    protected $table = 'user_events';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'user_id',
        'location_id',
        'date',
        'invitation_type',
        'description',
        'start_time',
        'end_time',
        'num_people_invited',
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

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class, 'user_event_id', 'id');
    }
    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class, 'user_event_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    // Accessor for start_time
    public function getStartTimeAttribute($value): string
    {
        return Carbon::parse($value)->format('h:i A');
    }

    // Accessor for end_time
    public function getEndTimeAttribute($value): string
    {
        return Carbon::parse($value)->format('h:i A');
    }
    protected static function newFactory(): UserEventFactory
    {
        return UserEventFactory::new();
    }

}
