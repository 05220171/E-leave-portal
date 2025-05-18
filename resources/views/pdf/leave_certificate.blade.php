<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Record - {{ $leave->student->name ?? 'Student' }}</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; line-height: 1.5; color: #333; margin: 0; padding: 0; font-size: 12px; }
        .container { width: 90%; margin: 20px auto; border: 1px solid #b0b0b0; padding: 25px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header-block { text-align: center; margin-bottom: 25px; padding-bottom:15px; border-bottom: 1px solid #eaeaea;}
        .header-block .institution-name { font-size: 20px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .header-block .certificate-title { font-size: 18px; font-weight: bold; margin-top: 10px; color: #34495e; }

        h2.section-heading { font-size: 16px; margin-top: 20px; margin-bottom: 10px; color: #34495e; border-bottom: 1px solid #eaeaea; padding-bottom: 5px;}

        table.details-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.details-table th, table.details-table td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #f0f0f0; }
        table.details-table th { font-weight: bold; width: 35%; background-color: #f9f9f9; color: #555;}
        table.details-table tr:last-child th, table.details-table tr:last-child td { border-bottom: none; }


        table.history-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        table.history-table th, table.history-table td { border: 1px solid #dddddd; padding: 6px 8px; text-align: left; }
        table.history-table th { background-color: #f9f9f9; font-weight: bold; }

        .status-approved { color: #28a745; font-weight: bold; }
        .remarks-block { margin-top: 8px; padding: 8px; border-left: 3px solid #17a2b8; background-color: #f1f9fa; font-style: italic; font-size: 11px; }

        .footer { text-align: center; font-size: 10px; color: #7f8c8d; margin-top: 30px; border-top: 1px solid #eaeaea; padding-top: 15px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="header-block">
            {{-- If you have a logo, ensure it's accessible via an absolute path or embed it --}}
            {{-- <img src="{{ public_path('images/institution_logo.png') }}" alt="Institution Logo" style="max-height: 70px; margin-bottom: 10px;"> --}}
            <div class="institution-name">{{ config('app.name', 'Your E-Leave Portal') }}</div>
            <div class="certificate-title">Official Leave Record</div>
        </div>

        <h2 class="section-heading">Student Information</h2>
        <table class="details-table">
            <tr><th>Student Name:</th><td>{{ $leave->student->name ?? 'N/A' }}</td></tr>
            <tr><th>Student ID:</th><td>{{-- Assuming User model has a student_id_number or similar --}}
                {{ $leave->student->student_id_number ?? $leave->student->id }} {{-- Fallback to user ID --}}
            </td></tr>
            <tr><th>Department:</th><td>{{ $leave->student->department->name ?? 'N/A' }}</td></tr>
            @if($leave->student->program)
            <tr><th>Program:</th><td>{{ $leave->student->program }}</td></tr>
            @endif
            @if($leave->student->class)
            <tr><th>Class/Year:</th><td>{{ $leave->student->class }}</td></tr>
            @endif
        </table>

        <h2 class="section-heading">Leave Details</h2>
        <table class="details-table">
            <tr><th>Leave Type:</th><td>{{ $leave->type->name ?? 'N/A' }}</td></tr>
            <tr><th>Start Date:</th><td>{{ $leave->start_date->format('F d, Y (l)') }}</td></tr>
            <tr><th>End Date:</th><td>{{ $leave->end_date->format('F d, Y (l)') }}</td></tr>
            <tr><th>Number of Days:</th><td>{{ $leave->number_of_days }}</td></tr>
            <tr><th>Reason Submitted:</th><td>{{ $leave->reason }}</td></tr>
            <tr><th>Date Applied:</th><td>{{ $leave->created_at->format('F d, Y H:i A') }}</td></tr>
            <tr>
                <th>Final Status:</th>
                <td><span class="status-approved">{{ Str::title(str_replace('_', ' ', $leave->overall_status)) }}</span></td>
            </tr>
            @if($leave->document)
            <tr><th>Supporting Document:</th><td>Available on Portal (Filename: {{ basename($leave->document) }})</td></tr>
            @endif
        </table>

        <h2 class="section-heading">Approval History</h2>
        @if($leave->approvalActions->isNotEmpty())
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Step</th>
                        <th>Action By (Role)</th>
                        <th>Action By (Name)</th>
                        <th>Action Taken</th>
                        <th>Date & Time</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leave->approvalActions->sortBy('workflow_step_number') as $action)
                        <tr>
                            <td>{{ $action->workflow_step_number }}</td>
                            <td>{{ Str::title($action->acted_as_role) }}</td>
                            <td>{{ $action->user->name ?? 'N/A' }}</td>
                            <td>{{ Str::title($action->action_taken) }}</td>
                            <td>{{ $action->action_at->format('F d, Y H:i A') }}</td>
                            <td>{!! nl2br(e($action->remarks ?: '-')) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No detailed approval actions found for this record (Status was directly set or history not fully logged).</p>
        @endif

        @if($leave->final_remarks && $leave->overall_status === 'approved')
            <h2 class="section-heading">Final Approver's Remarks</h2>
            <div class="remarks-block">
                <p>{!! nl2br(e($leave->final_remarks)) !!}</p>
            </div>
        @endif

        <div class="footer">
            Generated on: {{ now()->format('F d, Y H:i A') }}<br>
            This is a system-generated document from {{ config('app.name') }}.
        </div>
    </div>
</body>
</html>