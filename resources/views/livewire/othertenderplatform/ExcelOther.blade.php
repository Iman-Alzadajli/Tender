<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tenders Of Other Platforms</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tender Name</th>
                <th>Number</th>
                <th>Client Type</th>
                <th>Assigned To</th>
                <th>Submission Date</th>
                <th>Status</th>
                <th>Quarter</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tenders as $tender)
                <tr>
                    <td>{{ $tender->id }}</td>
                    <td>{{ $tender->name }}</td>
                    <td>{{ $tender->number }}</td>
                    <td>{{ $tender->client_type }}</td>
                    <td>{{ $tender->assigned_to }}</td>
                    <td>{{ $tender->date_of_submission->format('Y-m-d') }}</td>
                    <td>{{ $tender->status }}</td>
                    <td>{{ $tender->quarter }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
