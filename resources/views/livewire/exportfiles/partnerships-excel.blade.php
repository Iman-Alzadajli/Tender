<html>
<head>
    <meta charset="UTF-8">
    <title>Partnerships Report</title>
   
</head>
<body>
   
    
    <table>
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Client Name</th>
                <th>Client Type</th>
                <th>Contact Person</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach($partnerships as $partnership)
            <tr>
                <td>{{ $partnership->company_name }}</td>
                <td>{{ $partnership->client_name ?? 'N/A' }}</td>
                <td>{{ $partnership->client_type ?? 'N/A' }}</td>
                <td>{{ $partnership->person_name }}</td>
                <td>{{ $partnership->phone }}</td>
                <td>{{ $partnership->email }}</td>
                <td>{{ $partnership->details ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
</body>
</html>