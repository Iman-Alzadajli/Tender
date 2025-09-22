<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detailed Contact Report</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css' ) }}">
</head>
<body>
    <div class="header">
        <h1>Detailed Contact Report</h1>
        <p>Generated on: {{ now()->format('d M, Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                {{-- رؤوس الأعمدة حسب طلبك بالضبط --}}
                <th>Tender Name</th>
                <th>Client Name</th>
                <th>Tender Type</th>
                <th>Contact Type</th>
                <th>Company Name</th>
                <th>Person Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Department</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
            <tr>
                {{-- معلومات المناقصة (مكررة لكل جهة اتصال) --}}
                <td>{{ $contact->tender_name }}</td>
                <td>{{ $contact->client_name }}</td>
                <td>{{ str_replace('_', ' ', Str::title($contact->tender_type)) }}</td>

                {{-- معلومات جهة الاتصال --}}
                <td>{{ $contact->contact_type }}</td>
                <td>{{ $contact->contact_name }}</td>
                <td>{{ $contact->person_name ?? '-' }}</td>
                <td>{{ $contact->phone }}</td>
                <td>{{ $contact->email }}</td>
                <td>{{ $contact->department ?? '-' }}</td>
                <td>{{ $contact->details ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 20px;">
                    No contacts found for the selected criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
