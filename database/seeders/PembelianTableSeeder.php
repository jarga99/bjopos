<?php

namespace Database\Seeders;

use App\Models\Pembelian;
use Illuminate\Database\Seeder;

class PembelianTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id_user' => 1,
                'jumlah' => 1,
                'nama_bahan' => 'Ayam',
                'satuan' => 'Ekor',
                'harga' => 30000,
                'created_at' => date(now()),
                'updated_at' => date(now())
            ],
            [
                'id_user' => 1,
                'jumlah' => 1,
                'nama_bahan' => 'Minyak Goreng',
                'satuan' => 'Liter',
                'harga' => 36500,
                'created_at' => date(now()),
                'updated_at' => date(now())
            ],
        ];

        Pembelian::insert($data);
    }
}
