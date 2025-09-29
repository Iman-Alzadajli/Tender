<div>
    {{-- Header and Controls --}}
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">User Management</h2>
    </x-slot>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Search and Add User Button --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div class="input-group search-bar" style="max-width: 400px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search by name, email, or role...">
        </div>

        <div class="text-end">
            <button wire:click="addUser"
                class="btn btn-primary"
                @cannot('users.create') disabled @endcannot>
                <i class="bi bi-plus-lg me-2"></i>Add User
            </button>
        </div>

    </div>

    {{-- Users Table --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        {{-- ✅✅✅ تم تطبيق التعديلات هنا ✅✅✅ --}}
                        <th wire:click="setSortBy('name')" style="cursor: pointer;">
                            Name
                            @if($sortBy === 'name')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>
                        <th wire:click="setSortBy('email')" style="cursor: pointer;">
                            Email
                            @if($sortBy === 'email')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>
                        <th wire:click="setSortBy('roles.name')" style="cursor: pointer;">
                            Role
                            @if($sortBy === 'roles.name')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>
                        <th wire:click="setSortBy('status')" style="cursor: pointer;">
                            Status
                            @if($sortBy === 'status')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>
                        <th wire:click="setSortBy('last_login_at')" style="cursor: pointer;">
                            Last Login
                            @if($sortBy === 'last_login_at')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>
                        <th wire:click="setSortBy('created_at')" style="cursor: pointer;">
                            Created
                            @if($sortBy === 'created_at')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr wire:key="{{ $user->id }}">

                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->roles->isNotEmpty())
                            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill fw-normal">
                                {{ $user->roles->first()->name }}
                            </span>
                            @else
                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill fw-normal">No Role</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge rounded-pill {{ $user->status === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td>{{ $user->created_at->format('d M, Y') }}</td>
                        <td>

                            <div class="btn-group">
                                @if (Auth::id() === $user->id)
                                {{-- إذا كان هو المستخدم الحالي، اعرض الأزرار معطلة --}}
                                <button class="btn btn-sm btn-outline-primary" title="Edit" disabled><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-outline-danger" title="Delete" disabled><i class="bi bi-trash-fill"></i></button>
                                @else
                                <button wire:click="editUser({{ $user->id }})"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Edit"
                                    @if(Auth::id()===$user->id)
                                    disabled
                                    @else
                                    @cannot('users.edit') disabled @endcannot
                                    @endif>
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Delete"
                                    @if(Auth::id()===$user->id)
                                    disabled
                                    @else
                                    @cannot('users.delete') disabled @endcannot
                                    @endif>
                                    <i class="bi bi-trash-fill"></i>
                                </button>


                                @endif
                            </div>


                        </td>
                    </tr>
                    @empty
                    <tr>
                        {{-- تم تحديث عدد الأعمدة إلى 7 --}}
                        <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- ... (بقية النوافذ المنبثقة تبقى كما هي بدون تغيير) ... --}}
    @if ($showModal)
    <div class="modal fade show modal-backdrop-custom" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="{{ $isEditMode ? 'updateUser' : 'storeUser' }}">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditMode ? 'Edit User' : 'Add New User' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" wire:model.defer="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" wire:model.defer="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="selectedRole" class="form-label">Role</label>
                            <select id="selectedRole" wire:model.defer="selectedRole" class="form-select @error('selectedRole') is-invalid @enderror">
                                <option value="">Select a role</option>
                                @foreach($allRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('selectedRole') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="status" class="form-label">Status</label>
                            <select id="status" wire:model.defer="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="{{ $isEditMode ? 'updateUser' : 'storeUser' }}">
                                {{ $isEditMode ? 'Update User' : 'Create User' }}
                            </span>
                            <span wire:loading wire:target="{{ $isEditMode ? 'updateUser' : 'storeUser' }}">
                                {{ $isEditMode ? 'Updating...' : 'Creating...' }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if ($showDeleteModal)
    <div class="modal fade show modal-backdrop-custom" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="deleteUser">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                        <p>To confirm, please type the user's name: <strong>{{ \App\Models\User::find($deletingUserId)?->name }}</strong></p>
                        <input type="text" wire:model.live="deleteConfirmationName" class="form-control @error('deleteConfirmationName') is-invalid @enderror" placeholder="Enter the name to confirm">
                        @error('deleteConfirmationName') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="deleteUser">Delete User</span>
                            <span wire:loading wire:target="deleteUser">Deleting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>