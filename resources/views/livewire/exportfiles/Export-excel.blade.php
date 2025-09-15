<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tenders Export</title>
    <link rel="stylesheet" href="{{ asset('/css/excel.css') }}">


</head>
<body>
    <table>
        <thead>
            <tr>
                {{-- Basic Information --}}
                <th>ID</th>
                <th>Tender Name</th>
                <th>Tender Number</th>
                <th>Client Type</th>
                <th>Client Name</th>
                <th>Assigned To</th>
                <th>Date of Purchase</th>
                <th>Date of Submission</th>
                <th>Reviewed by</th>
                <th>Date of Submission of BA</th>
                <th>Date of Submission after Review</th>


                {{-- Follow-up & Status --}}
                <th>Has Third-Party?</th>
                <th>Last Follow-up Date</th>
                <th>Follow-up Channel</th>
                <th>Follow-up Notes</th>
                <th>Status</th>
                <th>Reason of Cancel</th>
            </tr>
         
        </thead>
        <tbody>
            @foreach($tenders as $tender)
                <tr>
                    {{-- Basic Information --}}
                    <td>{{ $tender->id }}</td>
                    <td>{{ $tender->name }}</td>
                    <td>{{ $tender->number }}</td>
                    <td>{{ $tender->client_type }}</td>
                    <td>{{ $tender->client_name }}</td>
                    <td>{{ $tender->assigned_to }}</td>
                    <td>{{ $tender->date_of_purchase ? $tender->date_of_purchase->format('Y-m-d') : '' }}</td>
                    <td>{{ $tender->date_of_submission ? $tender->date_of_submission->format('Y-m-d') : '' }}</td>
                    <td>{{ $tender->reviewed_by }}</td>
                    <td>{{ $tender->date_of_submission_ba ? $tender->date_of_submission_ba->format('Y-m-d') : '' }}</td>
                    <td>{{ $tender->date_of_submission_after_review ? $tender->date_of_submission_after_review->format('Y-m-d') : '' }}</td>
   

                    {{-- Follow-up & Status --}}
                    <td>{{ $tender->has_third_party ? 'Yes' : 'No' }}</td>
                    <td>{{ $tender->last_follow_up_date ? $tender->last_follow_up_date->format('Y-m-d') : '' }}</td>
                    <td>{{ $tender->follow_up_channel }}</td>
                    <td>{{ $tender->follow_up_notes }}</td>
                    <td>{{ $tender->status }}</td>
                    <td>{{ $tender->reason_of_cancel }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
