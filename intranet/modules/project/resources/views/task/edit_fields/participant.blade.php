<?php 
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Employee;
 ?>
<div class="form-group form-group-select2">
    <label for="participant" class="col-sm-3 control-label required">{{ trans('project::view.Participants') }}</label>
    <div class="col-md-9 fg-valid-custom">
        <select name="task_participant[]" class="select-search" id="participant"{{ $disabledParticipant }} multiple="multiple"
            data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
            @if ($participants)
                @foreach ($participants as $participant)
                    <option value="{{ $participant->employee_id }}" selected>{{ CoreView::getNickName($participant->email) }}</option>
                @endforeach
            @endif
        </select>
    </div>
</div>
