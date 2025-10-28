<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('role_has_permissions')->truncate();


        Permission::firstOrCreate(['name' => 'manage_employees']);
        Permission::firstOrCreate(['name' => 'manage_products']);
        Permission::firstOrCreate(['name' => 'do_sales']);

        $administrator = Role::create(['name' => User::ROLES[0], 'guard_name' => 'web']);
        $employee = Role::create(['name' => User::ROLES[1], 'guard_name' => 'web']);

        // Asignar permisos
        $administrator->givePermissionTo(['manage_employees', 'manage_products', 'do_sales']);
        $employee->givePermissionTo(['do_sales']);

        // //users
        // Permission::create(['name' => 'users.index'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'users.store'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'users.create'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'users.data'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'users.show'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'users.update'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'users.destroy'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'users.edit'])->syncRoles([$administrator]);

        // //products
        // Permission::create(['name' => 'products.index'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'products.store'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'products.create'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'products.data'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'products.show'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'products.update'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'products.destroy'])->syncRoles([$administrator]);
        // Permission::create(['name' => 'products.edit'])->syncRoles([$administrator]);

        // //employees-sale
        // Permission::create(['name' => 'employees-sale.index'])->syncRoles([$administrator, $employee]);
        // Permission::create(['name' => 'employees-sale.store'])->syncRoles([$administrator, $employee]);
        // Permission::create(['name' => 'employees-sale.create'])->syncRoles([$administrator, $employee]);
        // Permission::create(['name' => 'employees-sale.data'])->syncRoles([$administrator, $employee]);
        // Permission::create(['name' => 'employees-sale.show'])->syncRoles([$administrator, $employee]);
        // Permission::create(['name' => 'employees-sale.update'])->syncRoles([$administrator, $employee]);
        // Permission::create(['name' => 'employees-sale.destroy'])->syncRoles([$administrator, $employee]);
        // Permission::create(['name' => 'employees-sale.edit'])->syncRoles([$administrator, $employee]);
    }
}
