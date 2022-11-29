<!-- Modal edit -->
<div id="modalEdit" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title"> {{ trans('manage_time::view.Update information') }}</h3>
            </div>
            <div class="modal-body">
                <div class="col-container">
                    <div class="row">
                        <div class="col-md-12">
                            <input type='text' name="empId" class="form-control hidden" value=""/>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="required">{{ trans('manage_time::view.From date') }} <em>*</em></label>
                                <div class='input-group date datepickerModal' id="dateStartPickerEdit">
                                    <input type='text' name="startDateEdit" class="form-control" value=""/>
                                    <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="required">{{ trans('manage_time::view.End date') }} <em>*</em></label>
                                <div class='input-group date datepickerModal' id="dateEndPickerEdit">
                                    <input type='text' name="endDateEdit" class="form-control" value=""/>
                                    <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label class="required">{{ trans('manage_time::view.Select work time frame') }}<em>*</em></label>
                            <div class="input-box">
                                @if (isset($workingTimeFrame) && count ($workingTimeFrame))
                                    <select name="workingTimeEdit" id="" class="form-control select2-base">
                                    @foreach ($workingTimeFrame as $key => $item)
                                        <?php
                                            $strMorning = trans('manage_time::view.Morning') . ' '. $item[0] . ' - ' . $item[1];
                                            $strAfter = trans('manage_time::view.Afternoon') . ' '. $item[2] . ' - ' . $item[3];
                                        ?>
                                        <option value="{{$key}}">{{$strMorning}}; {{$strAfter}}</option>
                                    @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label class="required">{{ trans('manage_time::view.Select 1/4 break time') }}<em>*</em></label>
                            <div class="input-box">
                                @if (isset($workingTimeHalfFrame) && count ($workingTimeHalfFrame))
                                    <select name="workingTimeHalfEdit" id="" class="form-control select2-base" disabled>
                                    @foreach ($workingTimeHalfFrame as $key => $item)
                                        <?php
                                            $strMorning = trans('manage_time::view.Morning') . ' '. $item[0];
                                            $strAfter = trans('manage_time::view.Afternoon') . ' '. $item[1];
                                        ?>
                                        <option value="{{$key}}">{{$strMorning}}; {{$strAfter}}</option>
                                    @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default float-left" data-dismiss="modal">{{ trans('manage_time::view.Close') }}</button>
                <button type="button" class="btn btn-info btn-submit-update">{{ trans('manage_time::view.Update') }}</button>
            </div>
        </div>
    </div>
</div>