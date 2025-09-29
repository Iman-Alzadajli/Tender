<?php

namespace App\Livewire\Role;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role as SpatieRole; // استخدام اسم مستعار لتجنب التعارض
use Spatie\Permission\Models\Permission;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;



#[Layout('layouts.app')]
class RoleManager extends Component
{
    use WithPagination, AuthorizesRequests;
    // use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public bool $isEditMode = false;
    public ?SpatieRole $editingRole = null;

    //للحذف 
    public bool $showDeleteModal = false;
    public ?SpatieRole $deletingRole = null;
    public string $deleteConfirmationName = '';


    // --- خصائص النموذج ---
    // هذه الخصائص مرتبطة مباشرة بحقول نموذج الإنشاء والتعديل
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
     * تقوم بتصفير الحقول قبل عرض النافذة.
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
     * يتم استدعاؤها عند فتح النافذة لضمان عدم وجود بيانات قديمة.
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

        // الخطوة 1: التحقق من صحة البيانات المدخلة
        $validated = $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name',
            'roleDescription' => 'nullable|string|max:255',
            'selectedPermissions' => 'required|array|min:1',
        ]);

        // الخطوة 2: استخدام Transaction لضمان سلامة البيانات
        // هذا يضمن أنه إذا فشلت عملية إعطاء الصلاحيات، فلن يتم إنشاء الدور
        DB::transaction(function () use ($validated) {
            // 2.1: إنشاء الدور بالاسم والوصف
            $role = SpatieRole::create([
                'name' => $validated['roleName'],
                'description' => $validated['roleDescription']
            ]);

            // 2.2: إعطاء الدور الصلاحيات التي تم اختيارها في النموذج
            $role->givePermissionTo($validated['selectedPermissions']);
        });

        // الخطوة 3: إرسال رسالة نجاح وإغلاق النافذة
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


        $this->resetForm(); // نبدأ بتصفير النموذج
        $this->isEditMode = true;
        $this->editingRole = $role;

        // تعبئة النموذج ببيانات الدور الحالي
        $this->roleName = $role->name;
        $this->roleDescription = $role->description;
        // جلب أسماء الصلاحيات الحالية للدور
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->showModal = true; // عرض النافذة
    }

    // حفظ التعديلات

    public function updateRole()
    {
        if ($this->editingRole && $this->editingRole->name === 'Super-Admin') {
            abort(403, 'The Super-Admin role cannot be modified.');
        }

        $this->authorize('roles.edit');



        // قواعد التحقق للتعديل (نسمح بالاسم الحالي)
        $validated = $this->validate([
            'roleName' => 'required|string|max:255|unique:roles,name,' . $this->editingRole->id,
            'roleDescription' => 'nullable|string|max:255',
            'selectedPermissions' => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            // تحديث الاسم والوصف
            $this->editingRole->update([
                'name' => $validated['roleName'],
                'description' => $validated['roleDescription'],
            ]);

            // مزامنة الصلاحيات (sync) هي الطريقة الأفضل للتحديث
            // تقوم بإزالة الصلاحيات القديمة وإضافة الجديدة دفعة واحدة
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

        // لا يمكن حذف دور لديه مستخدمون
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

        // تحقق إضافي للأمان
        if (!$this->deletingRole) return;

        // التحقق من أن الاسم المدخل يطابق اسم الدور
        if ($this->deleteConfirmationName !== $this->deletingRole->name) {
            $this->addError('deleteConfirmationName', 'The entered name does not match.');
            return;
        }

        $this->deletingRole->delete();

        session()->flash('message', 'Role deleted successfully.');
        $this->cancelDelete(); // إغلاق النافذة وتصفير الحقول
    }


    /**
     * دالة العرض الرئيسية.
     * يتم استدعاؤها في كل مرة يتم فيها تحديث الكومبوننت.
     */
    public function render()
    {

        $this->authorize('roles.view');

        // جلب كل الصلاحيات من قاعدة البيانات
        $allPermissions = Permission::all();

        // تجميع الصلاحيات في مجموعات بناءً على الجزء الأول من اسمها
        // مثال: 'users.create' و 'users.edit' سيتم وضعهما تحت مجموعة 'users'
        $permissionGroups = $allPermissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        //للبحث 
        $rolesQuery = SpatieRole::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });

        // الخطوة 2: أكمل بناء الاستعلام على نفس المتغير ($rolesQuery)
        // ثم قم بتنفيذه باستخدام paginate()
        $roles = $rolesQuery->withCount('users')->paginate(6);

        // تمرير البيانات إلى ملف العرض (Blade)
        return view('livewire.role.role-manager', [
            'roles' => $roles,
            'permissionGroups' => $permissionGroups,
        ]);
    }
}
