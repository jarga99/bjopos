<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penjualan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penjualan';
    protected $primaryKey = 'id';
    protected $guarded = [];


    public function user()
    {
        return $this->hasOne(User::class, 'id', 'id_user');
    }

    public function penjualan_detail()
    {
        return $this->hasOne(PenjualanDetail::class, 'id_penjualan', 'id_penjualan');
    }

    public function detail()
    {
        return $this->hasMany(PenjualanDetail::class, 'id_penjualan', 'id');
    }
    
}
