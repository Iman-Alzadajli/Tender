<div>
 
    {{--(Header)--}}
 
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            Tender Management
        </h2>
    </x-slot>

    {{-- رسالة تأكيد --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{--(Filters)--}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div class="d-flex gap-2">
                    <button wire:click="prepareModal('add')" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add Tender
                    </button>

                    
                    <button wire:click="exportPdf" class="btn btn-outline-secondary">
                    <i class="bi bi-download me-2"></i>Export PDF

                    <div wire:loading wire:target="exportPdf" class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </button>

                {{--excel --}}
              
                    <button wire:click="exportSimpleExcel" class="btn btn-success">
                        <span wire:loading wire:target="exportSimpleExcel" class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </span>
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>


                </div>
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search tenders...">
                </div>
            </div>
            
            <div class="text-end">
                <button wire:click="$toggle('showFilters')" class="btn btn-light">
                    <i class="bi bi-funnel me-2"></i> {{ $showFilters ? 'Hide' : 'Show' }} Filters
                </button>
            </div>

            @if ($showFilters)
                <div class="row g-3 mt-2 pt-3 border-top">
                    <div class="col-sm-6 col-md-3"><select wire:model.live="quarterFilter" class="form-select"><option value="">All Quarters</option><option value="Q1">Q1</option><option value="Q2">Q2</option><option value="Q3">Q3</option><option value="Q4">Q4</option></select></div>
                    <div class="col-sm-6 col-md-3"><select wire:model.live="statusFilter" class="form-select"><option value="">All Statuses</option><option value="Pending">Pending</option><option value="Open">Open</option><option value="Under Evaluation">Under Evaluation</option><option value="Close">Close</option><option value="Declined">Declined</option></select></div>
                    <div class="col-sm-6 col-md-3"><select wire:model.live="assignedFilter" class="form-select"><option value="">All Assignees</option>@foreach ($uniqueAssignees as $assignee)<option value="{{ $assignee }}">{{ $assignee }}</option>@endforeach</select></div>
                    <div class="col-sm-6 col-md-3"><select wire:model.live="clientFilter" class="form-select"><option value="">All Client Types</option>@foreach ($uniqueClients as $client)<option value="{{ $client }}">{{ $client }}</option>@endforeach</select></div>
                </div>
            @endif
        </div>
    </div>

    {{--(Table)--}}

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;">Tender Name</th>
                        <th style="width: 20%;">Client Type</th>
                        <th style="width: 20%;">Assigned To</th>
                        <th style="width: 20%;">Submission Date</th>
                        <th style="width: 10%;">Quarter</th>
                        <th style="width: 10%;">Status</th>
                        <th class="text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenders as $tender)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $tender->name }}</div>
                                <small class="text-muted">{{ $tender->number }}</small>
                            </td>
                            <td>{{ $tender->client_type }}</td>
                            <td>{{ $tender->assigned_to }}</td>
                            <td>{{ $tender->date_of_submission->format('d M, Y') }}</td>
                            <td><span class="badge bg-info-subtle text-info-emphasis rounded-pill">{{ $tender->quarter }}</span></td>
                            <td>
                                <span class="badge rounded-pill
                                  @if($tender->status == 'Approved') 
                                   bg-success-subtle text-success-emphasis
                                  @elseif($tender->status == 'Pending') 
                                   bg-warning-subtle text-warning-emphasis
                                  @elseif($tender->status == 'Open')
                                   bg-primary-subtle text-primary-emphasis
                                  @elseif($tender->status == 'Under Evaluation')
                                   bg-info-subtle text-info-emphasis
                                  @elseif($tender->status == 'Close')
                                   bg-secondary-subtle text-secondary-emphasis
                                  @else {{--Declined --}}
                                   bg-danger-subtle text-danger-emphasis 
                                  @endif">
                                   {{ $tender->status }}
                                </span>

                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button wire:click="prepareModal('view', {{ $tender->id }})" class="btn btn-sm btn-outline-secondary" title="View"><i class="bi bi-eye"></i></button>
                                    <button wire:click="prepareModal('edit', {{ $tender->id }})" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></button>
                                    <button wire:click="deleteTender({{ $tender->id }})" wire:confirm="Are you sure?" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash2"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No tenders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tenders->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end">
                {{ $tenders->links() }}
            </div>
        @endif
    </div>

    {{--النافذة المنبثة بوب--}}
    @if ($showModal)
        <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <form wire:submit.prevent="save">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                @if($mode === 'add') Add New Tender
                                @elseif($mode === 'edit') Edit Tender
                                @else View Tender @endif
                            </h5>
                            <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            <h6 class="mb-3 fw-bold">Basic Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Tender Name <span class="text-danger">*</span></label><input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" @if($mode == 'view') readonly @endif>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-md-6"><label class="form-label">Tender Number <span class="text-danger">*</span></label><input type="text" wire:model="number" class="form-control @error('number') is-invalid @enderror" @if($mode == 'view') readonly @endif>@error('number')<div class="invalid-feedback">{{ $message}}</div>@enderror</div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Client Type <span class="text-danger">*</span></label>
                                    <select wire:model="client_type" class="form-select @error('client_type') is-invalid @enderror" @if($mode == 'view') disabled @endif>
                                        <option value="">Select Type</option>
                                        <option value="Government">Government</option>
                                        <option value="Corporate Collaboration">Corporate Collaboration</option>
                                        <option value="Company – Small & Medium">Company – Small & Medium</option>
                                        <option value="Individual">Individual</option>
                                    </select>
                                    @error('client_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6"><label class="form-label">Date of Purchase <span class="text-danger">*</span></label><input type="date" wire:model="date_of_purchase" class="form-control @error('date_of_purchase') is-invalid @enderror" @if($mode == 'view') readonly @endif>@error('date_of_purchase')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Assigned To <span class="text-danger">*</span></label>
                                    <select wire:model="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" @if($mode == 'view') disabled @endif>
                                        <option value="">Select Person</option>
                                        <option value="Dr.Zainb">Dr.Zainb</option>
                                        <option value="Mr.Ali">Mr.Ali</option>
                                    </select>
                                    @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6"><label class="form-label">Date of Submission <span class="text-danger">*</span></label><input type="date" wire:model="date_of_submission" class="form-control @error('date_of_submission') is-invalid @enderror" @if($mode == 'view') readonly @endif>@error('date_of_submission')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Reviewed by <span class="text-danger">*</span></label>
                                    <select wire:model="reviewed_by" class="form-select @error('reviewed_by') is-invalid @enderror" @if($mode == 'view') disabled @endif>
                                        <option value="">Select Person</option>
                                        <option value="Dr.Zainb">Dr.Zainb</option>
                                        <option value="Mr.Ali">Mr.Ali</option>
                                    </select>
                                </div>

                                <div class="col-md-6">

                                 <div><label class="form-label">Date of Submission of Business analysis <span class="text-danger">*</span></label><input type="date" wire:model="date_of_submission_ba" class="form-control @error('date_of_submission_ba') is-invalid @enderror" @if($mode == 'view') readonly @endif>@error('date_of_submission_ba')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                 <div><label class="form-label">Date of Submission after Review <span class="text-danger">*</span></label><input type="date" wire:model="date_of_submission_after_review" class="form-control @error('date_of_submission_after_review') is-invalid @enderror" @if($mode == 'view') readonly @endif>@error('date_of_submission_after_review')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>

                                </div>

                            </div>
                            <hr class="my-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold">Focal Points <span class="text-danger">*</span></h6>
                                <!--btn for add person-->
                                @if($mode != 'view')<button wire:click.prevent="addFocalPoint" type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus"></i> Add Person</button>@endif
                            </div>
                            @error('focalPoints')<div class="alert alert-danger p-2 mb-3">{{ $message }}</div>@enderror
                            @foreach($focalPoints as $index => $focalPoint)
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold">Person {{ $index + 1 }}</span>
            @if($mode != 'view')

            <!--btn for remove person-->
                <button wire:click.prevent="removeFocalPoint({{ $index }})" type="button" class="btn-close" title="Remove Person"></button>
            @endif
        </div>
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" wire:model="focalPoints.{{ $index }}.name" 
                    class="form-control @error('focalPoints.'.$index.'.name') is-invalid @enderror" 
                    @if($mode == 'view') readonly @endif>
                @error('focalPoints.'.$index.'.name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" wire:model="focalPoints.{{ $index }}.phone" 
                    class="form-control @error('focalPoints.'.$index.'.phone') is-invalid @enderror" 
                    @if($mode == 'view') readonly @endif>
                @error('focalPoints.'.$index.'.phone')
                    <div class="invalid-feedback">{{ $messages['focalPoints.*.phone.regex'] ?? $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" wire:model="focalPoints.{{ $index }}.email" 
                    class="form-control @error('focalPoints.'.$index.'.email') is-invalid @enderror" 
                    @if($mode == 'view') readonly @endif>
                @error('focalPoints.'.$index.'.email')
                    <div class="invalid-feedback">{{ $messagesemail['focalPoints.*.email.email'] ?? $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Department <span class="text-danger">*</span></label>
                <input type="text" wire:model="focalPoints.{{ $index }}.department" 
                    class="form-control @error('focalPoints.'.$index.'.department') is-invalid @enderror" 
                    @if($mode == 'view') readonly @endif>
                @error('focalPoints.'.$index.'.department')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <label class="form-label">Other Info</label>
                <textarea wire:model="focalPoints.{{ $index }}.other_info" 
                    class="form-control" rows="2" 
                    @if($mode == 'view') readonly @endif></textarea>
            </div>
        </div>
    </div>
</div>
                            @endforeach
                            <hr class="my-4">
                            <h6 class="mb-3 fw-bold">Follow-up & Status</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Is there a third-party? <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center pt-1"> 
                                        <div class="form-check me-4"> 
                                            <input class="form-check-input" type="radio" wire:model="has_third_party" value="1" id="thirdPartyYes" @if($mode == 'view') disabled @endif>
                                            <label class="form-check-label" for="thirdPartyYes">Yes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" wire:model="has_third_party" value="0" id="thirdPartyNo" @if($mode == 'view') disabled @endif>
                                            <label class="form-check-label" for="thirdPartyNo">No</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last date of Follow-up <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="last_follow_up_date" class="form-control @error('last_follow_up_date') is-invalid @enderror" @if($mode == 'view') readonly @endif>
                                    @error('last_follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Channel of Follow-up <span class="text-danger">*</span></label>
                                    <select wire:model="follow_up_channel" class="form-select @error('follow_up_channel') is-invalid @enderror" @if($mode == 'view') disabled @endif>
                                        <option value="">Select Channel</option>
                                        <option value="Email">Email</option>
                                        <option value="Call">Call</option>
                                        <option value="Meeting">Meeting</option>
                                    </select>
                                    @error('follow_up_channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12"><label class="form-label">Notes from Follow-up</label><textarea wire:model="follow_up_notes" class="form-control" rows="3" @if($mode == 'view') readonly @endif></textarea></div>
                                
                             
                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model.live="status" class="form-select @error('status') is-invalid @enderror" @if($mode == 'view') disabled @endif>
                                        <option value="Pending">Pending</option>
                                        <option value="Open">Open</option>
                                        <option value="Under Evaluation">Under Evaluation</option>
                                        <option value="Close">Close</option>
                                        <option value="Declined">Declined</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                
                                @if($status === 'Declined')
                                    <div class="col-md-6">
                                        <label class="form-label">Reason of Decline <span class="text-danger">*</span></label>
                                        <textarea wire:model="reason_of_decline" class="form-control @error('reason_of_decline') is-invalid @enderror" rows="1" @if($mode == 'view') readonly @endif></textarea>
                                        @error('reason_of_decline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endif
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">@if($mode == 'view') Close @else Cancel @endif</button>
                            @if($mode != 'view')
                                <button type="submit" class="btn btn-primary">
                                    <span wire:loading.remove wire:target="save">@if($mode === 'add') Add Tender @else Update Tender @endif</span>
                                    <span wire:loading wire:target="save">Saving...</span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

