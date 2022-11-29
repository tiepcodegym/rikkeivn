<?php
use Rikkei\Sales\View\OpporView;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;

$statusLabels = OpporView::statusLabels()
?>

@extends('layouts.default')

@section('title', $item ? trans('sales::view.Edit request opportunity') : trans('sales::view.Create request opportunity'))

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('sales/css/opportunity.css') }}">
@stop

@section('content')
<div class="box box-primary">
    <div class="box-header">
        
    </div>
    <div class="box-body">
        <div class="container">
            
            @if ($item)
            <div class="row">
                <div class="col-sm-4 col-md-3">
                {!! OpporView::renderStatusHtml($item->status, $statusLabels) !!}
                </div>
                <div class="col-sm-8 col-md-9 text-right">
                    <button type="button" class="btn-export-oppor btn btn-success" 
                            data-url="{{ route('sales::req.oppor.export', $item->id) }}">
                        {{ trans('sales::view.Export') }} 
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </button>
                </div>
            </div>
            @endif
            
            {!! Form::open([
                'method' => 'post',
                'route' => 'sales::req.oppor.save',
                'id' => 'req_oppor_form',
                'data-required-all' => 'num-emp'
            ]) !!}
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.Request name') }} <em class="required">*</em></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ $item ? $item->name : null }}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.Code') }}  <em class="required">*</em></label>
                        <input type="text" name="code" class="form-control"
                               value="{{ $item ? $item->code : $itemCode }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php $priorityLabels = OpporView::priorityLabels() ?>
                        <label>{{ trans('sales::view.Priority') }}</label>
                        <select name="priority" class="form-control select-search">
                            @foreach ($priorityLabels as $value => $label)
                            <option value="{{ $value }}" {{ $item && $item->priority == $value ? 'selected' : null }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.Status') }}</label>
                        <select name="status" class="form-control select-search">
                            @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" {{ $item && $item->status == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.Detail') }}</label>
                        <textarea name="detail" class="form-control text-resize-y" rows="3">{{ $item ? $item->detail : null }}</textarea>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.Potential') }}</label>
                        <textarea name="potential" class="form-control text-resize-y" rows="3">{{ $item ? $item->potential : null }}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php
                        $programsIds = $item ? $item->programs->lists('id')->toArray() : [];
                        ?>
                        <label>{{ trans('sales::view.Program language') }}</label>
                        <select name="prog_ids[]" class="form-control bootstrap-multiselect" multiple>
                            @if ($programs)
                                @foreach ($programs as $progId => $progName)
                                <option value="{{ $progId }}" {{ in_array($progId, $programsIds) ? 'selected' : null }}>{{ $progName }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php $languages = OpporView::listLanguages(); ?>
                        <label>{{ trans('sales::view.Language') }}</label>
                        <select name="lang" class="form-control select-search">
                            <option value="">&nbsp;</option>
                            @foreach ($languages as $code => $label)
                            <option value="{{ $code }}" {{ $item && $item->lang == $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.From date') }} <em class="required">*</em></label>
                        <input type="text" name="from_date" class="form-control date-picker" data-format="YYYY-MM-DD"
                               value="{{ $item ? $item->from_date : null }}">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.To date') }} <em class="required">*</em></label>
                        <input type="text" name="to_date" class="form-control date-picker" data-format="YYYY-MM-DD"
                               value="{{ $item ? $item->to_date : null }}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php
                        $sale = $item ? $item->sale : null;
                        ?>
                        <label>{{ trans('sales::view.Salesperson') }} <em class="required">*</em></label>
                        <select name="sale_id" class="form-control select-search"
                                data-remote-url="{{ route('team::employee.list.search.ajax', ['type' => null, 'team_type' => Team::TEAM_TYPE_SALE]) }}">
                            @if ($sale)
                            <option value="{{ $sale->id }}" selected>{{ $sale->getNickName() }}</option>
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <?php $locations = OpporView::listLocations(); ?>
                        <label>{{ trans('sales::view.Location') }}</label>
                        <select name="location" class="form-control select-search">
                            <option value="">&nbsp;</option>
                            @foreach ($locations as $code => $label)
                            <option value="{{ $code }}" {{ $item && $item->location == $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>{{ trans('sales::view.Customer') }}</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ $item ? $item->customer_name : null }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>{{ trans('sales::view.Note') }}</label>
                <textarea class="form-control text-resize-y" name="note" rows="3">{{ $item ? $item->note : null }}</textarea>
            </div>
            
            <div class="form-group">
                <label>
                    {{ trans('sales::view.Employees') }}: 
                    <strong class="total-number-emp">{{ $item ? $item->number_member : 0 }}</strong>
                </label>
                <div><i>({{ trans('sales::view.Click Add button bellow to add employees') }})</i></div>
                <?php
                $members = $item ? $item->membersWithProgs() : null;
                ?>
                <div id="list_employees" class="form-group">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ trans('sales::view.Number') }}</th>
                                <th>{{ trans('sales::view.Role') }}</th>
                                <th>{{ trans('sales::view.Program language') }}</th>
                                <th>{{ trans('sales::view.Expertise level') }}</th>
                                <th>{{ trans('sales::view.English level') }}</th>
                                <th>{{ trans('sales::view.Japanese level') }}</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                        @if ($members && !$members->isEmpty())
                            @foreach ($members as $order => $member)
                            @include('sales::req-oppor.includes.number-member-item', ['memberItem' => $member, 'itemOrder' => $order])
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                <div>
                    <button id="btn_add_employee" type="button" class="btn btn-primary"><i class="fa fa-plus"></i> {{ trans('sales::view.Add') }}</button>
                </div>
            </div>
            
            <div class="form-group text-center">
                @if ($item)
                    <input type="hidden" name="id" value="{{ $item->id }}">
                @endif
                <a href="{{ CoreView::previousUrl('sales::req.oppor.index') }}" class="btn btn-lg btn-warning">
                    <i class="fa fa-long-arrow-left"></i> {{ trans('sales::view.Back') }}
                </a>
                <button type="submit" class="btn btn-primary btn-lg btn-submit-oppor"
                        data-noti="{{ trans('sales::message.Submit will send email, are your sure want to continue?') }}"><i class="fa fa-save"></i> {{ trans('sales::view.Save') }}</button>
            </div>
            
            {!! Form::close() !!}
            
        </div>
    </div>
</div>

<div class="hidden">
    @include('sales::req-oppor.includes.number-member-item', ['memberItem' => null, 'itemOrder' => null])
    <div id="table_export"></div>
</div>
@stop

@section('script')
<script>
    var STT_SUBMIT = '{{ OpporView::STT_SUBMIT }}';
    var oldStatus = '{{ $item ? $item->status : '' }}';
    var urlCheckExists = '{{ route("sales::req.oppor.check_exists") }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/cpexcel.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/jszip.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/ods.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/shim.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/xlsx.full.min.js') }}"></script>
<script src="{{ CoreUrl::asset('/lib/xlsx-style/xlsx_table_to_book.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.3/FileSaver.min.js"></script>
<script src="{{ CoreUrl::asset('sales/js/opportunity.js') }}"></script>
@stop