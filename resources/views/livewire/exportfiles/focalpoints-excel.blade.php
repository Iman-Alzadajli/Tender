

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