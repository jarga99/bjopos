<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanDetail extends Model
{
    use HasFactory;

    protected $table = 'penjualan_detail';
    // protected $primaryKey = 'id_penjualan_detail';
    protected $guarded = [];

    public function produk()
    {
        return $this->hasOne(Produk::class, 'id', 'id_produk');
    }

    public function harga()
    {
        return $this->belongsTo(Harga::class, 'harga_jual', 'id');
    }

    public function goods_master()
    {
        return $this->hasOne(GoodsMaster::class, 'id', 'id_produk');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }
}
