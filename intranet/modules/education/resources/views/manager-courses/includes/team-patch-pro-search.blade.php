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
$filterDivision = CoreForm::getFilterData('search', 'division');
if ($filterDivision == null) {
    $filterDivision = [];
}
?>
<div class="col-md-12 filter-multi-select multi-select-style-search division select-full">
    @if ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
        <select name="filter[search][division][]" id="team_id_search"
                class="form-control filter-grid hidden select-multi" autocomplete="off" multiple>
            {{-- show team available --}}
            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                @foreach($teamsOptionAll as $option)
                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                        <option class="js-team-search" value="{{ $option['value'] }}"
                                {{ (!empty($filterDivision) && in_array($option['value'], $filterDivision)) ? 'selected' : '' }}
                                <?php if ($teamIdsAvailable === true): elseif (!in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else: ?>{{ $option['option'] }}<?php endif; ?> data-is-checkeds="{{in_array($option['value'], $filterDivision) ? 'true' : 'false'}}">{{ $option['label'] }}
                        </option>
                    @endif
                @endforeach
            @endif
        </select>
    @endif
</div>

