<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    // protected $primaryKey = 'id_produk';
    protected $guarded = [];

    public function goods_master() {
        return $this->hasMany(GoodsMaster::class, 'id_produk', 'id');
    }

    public function discount() {
        return $this->hasOne(Discount::class, 'id_produk', 'id');
    }

    public function modal()
    {
        return $this->belongsTo(Modal::class, 'id_produk', 'id');
    }
}
