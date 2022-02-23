<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsMaster extends Model
{
    use HasFactory;

    protected $table = 'goods_master';

    protected $guarded = [];

    public function produk() 
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }

    public function modal() {
        return $this->belongsTo(Modal::class, 'id_produk', 'id_produk');
    }

    public function modal_product() {
        return $this->belongsTo(Modal::class, 'id_modal', 'id')->pluck('modal_product');
    }

    public function stock() {
        return $this->belongsTo(Stock::class, 'id_stock', 'id');
    }

    public function kategori() {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id');
    }

    // new 
    public function harga()
    {
        return $this->belongsTo(Harga::class, 'id_harga', 'id');
    }

}
