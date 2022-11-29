<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Event\View\ViewEvent;
?>

@extends('layouts.default')

@section('body_class', 'send-salary-page')

@section('title', trans('event::view.Send mail tax infomation'))

@section('css')
<link rel="stylesheet" href="{{ CoreUrl::asset('/event/css/salary.css') }}">
@stop

@section('content')

<?php
$totalCol = 0;
if ($collectCols) {
    foreach ($collectCols as $columns) {
        $maxIdx = max(array_keys($columns));
        if ($maxIdx > $totalCol) {
            $totalCol = $maxIdx;
        }
    }
}
$totalCol++;
?>
<div class="box box-info">
    @if ($taxFile)
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <h4 class="box-title">{{ $taxFile->filename }}</h4>
                <p class="text-yellow">({{ trans('event::view.Data is only temporarily stored') }})</p>
            </div>
            <div class="col-md-3">
                @include('event::tax.links')
            </div>
            <div class="col-md-3">
                <div class="group-button text-right">
                    {!! Form::open(['method' => 'delete', 'route' => 'event::send.email.employees.delete_temp.tax', 'class' => 'form-inline no-validate', 'id' => 'delete_salary_form']) !!}
                    <button class="btn btn-warning" type="submit"><i class="fa fa-repeat"></i> {{ trans('event::view.Reupload file') }}</button>
                    {!! Form::close() !!}
                    <button class="btn btn-info" id="btn_send_mail"><i class="fa fa-send"></i> {{ trans('event::view.Send email') }}</button>
                    <button class="btn btn-danger hidden" id="btn_stop_send"><i class="fa fa-ban"></i> {{ trans('event::view.Stop sending') }}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover dataTable" id="salary_table"
               data-file-id="{{ $taxFile->id }}">
            <thead>
                @if ($collectCols)
                    @foreach ($collectCols as $numRow => $columns)
                    <tr>
                        @if ($numRow == 2)
                        <th rowspan="2">{{ trans('event::view.STT') }}</th>
                        <th rowspan="2">{{ trans('event::view.Send email') }}</th>
                        @endif
                        @for ($i = 0; $i < $totalCol; $i++)
                            @if (isset($columns[$i]))
                            <th rowspan="{{ $columns[$i]['rows'] }}" colspan="{{ $columns[$i]['cols'] }}">{{ $columns[$i]['title'] }}</th>
                            @endif
                        @endfor
                    </tr>
                    @endforeach
                @endif
            </thead>
            <tbody>
                @if ($arrayData)
                <?php $order = 0; ?>
                @foreach ($arrayData as $email => $row)
                <?php $order++; ?>
                <tr data-email="{{ $email }}">
                    <td>{{ $order }}</td>
                    <td class="text-center"><i class="sending-status text-green">...</i></td>
                    @for ($i = 0; $i < $totalCol; $i++)
                    <td class="white-space-nowrap">
                        @if (isset($row[$i]))
                        {{ ViewEvent::formatMoney($row[$i], $isFormatNumber) }}
                        @endif
                    </td>
                    @endfor
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="{{ $totalCol + 2 }}"><h4 class="text-center">{{ trans('event::message.None item read') }}</h4></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @else
    <div class="box-body">
        <h4 class="error">{{ trans('event::message.Not found file salary info') }}</h4>
    </div>
    @endif
</div>

@endsection

@section('script')
<script>
    var stopSending = 0;
    var sendMailUrl = '{{ route("event::send.email.employees.send_mail.tax") }}';
    var delaySendMail = {{ ViewEvent::DELAY_SEND_MAIL }};
    var detailMailSentLink = '';
    @if ($taxFile)
        detailMailSentLink = '{{ route("event::send.email.employees.tax.mail_detail", ["id" => $taxFile->id]) }}';
    @endif
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="{{ CoreUrl::asset('event/js/salary.js') }}"></script>
@endsection
