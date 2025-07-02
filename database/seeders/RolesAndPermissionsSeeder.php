<?php


// namespace Database\Seeders;

// use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

// class RolesAndPermissionsSeeder extends Seeder
// {
//     public function run()
//     {



//         $permissions = [
//             // Orders
//             'orders.view',
//             'orders.create',
//             'orders.update',
//             'orders.cancel',
//             'orders.delete',

//             // Products
//             'products.view',
//             'products.create',
//             'products.update',
//             'products.delete',

//             // Users
//             'users.view',
//             'users.create',
//             'users.update',
//             'users.delete',

//             // Roles
//             'roles.create',
//             'roles.assign',
//             'roles.revoke',
//             'roles.view',

//             // Discounts
//             'discounts.view',
//             'discounts.create',
//             'discounts.update',
//             'discounts.delete',

//             // Inventory
//             'inventory.view',
//             'inventory.update',
//         ];


//         foreach ($permissions as $permission) {
//             Permission::firstOrCreate([
//                 'name' => $permission,
//                 'guard_name' => 'web' // ✅ Ensure it's for sanctum guard
//             ]);
//         }


//         $adminRole = Role::firstOrCreate([
//             'name' => 'admin',
//            'guard_name' => 'web' // ✅ Also important for roles
//         ]);
//         $adminRole->givePermissionTo(Permission::all());


//         $this->command->info('Roles and permissions seeded successfully.');
//     }
// }






// Optional future modules:
// 'categories' => ['view', 'create', 'update', 'delete'],
// 'brands' => ['view', 'create', 'update', 'delete'],
// 'settings' => ['view', 'update'],










namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Define permission groups with CRUD-style structure
        $modules = [
            'dashboard' => ['view', 'orders', 'products', 'customers', 'revenue'],
            'orders' => ['view', 'create', 'update', 'cancel', 'delete'],
            'products' => ['view', 'create', 'update', 'delete'],
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'assign', 'revoke', 'update', 'delete'],
            'discounts' => ['view', 'create', 'update', 'delete'],
            'inventory' => ['view', 'update'],


        ];

        // Seed all permissions
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = "{$module}.{$action}";
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create admin role and assign all permissions
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $adminRole->syncPermissions(Permission::all());

        $this->command->info('Roles and permissions seeded successfully.');
    }
}