<?php
use Rikkei\Assets\View\AssetConst;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\TeamList;
use Rikkei\Assets\View\AssetView;

$teamsOptionAll = TeamList::toOption(null, true, false);
$teamDefault = $teamsOptionAll[0]['value'];
$employees = AssetView::getEmployeesByTeam($teamDefault)
?>
<div id="modalReport-{{ AssetConst::REPORT_TYPE_DETAIL_BY_EMPLOYEE }}" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <form id="" class="form-report-by-employee" method="POST" accept-charset="UTF-8" autocomplete="off">
                {!! csrf_field() !!}
                <input type="hidden" name="report_type" value="{{ AssetConst::REPORT_TYPE_DETAIL_BY_EMPLOYEE }}">
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('asset::view.Report parameters') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Team') }} <em>*</em></label>
                        <div class="input-box">
                            <select id="team_id_report" class="form-control select-search input-select-team-member" name="team_id" autocomplete="off" style="width: 100%;">
                                @foreach($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}" <?php if ($option['value'] == $teamDefault) { ?> selected<?php } ?>>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="group_employees">
                        @include('asset::item.report.include.employees_list', ['employees' => $employees])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                    <button type="submit" class="btn btn-primary pull-right btn-submit submit-report">{{ trans('asset::view.Select') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
