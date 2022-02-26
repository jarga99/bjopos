<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

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

    public function toggleStatus()
    {
        $this->status = !$this->status;
        return $this;
    }
    
}
