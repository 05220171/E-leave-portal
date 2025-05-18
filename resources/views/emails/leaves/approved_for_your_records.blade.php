@component('mail::message')
# Approved Leave Ready for Your Records ({{ $recordKeeperRoleTitle }})

Hello {{ $recordKeeperRoleTitle }},

A leave request for student **{{ $student->name }}** has been fully approved and is now ready for your records and any final processing.

**Student Details:**
- **Name:** {{ $student->name }}
- **Department:** {{ $student->department->name ?? 'N/A' }}
@if($student->program)- **Program:** {{ $student->program }}@endif
@if($student->class)- **Class:** {{ $student->class }}@endif

**Leave Details:**
- **Leave Type:** {{ $leave->type->name ?? 'N/A' }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Number of Days:** {{ $leave->number_of_days }}
- **Reason:** {{ $leave->reason }}
@if($leave->document)
- **Attachment:** A document was attached to this request.
@endif

**Final Approval By:** {{ $finalDecisionMaker->name }} ({{ Str::title(str_replace('_', ' ', $finalDecisionMaker->role)) }})
@if($leave->final_remarks)
**Final Remarks:**
{{ $leave->final_remarks }}
@endif

Please log in to the E-Leave portal to view the details if necessary.

@component('mail::button', ['url' => $actionUrl])
View Portal
@endcomponent

Thanks,<br>
{{ config('app.name') }} E-Leave System
@endcomponent