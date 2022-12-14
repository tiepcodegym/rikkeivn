                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            @extends('layouts.default')
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
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker3.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_welfare/css/style.css') }}" />
@endsection

@section('content')
@if(isset($welfare))
<div class="error-message hidden">
    <div class="alert alert-warning">
        <ul>
            <li></li>
        </ul>
    </div>
</div>
<div class="row confirm-participation-welfare">
    <div class="col-sm-12">
        <div class="box box-info">
            @if((WelEmployee::getEmployeeInWelfare($welfare->id, Auth::id()) && count(WelEmployee::getEmployeeInWelfare($welfare->id, Auth::id()))))
            <form action="{{ route('welfare::welfare.post.confirm.welfare') }}" method="POST" id="confirm-participation">
                {{ Form::token() }}
                <input type="hidden" id="welid" name="welid" value="{{ $welfare->id }}">
                <input type="hidden" id="is_joined" name="is_joined" value="{{ $welfare->is_joined }}">
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
                                        @if ($welfare->empl_offical_fee != 0 || $welfare->empl_offical_company_fee != 0)
                                            <strong>{{ trans('welfare::view.Employees:') }}</strong>
                                        @endif
                                        @if ($welfare->empl_offical_fee != 0)
                                        {{ trans('welfare::view.Employees contribute') }}: <b>{{ number_format($welfare->empl_offical_fee) }} {{ trans('welfare::view.Unit') }}</b>,&nbsp;
                                        @endif
                                        @if( $welfare->empl_offical_company_fee != 0)
                                        {{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->empl_offical_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                    </div>
                                    <div class="page-main">
                                        @if ($welfare->empl_trial_fee != 0 || $welfare->empl_trial_company_fee != 0)
                                            <strong>{{ trans('welfare::view.Employees trail:') }}</strong>
                                        @endif
                                        @if ($welfare->empl_trial_fee != 0)
                                        {{ trans('welfare::view.Employee trail work contribute') }}: <b>{{ number_format($welfare->empl_trial_fee) }} {{ trans('welfare::view.Unit') }}</b>,&nbsp;
                                        @endif
                                        @if( $welfare->empl_trial_company_fee != 0)
                                        {{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->empl_trial_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                    </div>
                                    <div class="page-main">
                                        @if ($welfare->intership_fee != 0 || $welfare->intership_company_fee != 0)
                                            <strong>{{ trans('welfare::view.Intern:') }}</strong>
                                        @endif
                                        @if ($welfare->intership_fee != 0)
                                        {{ trans('welfare::view.Intern contribute') }}: <b>{{ number_format($welfare->intership_fee) }} {{ trans('welfare::view.Unit') }}</b>,&nbsp;
                                        @endif
                                        @if( $welfare->intership_company_fee != 0)
                                        {{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->intership_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
                                        @endif
                                    </div>
                                    @if ($welfare->is_allow_attachments == Event::IS_ATTACHED)
                                    <div class="page-main">
                                        @if ($welfare->attachments_first_fee != 0 || $welfare->attachments_first_company_fee != 0)
                                            <strong>{{ trans('welfare::view.Employee Attach:') }}</strong>
                                        @endif
                                        @if ($welfare->attachments_first_fee != 0)
                                        {{ trans('welfare::view.Attached contribute') }}: <b>{{ number_format($welfare->attachments_first_fee) }} {{ trans('welfare::view.Unit') }}</b>,&nbsp;
                                        @endif
                                        @if( $welfare->attachments_first_company_fee != 0)
                                        {{ trans('welfare::view.Expected company support') }}: <b>{{ number_format($welfare->attachments_first_company_fee) }} {{ trans('welfare::view.Unit') }}</b>
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
                    <div class="confirm <?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?> ">
                        @if ($welfare->is_allow_attachments == Event::IS_ATTACHED)

                            @if($isConfirm && !$check)
                            <div class="table-responsive">
                                @if ($welRelativeAttachs && count($welRelativeAttachs))
                                <table class="table table-bordered table-grid-data">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('welfare::view.Full Name') }}</th>
                                            <th>{{ trans('welfare::view.Relation') }}</th>
                                            <th>{{ trans('welfare::view.Gender') }}</th>
                                            <th>{{ trans('welfare::view.Rep Card ID') }}</th>
                                            <th>{{ trans('welfare::view.Birthday') }}</th>
                                            <th>{{ trans('welfare::view.Phone') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($welRelativeAttachs as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->relation_name }}</td>
                                            <td>
                                                <?php if ($item->gender == WelEmployeeAttachs::GENDER_MALE) : ?> {{ trans('team::view.Male')}} <?php endif; ?>
                                                <?php if ($item->gender == WelEmployeeAttachs::GENDER_FEMALE) : ?> {{ trans('team::view.Female')}} <?php endif; ?>
                                            </td>
                                            <td>{{ $item->card_id }}</td>
                                            <td>{{ $item->birthday }}</td>
                                            <td>{{ $item->phone }}</td>
                                        </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                                @endif
                            </div>
                            @else
                            <div class="<?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?>">
                                <input id="is_register_relatives" <?php if ($welRelativeAttachs && count($welRelativeAttachs) || isset($attach)) : ?> checked="checked" <?php endif; ?> name="is_register_relatives" type="checkbox" value="1">
                                <label for="is_register_relatives" class="control-label">{{ trans('welfare::view.Attendee registration') }}</label>
                            </div>
                            <div class="panel panel-default <?php if ($endRegister->lte(Carbon::now()) ||( count($welRelativeAttachs) <= 0 && $attach == null)) : ?> hidden disabledbutton <?php endif; ?> ">
                                <div class="panel-heading <?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?> ">
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
                                                @if(Session::has('attached') && isset($attach))
                                                @foreach($attach as $key => $item)
                                                <tr>
                                                    <td>{{ $item['name'] }}</td>
                                                    <td>{{ RelationName::getNameById($item['relation_name_id']) }}</td>
                                                    <td>
                                                        <?php if ($item['gender'] == WelEmployeeAttachs::GENDER_MALE) : ?> {{ trans('team::view.Male')}} <?php endif; ?>
                                                        <?php if ($item['gender'] == WelEmployeeAttachs::GENDER_FEMALE) : ?> {{ trans('team::view.Female')}} <?php endif; ?>
                                                    </td>
                                                    <td>{{ $item['card_id'] }}</td>
                                                    <td>{{ $item['birthday'] }}</td>
                                                    <td>{{ $item['phone'] }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-edit edit-relative-attach"
                                                                title="{{ trans('team::view.Edit') }}" data-id="{{ $key }}"
                                                                data-url="{{ route('welfare::welfare.attach.session.edit') }}">
                                                            <i class="fa fa-edit"></i></button>
                                                        <button type="button" class="btn btn-delete delete-relative-attach"
                                                                data-url="{{ route('welfare::welfare.attach.session.delete') }}"
                                                                data-id="{{ $key }}" data-noti="{{ trans('welfare::view.Are you sure') }}">
                                                            <i class="fa fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif
                        <div class="">
                            @if (isset($isConfirm) && $isConfirm)
                            <div class="hidden">
                            <p class="text-hint <?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?>"><i class="fa fa-check"></i><i>&nbsp;{{ trans('welfare::view.Confirmed participation') }}</i></p>
                                @if(!$check)
                                <a href="{{ route('welfare::welfare.confirm.edit.welfare',  $welfare->id ) }}">
                                <button type="button" class="btn btn-edit <?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?> ">
                                    <i class="fa fa-edit"></i>&nbsp;
                                    {{ trans('welfare::view.Edit') }}
                                </button></a>
                                @else
                                <button type="submit" class="btn btn-primary <?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?> " name="submit">
                                    <i class="fa fa-check"></i>&nbsp;
                                    {{ trans('welfare::view.Update confirmation of participation') }}
                                </button>
                                @endif
                            </div>
                            @else
                            <button type="submit" class="btn btn-primary <?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?> " name="submit">
                                <i class="fa fa-check"></i>&nbsp;
                                {{ trans('welfare::view.Confirm participation') }}

                            </button>
                            @endif
                            @if (isset($isConfirm) && $isConfirm)
                            <button type="button" class="btn btn-danger submit-destroy <?php if ($endRegister->lte(Carbon::now())) : ?> disabledbutton <?php endif; ?>" data-noti="{{ trans('welfare::view.Unfollow event') }}">
                                <i class="fa fa-times"></i>&nbsp;{{ trans('welfare::view.Refuse to participate') }}</button>
                            @endif
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
            @else
            <div class="content-welfare-confirm">
                <p>{{ trans('welfare::view.You may not participate in this event') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@if(WelEmployee::getEmployeeInWelfare($welfare->id, Auth::id()) && count(WelEmployee::getEmployeeInWelfare($welfare->id, Auth::id())))
@if ($welfare->is_allow_attachments == Event::IS_ATTACHED)
<div class="modal fade" id="modal-add-relatives" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="{{ route('welfare::welfare.attach.session.add') }}" method="POST"
              class="form-horizontal" id="form-add-wel-empl-relatives">
            {{ Form::token() }}
            <input type="hidden" id="key" name="key" value="">
            <input type="hidden" id="id" name="id" value="">
            <input type="hidden" id="welid" name="welid" value="{{ $welfare->id }}">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{ trans('welfare::view.Information relatives') }}</h4>
            </div>
            <div class="modal-body">
                <div class="box box-info">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Full Name') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" name="name" id="name" class="form-control" placeholder="{{ trans('welfare::view.Full Name') }}" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label">{{ trans('welfare::view.Priority mode') }}</label>
                            <div class="input-box col-md-9 fg-valid-custom">
                                <select data-url = "{{ route('welfare::welfare.relation.select.ajax') }}" class="form-control fee_favorable_attached" style="width: 100%"
                                        name="support_cost" id="support_cost">
                                    <option value="0">{{ trans('welfare::view.Please choose') }}</option>
                                    @if(isset($listAttachFee))
                                        @foreach($listAttachFee as $keyFee => $valueFee)
                                        <option value="{{ $keyFee }}">{{ $valueFee }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <p style="color: red; display: none;" id="favorable-require-max" class="error">{{ trans('welfare::view.favorable-require-max') }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Relation') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <select class="form-control val-custom relation_name_id" id="confirm_relation_name_id" data-col="relation_name_id" style="width: 100%"
                                      name="relation_name_id" tabindex="-1" aria-hidden="true" data-placeholder="{{ trans('welfare::view.Please choose') }}">
                                    <option value="">{{ trans('welfare::view.Please choose') }}</option>
                                    @if(isset($relation))
                                        @foreach($relation as $keyRelation => $valueRelation)
                                        <option value="{{ $keyRelation }}">{{ $valueRelation }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="val-message"></div>
                            </div>
                        </div>
                        <div class="form-group input-attached-gender">
                            <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Gender') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                {{ Form::select('gender', WelEmployeeAttachs::optionGender(), null, ['class' => 'form-control', 'id' => 'gender', 'data-col' => 'gender']) }}
                            </div>
                        </div>
                        <div class="form-group check-allow-import-id">
                            <label class="col-md-3 control-label required label-card-id" aria-required="true">{{ trans('welfare::view.Rep Card ID') }}<em>*</em></label>
                            <div class="input-box col-md-9 input-relative_card_id">
                                <input type="text" name="relative_card_id" id="card_id" class="form-control" placeholder="{{ trans('welfare::view.Rep Card ID') }}" value="" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')">
                                <p id="custom-error-card-id" class="error hidden" style="color: red;">{{ trans('core::message.This field is required') }}</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Birthday') }}<em>*</em></label>
                            <div class="input-box col-md-9">
                                <input type="text" name="birthday" id="birthday" class="form-control" placeholder="{{ trans('welfare::view.Birthday') }}" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label required" aria-required="true">{{ trans('welfare::view.Phone') }}</label>
                            <div class="input-box col-md-9">
                                <input type="text" name="phone" id="phone" class="form-control" placeholder="{{ trans('welfare::view.Phone') }}" value="" data-col="phone">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-add-wel-empl-relatives" onclick="return checkFormSubmit();">{{trans('welfare::view.Save') }}</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('welfare::view.Close') }}</button>
            </div>
        </form>
    </div>
  </div>
</div>
@endif
<div class="modal fade modal-danger" id="modal-delete-relative-attach" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" class="form-confirm-delete">
                {{ Form::token() }}
                <input type="hidden" name="welid" id="id" value="{{ $welfare->id }}"/>
                <input type="hidden" name="submit_destroy" value="" />
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">??</span></button>
                    <h4 class="modal-title">{{ trans('welfare::view.Confirm Delete') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="deleteContent">

                    </div>
                    <p class="text-change"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('welfare::view.Close') }}</button>
                    <button type="button" class="btn btn-outline btn-ok">{{ trans('welfare::view.Confirm Delete') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@else
<p>{{ trans('welfare::view.Not Found Welfare') }} </p>
@endif
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="{{ URL::asset('asset_welfare/js/confirm.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
<script>
jQuery(document).ready(function($) {
    $.validator.addMethod('customphone', function (value, element) {
        return this.optional(element) || /^(0|\+)[\d]{9,13}$/.test(value);
    }, '{{ trans('welfare::view.Please enter a valid phone number') }}');
    var messages = {
        name: {
            required: '<?php echo trans('core::view.This field is required'); ?>',
            rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 50]); ?>',
        },
        'relation_name_id': {
            required: '<?php echo trans('core::view.This field is required'); ?>'
        },
        'relative_card_id': {
            required: '<?php echo trans('core::view.This field is required'); ?>'
        },
        'gender': {
            required: '<?php echo trans('core::view.This field is required'); ?>'
        },
        'phone': {
            rangelength: '<?php echo trans('core::view.This field not be greater than :number characters', ['number' => 15]); ?>',
        },
        'birthday': {
            required: '<?php echo trans('core::view.This field is required'); ?>'
        },
    };

    var rules = {
        'name': {
            required: true,
            rangelength: [1, 50],
        },
        'relation_name_id': {
            required: true,
        },
        'relative_card_id': {
            required: true,
        },
        'phone': {
            rangelength: [1, 15],
            customphone: true
        },
        'birthday': {
            required: true
        },
    };

    $('#form-add-wel-empl-relatives').validate({
        rules: rules,
        messages: messages,
        errorPlacement: function(error, element) {
            if(element.hasClass('val-custom')) {
                error.insertAfter(element.parent().find('.val-message'));
            }
            else if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            }
            else {
                error.insertAfter(element);
            }
        },
    });
});
</script>
@endsection