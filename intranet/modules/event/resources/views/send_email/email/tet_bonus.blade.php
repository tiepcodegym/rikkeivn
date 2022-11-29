<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Event\View\ViewEvent;

$layout = EmailQueue::getLayoutConfig();
$content = CoreConfigData::getValueDb('event.send.email.tet.content');
$titleIndex = ViewEvent::getHeadingIndexTetBonus();

if(isset($data['reg_replace']) && $data['reg_replace'] &&
    isset($data['reg_replace']['patterns']) && $data['reg_replace']['patterns'] && 
    isset($data['reg_replace']['replaces']) && $data['reg_replace']['replaces']
) {
    $content = preg_replace(
        $data['reg_replace']['patterns'], 
        $data['reg_replace']['replaces'], 
        $content
    );
}
$styleTable = 'border-collapse: collapse; border: 2px solid #767676; width: 100%;';
$styleTdLeft = 'padding: 10px; text-align: left; border: 1px solid #767676;';
$styleTdRight = 'padding: 10px; text-align: right; border: 1px solid #767676;';
?>
@extends($layout)

@section('css')
<style>
table.bonus-tet {
    border-collapse: collapse; 
    border: 2px solid #767676; 
    width: 100%;
    tbody {
        tr {
            td.left {
                padding: 10px;
                text-align: left;
                border: 1px solid #767676;
            }
            td.right {
                padding: 10px;
                text-align: right;
                border: 1px solid #767676;
            }
        }
    }
}
</style>
@endsection

@section('content')
{!! $content !!}

<table class="bonus-tet" style="{{ $styleTable }}">
    <tbody>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                Họ và tên
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['full_name']}))
                    {{ $data['bonus']->{$titleIndex['full_name']} }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                {{ trans('event::view.STT') }}
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['id']}))
                    {{ $data['bonus']->{$titleIndex['id']} }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                {{ trans('event::view.Email') }}
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['email']}))
                    {{ $data['bonus']->{$titleIndex['email']} }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                Số tháng làm việc chính thức
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['month_works']}))
                    {{ $data['bonus']->{$titleIndex['month_works']} }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                Thưởng tháng 13
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['bonus_13']}))
                    <?php $number = preg_replace('/\,|\s/', '', $data['bonus']->{$titleIndex['bonus_13']}); ?>
                    {{ number_format($number) }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                Thưởng tết
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['bonus_tet']}))
                    <?php $number = preg_replace('/\,|\s/', '', $data['bonus']->{$titleIndex['bonus_tet']}); ?>
                    {{ number_format($number) }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                Tổng nhận
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['get_total']}))
                    <?php $number = preg_replace('/\,|\s/', '', $data['bonus']->{$titleIndex['get_total']}); ?>
                    {{ number_format($number) }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                Thuế TNCN
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                @if (isset($data['bonus']->{$titleIndex['tax_bonus_tet']}))
                    <?php $number = preg_replace('/\,|\s/', '', $data['bonus']->{$titleIndex['tax_bonus_tet']}); ?>
                    {{ number_format($number) }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="left" style="{{ $styleTdLeft }}">
                <strong>Thực nhận</strong>
            </td>
            <td class="right" style="{{ $styleTdRight }}">
                <strong>
                @if (isset($data['bonus']->{$titleIndex['get_real']}))
                    <?php $number = preg_replace('/\,|\s/', '', $data['bonus']->{$titleIndex['get_real']}); ?>
                    {{ number_format($number) }}
                @endif
                </strong>
            </td>
        </tr>
    </tbody>
</table>
@endsection
