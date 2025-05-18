@component('mail::message')
# Leave Request Update

Hello **{{ $student->name }}**,

We regret to inform you that your leave request has been rejected by the DSA.

**Leave Details:**
- **Leave Type:** {{ $leave->type->name }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Reason Submitted:** {{ $leave->reason }}

@if($rejectionRemarks)
**Reason for Rejection:**
{{ $rejectionRemarks }}
@else
Please contact the student affairs office for more details regarding the rejection.
@endif

You can view your leave history for more details.

@component('mail::button', ['url' => $leaveUrl])
View Leave History
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent