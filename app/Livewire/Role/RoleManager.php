<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class RoleManager extends Component
{
    use WithPagination, AuthorizesRequests;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public bool $isEditMode = false;
    public ?SpatieRole $editingRole = null;

    //للحذف 
    public bool $showDeleteModal = false;
    public ?SpatieRole $deletingRole = null;
    public string $deleteConfirmationName = '';

    // --- خصائص النموذج ---
    public bool $showModal = false;
    public string $roleName = '';
    public string $roleDescription = '';
    public array $selectedPermissions = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * دالة لفتح نافذة الإنشاء المنبثقة.
     */
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    /**
     * دالة لإغلاق النافذة المنبثقة.
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->isEditMode = false;
        $this->editingRole = null;
    }

    /**
     * دالة لتصفير بيانات النموذج ورسائل التحقق.
     */
    public function resetForm()
    {
        $this->roleName = '';
        $this->roleDescription = '';
        $this->selectedPermissions = [];
        $this->resetValidation();
    }

    /**
     * دالة لحفظ الدور الجديد في قاعدة البيانات.
     */
    public function storeRole()
    {
        $this->authorize('roles.create');

        $validated = $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name',
            'roleDescription' => 'nullable|string|max:255',
            'selectedPermissions' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $role = SpatieRole::create([
                'name' => $validated['roleName'],
                'description' => $validated['roleDescription']
            ]);

            $role->givePermissionTo($validated['selectedPermissions']);
        });

        session()->flash('message', 'Role created successfully.');
        $this->closeModal();
    }

    // تعديل 
    public function editRole(SpatieRole $role)
    {
        if ($role->name === 'Super-Admin') {
            session()->flash('error', 'The Super-Admin role cannot be edited.');
            return;
        }

        $this->authorize('roles.edit');

        $this->resetForm();
        $this->isEditMode = true;
        $this->editingRole = $role;

        $this->roleName = $role->name;
        $this->roleDescription = $role->description;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->showModal = true;
    }

    // حفظ التعديلات
    public function updateRole()
    {
        if ($this->editingRole && $this->editingRole->name === 'Super-Admin') {
            abort(403, 'The Super-Admin role cannot be modified.');
        }

        $this->authorize('roles.edit');

        $validated = $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . $this->editingRole->id,
            'roleDescription' => 'nullable|string|max:255',
            'selectedPermissions' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            $this->editingRole->update([
                'name' => $validated['roleName'],
                'description' => $validated['roleDescription'],
            ]);

            $this->editingRole->syncPermissions($validated['selectedPermissions']);
        });

        session()->flash('message', 'Role updated successfully.');
        $this->closeModal();
    }

    public function confirmDelete(SpatieRole $role)
    {
        if ($role->name === 'Super-Admin') {
            session()->flash('error', 'The Super-Admin role cannot be deleted.');
            return;
        }

        $this->authorize('roles.delete');

        if ($role->users()->count() > 0) {
            session()->flash('error', 'This role cannot be deleted because it is assigned to one or more users.');
            return;
        }
        $this->deletingRole = $role;
        $this->showDeleteModal = true;
    }

    /**
     * دالة لإغلاق نافذة تأكيد الحذف.
     */
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deletingRole = null;
        $this->deleteConfirmationName = '';
        $this->resetValidation('deleteConfirmationName');
    }

    /**
     * دالة لتنفيذ الحذف النهائي بعد التأكيد.
     */
    public function deleteRole()
    {
        if ($this->deletingRole && $this->deletingRole->name === 'Super-Admin') {
            abort(403, 'The Super-Admin role cannot be deleted.');
        }

        $this->authorize('roles.delete');

        if (!$this->deletingRole) return;

        if ($this->deleteConfirmationName !== $this->deletingRole->name) {
            $this->addError('deleteConfirmationName', 'The entered name does not match.');
            return;
        }

        $this->deletingRole->delete();

        session()->flash('message', 'Role deleted successfully.');
        $this->cancelDelete();
    }

    /**
     * دالة العرض الرئيسية.
     */
    public function render()
    {
        $this->authorize('roles.view');

        // ✅ تعريف هيكل المجموعات بعد إزالة Contact List
        $groups = [
            'General' => ['dashboard.view', 'dashboard.edit-tender', 'dashboard.delete-tender', 'dashboard.manage-focal-points', 'dashboard.manage-partnerships', 'dashboard.manage-notes', 'notes.view-history'],
            'User' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
            'Role' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
            'Internal Tender' => ['internal-tenders.view', 'internal-tenders.create', 'internal-tenders.edit', 'internal-tenders.delete', 'internal-tenders.manage-focal-points', 'internal-tenders.manage-partnerships', 'internal-tenders.manage-notes', 'internal-tenders.export', 'notes.view-history'],
            'E-Tender' => ['e-tenders.view', 'e-tenders.create', 'e-tenders.edit', 'e-tenders.delete', 'e-tenders.manage-focal-points', 'e-tenders.manage-partnerships', 'e-tenders.manage-notes', 'e-tenders.export', 'notes.view-history'],
            'Other Tender' => ['other-tenders.view', 'other-tenders.create', 'other-tenders.edit', 'other-tenders.delete', 'other-tenders.manage-focal-points', 'other-tenders.manage-partnerships', 'other-tenders.manage-notes', 'other-tenders.export', 'notes.view-history'],
            // ✅ تم إزالة Contact List Management
            'Focal Points Management' => ['focal-points.view', 'focal-points.edit', 'focal-points.delete', 'focal-points.export'],
            'Partnerships Management' => ['partnerships.view', 'partnerships.edit', 'partnerships.delete', 'partnerships.export'],
        ];

        // ✅ جلب كل الصلاحيات من قاعدة البيانات مرة واحدة
        $allPermissions = Permission::all()->keyBy('name');

        // ✅ بناء مصفوفة permissionGroups بناءً على الهيكل الذي عرفناه
        $permissionGroups = [];
        foreach ($groups as $groupName => $permissionNames) {
            $permissionGroup = [];
            foreach ($permissionNames as $name) {
                if (isset($allPermissions[$name])) {
                    $permissionGroup[] = $allPermissions[$name];
                }
            }
            if (!empty($permissionGroup)) {
                $permissionGroups[$groupName] = collect($permissionGroup);
            }
        }

        $rolesQuery = SpatieRole::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });

        $roles = $rolesQuery->withCount('users')->paginate(6);

        return view('livewire.role.role-manager', [
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
        ]);
    }
}