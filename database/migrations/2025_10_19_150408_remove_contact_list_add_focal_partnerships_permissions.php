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
        // إضافة الصلاحيات الجديدة فقط
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

        // ✅ إزالة صلاحيات Contact List القديمة
        $oldPermissions = [
            'contact-list.view',
            'contact-list.add-focal-point', 
            'contact-list.add-partnership',
            'contact-list.export',
        ];

        foreach ($oldPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // يمكنك إعادة إنشاء صلاحيات Contact List إذا أردت التراجع
        $oldPermissions = [
            'contact-list.view',
            'contact-list.add-focal-point',
            'contact-list.add-partnership',
            'contact-list.export',
        ];

        foreach ($oldPermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // إزالة الصلاحيات الجديدة
        $newPermissions = [
            'focal-points.view',
            'focal-points.edit',
            'focal-points.delete', 
            'focal-points.export',
            'partnerships.view',
            'partnerships.edit',
            'partnerships.delete',
            'partnerships.export',
        ];

        foreach ($newPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
};