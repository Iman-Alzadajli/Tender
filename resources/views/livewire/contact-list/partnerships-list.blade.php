<div>
    {{-- Header --}}
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            Partnerships List
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
                    <button wire:click="exportPdf" class="btn btn-danger">
                        <div wire:loading wire:target="exportPdf" class="spinner-border spinner-border-sm"></div><i class="bi bi-file-earmark-pdf-fill me-2"></i>Export PDF
                    </button>
                    <button wire:click="exportExcel" class="btn btn-success">
                        <div wire:loading wire:target="exportExcel" class="spinner-border spinner-border-sm"></div><i class="bi bi-file-earmark-excel-fill me-2"></i>Export Excel
                    </button>
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
                    <div class="col-md-6">
                        <label for="clientFilterP" class="form-label">Client Name</label>
                        <select id="clientFilterP" wire:model.live="clientFilter" class="form-select">
                            <option value="">All Clients</option>
                            @foreach($clients as $client)
                                @if($client)
                                    <option value="{{ $client }}">{{ $client }}</option>
                                @endif 
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="clientTypeFilterP" class="form-label">Client Type</label>
                        <select id="clientTypeFilterP" wire:model.live="clientTypeFilter" class="form-select">
                            <option value="">All Client Types</option>
                            @foreach($clientTypes as $type)
                                @if($type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endif 
                            @endforeach
                        </select>
                    </div>
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
                        <th wire:click="setSortBy('company_name')" style="cursor: pointer;">Company @if ($sortBy === 'company_name') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('client_name')" style="cursor: pointer;">Client Name @if ($sortBy === 'client_name') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('person_name')" style="cursor: pointer;">Contact Person @if ($sortBy === 'person_name') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('phone')" style="cursor: pointer;">Phone @if ($sortBy === 'phone') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th wire:click="setSortBy('email')" style="cursor: pointer;">Email @if ($sortBy === 'email') <i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i> @endif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partnerships as $p)
                    <tr wire:key="{{ $p->id }}">
                        <td>{{ $p->company_name }}</td>
                        <td>{{ $p->client_name ?? 'N/A' }}</td>
                        <td>{{ $p->person_name }}</td>
                        <td>{{ $p->phone }}</td>
                        <td>{{ $p->email }}</td>
                        <td>
                            <button wire:click="editPartnership({{ $p->id }}, '{{ $p->phone }}', '{{ $p->email }}')"
                                class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button wire:click="confirmDelete({{ $p->id }}, '{{ $p->phone }}', '{{ $p->email }}')"
                                class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No partnerships found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($partnerships->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end">{{ $partnerships->links() }}</div>
        @endif
    </div>

    {{-- Edit Modal --}}
    @if($showEditModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form wire:submit.prevent="updatePartnership">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Partnership</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="p_company_name" class="form-label">Company Name</label>
                            <input type="text" id="p_company_name" wire:model="p_company_name" class="form-control @error('p_company_name') is-invalid @enderror">
                            @error('p_company_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="p_person_name" class="form-label">Contact Person</label>
                            <input type="text" id="p_person_name" wire:model="p_person_name" class="form-control @error('p_person_name') is-invalid @enderror">
                            @error('p_person_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="p_phone" class="form-label">Phone</label>
                                <input type="text" id="p_phone" wire:model="p_phone" class="form-control @error('p_phone') is-invalid @enderror">
                                @error('p_phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="p_email" class="form-label">Email</label>
                                <input type="email" id="p_email" wire:model="p_email" class="form-control @error('p_email') is-invalid @enderror">
                                @error('p_email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="p_details" class="form-label">Collaboration Details</label>
                            <textarea id="p_details" wire:model="p_details" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="updatePartnership">Save Changes</span>
                            <span wire:loading wire:target="updatePartnership">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Confirm Deletion</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this partnership?</p>
                    <p class="fw-bold text-center fs-5 my-3">{{ $deletingPartnershipName }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deletePartnership">
                        <span wire:loading.remove wire:target="deletePartnership">Delete</span>
                        <span wire:loading wire:target="deletePartnership">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>