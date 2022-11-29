@if (!empty($approvers))
    @foreach ($approvers as $approver)
        <option value="{{ $approver->emp_id }}">{{ $approver->emp_name . ' (' . preg_replace('/@.*/', '',$approver->emp_email) . ')' }}</option>
    @endforeach
@endif