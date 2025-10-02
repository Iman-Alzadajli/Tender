<div>
    {{-- Header --}}
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            Focal Points List
        </h2>
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

    {{-- Filters and Export --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div class="d-flex gap-2">
                    <button wire:click="exportPdf" class="btn btn-danger"><div wire:loading wire:target="exportPdf" class="spinner-border spinner-border-sm"></div><i class="bi bi-file-earmark-pdf-fill me-2"></i>Export PDF</button>
                    <button wire:click="exportExcel" class="btn btn-success"><div wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm"></div><i class="bi bi-file-earmark-excel-fill me-2"></i>Export Excel</button>
                </div>
                <div class="d-flex flex-column flex-md-row gap-2 flex-grow-1 justify-content-end">
                    <div class="input-group" style="max-width: 350px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search...">
                    </div>
                    <div class="text-end">
                        <button wire:click="$toggle('showFilters')" class="btn btn-light border"><i class="bi bi-funnel me-2"></i> {{ $showFilters ? 'Hide' : 'Show' }} Filters</button>
                    </div>
                </div>
            </div>
            @if ($showFilters)
            <div class="border-top pt-3 mt-3">
                <div class="row g-3">
                    <div class="col-md-6"><label for="clientFilter" class="form-label">Client Name</label><select id="clientFilter" wire:model.live="clientFilter" class="form-select"><option value="">All Clients</option>@foreach($clients as $client)<option value="{{ $client }}">{{ $client }}</option>@endforeach</select></div>
                    <div class="col-md-6"><label for="clientTypeFilter" class="form-label">Client Type</label><select id="clientTypeFilter" wire:model.live="clientTypeFilter" class="form-select"><option value="">All Client Types</option>@foreach($clientTypes as $type)<option value="{{ $type }}">{{ $type }}</option>@endforeach</select></div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th wire:click="setSortBy('name')" style="cursor: pointer;">Name @if ($sortBy === 'name') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('client_name')" style="cursor: pointer;">Client Name @if ($sortBy === 'client_name') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('client_type')" style="cursor: pointer;">Client Type @if ($sortBy === 'client_type') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('tender_type_label')" style="cursor: pointer;">Tender Type @if ($sortBy === 'tender_type_label') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        {{-- ✅✅✅ تم إضافة العمود الجديد هنا ✅✅✅ --}}
                        <th wire:click="setSortBy('department')" style="cursor: pointer;">Department @if ($sortBy === 'department') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('phone')" style="cursor: pointer;">Phone @if ($sortBy === 'phone') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('email')" style="cursor: pointer;">Email @if ($sortBy === 'email') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($focalPoints as $fp)
                        <tr wire:key="{{ $fp->id }}-{{ $fp->tender_type_label }}">
                            <td>{{ $fp->name }}</td>
                            <td>{{ $fp->client_name }}</td>
                            <td>{{ $fp->client_type }}</td>
                            <td>
                                @php
                                    $badgeColor = match($fp->tender_type_label) {
                                        'Internal Tender' => 'badge-violet',
                                        'E-Tender' => 'badge-green',
                                        'Other Tender' => 'badge-blue',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeColor }}">{{ $fp->tender_type_label }}</span>
                            </td>
                            {{-- ✅✅✅ تم إضافة العمود الجديد هنا ✅✅✅ --}}
                            <td>{{ $fp->department }}</td>
                            <td>{{ $fp->phone }}</td>
                            <td>{{ $fp->email }}</td>
                            <td>
                                <button wire:click="editFocalPoint({{ $fp->id }}, '{{ str_replace(' ', '_', strtolower($fp->tender_type_label)) }}')" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                <button wire:click="confirmDelete({{ $fp->id }}, '{{ str_replace(' ', '_', strtolower($fp->tender_type_label)) }}')" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash-fill"></i></button>
                            </td>
                        </tr>
                    @empty
                        {{-- ✅ تم تعديل colspan ليناسب عدد الأعمدة الجديد --}}
                        <tr><td colspan="8" class="text-center text-muted py-4">No focal points found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($focalPoints->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end">{{ $focalPoints->links() }}</div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @if($showEditModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <form wire:submit.prevent="updateFocalPoint">
                <div class="modal-header"><h5 class="modal-title">Edit Focal Point</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label for="fp_name" class="form-label">Name</label><input type="text" id="fp_name" wire:model="fp_name" class="form-control @error('fp_name') is-invalid @enderror">@error('fp_name') <span class="invalid-feedback">{{ $message }}</span> @enderror</div>
                    <div class="row"><div class="col-md-6 mb-3"><label for="fp_phone" class="form-label">Phone</label><input type="text" id="fp_phone" wire:model="fp_phone" class="form-control @error('fp_phone') is-invalid @enderror">@error('fp_phone') <span class="invalid-feedback">{{ $message }}</span> @enderror</div><div class="col-md-6 mb-3"><label for="fp_email" class="form-label">Email</label><input type="email" id="fp_email" wire:model="fp_email" class="form-control @error('fp_email') is-invalid @enderror">@error('fp_email') <span class="invalid-feedback">{{ $message }}</span> @enderror</div></div>
                    <div class="mb-3"><label for="fp_department" class="form-label">Department</label><input type="text" id="fp_department" wire:model="fp_department" class="form-control @error('fp_department') is-invalid @enderror">@error('fp_department') <span class="invalid-feedback">{{ $message }}</span> @enderror</div>
                    <div class="mb-3"><label for="fp_other_info" class="form-label">Other Info</label><textarea id="fp_other_info" wire:model="fp_other_info" class="form-control"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button><button type="submit" class="btn btn-primary"><span wire:loading.remove wire:target="updateFocalPoint">Save Changes</span><span wire:loading wire:target="updateFocalPoint">Saving...</span></button></div>
            </form>
        </div></div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title text-danger">Confirm Deletion</h5><button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button></div>
            <div class="modal-body"><p>Are you sure you want to delete this focal point?</p><p class="fw-bold text-center fs-5 my-3">{{ $deletingFocalPointName }}</p><p>This action cannot be undone.</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button><button type="button" class="btn btn-danger" wire:click="deleteFocalPoint"><span wire:loading.remove wire:target="deleteFocalPoint">Delete</span><span wire:loading wire:target="deleteFocalPoint">Deleting...</span></button></div>
        </div></div>
    </div>
    @endif
</div>
