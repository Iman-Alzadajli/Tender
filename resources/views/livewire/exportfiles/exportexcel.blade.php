<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        
        thead tr {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Focal Points Contact List</h2>
        <p>Generated on {{ date('F d, Y') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Client Type</th>
                <th>Name</th>
                <th>Department</th>
                <th>Phone</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contacts as $contact)
                <tr>
                    <td>{{ $contact->client_name ?? 'N/A' }}</td>
                    <td>{{ $contact->client_type ?? 'N/A' }}</td>
                    <td>{{ $contact->name ?? 'N/A' }}</td>
                    <td>{{ $contact->department ?? 'N/A' }}</td>
                    <td>{{ $contact->phone ?? 'N/A' }}</td>
                    <td>{{ $contact->email ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>