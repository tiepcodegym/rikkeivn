@extends('asset::item.report.layout')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Assets\Model\AssetHistory;

    $labelStates = AssetHistory::labelStates();
    $tblBorder = 'border: 1px solid #555; ';
    $textCenter = 'text-align: center; ';
    $fontSize = 'font-size: 13px; ';
    $fontWeight = 'font-weight: bold; ';
?>

@section('title')
    {{ trans('asset::view.Report detail on the asset use process') }}
@endsection

@section('css')
    <style>
        .font-italic {
            font-style: italic;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-transform-uppercase {
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
@endsection

@section('content')
    @if(isset($datasReport) && count($datasReport))
        <?php
            $countDatasReport = count($datasReport);
            $countLoop = 0;
        ?>
        @foreach($datasReport as $item)
            <?php
                $countLoop++;
                $last = false;
                if ($countLoop == $countDatasReport) {
                    $last = true;
                }
            ?>
            <div class="row">
                <div class="col-md-12" style="text-align: center;">
                    <h2>{{ trans('asset::view.Report detail on the asset use process') }}</h2>
                </div>
                <div class="col-md-12" style="margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <span><span class="font-bold">{{ trans('asset::view.Asset code:') }}</span> {{ $item['asset_code'] }}</span>
                        </div>
                        <div class="col-md-6">
                            <span><span class="font-bold">{{ trans('asset::view.Asset name:') }}</span> {{ $item['asset_name'] }}</span>
                        </div>
                    </div>
                </div>
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data asset-table" style="width: 100%;">
                    <tbody class="table-body">
                        <tr>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 60px;">{{ trans('core::view.NO.') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 100px;">{{ trans('asset::view.Employee code') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 130px;">{{ trans('asset::view.Employee name') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 160px;">{{ trans('asset::view.Position') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 110px;">{{ trans('asset::view.State') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 90px;">{{ trans('asset::view.Date') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 290px;">{{ trans('asset::view.Reason') }}</td>
                        </tr>
                        @if(!empty($item['history']))
                            <?php $i = 1; ?>
                            @foreach($item['history'] as $history)
                                <?php
                                    $changeDate = '';
                                    if ($history['change_date']) {
                                        $changeDate = Carbon::parse($history['change_date'])->format('d/m/Y');
                                    }
                                ?>
                                <tr>
                                    <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $i }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{{ $history['employee_code'] }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{{ $history['employee_name'] }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{{ $history['role_name'] }}</td>
                                    <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $history['state_history'] }}</td>
                                    <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $changeDate }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{!! View::nl2br($history['change_reason']) !!}</td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('asset::view.No results data') }}</h2>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="4">&nbsp;</td>
                            <td colspan="4">
                                <div style="{!!$textCenter!!}">
                                    <div class="group-sign">
                                        <span class="font-italic">{{ trans('asset::view.Day...month...year') }}</span><br>
                                        <span class="text-transform-uppercase"><b>{{ trans('asset::view.Scheduler person') }}</b></span><br>
                                        <span class="font-italic">{{ trans('asset::view.(Sign, write full name)') }}</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if (!$last)
                <div class="page-break"></div>
            @endif
        @endforeach
    @else
        <h2 class="no-result-grid">{{ trans('asset::view.No results data') }}</h2>
    @endif
@endsection