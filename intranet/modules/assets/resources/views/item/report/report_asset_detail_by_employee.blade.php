@extends('asset::item.report.layout')

<?php
    use Rikkei\Core\View\View;
    use Rikkei\Assets\Model\AssetItem;
    use Carbon\Carbon;
    $labelStates = AssetItem::labelStates();
    $now = Carbon::now();
    $tblBorder = 'border: 1px solid #555; ';
    $textCenter = 'text-align: center; ';
    $fontSize = 'font-size: 13px; ';
    $fontWeight = 'font-weight: bold; ';
?>

@section('title')
    {{ trans('asset::view.Report asset detail by employee') }}
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
                $employee = $item;
                $last = false;
                if ($countLoop == $countDatasReport) {
                    $last = true;
                }
            ?>
            <div class="row">
                <div class="col-md-12" style="text-align: center; margin-bottom: 30px;">
                    <h2>{{ trans('asset::view.Report asset detail by employee') }}</h2>
                    <span class="font-italic">{{ trans('asset::view.To date: :date', ['date' => $now->format('d/m/Y')]) }}</span>
                </div>
                <div class="col-md-12" style="margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-6">
                            <span><span class="font-bold">{{ trans('asset::view.Employee name: ') }}</span>{{ $employee['employee_name'] }}</span>
                        </div>
                        <div class="col-md-6">
                            <span><span class="font-bold">{{ trans('asset::view.Position: ') }}</span>{{ $employee['role_name'] }}</span>
                        </div>
                    </div>
                </div>
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data asset-table" style="width: 100%;">
                    <tbody class="table-body">
                        <tr>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 60px;">{{ trans('core::view.NO.') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 100px;">{{ trans('asset::view.Asset code') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 160px;">{{ trans('asset::view.Asset name') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 150px;">{{ trans('asset::view.Asset category') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 110px;">{{ trans('asset::view.State') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 100px;">{{ trans('asset::view.Received date') }}</td>
                            <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 260px;">{{ trans('asset::view.Specification') }}</td>
                        </tr>
                        @if(count($item['assets']))
                            <?php $i = 1; ?>
                            @foreach($item['assets'] as $asset)
                                <?php
                                    $receivedDate = '';
                                    if ($asset['received_date']) {
                                        $receivedDate = Carbon::parse($asset['received_date'])->format('d/m/Y');
                                    }
                                ?>
                                <tr>
                                    <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $i }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{{ $asset['code'] }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{{ $asset['name'] }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{{ $asset['category'] }}</td>
                                    <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $labelStates[$asset['state']] }}</td>
                                    <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $receivedDate }}</td>
                                    <td style="{!! $tblBorder . $fontSize !!}">{!! View::nl2br($asset['specification']) !!}</td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
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