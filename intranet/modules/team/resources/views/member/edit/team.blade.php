<?php
/**
 * html add team / position
 * 
 * @param array $teamsOption
 * @param array $postionsOption
 * @param int $index
 * @param int $teamId
 * @param int $positionId
 */
use Rikkei\Team\View\TeamList;

if (!function_exists('teamHtmladdTeamPostion')) {
    function teamHtmladdTeamPostion(
        $id,
        $teamsOption,
        $postionsOption,
        $isProfile = false,
        $index = 0,
        $teamId = 0,
        $positionId = 0,
        $start_at = '',
        $end_at = '',
        $is_working = '',
        $employeePermission = '',
        $classSelectSearch = ' select-search',
        $isScopeCompany,
        $isScopeTeam,
        $teamScope
        )
    { ?>
        <div class="row form-group group-team-position">
            <div class="col-md-2 input-team-position input-team">
                <input type="hidden" class="input-id-hidden" name="team[{{ $index }}][id]" value="{{ $id }}"/>
                <label class="control-label">{!!trans('team::view.Team')!!}</label>
                <select name="team[{{ $index }}][team]" id="{{ $index }}" class="has-search form-control{!!$classSelectSearch!!} team" style="width:100%"
                    data-flag-dom="select2"{!!$employeePermission!!}>
                    @if ($name = TeamList::getNameTeamDeleted($teamId))
                        <option value="{{ $teamId }}" selected >{{ $name->name }}</option>
                    @else
                        @foreach($teamsOption as $option)
                        @if ($isScopeCompany || ($isScopeTeam && is_array($teamScope) && in_array($option['value'], $teamScope)))
                            <option value="{{ $option['value'] }}"<?php
                            if ($option['value'] == $teamId): ?> selected<?php endif;
                            ?>{{ $option['option'] }}>{{ $option['label'] }}</option>
                        @endif
                        @endforeach
                    @endif
                </select>
                @if ($teamId != 0)
                    @if ($name = TeamList::getNameTeamDeleted($teamId))
                        <input type="hidden" class="input-team-hidden" name="team[{{ $index }}][team]" value="{{ $teamId }}"/>
                    @else
                        @foreach($teamsOption as $input)
                            @if ($input['value'] == $teamId)
                                <input type="hidden" class="input-team-hidden" name="team[{{ $index }}][team]" value="{{ $teamId }}"/>
                            @endif
                        @endforeach
                    @endif
                @endif
                </select>
            </div>
            <div class="col-md-2 input-team-position input-position">
                <label class="control-label">{{ trans('team::view.Position') }}</label>
                <select name="team[{{ $index }}][position]" class="form-control{!!$classSelectSearch!!}" style="width:100%"
                    data-flag-dom="select2"{!!$employeePermission!!}>
                    @foreach($postionsOption as $option)
                        <option value="{{ $option['value'] }}"<?php
                            if ($option['value'] == $positionId): ?> selected<?php endif; 
                        ?>>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 input-team-position input-start_date">
                <label class="control-label required-text">{{ trans('team::view.Start Date') }}</label>
                <span class="fa fa-question-circle help" title="{{ trans('team::view.The day began to work in team.') }}"></span>
                <input type="text" class="form-control date-picker input-start-at" data-flag-type="date" placeholder="yyyy-mm-dd" value="{{ $start_at }}" name="team[{{ $index }}][start_at]" />
                <i class="fa fa-calendar form-control-feedback margin-right-20" style="line-height: 84px"></i>
            </div>
            <div class="col-md-3 input-team-position input-end_date">
                <label class="control-label">{{ trans('team::view.End Date') }}</label>
                <span class="fa fa-question-circle help" title="{{ trans('team::view.The day end to work in team.') }}"></span>
                <input type="text" class="form-control date-picker input-end-at" data-flag-type="date" placeholder="yyyy-mm-dd" value="{{ $end_at }}" name="team[{{ $index }}][end_at]" />
                <i class="fa fa-calendar form-control-feedback margin-right-20" style="line-height: 84px"></i>
            </div>
            <div class="col-md-1 input-team-position is_working">
                <div>
                    <label class=" control-label">{{ trans('team::view.now') }}</label>
                    <span class="fa fa-question-circle help" title="{{ trans('team::view.current_team_help') }}"></span>
                </div>
                <div>
                    <input type="radio" name="team[{{ $index }}][is_working]" class="is-working" @if($is_working == 1) checked @endif />
                </div>
            </div>
            <div class="col-md-1">
                @if (!$employeePermission)
                    <label class=" control-label">&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-danger input-team-position input-remove warn-confirm btn-remove-profile-team" data-toggle="tooltip"
                            data-flag-dom="btn-remove-profile-team"
                            data-placement="top" title="{{ trans('team::view.Remove team') }}" data-noti="{{ trans('team::view.Employee must belong to at least one team') }}" data-noti-confirm="{{ trans('team::view.Are you sure want to delete team?') }}">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                @endif
            </div>
        </div>
<?php }
}
?>

<div class="form-label-left box-form-team-position">
    <?php 
    $i = 1;
    $employeePermission = $isScopeCompany ? '' : ' disabled'
    ?>
    @if (count($employeeTeamPositions))
        @foreach ($employeeTeamPositions as $employeeTeamPosition)
            <?php teamHtmladdTeamPostion(
                    $employeeTeamPosition->id,
                    $teamsOption, 
                    $postionsOption, 
                    $isSelfProfile,
                    $i,
                    $employeeTeamPosition->team_id, 
                    $employeeTeamPosition->role_id,
                    $employeeTeamPosition->start_at,
                    $employeeTeamPosition->end_at,
                    $employeeTeamPosition->is_working,
                    $employeePermission,
                    ' select-search',
                    $isScopeCompany,
                    $isScopeTeam,
                    $teamScope
                ); ?>
            <?php $i++; ?>
        @endforeach
    @endif

    @if ($isScopeCompany)
        <div class="group-team-position-orgin hidden">
            <?php teamHtmladdTeamPostion(
                    0,
                    $teamsOption,
                    $postionsOption,
                    $isSelfProfile,
                    $i++,
                    0,
                    0,
                    '',
                    '',
                    '',
                    '',
                    '',
                    $isScopeCompany,
                    $isScopeTeam,
                    $teamScope
                ); ?>
        </div>
    @endif
    
</div>
<span class="error is-working-error hidden" style="float: right; margin-right: 20px;">{{ trans('team::view.Check a working team') }}</span>
<div style="margin-bottom: 10px;">
    <a href="javascript:void(0)" class="show_team_leave">{{ trans('team::view.Show teams leave') }}</a>
    <a href="javascript:void(0)" class="hide_team_leave hidden">{{ trans('team::view.Hide teams leave') }}</a>
</div>

@if ($isScopeCompany)
<div class="form-horizontal">
    <div class="form-group">
        <div class="input-team-position input-add-new col-md-12">
            <button type="button" class="btn-add hidden"
                data-flag-dom="btn-add-profile-team" data-btn-add="profile-last">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
</div>
@endif