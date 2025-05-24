<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'create roles']);
        Permission::create(['name' => 'assign role']);
        Permission::create(['name' => 'revoke role']);

        // Additional supplier-related permissions
        Permission::create(['name' => 'manage supplier orders']);
        Permission::create(['name' => 'view supplier orders']);
        Permission::create(['name' => 'create supplier products']);
        Permission::create(['name' => 'update supplier products']);
        Permission::create(['name' => 'delete supplier products']);

        // Create roles and assign created permissions

        // Supplier role
        $supplierRole = Role::create(['name' => 'supplier']);
        $supplierRole->givePermissionTo(['manage supplier orders', 'view supplier orders', 'create supplier products', 'update supplier products', 'delete supplier products']);

        // Admin role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Optional: Assign roles to users (uncomment if needed)
        // \App\Models\User::find(1)->assignRole('admin'); // Assign Admin role to first user
        // \App\Models\User::find(2)->assignRole('supplier'); // Assign Supplier role to second user

        // Output a message for confirmation
        $this->command->info('Roles and permissions seeded successfully.');
    }
}
