<?php
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
$tableBorder = 'border-collapse: collapse; border: 2px solid #767676; width: 100%;';
$tablePaddingCell = 'padding: 10px;';
$tableAlignRightCell = 'text-align: right;';
$tableAlignLeftCell = 'text-align: left;';
$tableCellBorder = 'border: 1px solid #767676;';
$lineHeight = 'line-height: 1.5;';
?>

@extends($layout)

@section('content')
    <p>{{ trans('asset::view.Dear :receiver_name,', ['receiver_name' => $data['receiver_name']]) }}</p>
    <p>{{ $data['header']}}</p>
    <table style="{{ $tableBorder }}" >
        <thead>
            <tr>
                <th class="width-10" style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}" >{{ trans('core::view.NO.') }}</th>
                <th class="width-50" style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}" >{{ trans('asset::view.Asset code') }}</th>
                <th class="width-100" style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}" >{{ trans('asset::view.Asset name') }}</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; ?>
        @foreach($data['assets'] as $item)
            <tr>
                <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">{{ $i }}</td>
                <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">{{ $item['code'] }}</td>
                <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">{{ $item['name'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endsection