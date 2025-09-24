<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contacts Export</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        thead { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                {{-- هذه هي الأعمدة الصحيحة لبيانات جهات الاتصال --}}
                <th>Tender Name</th>
                <th>Client Name</th>
                <th>Tender Type</th>
                <th>Contact Type</th>
                <th>Contact/Company Name</th>
                <th>Person Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Department</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            {{-- ✅ نستخدم متغير 'tenders' الذي ترسله الدالة --}}
            @forelse($tenders as $contact)
            <tr>
                <td>{{ $contact->tender_name ?? '' }}</td>
                <td>{{ $contact->client_name ?? '' }}</td>
                <td>{{ $contact->tender_type ? str_replace('_', ' ', $contact->tender_type) : '' }}</td>
                <td>{{ $contact->contact_type ?? '' }}</td>
                <td>{{ $contact->contact_name ?? '' }}</td>
                <td>{{ $contact->person_name ?? '' }}</td>
                <td>{{ $contact->phone ?? '' }}</td>
                <td>{{ $contact->email ?? '' }}</td>
                <td>{{ $contact->department ?? '' }}</td>
                <td>{{ $contact->details ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10">No contacts found for the selected criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
