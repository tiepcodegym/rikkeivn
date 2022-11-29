<?php
    use Rikkei\Assets\View\AssetConst;
    use Rikkei\Team\View\TeamList;

    $teamsOptionAll = TeamList::toOption(null, true, false);
?>
<div id="report_lost_and_broken" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <form id="form_report_lost_and_broken" method="POST" accept-charset="UTF-8" autocomplete="off" action="{{ route('asset::asset.view-report') }}">
                {!! csrf_field() !!}
                <input type="hidden" name="report_type" value="{{ AssetConst::REPORT_TYPE_LOST_AND_BROKEN }}">
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('asset::view.Report parameters') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Statistics from date') }} <em>*</em></label>
                         <div class="input-box">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class='input-group date statistic_date_picker'>
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                        <input type="text" name="date" id="statistic_date" class="form-control" />
                                    </div>
                                    <label class="asset-error" id="statistic_date-error">{{ trans('asset::message.The field is required') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required">{{ trans('asset::view.Team') }} </label>
                        <div class="input-box">
                            <select name="team_id" class="form-control select-search input-select-team-member" id="team_id" autocomplete="off" style="width: 100%;">
                                <option value="">{{ trans('asset::view.Select team') }}</option>
                                @foreach($teamsOptionAll as $option)
                                    <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                            <label class="asset-error" id="team_id-error">{{ trans('asset::message.The field is required') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">{{ trans('asset::view.Close') }}</button>
                    <button type="button" class="btn btn-primary pull-right btn-submit" onclick="return submitReportLostAndBroken();">{{ trans('asset::view.Select') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>