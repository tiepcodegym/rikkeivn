@extends('asset::item.report.layout')

<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Assets\Model\AssetItem;
    use Rikkei\Team\Model\Team;

    $labelStates = AssetItem::labelStates();
    $team = Team::find($optionsReport['team_id']);
    $teamName = '';
    if ($team) {
        $teamName = $team->name;
    }
    $tblBorder = 'border: 1px solid #555; ';
    $textCenter = 'text-align: center; ';
    $fontSize = 'font-size: 13px; ';
    $fontWeight = 'font-weight: bold; ';
?>

@section('title')
    {{ trans('asset::view.Assets lost and broken list') }}
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12" style="text-align: center;">
            <h1>{{ trans('asset::view.Assets lost and broken list') }}</h1>
            <span style="font-style: italic;">{{ trans('asset::view.From date :start_date to date :end_date', ['start_date' => $optionsReport['start_date']->format('d/m/Y'), 'end_date' => $optionsReport['end_date']->format('d/m/Y')]) }}</span>
        </div>
        <div class="col-md-12" style="margin-bottom: 20px;">
            <span><span style="{!! $fontWeight !!}">{{ trans('asset::view.Management team: ') }}</span>{{ $teamName }}</span>
        </div>
        <br>
        <table class="table table-striped dataTable table-bordered table-hover table-grid-data asset-table" style="width: 100%;">
            <tbody class="table-body">
                <tr>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 60px;">{{ trans('core::view.NO.') }}</td>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 100px;">{{ trans('asset::view.Asset code') }}</td>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 120px;">{{ trans('asset::view.Asset name') }}</td>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 100px;">{{ trans('asset::view.Asset user') }}</td>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 160px;">{{ trans('asset::view.Position') }}</td>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 80px;">{{ trans('asset::view.State') }}</td>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 110px;">{{ trans('asset::view.Broken/lost date') }}</td>
                    <td style="{!! $tblBorder . $textCenter . $fontWeight !!} width: 250px;">{{ trans('asset::view.Reason') }}</td>
                </tr>
            
                @if(isset($assetItems) && count($assetItems))
                    <?php $i = 1; ?>
                    @foreach($assetItems as $item)
                        <?php
                            $changeDate = '';
                            if ($item->change_date) {
                                $changeDate = Carbon::parse($item->change_date)->format('d/m/Y');
                            }
                        ?>
                        <tr>
                            <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $i }}</td>
                            <td style="{!! $tblBorder . $fontSize !!}">{{ $item->code }}</td>
                            <td style="{!!$tblBorder . $fontSize !!}">{{ $item->name }}</td>
                            <td style="{!!$tblBorder . $fontSize !!}">{{ $item->user_name }}</td>
                            <td style="{!!$tblBorder . $fontSize !!}">{{ $item->role_name }}</td>
                            <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $labelStates[$item->state] }}</td>
                            <td style="{!! $tblBorder . $fontSize . $textCenter !!}">{{ $changeDate }}</td>
                            <td style="{!! $tblBorder . $fontSize !!}">{!! View::nl2br($item->reason) !!}</td>
                        </tr>
                        <?php $i++; ?>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center">
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
@endsection