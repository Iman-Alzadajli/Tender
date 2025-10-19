<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Focal Points List</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.5in;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #555;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }
        
        th, td {
            border: 1px solid #ccc;
            text-align: left;
            padding: 5px 4px;
            word-wrap: break-word;
            overflow: hidden;
            vertical-align: top;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Column widths */
        th:nth-child(1), td:nth-child(1) { width: 20%; } /* Client Name */
        th:nth-child(2), td:nth-child(2) { width: 13%; } /* Client Type */
        th:nth-child(3), td:nth-child(3) { width: 15%; } /* Name */
        th:nth-child(4), td:nth-child(4) { width: 13%; } /* Department */
        th:nth-child(5), td:nth-child(5) { width: 17%; } /* Phone */
        th:nth-child(6), td:nth-child(6) { width: 22%; } /* Email */
        
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Focal Points Contact List</h1>
        <p>Generated on {{ date('F d, Y') }} at {{ date('h:i A') }}</p>
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
            @forelse($contacts as $contact)
                <tr class="no-break">
                    <td>{{ $contact->client_name ?? 'N/A' }}</td>
                    <td>{{ $contact->client_type ?? 'N/A' }}</td>
                    <td>{{ $contact->name ?? 'N/A' }}</td>
                    <td>{{ $contact->department ?? 'N/A' }}</td>
                    <td>{{ $contact->phone ?? 'N/A' }}</td>
                    <td class="truncate">{{ $contact->email ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No focal points found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 20px; text-align: center; font-size: 8px; color: #666;">
        <p>Total Records: {{ count($contacts) }}</p>
    </div>
</body>
</html>