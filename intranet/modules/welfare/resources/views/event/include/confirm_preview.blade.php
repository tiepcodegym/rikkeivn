@extends('layouts.default')
<?php
use Rikkei\Core\View\CoreUrl;
use Carbon\Carbon;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Rikkei\Welfare\Model\WelEmployee;
use Rikkei\Welfare\Model\Event;
use Rikkei\Welfare\Model\RelationName;
use Rikkei\Welfare\Model\WelAttachFee;

if (isset($welfare)) {
    $endRegister = $welfare->end_at_register != null ?   Carbon::createFromFormat('d/m/Y', $welfare->end_at_register)->setTime(23, 59, 59)
                        : Carbon::now()->setTime(00, 00, 00);
}
?>
@section('title')
{{ trans('welfare::view.Registration for participation in the event') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_welfare/css/style.css') }}" />
@endsection

@section('content')
<div class="row confirm-participation-welfare">
    <div class="col-sm-12">
        <div class="box box-info">
            <form action="{{ route('welfare::welfare.post.confirm.welfare') }}" method="POST" id="confirm-participation">
                {{ Form::token() }}
                <input type="hidden" id="welid" name="welid" value="{{ $welfare->id }}">
                <input type="hidden" name="is_joined" value="{{ $welfare->is_joined }}">
                <div class="content-welfare row">
                    <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="box box-solid">
                            <div class="box-body">
                                    <h4>{{ trans('welfare::view.Basic Information') }}</h4>
                                    <div class="page-main">
                                        {!! trans('welfare::view.Time takes place', ['welfare'=> $welfare->name, 'team' => $welfare->name_team, 'start' => $welfare->start_at_exec, 'end' => $welfare->end_at_exec]) !!}
                                    </div>
                                    <div class="page-main">
                                        {{ trans('welfare::view.Place') }}: <b>{{ $welfare->address }}</b>
                                    </div>
                                    <div class="page-main">
                                        {{ trans('welfare::view.Organizer') }}: <b>{{ $welfare->organizers_name }}</b>
                                    </div>
                                    <div class="page-main">
                                        {{ trans('welfare::view.End At Register') }}: <b>{{ $welfare->end_at_register }}</b>
                                    </div>
                                    <div class="page-main">
                                        @if ($welfare->empl_offical_fee != 0)
                                        {{ trans('welfare::view.Employees contribute') }}: <b>{{ number_format($welfare->empl_offical_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                        @if( $welfare->empl_offical_company_fee != 0)
                                        ,&nbsp;{{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->empl_offical_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                    </div>
                                    <div class="page-main">
                                        @if ($welfare->empl_trial_fee != 0)
                                        {{ trans('welfare::view.Employee trail work contribute') }}: <b>{{ number_format($welfare->empl_trial_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                        @if( $welfare->empl_trial_company_fee != 0)
                                        ,&nbsp;{{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->empl_trial_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                    </div>
                                    <div class="page-main">
                                        @if ($welfare->intership_fee != 0)
                                        {{ trans('welfare::view.Intern contribute') }}: <b>{{ number_format($welfare->intership_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                        @if( $welfare->intership_company_fee != 0)
                                        ,&nbsp;{{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->intership_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                    </div>
                                    @if ($welfare->is_allow_attachments == Event::IS_ATTACHED)
                                    <div class="page-main">
                                        @if ($welfare->attachments_first_fee != 0)
                                        {{ trans('welfare::view.Attached contribute') }}: <b>{{ number_format($welfare->attachments_first_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                        @if( $welfare->attachments_first_company_fee != 0)
                                        ,&nbsp;{{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->attachments_first_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        @if ($welfare->description != '')
                        <div class="box box-solid">
                            <div class="box-body">
                                <h4>{{ trans('welfare::view.Description') }}</h4>
                                <div class="page-main">
                                    <p>{!! $welfare->description !!}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @if (($welfare->is_register_online == Event::IS_REGISTER_ONLINE) && ($endRegister->gte(Carbon::now())))
                <div class="confirm">
                    @if ($welfare->is_allow_attachments == Event::IS_ATTACHED)
                    <div class="">
                        <input id="is_register_relatives" checked="checked" name="is_register_relatives" type="checkbox" value="1">
                        <label for="is_register_relatives" class="control-label">{{ trans('welfare::view.Attendee registration') }}</label>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading ">
                            <button type="button" class="btn btn-add btn-open-modal-add" data-toggle="modal" data-target=""><i class="fa fa-plus"></i>&nbsp;{{ trans('welfare::view.Add New') }}</button>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                            <table class="table table-bordered table-grid-data">
                                <thead>
                                    <tr>
                                        <th>{{ trans('welfare::view.Full Name') }}</th>
                                        <th>{{ trans('welfare::view.Relation') }}</th>
                                        <th>{{ trans('welfare::view.Gender') }}</th>
                                        <th>{{ trans('welfare::view.Rep Card ID') }}</th>
                                        <th>{{ trans('welfare::view.Birthday') }}</th>
                                        <th>{{ trans('welfare::view.Phone') }}</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="">
                        <button type="button" class="btn btn-primary" name="submit">
                            <i class="fa fa-check"></i>&nbsp;
                            {{ trans('welfare::view.Confirm participation') }}
                        </button>
                    </div>
                </div>
                @else
                    @if($welfare->is_register_online != Event::IS_REGISTER_ONLINE)
                    <div class="content-welfare-confirm">
                        <p>{{ trans('welfare::view.Events do not allow online registration') }}</p>
                    </div>
                    @elseif($endRegister->lte(Carbon::now()))
                    <div class="content-welfare-confirm">
                        <p>{{ trans('welfare::view.Event registration time has expired') }}</p>
                    </div>
                    @endif
                @endif
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-add-relatives" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="{{ route('welfare::welfare.relative.attach.add') }}" method="POST"
              class="form-horizontal" id="form-add-wel-empl-relatives">
            {{ Form::token() }}
            <input type="hidden" id="id" name="id" value="">
            <input type="hidden" id="welid" name="welid" value="{{ $welfare->id }}">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{ trans('welfare::view.Information relatives') }}</h4>
            </div>
            <div class="modal-body">
                <div class="modal-body">
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="form-group form-label-left">
                                <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Full Name') }}<em>*</em></label>
                                <div class="input-box col-md-9">
                                    <input type="text" name="name" id="name" class="form-control" placeholder="{{ trans('welfare::view.Full Name') }}" value="">
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Relation') }}<em>*</em></label>
                                <div class="input-box col-md-9">
                                    {{ Form::select('relation_name_id', $relation, null, ['class' => 'form-control relation_name_id', 'id' => 'relation_name_id', 'data-col' => 'relation_name_id']) }}
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Gender') }}</label>
                                <div class="input-box col-md-9">
                                    {{ Form::select('gender', WelEmployeeAttachs::optionGender(), null, ['class' => 'form-control', 'id' => 'gender', 'data-col' => 'gender']) }}
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Rep Card ID') }}</label>
                                <div class="input-box col-md-9">
                                    <input type="text" name="relative_card_id" id="card_id" class="form-control" placeholder="{{ trans('welfare::view.Rep Card ID') }}" value="" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')">
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Birthday') }}</label>
                                <div class="input-box col-md-9">
                                    <input type="text" name="birthday" id="birthday" class="form-control" placeholder="{{ trans('welfare::view.Birthday') }}" value="">
                                </div>
                            </div>
                            <div class="form-group form-label-left">
                                <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Phone') }}</label>
                                <div class="input-box col-md-9">
                                    <input type="text" name="phone" id="phone" class="form-control" placeholder="{{ trans('welfare::view.Phone') }}" value="" data-col="phone">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary btn-add-wel-empl-relatives">{{trans('welfare::view.Save') }}</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('welfare::view.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
  </div>
</div>
<div class="modal fade modal-danger" id="modal-delete-relative-attach" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" class="form-confirm-delete">
                {{ Form::token() }}
                <input type="hidden" name="welid" id="id" value="{{ $welfare->id }}"/>
                <input type="hidden" name="submit_destroy" value="{{ $welfare->is_joined }}" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('welfare::view.Confirm Delete') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="deleteContent">

                    </div>
                    <p class="text-change"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                    <button type="button" class="btn btn-outline " data-dismiss="modal">{{ trans('welfare::view.Confirm Delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="{{ URL::asset('asset_welfare/js/confirm.js') }}"></script>
@endsection