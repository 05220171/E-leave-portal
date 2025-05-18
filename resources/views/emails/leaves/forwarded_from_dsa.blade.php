@component('mail::message')
@if($isForStudent)
# Leave Request Progress Update

Hello **{{ $student->name }}**,

Your leave request has been approved by the DSA and has been forwarded to the **{{ $nextApproverRoleTitle }}** for further review.

**Leave Details:**
- **Leave Type:** {{ $leave->type->name }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}

We will notify you once a final decision is made.
@else
# New Leave Request for Your Approval

Hello {{ $nextApproverRoleTitle }},

A leave request from student **{{ $student->name }}** ({{ $student->department->name ?? 'N/A' }}) requires your approval.

**Leave Details:**
- **Leave Type:** {{ $leave->type->name }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Number of Days:** {{ $leave->number_of_days }}
- **Reason:** {{ $leave->reason }}

Please log in to the portal to review and take action on this request.
@endif

@component('mail::button', ['url' => $actionUrl])
{{ $actionText }}
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent