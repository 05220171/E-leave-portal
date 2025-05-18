@component('mail::message')
# Student Leave Approved - For Your Information

This is to inform you that a leave request for the following student has been approved:

**Student Details:**
- **Name:** {{ $student->name }}
- **Email:** {{ $student->email }}
- **Department:** {{ $student->department->name ?? 'N/A' }}
@if($student->program)
- **Program:** {{ $student->program }}
@endif
@if($student->class)
- **Class:** {{ $student->class }}
@endif

**Leave Details:**
- **Leave Type:** {{ $leave->type->name }}
- **Start Date:** {{ $leave->start_date->format('D, M j, Y') }}
- **End Date:** {{ $leave->end_date->format('D, M j, Y') }}
- **Number of Days:** {{ $leave->number_of_days }}
- **Reason:** {{ $leave->reason }}

**Approved By:** {{ $approvingUser->name }} ({{ Str::title($approvingUser->role) }})
@if($leave->final_remarks)
**Approver Remarks:**
{{ $leave->final_remarks }}
@endif

This email is for your information and record-keeping.

Thanks,<br>
{{ config('E-Leave System') }} 
@endcomponent