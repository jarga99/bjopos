<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = array(
            [
                'name' => 'Owner',
                'email' => 'owner@gmail.com',
                'password' => bcrypt('owner'),
                'foto' => '/img/owner.png',
                'level' => 1
            ],
            [
                'name' => 'Kasir01',
                'email' => 'kasir01@gmail.com',
                'password' => bcrypt('kasir01'),
                'foto' => '/img/kasir.png',
                'level' => 2
            ],
            [
                'name' => 'Dapur',
                'email' => 'dapur@gmail.com',
                'password' => bcrypt('dapur'),
                'foto' => '/img/kasir.png',
                'level' => 3
            ]
        );

        array_map(function (array $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }, $users);
    }
}
