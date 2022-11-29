<?php
use Rikkei\Education\Model\Status;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Http\Services\ManagerService;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\Model\Team;

$tableTask = EducationCourse::getTableName();
$status = Status::$STATUS;
$status_new = Status::STATUS_NEW;
$status_close = Status::STATUS_CLOSED;
$role = Status::$ROLE;
$teamPath = Team::getTeamPathTree();
?>
<div class="filter-multi-select multi-select-style division select-full">
    <select name="filter[search][{{$test}}][]" id="team_id" class="form-control filter-grid hidden select-multi"
            autocomplete="off" multiple {{ $dataCourse[0]->status != $statusNew ? 'disabled' : '' }}>
        {{-- show team available --}} 
        @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
            @foreach($teamsOptionAll as $option)
                @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                    <option class="js-team" value="{{ $option['value'] }}"
                            <?php if (in_array($option['value'], $teamIdSelected)): ?> selected <?php endif ;?>
                            <?php if ($teamIdsAvailable === true): elseif (!in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else: ?>{{ $option['option'] }}<?php endif; ?> data-is-checked="{{in_array($option['value'], $teamIdSelected) ? 'true' : 'false'}}">{{ $option['label'] }}</option>
                @endif
            @endforeach
        @endif
    </select>
</div>

