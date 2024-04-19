<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessoryCategory extends Model
{
    use HasFactory;
    protected $table = 'accessory_categories';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'category',
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

    public function accessories()
    {
        return $this->hasMany(Accessory::class, 'accessory_category_id', 'id');
    }
    public function MEHACs()
    {
        return $this->hasMany(MEHAC::class, 'accessory_category_id', 'id');
    }
}
