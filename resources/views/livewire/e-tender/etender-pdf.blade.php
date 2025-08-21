<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tenders Report</title>
    <style>
        /* أضفنا بعض التنسيقات الأساسية لتحسين شكل الـ PDF */
        body {
            font-family: 'DejaVu Sans', sans-serif; /* خط يدعم اللغة العربية والرموز */
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even ) {
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Tenders Report</h1>
        <p>Generated on: {{ now()->format('d M, Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tender Name</th>
                <th>Client Type</th>
                <th>Assigned To</th>
                <th>Submission Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tenders as $index => $tender)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $tender->name ?? 'N/A' }}</td>
                    <td>{{ $tender->client_type ?? 'N/A' }}</td>
                    <td>{{ $tender->assigned_to ?? 'N/A' }}</td>
                    <td>
                        {{-- استخدام عامل التشغيل الآمن لتجنب الخطأ إذا كان التاريخ فارغاً --}}
                        {{ $tender->date_of_submission?->format('d M, Y') ?? 'N/A' }}
                    </td>
                    <td>{{ $tender->status ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No tenders found matching the criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
