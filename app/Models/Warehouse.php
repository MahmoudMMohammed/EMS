<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $table = 'warehouses';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */

    protected $fillable = [
        'governorate',
        'address',
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
    public function eventSupplements()
    {
        return $this->hasMany(EventSupplement::class, 'warehouse_id', 'id');
    }
    public function warehouseAccessories()
    {
        return $this->hasMany(WarehouseAccessory::class, 'warehouse_id', 'id');
    }
}
