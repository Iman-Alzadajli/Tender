<div>
    {{-- Header --}}
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            Contact List
        </h2>
    </x-slot>

    {{-- Message show --}}
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

    {{-- Filters and Export Buttons --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                {{-- أزرار التصدير على اليسار --}}
                <div class="d-flex gap-2">
                    <button wire:click="exportPdf" class="btn btn-outline-secondary">
                        <i class="bi bi-download me-2"></i>Export PDF
                    </button>
                    <button wire:click="exportExcel" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                    </button>
                </div>

                {{-- البحث والفلاتر على اليمين --}}
                <div class="d-flex flex-column flex-md-row gap-2 flex-grow-1 justify-content-end">
                    <div class="input-group" style="max-width: 350px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search anything...">
                    </div>
                    <div class="text-end">
                        <button wire:click="$toggle('showFilters')" class="btn btn-light">
                            <i class="bi bi-funnel me-2"></i> {{ $showFilters ? 'Hide' : 'Show' }} Filters
                        </button>
                    </div>
                </div>
            </div>

            {{-- ✅✅✅ قسم الفلاتر المخفي (بدون مسافة علوية) ✅✅✅ --}}
            @if ($showFilters)
            <div class="row g-3 pt-2">
                <div class="col-md-6">
                    <label for="clientTypeFilter" class="form-label">Client Type</label>
                    <select id="clientTypeFilter" wire:model.live="searchClientType" class="form-select">
                        <option value="">All Client Types</option>
                        @foreach($uniqueClientTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tenderTypeFilter" class="form-label">Tender Type</label>
                    <select id="tenderTypeFilter" wire:model.live="searchTenderType" class="form-select">
                        <option value="">All Tender Types</option>
                        <option value="internal_tender">Internal Tender</option>
                        <option value="e_tender">E-Tender</option>
                        <option value="other_tender">Other Tender</option>
                    </select>
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
                        <th style="width: 50px;"></th>
                        <th wire:click="setSortBy('name')" style="cursor: pointer;">Tender Name @if($sortBy === 'name')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif</th>
                        <th wire:click="setSortBy('client_name')" style="cursor: pointer;">Client Name @if($sortBy === 'client_name')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif</th>
                        <th wire:click="setSortBy('client_type')" style="cursor: pointer;">Client Type @if($sortBy === 'client_type')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif</th>
                        <th wire:click="setSortBy('tender_type')" style="cursor: pointer;">Tender Type @if($sortBy === 'tender_type')<i class="bi bi-arrow-{{ $sortDir === 'ASC' ? 'up' : 'down' }}"></i>@endif</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                @forelse ($tenders as $tender)
                <tbody x-data="{ open: false, detailsLoaded: false, details: null }" @scope>
                    <tr wire:key="tender-{{ $tender->tender_type }}-{{ $tender->id }}">
                        <td>
                            <button @click="open = !open; if (!detailsLoaded) { $wire.call('loadTenderDetails', '{{ $tender->tender_type }}', {{ $tender->id }}).then(result => { details = result; detailsLoaded = true; }) }" class="btn btn-sm btn-light">
                                <i :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            </button>
                        </td>
                        <td>{{ $tender->name }}</td>
                        <td>{{ $tender->client_name }}</td>
                        <td>{{ $tender->client_type }}</td>
                        <td>
                            <span class="badge
                             @if($tender->tender_type == 'internal_tender') badge-internal
                             @elseif($tender->tender_type == 'e_tender') badge-external
                             @else badge-other @endif">
                                {{ str_replace('_', ' ', Str::title($tender->tender_type)) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-primary" wire:click="prepareFocalPointModal('{{ $tender->tender_type }}', {{ $tender->id }})">
                                <i class="bi bi-plus-lg"></i> Add Focal Point
                            </button>
                        </td>
                    </tr>
                    <tr x-show="open" style="display: none;">
                        <td colspan="6" class="p-0 bg-light">
                            <div class="p-3">
                                <div wire:loading wire:target="loadTenderDetails('{{ $tender->tender_type }}', {{ $tender->id }})">
                                    <div class="text-center p-3">
                                        <div class="spinner-border text-primary" role="status"></div>
                                    </div>
                                </div>
                                <div x-show="detailsLoaded">
                                    <template x-if="details">
                                        <div>
                                            <h6 class="fw-bold">Focal Points</h6>
                                            <template x-if="details.focalPoints && details.focalPoints.length > 0">
                                                <ul class="list-group mb-3">
                                                    <template x-for="fp in details.focalPoints" :key="fp.id">
                                                        <li class="list-group-item">
                                                            <strong x-text="fp.name"></strong> (<span x-text="fp.department"></span>)


                                                            <small class="text-muted">
                                                                <i class="bi bi-telephone-fill"></i> <span x-text="fp.phone"></span> |
                                                                <i class="bi bi-envelope-fill"></i> <span x-text="fp.email"></span>
                                                            </small>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </template>
                                            <template x-if="!details.focalPoints || details.focalPoints.length === 0">
                                                <p class="text-muted">No focal points found.</p>
                                            </template>
                                            <hr class="my-3">
                                            <h6 class="fw-bold">Partnership</h6>
                                            <template x-if="details.partnership">
                                                <ul class="list-group">
                                                    <li class="list-group-item">
                                                        <strong x-text="details.partnership.partnership_company"></strong>


                                                        <small class="text-muted">
                                                            <i class="bi bi-person-fill"></i> <span x-text="details.partnership.partnership_person"></span> |
                                                            <i class="bi bi-telephone-fill"></i> <span x-text="details.partnership.partnership_phone"></span> |
                                                            <i class="bi bi-envelope-fill"></i> <span x-text="details.partnership.partnership_email"></span>
                                                        </small>
                                                    </li>
                                                </ul>
                                            </template>
                                            <template x-if="!details.partnership">
                                                <p class="text-muted">No partnership details found.</p>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                @empty
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No tenders found.</td>
                    </tr>
                </tbody>
                @endforelse
            </table>
        </div>
        @if ($tenders->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end">{{ $tenders->links() }}</div>
        @endif
    </div>

    {{-- Modal for adding Focal Point --}}
    @if($showFocalPointModal)
    <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="addFocalPoint">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Focal Point</h5>
                        <button type="button" class="btn-close" wire:click="$set('showFocalPointModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="fp_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="fp_name" wire:model="fp_name" class="form-control @error('fp_name') is-invalid @enderror">
                            @error('fp_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fp_phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" id="fp_phone" wire:model="fp_phone" class="form-control @error('fp_phone') is-invalid @enderror">
                            @error('fp_phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fp_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="fp_email" wire:model="fp_email" class="form-control @error('fp_email') is-invalid @enderror">
                            @error('fp_email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fp_department" class="form-label">Department <span class="text-danger">*</span></label>
                            <input type="text" id="fp_department" wire:model="fp_department" class="form-control @error('fp_department') is-invalid @enderror">
                            @error('fp_department') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="fp_other_info" class="form-label">Other Info</label>
                            <textarea id="fp_other_info" wire:model="fp_other_info" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showFocalPointModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Focal Point</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>