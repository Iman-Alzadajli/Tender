<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Focal Points Report</title>

    <link rel="stylesheet" href="{{ public_path('css/pdffocal.css' ) }}">

</head>

<body>
    <h1>Focal Points Report</h1>
    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Client Type</th>
                <th>Person Name</th>
                <th>Department</th>
                <th>Phone</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($focalPoints as $fp)
            <tr>
                <td>{{ $fp->client_name }}</td>
                <td>{{ $fp->client_type }}</td>
                <td>{{ $fp->name }}</td>
                <td>{{ $fp->department }}</td>
                <td>{{ $fp->phone }}</td>
                <td>{{ $fp->email }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>