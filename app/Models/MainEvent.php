<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainEvent extends Model
{
    use HasFactory;
    protected $table = 'main_events';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
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
    public function mainEventHosts()
    {
        return $this->hasMany(MainEventHost::class , 'main_event_id' , 'id');
    }

    public function userEvents() : HasMany
    {
        return $this->hasMany(UserEvent::class , 'main_event_id' , 'id');
    }
}
