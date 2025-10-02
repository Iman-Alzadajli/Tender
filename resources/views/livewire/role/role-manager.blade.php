<div>
    {{-- Header and Add Role Button --}}
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">Role Management</h2>
    </x-slot>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        {{-- شريط البحث --}}
        <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                class="form-control"
                placeholder="Search roles by name or description...">
        </div>

        {{-- زر إضافة دور جديد --}}
        @can('roles.create')
        <button wire:click="openModal" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add Role
        </button>
        @endcan
    </div>

    {{-- Role Cards --}}
    <div class="row">
        @forelse ($roles as $role)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title fw-bold">{{ $role->name }}</h5>

                        {{-- Dropdown for Edit/Delete --}}
                        @if ($role->name !== 'Super-Admin' && (auth()->user()->can('roles.edit') || auth()->user()->can('roles.delete')))
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                {{-- يظهر فقط لمن يملك صلاحية التعديل --}}
                                @can('roles.edit')
                                <li>
                                    <a class="dropdown-item" href="#" wire:click.prevent="editRole({{ $role->id }})">
                                        <i class="bi bi-pencil-square me-2"></i>Edit
                                    </a>
                                </li>
                                @endcan

                                {{-- يظهر فقط لمن يملك صلاحية الحذف --}}
                                @can('roles.delete')
                                <li>
                                    <a class="dropdown-item text-danger" href="#" wire:click.prevent="confirmDelete({{ $role->id }})">
                                        <i class="bi bi-trash-fill me-2"></i>Delete
                                    </a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                        @endif
                    </div>

                    <p class="card-text text-muted small flex-grow-1">{{ $role->description ?? 'No description provided.' }}</p>

                    <div class="mb-3">
                        <strong class="d-block mb-2">Permissions ({{ $role->permissions->count() }})</strong>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach ($role->permissions->take(4) as $permission)
                            <span class="badge bg-primary-subtle text-primary-emphasis rounded-pill fw-normal">
                                {{ $permission->name }}
                            </span>
                            @endforeach
                            @if ($role->permissions->count() > 4)
                            <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill fw-normal">
                                +{{ $role->permissions->count() - 4 }} more
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-auto d-flex justify-content-between align-items-center border-top pt-2">
                        <small class="text-muted">
                            <i class="bi bi-people-fill me-1"></i>
                            {{ $role->users_count }} {{ Str::plural('User', $role->users_count) }}
                        </small>
                        <small class="text-muted">
                            Created: {{ $role->created_at->format('d M, Y') }}
                        </small>
                    </div>
                </div>
            </div>
        </div> {{-- ✅✅✅ هذا هو المكان الصحيح للـ div ✅✅✅ --}}
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                No roles found. Click "Add Role" to create one.
            </div>
        </div>
        @endforelse
    </div>


    {{-- Pagination --}}
    @if ($roles->hasPages())
    <div class="mt-4">{{ $roles->links() }}</div>
    @endif

    {{-- =================================================================== --}}
    {{-- |                        MODAL SECTION                            | --}}
    {{-- =================================================================== --}}

    {{-- Add wire:ignore.self to the wrapper div to prevent Livewire from interfering with Bootstrap's JS --}}
    <div wire:ignore.self>
        @if ($showModal)
        <div class="modal fade show modal-backdrop-custom" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <form wire:submit.prevent="{{ $isEditMode ? 'updateRole' : 'storeRole' }}">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $isEditMode ? 'Edit Role' : 'Create New Role' }}</h5>
                            <button type="button" class="btn-close" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body">
                            {{-- Role Name & Description --}}
                            <div class="mb-3">
                                <label for="roleName" class="form-label">Role Name</label>
                                <input type="text" id="roleName" wire:model.defer="roleName" class="form-control @error('roleName') is-invalid @enderror" placeholder="Enter role name">
                                @error('roleName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-4">
                                <label for="roleDescription" class="form-label">Description</label>
                                <textarea id="roleDescription" wire:model.defer="roleDescription" class="form-control" rows="2" placeholder="Enter a short description for the role"></textarea>
                            </div>

                            {{-- Permissions Accordion --}}
                            <div class="accordion" id="permissionsAccordion" wire:ignore>
                                @foreach ($permissionGroups as $groupName => $permissions)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                                        <button class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $loop->index }}"
                                            @if ($groupName==='roles' && !auth()->user()->hasRole('Super-Admin'))
                                            disabled
                                            @endif
                                            >
                                            {{ Str::title(str_replace('-', ' ', $groupName)) }} Management
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $loop->index }}" class="accordion-collapse collapse" data-bs-parent="#permissionsAccordion">
                                        <div class="accordion-body">
                                            <div class="row">
                                                @foreach ($permissions as $permission)
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="{{ $permission->name }}" id="perm-{{ $permission->id }}" wire:model.defer="selectedPermissions">
                                                        <label class="form-check-label" for="perm-{{ $permission->id }}">
                                                            @php
                                                            // 1. فصل اسم الصلاحية عند النقطة
                                                            $parts = explode('.', $permission->name);
                                                            // 2. أخذ الجزء الثاني فقط (مثلاً: 'edit-tender')
                                                            $action = end($parts);
                                                            // 3. استبدال الشرطة (-) بمسافة وجعل الحرف الأول كبيراً
                                                            $displayName = Str::title(str_replace('-', ' ', $action));

                                                            // 4. تطبيق الحالات الخاصة التي طلبتِها
                                                            if ($displayName === 'Edit Tender') {
                                                            $displayName = 'Edit';
                                                            }
                                                            if ($displayName === 'Delete Tender') {
                                                            $displayName = 'Delete';
                                                            }
                                                            @endphp
                                                            {{ $displayName }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div> {{-- نهاية .accordion-body --}}
                                    </div> {{-- نهاية .accordion-collapse --}}
                                </div> {{-- نهاية .accordion-item --}}
                                @endforeach
                            </div>

                            <div class="modal-footer">

                                <button type="submit" class="btn btn-primary">
                                    @if ($isEditMode)
                                    <span wire:loading.remove wire:target="updateRole">Update Role</span>
                                    <span wire:loading wire:target="updateRole">Updating...</span>
                                    @else
                                    <span wire:loading.remove wire:target="storeRole">Create Role</span>
                                    <span wire:loading wire:target="storeRole">Creating...</span>
                                    @endif
                                </button>

                            </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>


    {{--نافذة تاكيد الحذف--}}
    @if ($showDeleteModal)
    <div class="modal fade show modal-backdrop-custom" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="deleteRole">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Deletion</h5>
                        <button type="button" class="btn-close" wire:click="cancelDelete"></button>
                    </div>
                    <div class="modal-body">
                        <p>This action is irreversible. You are about to delete the role:</p>
                        <p class="fw-bold text-center fs-5 my-3">{{ $deletingRole?->name }}</p>
                        <p>To confirm, please type the role name exactly as it appears above.</p>

                        <div class="mt-3">
                            <label for="deleteConfirmation" class="form-label">Role Name</label>
                            <input type="text" id="deleteConfirmation" wire:model.live="deleteConfirmationName" class="form-control @error('deleteConfirmationName') is-invalid @enderror">
                            @error('deleteConfirmationName') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                        <button type="submit" class="btn btn-danger" @if($deleteConfirmationName !==$deletingRole?->name) disabled @endif>
                            <span wire:loading.remove wire:target="deleteRole">Delete Role</span>
                            <span wire:loading wire:target="deleteRole">Deleting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>