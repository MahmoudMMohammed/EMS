<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MEHAC extends Model
{
    use HasFactory;
    protected $table = 'm_e_h_a_c_s';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int>
     */

    protected $fillable = [
        'accessory_category_id',
        'main_event_host_id',
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

    public function category()
    {
        return $this->belongsTo(AccessoryCategory::class, 'accessory_category_id', 'id');
    }
    public function hosts()
    {
        return $this->belongsTo(MainEventHost::class, 'main_event_host_id', 'id');
    }
}
