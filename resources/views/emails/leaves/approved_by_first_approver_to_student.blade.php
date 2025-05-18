@component('mail::message')
# Your Leave Request has been Approved and Forwarded

Hello **{{ $student->name }}**,

Your leave request has been approved by the **{{ $actingApproverRoleTitle }} ({{ $actingApprover->name }})**.

It has now been forwarded to the **{{ $nextApproverRoleTitle }}** for the next stage of review.

**Leave Details:**
- **Leave Type:** {{ $leave->type->name ?? 'N/A' }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}

@if($leave->approvalActions->where('acted_as_role', strtolower($actingApproverRoleTitle))->last()?->remarks)
**Remarks from {{ $actingApproverRoleTitle }}:**
{{ $leave->approvalActions->where('acted_as_role', strtolower($actingApproverRoleTitle))->last()->remarks }}
@endif

We will keep you updated on its progress.

You can view the status of your request here:
@component('mail::button', ['url' => $leaveUrl])
View Leave Status
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent