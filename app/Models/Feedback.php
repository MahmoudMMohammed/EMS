<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use HasFactory , SoftDeletes;
    protected $table = 'feedbacks';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'user_id',
        'location_id',
        'comment',
        'rate',
        'date',
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
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function getDateAttribute($value): string
    {
        $feedbackDate = Carbon::parse($value);
        $now = Carbon::now();

        $diffInSeconds = $feedbackDate->diffInSeconds($now);
        $diffInMinutes = $feedbackDate->diffInMinutes($now);
        $diffInHours = $feedbackDate->diffInHours($now);
        $diffInDays = $feedbackDate->diffInDays($now);

        if($diffInSeconds < 60 ){
            $formattedDate = $diffInSeconds . ' seconds ago';
        } elseif ($diffInMinutes < 60){
            $formattedDate = $diffInMinutes. ' minutes ago';
        } elseif ($diffInHours < 24){
            $formattedDate = $diffInHours . ' hours ago';
        } else {
            $formattedDate = $feedbackDate->format('Y-m-d');
        }
        return $formattedDate ;
    }
}
