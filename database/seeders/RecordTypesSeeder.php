<?php

namespace Database\Seeders;

use App\Models\RecordType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecordTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('record_types')->truncate();
        foreach (RecordType::TYPES as $type) {
            RecordType::create(['name' => $type, 'is_active' => true]);
        }
    }
}
