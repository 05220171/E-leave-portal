@component('mail::message')
# Leave Request Submitted Successfully

Hello **{{ $student->name }}**,

Your leave request has been successfully submitted and is now pending review.

**Leave Details:**
- **Leave Type:** {{ $leave->type->name ?? 'N/A' }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Number of Days:** {{ $leave->number_of_days }}
- **Reason:** {{ $leave->reason }}

Your request has been forwarded to the **{{ $firstApproverRoleTitle }}** for initial approval. You will be notified of any updates.

You can view the status of your request here:
@component('mail::button', ['url' => $leaveUrl])
View Leave Status
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent