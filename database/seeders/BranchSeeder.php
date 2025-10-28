<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('branches')->truncate();
        $branch = Branch::create([
            'name' => 'Luna',
            'address' => 'Laguna de chapala #120-B, Col. San Luis Rey',
            'is_active' => true,
        ]);

        $branch->users()->attach([1, 2]);
    }
}
