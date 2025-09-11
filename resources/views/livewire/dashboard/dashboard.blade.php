<div>
    {{-- message show when delete or add tender --}}
    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="container-fluid py-4">
        <!-- Section 1: Status Cards (Final Multi-Breakpoint Fix for iPad Pro/Nest Hub) -->
        <div class="row g-3">
            {{-- We use a combination of breakpoints for maximum responsiveness --}}
            {{-- col-6: 2 cards on extra-small screens (phones) --}}
            {{-- col-md-4: 3 cards on medium screens (tablets) --}}
            {{-- col-lg-3: 4 cards on large screens (iPad Pro, Nest Hub) --}}
            {{-- col-xl-2: 6 cards on extra-large screens (desktops) --}}

            {{-- 1. Awarded to Company (win) --}}
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card status-card border-success border-2 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <div style="min-width: 0;">
                            <div class="text-success fw-bold text-uppercase small">Awarded (Win)</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['awarded_to_company_win'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-trophy-fill text-success fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            {{-- 2. Recall --}}
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card status-card border-warning border-2 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <div style="min-width: 0;">
                            <div class="text-warning fw-bold text-uppercase small">Recall</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['recall'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-arrow-counterclockwise text-warning fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Build Proposal --}}
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card status-card border-primary border-2 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <div style="min-width: 0;">
                            <div class="text-primary fw-bold text-uppercase small">Build Proposal</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['buildproposal'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-tools text-primary fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            {{-- 4. Under Evaluation --}}
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card status-card border-info border-2 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <div style="min-width: 0;">
                            <div class="text-info fw-bold text-uppercase small">Under Evaluation</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['under_evaluation'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-search text-info fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            {{-- 5. Awarded to Others (loss) --}}
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card status-card border-secondary border-2 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <div style="min-width: 0;">
                            <div class="text-secondary fw-bold text-uppercase small">Awarded (Loss)</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['awarded_to_others_loss'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-archive-fill text-secondary fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            {{-- 6. Cancel --}}
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <div class="card status-card border-danger border-2 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center gap-2">
                        <div style="min-width: 0;">
                            <div class="text-danger fw-bold text-uppercase small">Cancel</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['cancel'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-x-circle-fill text-danger fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>




        <!-- Section 2: Urgent Tenders Table-->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Urgent Tenders</h5>
                    </div>
                    <div class="table-responsive">


                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tender Name</th>
                                    <th>Client Name</th>
                                    <th>Submission Date</th>
                                    <th>Days Left</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($urgentTenders as $tender)
                                <tr wire:key="{{ $tender->tender_type }}-{{ $tender->id }}">
                                    {{-- 1. Tender Name --}}
                                    <td>{{ $tender->name ?? 'N/A' }}</td>

                                    {{-- 2. Client Name --}}
                                    <td>{{ $tender->client_name ?? 'N/A' }}</td>

                                    {{-- 3. Submission Date --}}
                                    <td>{{ \Carbon\Carbon::parse($tender->date_of_submission)->format('d M, Y') }}</td>

                                    {{-- 4. Days Left --}}
                                    <td>
                                        @php
                                        $daysLeft = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($tender->date_of_submission)->startOfDay(), false);
                                        @endphp
                                        @if ($daysLeft <= 0)
                                            <span class="badge bg-danger fw-bold rounded-pill">Due Today!</span>
                                            @elseif ($daysLeft == 1)
                                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">1 Day</span>
                                            @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">{{ $daysLeft }} Days</span>
                                            @endif
                                    </td>

                                    {{-- 5. Actions --}}
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button wire:click="showTender('{{ $tender->tender_type }}', {{ $tender->id }}, false)" type="button" class="btn btn-outline-secondary" title="View"><i class="bi bi-eye"></i></button>
                                            <button wire:click="showTender('{{ $tender->tender_type }}', {{ $tender->id }}, true)" type="button" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                            <button wire:click="deleteTender('{{ $tender->tender_type }}', {{ $tender->id }})" wire:confirm="Are you sure you want to delete this tender?" type="button" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash2-fill"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                  
                                    <td colspan="5" class="text-center py-4">No urgent tenders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>


                    </div>



                </div>
            </div>
        </div>

        <!-- Section 3: Charts -->
        <div class="row mt-4 g-4" wire:ignore>
            <div class="col-lg-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Tender Quantities by Quarter</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tenderBarChart" data-chart-data="{{ $tenderQuantitiesJson }}"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Client Types</h5>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <div style="position: relative; height:300px; width:300px">
                            <canvas id="clientPieChart" data-chart-data="{{ $clientTypesJson }}"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!--pop Tender Model (Form) -->
    @if ($showingTenderModal)
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog-scrollable modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="saveTender">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isEditMode ? 'Edit Tender' : 'View Tender' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showingTenderModal', false)"></button>
                    </div>
                    <div class="modal-body">


                        <h6 class="mb-3 fw-bold">Basic Information</h6>


                        <div class="row g-3">
                            {{-- ======================================================= --}}
                            {{-- |                 العمود الأيسر (5 حقول)                | --}}
                            {{-- ======================================================= --}}
                            <div class="col-md-6">
                                {{-- 1. Tender Name --}}
                                <div class="mb-3">
                                    <label class="form-label">Tender Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 2. Client Type --}}
                                <div class="mb-3">
                                    <label class="form-label">Client Type <span class="text-danger">*</span></label>
                                    <select wire:model="client_type" class="form-select @error('client_type') is-invalid @enderror" @if(!$isEditMode) disabled @endif>
                                        <option value="">-- Select Client Type --</option>
                                        <option value="Government">Government</option>
                                        <option value="Corporate Collaboration">Corporate Collaboration</option>
                                        <option value="Company – Small & Medium">Company – Small & Medium</option>
                                        <option value="Individual">Individual</option>
                                    </select>
                                    @error('client_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 3. Client Name --}}
                                <div class="mb-3">
                                    <label class="form-label">Client Name</label>
                                    <input type="text" wire:model="client_name" class="form-control @error('client_name') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                    @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 4. Assigned To --}}
                                <div class="mb-3">
                                    <label class="form-label">Assigned To <span class="text-danger">*</span></label>
                                    <select wire:model="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" @if(!$isEditMode) disabled @endif>
                                        <option value="">-- Select Person --</option>
                                    
                                        @foreach ($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 5. Reviewed by --}}
                                <div>
                                    <label class="form-label">Reviewed by <span class="text-danger">*</span></label>
                                    <select wire:model="reviewed_by" class="form-select @error('reviewed_by') is-invalid @enderror" @if(!$isEditMode) disabled @endif>
                                        <option value="">-- Select Person --</option>
                               
                                        @foreach ($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('reviewed_by')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- ======================================================= --}}
                            {{-- |                 العمود الأيمن (5 حقول)                | --}}
                            {{-- ======================================================= --}}
                            <div class="col-md-6">
                                {{-- 1. Tender Number --}}
                                <div class="mb-3">
                                    <label class="form-label">Tender Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="number" class="form-control @error('number') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                    @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 2. Date of Purchase --}}
                                <div class="mb-3">
                                    <label class="form-label">Date of Purchase <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="date_of_purchase" class="form-control @error('date_of_purchase') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                    @error('date_of_purchase')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 3. Date of Submission --}}
                                <div class="mb-3">
                                    <label class="form-label">Date of Submission <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="date_of_submission" class="form-control @error('date_of_submission') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                    @error('date_of_submission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 4. Date of Submission of BA --}}
                                <div class="mb-3">
                                    <label class="form-label">Date of Submission of BA <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="date_of_submission_ba" class="form-control @error('date_of_submission_ba') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                    @error('date_of_submission_ba')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- 5. Date of Submission after Review --}}
                                <div>
                                    <label class="form-label">Date of Submission after Review <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="date_of_submission_after_review" class="form-control @error('date_of_submission_after_review') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                    @error('date_of_submission_after_review')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>





                        <hr class="my-4">

                        {{-- Focal Points Section --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-bold">Focal Points</h6>
                            @if($isEditMode)<button wire:click.prevent="addFocalPoint" type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus"></i> Add Person</button>@endif
                        </div>

                        {{-- رسالة الخطأ عند تجاوز الحد --}}
                        @if($focalPointError)
                        <div class="alert alert-warning alert-dismissible fade show">
                            {{ $focalPointError }}
                            <button type="button" class="btn-close" wire:click="$set('focalPointError', '')"></button>
                        </div>
                        @endif


                        @foreach($focalPoints as $index => $focalPoint)
                        <div class="card mb-3" wire:key="focal-point-{{ $index }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold">Person {{ $index + 1 }}</span>
                                    @if($isEditMode)<button wire:click.prevent="removeFocalPoint({{ $index }})" type="button" class="btn-close" title="Remove Person"></button>@endif
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6 col-lg-3"><label class="form-label">Name <span class="text-danger">*</span></label><input type="text" wire:model="focalPoints.{{ $index }}.name" class="form-control @error('focalPoints.'.$index.'.name') is-invalid @enderror" @if(!$isEditMode) readonly @endif>@error('focalPoints.'.$index.'.name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6 col-lg-3"><label class="form-label">Phone <span class="text-danger">*</span></label><input type="text" wire:model="focalPoints.{{ $index }}.phone" class="form-control @error('focalPoints.'.$index.'.phone') is-invalid @enderror" @if(!$isEditMode) readonly @endif>@error('focalPoints.'.$index.'.phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6 col-lg-3"><label class="form-label">Email <span class="text-danger">*</span></label><input type="email" wire:model="focalPoints.{{ $index }}.email" class="form-control @error('focalPoints.'.$index.'.email') is-invalid @enderror" @if(!$isEditMode) readonly @endif>@error('focalPoints.'.$index.'.email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                    <div class="col-md-6 col-lg-3"><label class="form-label">Department <span class="text-danger">*</span></label><input type="text" wire:model="focalPoints.{{ $index }}.department" class="form-control @error('focalPoints.'.$index.'.department') is-invalid @enderror" @if(!$isEditMode) readonly @endif>@error('focalPoints.'.$index.'.department')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <hr class="my-4">

                        {{-- Follow-up & Status Section --}}
                        <h6 class="mb-3 fw-bold">Follow-up & Status</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Is there a third-party? <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center pt-1">
                                    <div class="form-check me-4"><input class="form-check-input" type="radio" wire:model="has_third_party" value="1" id="thirdPartyYes" @if(!$isEditMode) disabled @endif><label class="form-check-label" for="thirdPartyYes">Yes</label></div>
                                    <div class="form-check"><input class="form-check-input" type="radio" wire:model="has_third_party" value="0" id="thirdPartyNo" @if(!$isEditMode) disabled @endif><label class="form-check-label" for="thirdPartyNo">No</label></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last date of Follow-up <span class="text-danger">*</span></label>
                                <input type="date" wire:model="last_follow_up_date" class="form-control @error('last_follow_up_date') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                @error('last_follow_up_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Channel of Follow-up <span class="text-danger">*</span></label>
                                <input type="text" wire:model="follow_up_channel" class="form-control @error('follow_up_channel') is-invalid @enderror" @if(!$isEditMode) readonly @endif>
                                @error('follow_up_channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes from Follow-up</label>
                                <textarea wire:model="follow_up_notes" class="form-control" rows="3" @if(!$isEditMode) readonly @endif></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model.live="status" class="form-select @error('status') is-invalid @enderror" @if(!$isEditMode) disabled @endif>
                                    <option value="Recall">Recall</option>
                                    <option value="Awarded to Company (win)">Awarded to Company (win)</option>
                                    <option value="Under Evaluation">Under Evaluation</option>
                                    <option value="Awarded to Others (loss)">Awarded to Others (loss)</option>

                                    <option value="Cancel">Cancel</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @if($status === 'Cancel')
                            <div class="col-md-6">
                                <label class="form-label">Reason of Cancel <span class="text-danger">*</span></label>
                                <textarea wire:model="reason_of_cancel" class="form-control @error('reason_of_cancel') is-invalid @enderror" rows="1" @if(!$isEditMode) readonly @endif></textarea>
                                @error('reason_of_cancel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showingTenderModal', false)">Close</button>
                        @if($isEditMode)
                        <button type="submit" class="btn btn-primary">Update Tender</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif