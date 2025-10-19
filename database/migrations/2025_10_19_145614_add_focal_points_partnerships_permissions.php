<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إضافة الصلاحيات الجديدة
        $permissions = [
            'focal-points.view',
            'focal-points.edit', 
            'focal-points.delete',
            'focal-points.export',
            'partnerships.view',
            'partnerships.edit',
            'partnerships.delete',
            'partnerships.export',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // منح الصلاحيات الجديدة لـ Super-Admin
        $superAdminRole = Role::where('name', 'Super-Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        // منح صلاحيات العرض للـ Viewer
        $viewerRole = Role::where('name', 'Viewer')->first();
        if ($viewerRole) {
            $viewerRole->givePermissionTo(['focal-points.view', 'partnerships.view']);
        }

        // منح صلاحيات العرض والتعديل للـ Data-Entry
        $dataEntryRole = Role::where('name', 'Data-Entry')->first();
        if ($dataEntryRole) {
            $dataEntryRole->givePermissionTo(['focal-points.view', 'focal-points.edit', 'partnerships.view', 'partnerships.edit']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // يمكنك إزالة الصلاحيات إذا أردت التراجع
        $permissions = [
            'focal-points.view',
            'focal-points.edit',
            'focal-points.delete', 
            'focal-points.export',
            'partnerships.view',
            'partnerships.edit',
            'partnerships.delete',
            'partnerships.export',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
};