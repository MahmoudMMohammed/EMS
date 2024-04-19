<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainEventHost extends Model
{
    use HasFactory;
    protected $table = 'main_event_hosts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int>
     */

    protected $fillable = [
        'host_id',
        'main_event_id',
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

    public function host()
    {
        return $this->belongsTo(Host::class, 'host_id', 'id');
    }
    public function mainEvent()
    {
        return $this->belongsTo(MainEvent::class, 'main_event_id', 'id');
    }
    public function MEHACs()
    {
        return $this->hasMany(MEHAC::class , 'main_event_host_id' , 'id');
    }
}
