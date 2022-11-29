@extends('layouts.default')

@section('title', trans('event::view.send_mail_off_confirm_title'))

@section('content')

<?php
if (isset($emailByLeaders[''])) {
    $emailNoneLeaders = $emailByLeaders[''];
    unset($emailByLeaders['']);
    $emailByLeaders[] = $emailNoneLeaders;
}
?>

<div class="box box-info">
    <div class="box-body">
        <a href="{{ route('event::mailoff.upload') }}" class="btn btn-warning">
            <i class="fa fa-long-arrow-left"></i> {{ trans('event::view.Back') }}
        </a>
        <form class="form-inline no-validate" method="post" action="{{ route('event::mailoff.sendmail') }}">
            {!! csrf_field() !!}
            <button type="submit" class="btn btn-success" id="btn_send_mail"><i class="fa fa-send"></i> {{ trans('event::view.Send email') }}</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover dataTable table-bordered">
            <thead>
                <tr>
                    <th>{{ trans('event::view.STT') }}</th>
                    <th>{{ trans('event::view.Leader email') }}</th>
                    <th>{{ trans('event::view.Team') }}</th>
                    <th>{{ trans('event::view.Email') }}</th>
                    <th>{{ trans('event::view.Password') }}</th>
                </tr>
            </thead>
            <tbody>
                @if (count($emailByLeaders) > 0)
                    <?php $order = 0; ?>
                    @foreach ($emailByLeaders as $keyGr => $groupLeaders)
                        <?php $order++; ?>
                        @foreach ($groupLeaders as $keyItem => $item)
                        <tr class="{{ !$item['ld_email'] ? 'text-red' : '' }}">
                            @if ($keyItem == 0)
                            <td rowspan="{{ count($groupLeaders) }}">{{ $order }}</td>
                            <td rowspan="{{ count($groupLeaders) }}">{{ $item['ld_email'] ? $item['ld_email'] : 'None' }}</td>
                            <td rowspan="{{ count($groupLeaders) }}">{{ $item['team_name'] }}</td>
                            @endif
                            <td>{{ $item['email'] }}</td>
                            <td>{{ $item['password'] }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
    <div class="box-body"></div>
</div>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script>

    $('#btn_send_mail').click(function (e) {
        e.preventDefault();
        var btn = $(this);
        bootbox.confirm({
            message: '<?php echo trans('event::message.Are you sure want to send mail?') ?>',
            className: 'modal-warning',
            callback: function (result) {
                if (result) {
                    btn.closest('form').submit();
                }
            }
        });
    });

</script>
@stop

