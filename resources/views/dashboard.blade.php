<x-app-layout>

    @push('styles')
    {{-- We are pushing the required CSS links to the main layout --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('/css/dashboard.css' ) }}">
    @endpush

    <div class="container-fluid py-4">

        <!-- Section 1: Status Cards (Dynamic) -->
        <div class="row g-3">
            <div class="col-12 col-md-4 col-lg">
                <div class="card status-card border-primary border-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-primary fw-bold text-uppercase small">Open</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['open'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-folder2-open text-primary fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg">
                <div class="card status-card border-warning border-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-warning fw-bold text-uppercase small">Pending</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['pending'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-hourglass-split text-warning fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg">
                <div class="card status-card border-info border-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-info fw-bold text-uppercase small">Under Evaluation</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['under_evaluation'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-search text-info fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg">
                <div class="card status-card border-secondary border-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-secondary fw-bold text-uppercase small">Closed</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['closed'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-archive-fill text-secondary fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg">
                <div class="card status-card border-danger border-2">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-danger fw-bold text-uppercase small">Declined</div>
                            <div class="fs-4 fw-bold text-dark">{{ $statusCounts['declined'] ?? 0 }}</div>
                        </div>
                        <i class="bi bi-x-circle-fill text-danger fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Urgent Tenders Table (Dynamic) -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Urgent Tenders (Next 3 Days)</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-start">Tender Name</th>
                                    <th class="text-start">Submission Date</th>
                                    <th class="text-center">Days Left</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($urgentTenders as $tender)
                                <tr>
                                    <td>{{ $tender->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($tender->submission_date)->format('d M, Y') }}</td>
                                    <td class="text-center">
                                        @php
                                            $daysLeft = \Carbon\Carbon::now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($tender->submission_date)->startOfDay(), false);
                                        @endphp
                                        @if ($daysLeft <= 0)
                                            <span class="badge bg-danger fw-bold rounded-pill">Due Today!</span>
                                        @elseif ($daysLeft == 1)
                                            <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">1 Day</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">{{ $daysLeft }} Days</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            {{-- استبدل '#' بالمسارات الصحيحة --}}
                                            <a href="#" class="btn btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="#" class="btn btn-outline-primary" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                            <a href="#" class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash2-fill"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4">No urgent tenders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Charts (Dynamic Data passed via data-attributes) -->
        <div class="row mt-4 g-4">
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

    @push('scripts')
    {{-- We push the required JS libraries to the main layout --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- تأكد من أن هذا المسار صحيح --}}
    <script src="{{ asset('/js/dashboard.js' ) }}"></script>
    @endpush

</x-app-layout>
