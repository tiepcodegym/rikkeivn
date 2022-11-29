<?php
use Rikkei\Core\Model\CoreConfigData;
?>
<div class="col-md-12">
    <div class="box box-rikkei">
        <div class="box-body">
            <?php
            $cssMail = CoreConfigData::getCssMail();
            ?>
            <form id="form-system-css-mail" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="form-group form-label-left row">
                            <label for="cssmail" class="col-sm-2 control-label">{{ trans('core::view.CSS Sender') }} </label>
                            <div class="col-md-10">
                                <input name="item[cssmail]" class="form-control input-field" type="text" 
                                       id="cssmail" value="{{ $cssMail }}" />
                                <p class="hint">{{ trans('core::view.CSS sent to customers with the format') }} </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    </div>
                </div>
            </form>

            <?php
            $value = CoreConfigData::getAccountToEmail(1, 'auto_approve');
            $autoOptions = CoreConfigData::autoOptions();
            ?>
            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group form-label-left row">
                            <label for="{{CoreConfigData::AUTO_APPROVE_KEY}}" class="col-sm-2 control-label">{{trans('core::view.Request Approval Settings')}} </label>
                            <div class="col-md-9">
                                @foreach ($autoOptions as $k => $v)
                                <label>
                                    <input type="radio" name="item[{{CoreConfigData::AUTO_APPROVE_KEY}}]" value="{{$k}}"
                                           @if ($k == $value) checked @endif   />
                                           {{$v}}
                                </label>
                                &nbsp;&nbsp;&nbsp;
                                @endforeach
                                <p class="hint">{{trans('core::view.selecting Automatically approved, each request will be assigned automatically to the first account on HR')}}</p>
                            </div>
                            <div class="col-md-1">
                                <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php
            $rangeTimes = CoreConfigData::getValueDb('working_range_times');
            $rangeTimes = $rangeTimes ? unserialize($rangeTimes) : [
                'start1' => '07:00',
                'end1' => '08:30',
                'start2' => '12:00',
                'end2' => '13:30',
                'min_mor' => 4,
                'min_aft' => 3,
                'max_end_mor' => '12:00',
                'max_end_aft' => '19:00',
            ];
            ?>
            <form id="form-system-general" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="form-group form-label-left row">
                    <label class="col-md-2 control-label">{{ trans('manage_time::view.Range working time start') }}</label>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ trans('manage_time::view.Morning shift') }}</label>
                                <div class="row">
                                    <div class="col-xs-5">
                                        <input type="text" name="item[working_range_times][start1]" value="{{ isset($rangeTimes['start1']) ? $rangeTimes['start1'] : null }}" class="form-control" placeholder="HH:mm">
                                    </div>
                                    <div class="col-xs-1 text-center"> -- </div>
                                    <div class="col-xs-5">
                                        <input type="text" name="item[working_range_times][end1]" value="{{ isset($rangeTimes['end1']) ? $rangeTimes['end1'] : null }}" class="form-control" placeholder="HH:mm">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('manage_time::view.Afternoon shift') }}</label>
                                <div class="row">
                                    <div class="col-xs-5">
                                        <input type="text" name="item[working_range_times][start2]" value="{{ isset($rangeTimes['start2']) ? $rangeTimes['start2'] : null }}" class="form-control" placeholder="HH:mm">
                                    </div>
                                    <div class="col-xs-1 text-center"> -- </div>
                                    <div class="col-xs-5">
                                        <input type="text" name="item[working_range_times][end2]" value="{{ isset($rangeTimes['end2']) ? $rangeTimes['end2'] : null }}" class="form-control" placeholder="HH:mm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <label>{{ trans('manage_time::view.Time min morning shift') }}</label>
                                        <input type="text" name="item[working_range_times][min_mor]" value="{{ isset($rangeTimes['min_mor']) ? $rangeTimes['min_mor'] : null }}" class="form-control">
                                    </div>
                                    <div class="col-xs-5 col-xs-offset-1">
                                        <label>{{ trans('manage_time::view.Time max morning shift end') }}</label>
                                        <input type="text" name="item[working_range_times][max_end_mor]" value="{{ isset($rangeTimes['max_end_mor']) ? $rangeTimes['max_end_mor'] : null }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="row">
                                    <div class="col-xs-5">
                                        <label>{{ trans('manage_time::view.Time min afternoon shift') }}</label>
                                        <input type="text" name="item[working_range_times][min_aft]" value="{{ isset($rangeTimes['min_aft']) ? $rangeTimes['min_aft'] : null }}" class="form-control">
                                    </div>
                                    <div class="col-xs-5 col-xs-offset-1">
                                        <label>{{ trans('manage_time::view.Time max afternoon shift end') }}</label>
                                        <input type="text" name="item[working_range_times][max_end_aft]" value="{{ isset($rangeTimes['max_end_aft']) ? $rangeTimes['max_end_aft'] : null }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label>&nbsp;</label>
                        <div>
                            <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        </div>
                    </div>
                </div>
            </form>

            <form id="form-system-css-mail" method="post" action="{{ route('core::setting.system.data.save') }}"
                  class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-11">
                        <div class="form-group form-label-left row">
                            <label class="col-sm-2 control-label">{{ trans('manage_time::view.Email related need notify while register working time') }}</label>
                            <div class="col-md-10">
                                <input name="item[working_time_relator_vn]" class="form-control input-field" type="text" 
                                       id="cssmail" value="{{ CoreConfigData::getValueDb('working_time_relator_vn') }}" />
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button class="btn-add margin-bottom-5" type="submit">{{ trans('core::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
