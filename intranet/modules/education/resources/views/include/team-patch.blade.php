<?php
use Rikkei\Education\Model\Status;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Http\Services\ManagerService;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\Model\Team;
?>
<div class="col-md-12 filter-multi-select multi-select-style division select-full">
    <label style="text-align: center" for="status" class="col-md-3 control-label margin-top-10">{{ trans('education::view.scale') }}</label>
    <?php $filterDivision = CoreForm::getFilterData('search', 'division');?>
    @if ($teamIdsAvailable || ($teamTreeAvailable && count($teamTreeAvailable)))
        <select id="team_id" name="filter[search][division][]" class="form-control filter-grid hidden select-multi" autocomplete="off" multiple>
            {{-- show team available --}}
            @if ($teamIdsAvailable === true || (count($teamsOptionAll) && $teamTreeAvailable))
                @foreach($teamsOptionAll as $option)
                    @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                        <option class="js-team" value="{{ $option['value'] }}"
                                {{ (!empty($filterDivision) && in_array($option['value'], $filterDivision)) ? 'selected' : '' }}
                                <?php if ($teamIdsAvailable === true):
                                elseif (!in_array($option['value'], $teamIdsAvailable)): ?> disabled<?php else:
                            ?>{{ $option['option'] }}<?php endif; ?>>{{ $option['label'] }}
                        </option>
                    @endif
                @endforeach
            @endif
        </select>
    @endif
</div>

