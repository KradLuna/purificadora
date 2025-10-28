<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('products')->truncate();
        Product::create([
            'name' => 'Llenado Garrafón 20L',
            'price' => 15.00,
            'is_active' => true,
            'liters' => 20,
        ]);
        Product::create([
            'name' => 'Llenado Garrafón 11L',
            'price' => 8.00,
            'is_active' => true,
            'liters' => 11,
        ]);
        Product::create([
            'name' => 'Llenado Garrafón 5L',
            'price' => 5.00,
            'is_active' => true,
            'liters' => 5,
        ]);
        Product::create([
            'name' => 'Promo 2x20',
            'price' => 20.00,
            'is_active' => true,
            'liters' => 40,
        ]);
        Product::create([
            'name' => 'Promo 2x25',
            'price' => 25.00,
            'is_active' => true,
            'liters' => 40,
        ]);
        Product::create([
            'name' => 'Venta Garrafón 20L',
            'price' => 150.00,
            'is_active' => true,
            'liters' => 0,
        ]);
        Product::create([
            'name' => 'Venta Garrafón 11L',
            'price' => 130.00,
            'is_active' => true,
            'liters' => 0,
        ]);
    }
}
