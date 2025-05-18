@component('mail::message')
# New Leave Request for Your Approval ({{ $approverRoleTitle }})

Hello {{ $approverRoleTitle }},

A new leave request has been submitted by **{{ $student->name }}** and requires your attention.

**Student Details:**
- **Name:** {{ $student->name }}
- **Department:** {{ $student->department->name ?? 'N/A' }}
@if($student->program)- **Program:** {{ $student->program }}
@endif
@if($student->class)- **Class:** {{ $student->class }}
@endif

**Leave Details:**

- **Leave Type:** {{ $leave->type->name ?? 'N/A' }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Number of Days:** {{ $leave->number_of_days }}
- **Reason:** {{ $leave->reason }}
@if($leave->document)
- **Attachment:** A document was attached to this request. Please view it in the portal.
@endif

Please log in to the E-Leave portal to review and take action on this request.

@component('mail::button', ['url' => $actionUrl])
View Pending Leaves
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent