@extends('layouts.default')

@section('title')
{{ trans('core::view.Setting system data') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-body">
                <!-- Centered Pills -->
                <ul class="nav nav-pills nav-justified">
                    @foreach ($typesView as $typeView => $labelTypeView)
                        <li {{ ($typeView == $typeViewMain) ? ' class=active' : '' }}>
                            <a href="{{ route('core::setting.system.data.index', ['type' => $typeView]) }}">{{ $labelTypeView }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="row setting-system-data-page">
    @if (view()->exists('core::system.data.' . $typeViewMain))
        @include('core::system.data.' . $typeViewMain)
    @else
        @include('core::system.data.general')
    @endif
</div>
    <!-- birthday event tour -->
    <?php /*
    <!-- birthday event tour -->
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="box-body-header">
                    <h2 class="box-body-title">{{ trans('core::view.Tour event birthday') }}</h2>
                </div>
                
                <form id="form-system-tour_event_birthday" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label class="col-sm-2 control-label">No join short</label>
                                <div class="col-md-9">
                                    <textarea name="item[event.birthday.tour.nojoin.short]" class="form-control input-field" type="text" 
                                       id="event_birthday_tour_nojoin_short">{{ CoreConfigData::getValueDb('event.birthday.tour.nojoin.short') }}</textarea>
                                       <p class="hint">&#123;&#123; readmoreLink &#125;&#125;: {{ trans('core::view.Link read more') }}</p>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add btn-submit-ckeditor" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-sm-2 control-label">No join</label>
                                <div class="col-md-9">
                                    <textarea name="item[event.birthday.tour.nojoin]" class="form-control input-field" type="text" 
                                       id="event_birthday_tour_nojoin">{{ CoreConfigData::getValueDb('event.birthday.tour.nojoin') }}</textarea>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add btn-submit-ckeditor" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <form id="form-system-tour_event_birthday" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label class="col-sm-2 control-label">Hanoi short</label>
                                <div class="col-md-9">
                                    <textarea name="item[event.birthday.tour.hanoi.short]" class="form-control input-field" type="text" 
                                       id="event_birthday_tour_hanoi_short">{{ CoreConfigData::getValueDb('event.birthday.tour.hanoi.short') }}</textarea>
                                    <p class="hint">&#123;&#123; readmoreLink &#125;&#125;: {{ trans('core::view.Link read more') }}</p>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add btn-submit-ckeditor" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-sm-2 control-label">Hanoi</label>
                                <div class="col-md-9">
                                    <textarea name="item[event.birthday.tour.hanoi]" class="form-control input-field" type="text" 
                                       id="event_birthday_tour_hanoi">{{ CoreConfigData::getValueDb('event.birthday.tour.hanoi') }}</textarea>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add btn-submit-ckeditor" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <form id="form-system-tour_event_birthday" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label class="col-sm-2 control-label">Danang short</label>
                                <div class="col-md-9">
                                    <textarea name="item[event.birthday.tour.danang.short]" class="form-control input-field" type="text" 
                                       id="event_birthday_tour_danang_short">{{ CoreConfigData::getValueDb('event.birthday.tour.danang.short') }}</textarea>
                                    <p class="hint">&#123;&#123; readmoreLink &#125;&#125;: {{ trans('core::view.Link read more') }}</p>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add btn-submit-ckeditor" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-sm-2 control-label">Danang</label>
                                <div class="col-md-9">
                                    <textarea name="item[event.birthday.tour.danang]" class="form-control input-field" type="text" 
                                       id="event_birthday_tour_danang">{{ CoreConfigData::getValueDb('event.birthday.tour.danang') }}</textarea>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add btn-submit-ckeditor" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <form id="form-system-tour_event_birthday" method="post" action="{{ route('core::setting.system.data.save') }}"
                      class="form-horizontal form-submit-ajax no-validate" autocomplete="off">
                    {!! csrf_field() !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group form-label-left">
                                <label class="col-sm-2 control-label">Contact</label>
                                <div class="col-md-9">
                                    <textarea name="item[event.birthday.contact]" class="form-control input-field" type="text" 
                                       id="event_birthday_contact">{{ CoreConfigData::getValueDb('event.birthday.contact') }}</textarea>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn-add btn-submit-ckeditor" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
            </div>
        </div>
    </div> <!-- end birthday event tour -->
    */ ?>
@endsection

@section('script')
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('common/js/setting-data.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    selectSearchReload();
//    RKfuncion.CKEditor.init([
//        'event_birthday_tour_nojoin',
//        'event_birthday_tour_hanoi',
//        'event_birthday_tour_danang',
//        'event_birthday_tour_nojoin_short',
//        'event_birthday_tour_hanoi_short',
//        'event_birthday_tour_danang_short',
//        'event_birthday_contact'
//    ]);
});
</script>
@endsection
<script>
    var selectBranchMes = '{!! trans('core::view.Select a branch') !!}';
    var selectProjectMes = '{!! trans('core::view.Select a project') !!}';
    var duplicatedDateMes = '{!! trans('core::view.Duplicated date') !!}';
</script>