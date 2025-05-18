@component('mail::message')
# Leave Request Approved

Hello **{{ $student->name }}**,

We want to inform you that your leave request has been approved.

**Leave Details:**
- **Leave Type:** {{ $leave->type->name }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Number of Days:** {{ $leave->number_of_days }}
- **Reason:** {{ $leave->reason }}

@if($leave->final_remarks)
**Remarks from Approver:**
{{ $leave->final_remarks }}
@endif

You can view your leave history for more details.

@component('mail::button', ['url' => $leaveUrl])
View Leave History
@endcomponent

Thanks,<br>
{{ config('e-leave-portal') }}
@endcomponent