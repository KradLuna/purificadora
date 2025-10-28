<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        $password = config('constants.admin.password');
        $password_b = config('constants.admin.password_b');
        User::storeModel([
            'full_name' => 'Abraham Portales Nájera',
            'phone_number' => '4444922872',
            'password' => $password,
            'role' => User::ROLES[0], //administrador
            'is_active' => true,
        ]);
        User::storeModel([
            'full_name' => 'Paula Citlali Galván Martínez',
            'phone_number' => '4443076017',
            'password' => $password_b,
            'role' => User::ROLES[0], //administrador
            'is_active' => true,
        ]);
    }
}
