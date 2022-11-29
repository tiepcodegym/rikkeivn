<?php
use Rikkei\Education\Model\Status;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Http\Services\ManagerService;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\CookieCore;

$tableTask = EducationCourse::getTableName();
$status = Status::$STATUS;
$status_new = Status::STATUS_NEW;
$status_close = Status::STATUS_CLOSED;
$role = Status::$ROLE;
$teamPath = Team::getTeamPathTree();
$filterDivision = CoreForm::getFilterData('search', 'division');

$teamIdSelectedJson = \Rikkei\Core\View\CacheBase::getFile('Education/', 'teamIdSelected');
$teamIdSelected = json_decode($teamIdSelectedJson);

\Rikkei\Core\View\CacheBase::forgetFile('Education/', 'teamIdSelected');

?>
<div class="filter-multi-select multi-select-style division select-full">
    <?php $filterDivision = CoreForm::getFilterData('search', 'division');?>
    @if (isset($teamIdSelected) && count($teamIdSelected) > 0)
        <select name="filter[search][division][]" id="team_id_add" class="form-control filter-grid hidden select-multi"
                autocomplete="off" multiple>
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
    @else
        @if ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
            <select name="filter[search][division][]" id="team_id_add"
                    class="form-control filter-grid hidden select-multi" autocomplete="off" multiple>
                {{-- show team available --}}
                @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                    @foreach($teamsOptionAll as $option)
                        @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                            <option class="js-team" value="{{ $option['value'] }}"
                                    {{ (!empty($filterDivision) && in_array($option['value'], $teamIdSelected)) ? 'selected' : '' }}
                                    <?php if ($teamIdsAvailable === true):
                                    elseif (!in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                                ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}
                            </option>
                        @endif
                    @endforeach
                @endif
            </select>
        @endif
    @endif
</div>

