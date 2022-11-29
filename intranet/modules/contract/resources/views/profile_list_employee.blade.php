<?php
    use Carbon\Carbon;
    use Rikkei\Contract\Model\ContractConfirmExpire;

    $title = trans('contract::vi.Contract list') . ' ' . trans('contract::vi.of') . ': ' . $user->name;
?>
@extends('layouts.default')

@section('title', $title)

@section('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('resource/css/candidate/list.css') }}">
    <style>
        .fade-scale {
            transform: scale(0);
            opacity: 0;
            -webkit-transition: all .25s linear;
            -o-transition: all .25s linear;
            transition: all .25s linear;
        }

        .fade-scale.in {
            opacity: 1;
            transform: scale(1);
        }
        .box .table-responsive {
            margin-left: 0;
        }
        @media only screen and (min-width: 992px) {
            .min-w-30 {
                width: 35%;
            }
        }
    </style>
@endsection

@section('content')

    @include('contract::message-alert')
    <div class="box box-info">
        <div class="box-body">
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-bordered table-hover table-responsive">
                        <thead>
                        <tr class="text-center info">
                            <th class="w-70 text-center">STT</th>
                            <th>{{ trans('contract::vi.contract type') }}</th>
                            <th class="text-center">{{ trans('contract::vi.start at') }}</th>
                            <th class="text-center">{{ trans('contract::vi.end at') }}</th>
                            <th class="text-center">{{ trans('contract::vi.Status confirm') }}</th>
                            <th class="min-w-30">{{ trans('contract::vi.Note') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if (isset($listContract) && count($listContract))
                            <?php
                                $i = 0;
                            ?>
                            @foreach($listContract as $item)
                                <tr data-id="{{ $item->id }}">
                                <td class="text-center">{{ ++$i }}</td>
                                <?php
                                    $textType = isset($allTypeContract[$item->type]) ? $allTypeContract[$item->type] : '';
                                    $startTime = Carbon::parse($item->start_at)->format('d-m-Y');
                                    $endTime = $item->end_at ? Carbon::parse($item->end_at)->format('d-m-Y') :'';
                                    $labelContractType = trans('contract::vi.contract type');
                                    $labelStartDate = trans('contract::vi.start at');
                                    $labelEndDate = trans('contract::vi.end at');
                                    $labelInfoContract = trans('contract::vi.Contract information is about to expire');
                                ?>
                                <td>{{ $textType }}</td>
                                <td class="text-center">{{ $startTime }}</td>
                                <td class="text-center">{{ $endTime }}</td>
                                @if (isset($item->confirmExpire->id))
                                    @if (isset($allTypeContractExpire[$item->confirmExpire->type]))
                                        <td class="text-center">
                                            <span class="label {{ $bgText[$item->confirmExpire->type] }}">{{$allTypeContractExpire[$item->confirmExpire->type]}}</span>
                                        </td>
                                    @else
                                        <td>&nbsp</td>
                                    @endif
                                    <td>{{ $item->confirmExpire->note }}</td>
                                @else
                                    <td>&nbsp</td>
                                    <td>&nbsp</td>
                                @endif
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')

@endsection