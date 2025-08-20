<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tender Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .badge {
            display: inline-block;
            padding: .35em .65em;
            font-size: .75em;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: .25rem;
        }
        .bg-primary-subtle { background-color: #53C29D; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .bg-info { background-color: #0dcaf0; color: #000; }
        .bg-secondary { background-color: #6c757d; }
        .bg-danger { background-color: #dc3545; }
    </style>
</head>
<body>
    <h2>Tender Report</h2>
    <p>Date: {{ now()->format('d M, Y') }}</p>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Tender Name</th>
                <th>Client Type</th>
                <th>Assigned To</th>
                <th>Submission Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tenders as $tender)
                <tr>
                    <td>{{ $tender->name }}</td>
                    <td>{{ $tender->client_type }}</td>
                    <td>{{ $tender->assigned_to }}</td>
                    <td>{{ $tender->date_of_submission->format('d M, Y') }}</td>
                    <td>
                        <span class="badge 
                            @if($tender->status == 'Open')  bg-primary-subtle
                            @elseif($tender->status == 'Pending') bg-warning
                            @elseif($tender->status == 'Under Evaluation') bg-info
                            @elseif($tender->status == 'Close') bg-secondary
                            @else bg-danger @endif">
                            {{ $tender->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No tenders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
