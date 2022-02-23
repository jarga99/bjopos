<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('setting')->insert([
            'id_setting' => 1,
            'nama_perusahaan' => 'WAROENG B-JO',
            'alamat' => 'Metland Cileungsi Sektor 6 FD2 NO.08',
            'telepon' => '085223830757',
            'tipe_nota' => 1,
            'path_logo' => '/img/store.png',

        ]);
    }
}
