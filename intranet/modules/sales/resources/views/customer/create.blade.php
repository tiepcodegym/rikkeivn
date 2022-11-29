@extends('layouts.default')
<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewHelper;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\Model\CoreConfigData;
?>
@section('title')
@if (isset($customer) && $customer->id)
{{ trans('sales::view.Edit customer') }}
@else
{{ trans('sales::view.Create customer') }}
@endif
@endsection
<?php 
if ($customer->id) {
    $urlSubmit = route('sales::customer.postCreate', ['id' => $customer->id]);
} else {
    $urlSubmit = route('sales::customer.postCreate');
    $customer->id = 0;
}
$tabActive = CookieCore::get('tab-keep-status-customer-'.$customer->id);
if (!$tabActive) {
  $tabActive = 'tab_basic_info';
}
?>

@section('css')
<link href="{{ asset('sales/css/sales.css') }}" rel="stylesheet" type="text/css" >
<link href="{{ asset('sales/css/customer_create.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
@endsection

@section('content')
<form id="form-create-customer" method="post" action="{{ $urlSubmit }}" 
    enctype="multipart/form-data" autocomplete="off" class="form-horizontal form-sales-module">
  {!! csrf_field() !!}
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h4>{{ trans('sales::view.Basic info') }}</h4>
            </div>
            <div class="box-body">
                @if($customer->id)
                <input type="hidden" name="customer_id" value="{{ $customer->id }}" />
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group form-label-left">
                            <label for="name" class="col-sm-3 control-label">{{ trans('sales::view.Customer name') }} <em class="required">*</em></label>
                            <div class="col-sm-9">
                                @if ($hasPermissionEdit)
                                <input name="name" class="form-control input-field" type="text" id="name"
                                       value="{{ $customer->name }}" placeholder="{{ trans('sales::view.Customer name') }}" />
                                @else
                                <label class="control-label">{{ $customer->name }}</label>
                                <input type="hidden" name="name" value="{{ $customer->name }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-label-left">
                            <label for="name_ja" class="col-sm-3 control-label">{{ trans('sales::view.Customer name (jp)') }}</label>
                            <div class="col-sm-9">
                                @if ($hasPermissionEdit)
                                <input name="name_ja" class="form-control input-field" type="text" id="name_ja" {{ !$hasPermissionEdit ? 'readonly' : '' }}
                                       value="{{ $customer->name_ja }}" placeholder="{{ trans('sales::view.Customer name (jp)') }}" />
                                @else
                                <label class="control-label">{{ $customer->name_ja }}</label>
                                <input type="hidden" name="name_ja" value="{{ $customer->name_ja }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group form-label-left form-group-select2">
                            <label for="company_id" class="col-sm-3 control-label">{{ trans('sales::view.Company') }} <em class="required">*</em></label>
                            <div class="col-sm-9 fg-valid-custom">
                                @if ($hasPermissionEdit)
                                <select name="company_id" class="form-control" id="company_id">
                                    <option>&nbsp;</option>
                                    @if(isset($companies) && count($companies))
                                    @foreach($companies as $company)
                                    <option value="{{ $company->id }}"{{ ($company->id == $customer->company_id) ? ' selected' : '' }}>{{ $company->company }}</option>
                                    @endforeach
                                    @endif
                                </select>
                                @else
                                <label class="control-label">{{ $customer->company }}</label>
                                <input type="hidden" name="company_id" value="{{ $customer->company_id }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-label-left">
                            <label for="note" class="col-sm-3 control-label">{{ trans('sales::view.Note') }}</label>
                            <div class="col-sm-9">
                                <textarea name="note" rows="5" class="form-control input-field" id="note" 
                                    placeholder="{{ trans('sales::view.Note') }}" >{{ $customer->note }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row margin-top-30">
                    <div class="col-md-12 align-center">
                        <button class="btn-add" type="submit">
                            @if($customer->id)
                            {{ trans('sales::view.Update') }}
                            @else
                            {{ trans('sales::view.Create new') }}
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @if (!empty($customer->id))
        <div class="box box-info">
            <div class="box-header with-border">
                <h4>{{ trans('sales::view.Projects list') }}</h4>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="edit-table table table-striped table-bordered table-condensed dataTable" cellspacing="0" width="100%" id="projects-table">
                        <thead>
                            <tr>
                                <th>{{ trans('sales::view.Id') }}</th>
                                <th>{{ trans('sales::view.Project name') }}</th>
                                <th>{{ trans('sales::view.Team') }}</th>
                                <th>{{ trans('sales::view.Create.PM') }}</th>
                                <th>{{ trans('sales::view.Status') }}</th>
                                <th>{{ trans('sales::view.Type') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
</form>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script>
$('#company_id').select2();
var urlCheckExistsCustomer = '{{route('sales::customer.checkExistsCustomer')}}';
var skypeFormat = '{{trans('sales::view.Please enter by format skype')}}';
var chatworkFormat = '{{trans('sales::view.Please enter by format chatwork')}}';
var phoneFormat = '{{trans('sales::view.Please enter by format phone')}}';
var emailFormat = '{{trans('sales::view.Customer email must be email')}}';
var urlProjectsList = '{{ route("sales::customer.getProjectsList", ['type' => 'customer','id' => $customer->id]) }}';
var messageEmptyData = '{{trans('sales::message.project not found')}}';
var textBefore = '{{trans('sales::view.Before')}}';
var textNext = '{{trans('sales::view.After')}}';
var textSInfo = '{{trans('sales::view.display :start to :end of :total record', ["start" => "_START_", "end" => "_END_", "total" => "_TOTAL_"])}}';
token = $('input[name=_token]').val();
@if ($customer->id)
var isEdit = true;
var customerId = {{$customer->id}};
@else
var isEdit = false;
var customerId = false;
@endif
</script>
<script>
    jQuery(document).ready(function ($) {
        RKfuncion.keepStatusTab.init();
        messages = {
            name : {
                required: '<?php echo trans('sales::view.Customer name field is required'); ?>',
                rangelength: '<?php echo trans('sales::view.Customer name field not be greater than :number characters', ['number'=> 255]); ?>'
            },
            name_ja : {
                rangelength: '<?php echo trans('sales::view.Customer name jp field not be greater than :number characters', ['number'=> 255]); ?>'
            },
            company_id : {
                required: '<?php echo trans('sales::view.Company name field is required'); ?>',
            },
            email : {
                required: '<?php echo trans('sales::view.Customer email field is required'); ?>',
                email: '<?php echo trans('sales::view.Customer email must be email'); ?>',
                rangelength: '<?php echo trans('sales::view.Customer email field no be greater than :number characters', ['number' => 100]); ?>',
                remote: '<?php echo trans('sales::view.The value of email field must be unique');?>'
            },
            phone : {
                rangelength: '<?php echo trans('sales::view.Customer phone field not be greater than :number characters', ['number' => 100]); ?>',
                number: '<?php echo trans('sales::view.Please enter a valid number.'); ?>',
            },
            skype : {
                rangelength : '<?php echo trans('sales::view.Customer skype field not be greater than :number characters', ['number' => 45]); ?>'
            },
            chatwork : {
                rangelength : '<?php echo trans('sales::view.Customer chatwork field not be greater than :number characters', ['number' => 45]); ?>'
            },
            birthday : {
                date: '<?php echo trans('sales::view.Customer birthday must be format date') ?>'
            }       
        };

        rules = {
            name : {
                required: true,
                rangelength: [1, 255]
            },
            name_ja : {
                rangelength: [1, 255]
            },
            email : {
                required: true,
                validateEmail: true,
                rangelength: [1, 100],
                remote: {
                url: urlCheckExistsCustomer,
                type: "post",
                data: {
                    _token: token,
                    email: function () {
                        return $("#email").val().trim();
                    },
                    isEdit: isEdit,
                    customerId: customerId,
                }
            }
            },
            phone : {
                rangelength: [1, 100],
                validatePhone: true
            },
            skype : {
                rangelength: [1, 45],
                validateSkype: true
            },
            chatwork : {
                rangelength: [1, 45]
            },
            birthday : {
                date: true
            },
            company_id: {
                required: true
            }
        };

        optionPassGlobal = {
            imageTypeAllow: ["image/jpeg","image/png","image/gif", "image/bmp"],
            logoErrorSizeImage: jQuery.parseHTML('{{ trans('sales::view.Customer avatar must smaller :number MB', ['number' =>1]) }}')[0].nodeValue,
            logoMustImage: jQuery.parseHTML('{{ trans('sales::view.Customer avatar must be format image') }}')[0].nodeValue,
            sizeLogoMax: 1,
            imageTypeAllowOr: 'jpeg|jpg|png|gif',
        };
        siteUrlObject = {
            capacityUnit: 1024
        };
        imageDefault = '{{ URL::asset('common/images/noimage.png') }}';
        $('#form-create-customer').validate({
            rules: rules,
            messages: messages
        });
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });

        //Display image when upload  and choose avatar
        $('#image').change(function (event) {
            var file = $(this).prop('files');
            if (file.length) {
                fileUpload = file[0];
                if($.inArray(fileUpload.type, optionPassGlobal.imageTypeAllow) < 0) {
                    alert(optionPassGlobal.logoMustImage);
                    $('#image-preview').attr('src', imageDefault);
                    $('#image').val('');
                    return true;
                } else if (fileUpload.size / Math.pow(siteUrlObject.capacityUnit, 2) > optionPassGlobal.sizeLogoMax) {
                    alert(optionPassGlobal.logoErrorSizeImage);
                    $('#image-preview').attr('src', imageDefault);
                    $('#image').val('');
                    return true;
                }
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#image-preview').attr('src', e.target.result);
                    $('#image-preview').css('height', '96px');
                };
                reader.readAsDataURL(file[0]);
            }else {
                $('#image-preview').attr('src', imageDefault);
            }
        });
    });

    jQuery.validator.addMethod("validateSkype", function(value, element) {
      return validateSkype(value.trim());
    }, skypeFormat);

    jQuery.validator.addMethod("validateChatWork", function(value, element) {
      return validateSkype(value.trim());
    }, chatworkFormat);
    jQuery.validator.addMethod("validatePhone", function(value, element) {
      return validatePhone(value.trim());
    }, phoneFormat);
    jQuery.validator.addMethod("validateEmail", function(value, element) {
      return validateEmail(value.trim());
    }, emailFormat);

/**
 * validate skype
 * @param string
 * @returns boolean
 */
function validateSkype(skype) {
  if (skype) {
    var regex = /^[a-z0-9_-]{3,15}$/;
    return regex.test(skype);
  }
  return true;
}

function validatePhone(phone) {
  if (phone) {
    var regex = /^[0-9_-]+$/;
    return regex.test(phone);
  }
  return true;
}

function validateEmail(email) {
  if (email) {
    var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return regex.test(email);
  }
  return true;
}

/**
 * Init table candidate list of request
 * @param {type} param
 */
$('#projects-table').DataTable({
    processing: true,
    lengthChange: false,
    bFilter: false,
    serverSide: true,
    ajax: urlProjectsList,
    pageLength: 10,
    columns: [
        {data: 'id', name: 'id'},
        {data: 'name', name: 'name'},
        {data: 'team_name', name: 'team_name'},
        {data: 'email', name: 'email'},
        {data: 'state', name: 'state'},
        {data: 'type', name: 'type'},
    ],
    oLanguage: {
        sEmptyTable: messageEmptyData,
        sInfo: textSInfo,
        oPaginate: {
            sPrevious: textBefore,
            sNext: textNext,
        },
    },
});
</script>
@endsection
