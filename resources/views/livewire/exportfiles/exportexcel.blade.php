<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Tenders Export</title>
    <link rel="stylesheet" href="{{ asset('/css/excel.css') }}">
</head>

<body>
    <table>
        <thead>
            <tr>
                {{-- نفس الأعمدة الموجودة في ملف الـ PDF المحدث --}}
                <th>ID</th>
                <th>Name</th>
                <th>Number</th>
                <th>Client Type</th>
                <th>Client Name</th>
                <th>Assigned To</th>
                <th>Purchase Date</th>
                <th>Submission Date</th>
                <th>Reviewed by</th>
                <th>Last Date of Clarification</th>
                <th>3rd Party?</th>
                <th>Status</th>
                <th>Submitted Price</th>
                <th>Awarded Price</th>
                <th>Reason of Recall</th>
                <th>Reason of Cancel</th>
            </tr>
        </thead>
        <tbody>
            {{-- ✅✅✅ تم تصحيح اسم المتغير إلى $tenders ✅✅✅ --}}
            @forelse($tenders as $tender)
            <tr>
                <td>{{ $tender->id }}</td>
                <td>{{ $tender->name ?? '' }}</td>
                <td>{{ $tender->number ?? '' }}</td>
                <td>{{ $tender->client_type ?? '' }}</td>
                <td>{{ $tender->client_name ?? '' }}</td>
                <td>{{ $tender->assigned_to ?? '' }}</td>
                <td>{{ $tender->date_of_purchase ? $tender->date_of_purchase->format('Y-m-d') : '' }}</td>
                <td>{{ $tender->date_of_submission ? $tender->date_of_submission->format('Y-m-d') : '' }}</td>
                <td>{{ $tender->reviewed_by ?? '' }}</td>
                <td>{{ $tender->last_date_of_clarification ? $tender->last_date_of_clarification->format('Y-m-d') : '' }}</td>
                <td>{{ $tender->has_third_party ? 'Yes' : 'No' }}</td>
                <td>{{ $tender->status ?? '' }}</td>
                <td>{{ $tender->submitted_price ?? '' }}</td>
                <td>{{ $tender->awarded_price ?? '' }}</td>
                <td>{{ $tender->reason_of_recall ?? '' }}</td>
                <td>{{ $tender->reason_of_cancel ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="16">No tenders found for the selected criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>