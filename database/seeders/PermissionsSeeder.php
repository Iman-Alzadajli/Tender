<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define Permission Groups
        $permissionsByGroup = [
            'General' => [
                'dashboard.view',
            ],
            'User Management' => [
                'users.view',
                'users.create',
                'users.edit',
                'users.delete',
            ],
            'Role Management' => [
                'roles.view',
                'roles.create',
                'roles.edit',
                'roles.delete',
            ],
            'Internal Tender' => [
                'internal-tenders.view',
                'internal-tenders.create',
                'internal-tenders.edit',
                'internal-tenders.delete',
                'internal-tenders.manage-focal-points',
                'internal-tenders.manage-partnerships',
                'internal-tenders.manage-notes',
                'internal-tenders.export',
            ],
            'E-Tender' => [
                'e-tenders.view',
                'e-tenders.create',
                'e-tenders.edit',
                'e-tenders.delete',
                'e-tenders.manage-focal-points',
                'e-tenders.manage-partnerships',
                'e-tenders.manage-notes',
                'e-tenders.export',
            ],
            'Other Tender' => [
                'other-tenders.view',
                'other-tenders.create',
                'other-tenders.edit',
                'other-tenders.delete',
                'other-tenders.manage-focal-points',
                'other-tenders.manage-partnerships',
                'other-tenders.manage-notes',
                'other-tenders.export',
            ],
            'Contact List' => [
                'contact-list.view',
                'contact-list.add-focal-point',
                'contact-list.add-partnership',
                'contact-list.export',
            ],
        ];

        // Create Permissions
        foreach ($permissionsByGroup as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            }
        }

        // --- Create a Super-Admin Role and assign all permissions ---
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super-Admin'],
            ['description' => 'Has full access to the entire system']
        );
        $superAdminRole->givePermissionTo(Permission::all());

        // --- Assign Super-Admin role to the first user (or a specific user) ---
        // This is useful for initial setup. Change the email if needed.
        $adminUser = User::first(); // Or use User::where('email', 'your-email@example.com')->first();
        if ($adminUser) {
            $adminUser->assignRole($superAdminRole);
        }
        
        // --- (Optional) Create other basic roles for demonstration ---
        Role::firstOrCreate(
            ['name' => 'Viewer'],
            ['description' => 'Can only view data, cannot make changes']
        )->givePermissionTo([
            'dashboard.view',
            'internal-tenders.view',
            'e-tenders.view',
            'other-tenders.view',
            'contact-list.view',
        ]);

        Role::firstOrCreate(
            ['name' => 'Data-Entry'],
            ['description' => 'Can create and manage tenders but not users or roles']
        )->givePermissionTo([
            'dashboard.view',
            'internal-tenders.view', 'internal-tenders.create', 'internal-tenders.edit', 'internal-tenders.manage-notes',
            'e-tenders.view', 'e-tenders.create', 'e-tenders.edit', 'e-tenders.manage-notes',
            'other-tenders.view', 'other-tenders.create', 'other-tenders.edit', 'other-tenders.manage-notes',
            'contact-list.view',
        ]);
    }
}
