<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;

extract($data);

$layout = EmailQueue::getLayoutConfig();
$content = CoreConfigData::getValueDb('it.email_content.mailoff');
$content = preg_replace(
    [
        '/\{\{\sname\s\}\}/',
        '/\{\{\saccount\s\}\}/',
    ],
    [
        $leaderName,
        preg_replace('/\@.*/', '', $leaderEmail)
    ],
    $content
);
?>

@extends($layout)

@section('css')
<style>
    .mail-table {
        border-collapse: collapse;
        border: 2px solid #767676;
        width: 100%;
    }
    .mail-table tr th, .mail-table tr td {
        padding: 8px 10px;
        border: 1px solid #767676;
        text-align: left;
    }
</style>
@stop

@section('content')

{!! $content !!}

<p>&nbsp;</p>

<h4>Danh sách emails</h4>

<table class="mail-table">
    <thead>
        <tr>
            <th>{{ trans('event::view.STT') }}</th>
            <th>{{ trans('event::view.Email') }}</th>
            <th>{{ trans('event::view.Password') }}</th>
        </tr>
    </thead>
    <tbody>
        @if ($empEmails)
            @foreach ($empEmails as $order => $account)
            <tr>
                <td>{{ $order + 1 }}</td>
                <td>{{ $account['email'] }}</td>
                <td>{{ $account['password'] }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>

@endsection
