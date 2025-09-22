<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tenders Report</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css' ) }}">
</head>
<body>
    <div class="header">
        <h1>Tenders Report</h1>
        <p>Generated on: {{ now()->format('d M, Y H:i') }}</p>
        <p>Total Tenders: {{ $tenders->count() }}</p>
    </div>

    <table class="no-break">
        <thead>
            <tr>
                {{-- تم تعديل العناوين لتشمل الأعمدة الجديدة --}}
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
                <th>Submitted Price</th> {{-- ✅ جديد --}}
                <th>Awarded Price</th>   {{-- ✅ جديد --}}
                <th>Reason of Recall</th>  {{-- ✅ جديد --}}
                <th>Reason of Cancel</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenders as $tender)
            <tr class="no-break">
                <td style="text-align: center;">{{ $tender->id }}</td>
                <td class="truncate">{{ $tender->name ?? '-' }}</td>
                <td>{{ $tender->number ?? '-' }}</td>
                <td>{{ $tender->client_type ?? '-' }}</td>
                <td class="truncate">{{ $tender->client_name ?? '-' }}</td>
                <td>{{ $tender->assigned_to ?? '-' }}</td>
                <td>{{ $tender->date_of_purchase ? $tender->date_of_purchase->format('d-m-y') : '-' }}</td>
                <td>{{ $tender->date_of_submission ? $tender->date_of_submission->format('d-m-y') : '-' }}</td>
                <td>{{ $tender->reviewed_by ?? '-' }}</td>
                <td>{{ $tender->last_date_of_clarification ? $tender->last_date_of_clarification->format('d-m-y') : '-' }}</td>
                <td style="text-align: center;">{{ $tender->has_third_party ? 'Yes' : 'No' }}</td>
                
                {{-- ✅ تم حذف الكلاسات الشرطية من هنا --}}
                <td>{{ $tender->status ?? '-' }}</td>

                {{-- ✅ إضافة الأعمدة الجديدة --}}
                <td>{{ $tender->submitted_price ? number_format($tender->submitted_price, 2) : '-' }}</td>
                <td>{{ $tender->awarded_price ? number_format($tender->awarded_price, 2) : '-' }}</td>
                <td class="truncate">{{ $tender->reason_of_recall ?? '-' }}</td>

                <td class="truncate">{{ $tender->reason_of_cancel ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                {{-- تم تحديث عدد الأعمدة إلى 16 --}}
                <td colspan="16" style="text-align: center; padding: 20px; font-size: 12px;">
                    No tenders found for the selected criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
