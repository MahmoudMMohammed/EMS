<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    protected $table = 'receipts';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int>
     */

    protected $fillable = [
        'user_id',
        'event_supplement_id',
        'user_event_id',
        'qr_code',
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
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function eventSupplement()
    {
        return $this->belongsTo(EventSupplement::class, 'event_supplement_id', 'id');
    }
    public function userEvent()
    {
        return $this->belongsTo(UserEvent::class, 'user_event_id', 'id');
    }
    public function getQrCodeAttribute($value)
    {
        return env('APP_URL') . '/storage/' . $value;
    }
}
