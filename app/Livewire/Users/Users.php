<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Pagination\LengthAwarePaginator; // ✅✅✅ الخطوة 1: استيراد الكلاس الجديد ✅✅✅
use Illuminate\Support\Collection; // ✅✅✅ استيراد الكلاس الجديد ✅✅✅

#[Layout('layouts.app', ['header' => 'Users List'])]
class Users extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // ... (بقية الخصائص تبقى كما هي) ...
    public string $search = '';
    public string $sortBy = 'created_at';
    public string $sortDir = 'DESC';
    public bool $showModal = false;
    public bool $isEditMode = false;
    public ?int $editingUserId = null;
    public string $name = '';
    public string $email = '';
    public ?int $selectedRole = null;
    public string $status = 'active';
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;
    public string $deleteConfirmationName = '';

    // ... (بقية الدوال تبقى كما هي) ...
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = ($this->sortDir === 'ASC') ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'ASC';
        }
    }

    public function resetForm()
    {
        $this->reset(['name', 'email', 'selectedRole', 'status', 'isEditMode', 'editingUserId', 'showDeleteModal', 'deletingUserId', 'deleteConfirmationName']);
        $this->resetValidation();
    }

    public function addUser()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function storeUser()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'selectedRole' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(str()->random(20)),
            'status' => $validated['status'],
        ]);

        $user->assignRole($validated['selectedRole']);

        $token = app('auth.password.broker')->createToken($user);
        $user->sendPasswordResetNotification($token);

        $this->showModal = false;
        session()->flash('message', 'User created successfully. A password setup email has been sent.');
    }

    public function editUser($userId)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->editingUserId = $userId;
        
        $user = User::find($userId);
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->status = $user->status;
            $this->selectedRole = $user->roles->first()->id ?? null;
            $this->showModal = true;
        }
    }

    public function updateUser()
    {
        if (!$this->editingUserId) return;

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->editingUserId,
            'selectedRole' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::find($this->editingUserId);
        if ($user) {
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ]);
            $user->syncRoles([$validated['selectedRole']]);
            $this->showModal = false;
            session()->flash('message', 'User updated successfully.');
        }
    }

    public function confirmDelete($userId)
    {
        $this->deletingUserId = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        $user = User::find($this->deletingUserId);

        if (!$user) {
            session()->flash('error', 'User not found.');
            $this->resetForm();
            return;
        }

        if ($this->deleteConfirmationName !== $user->name) {
            $this->addError('deleteConfirmationName', 'The entered name does not match.');
            return;
        }

        if (Auth::id() === $user->id) {
            session()->flash('error', 'You cannot delete your own account.');
            $this->resetForm();
            return;
        }

        if ($user->hasRole('Super-Admin')) {
            if (User::role('Super-Admin')->count() <= 1) {
                session()->flash('error', 'Cannot delete the last Super-Admin.');
                $this->resetForm();
                return;
            }
        }

        $user->delete();
        session()->flash('message', 'User deleted successfully.');
        $this->resetForm();
    }

    /**
     * دالة العرض الرئيسية.
     */
    public function render()
    {
        // ✅✅✅ الكود المصحح والنهائي لمشكلة الترقيم ✅✅✅

        // الخطوة 1: بناء الاستعلام الأساسي مع البحث
        $usersQuery = User::query()
            ->with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhereHas('roles', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            });

        // الخطوة 2: إذا لم يكن الفرز حسب الدور، قم بالفرز العادي ونفذ الترقيم
        if ($this->sortBy !== 'roles.name') {
            $users = $usersQuery->orderBy($this->sortBy, $this->sortDir)->paginate(10);
        } else {
            // الخطوة 3: إذا كان الفرز حسب الدور، قم بجلب كل النتائج أولاً
            $allUsers = $usersQuery->get();

            // الخطوة 4: قم بفرز النتائج في الذاكرة
            $sortedUsers = $allUsers->sortBy(function ($user) {
                return $user->roles->first()->name ?? '';
            }, SORT_REGULAR, $this->sortDir === 'DESC');

            // الخطوة 5: قم بإنشاء كائن الترقيم يدوياً
            $perPage = 10;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageItems = $sortedUsers->slice(($currentPage - 1) * $perPage, $perPage);
            
            $users = new LengthAwarePaginator($currentPageItems, $sortedUsers->count(), $perPage, $currentPage, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);
        }

        // جلب كل الأدوار لعرضها في القائمة المنسدلة
        $allRoles = SpatieRole::orderBy('name')->get();

        return view('livewire.users.users', [
            'users' => $users,
            'allRoles' => $allRoles,
        ]);
    }
}
