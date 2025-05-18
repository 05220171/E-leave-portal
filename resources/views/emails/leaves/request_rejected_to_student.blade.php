@component('mail::message')
# Your Leave Request Has Been Rejected

Hello **{{ $student->name }}**,

We regret to inform you that your leave request has been rejected.

**Rejected By:** {{ $rejectingApprover->name }} ({{ $rejectingApproverRoleTitle }})

@if($rejectionRemarks)
**Reason for Rejection:**
{{ $rejectionRemarks }}
@else
Please contact the administration or the respective authority for more details regarding the rejection.
@endif

**Leave Details Reviewed:**
- **Leave Type:** {{ $leave->type->name ?? 'N/A' }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Reason Submitted:** {{ $leave->reason }}

You can view your leave history for more details.

@component('mail::button', ['url' => $leaveUrl])
View Leave History
@endcomponent

Regards,<br>
{{ config('app.name') }}
@endcomponent