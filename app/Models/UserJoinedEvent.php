<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserJoinedEvent extends Model
{
    use HasFactory;
    protected $table = 'user_joined_events';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int>
     */

    protected $fillable = [
        'user_id',
        'user_event_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function event(): BelongsTo
    {
        return $this->belongsTo(UserEvent::class, 'user_event_id', 'id');
    }
}
