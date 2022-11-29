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
    <?php
        $set = false;
        $dataComfirm = [];
    ?>
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
                            <th class="set"></th>
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
                                    <td class="w-100 text-center set">
                                        @if (isset($endTime) && strtotime($endTime) >= strtotime(Carbon::now()->format('d-m-Y')))
                                            <?php
                                                $set = true;
                                                if ($item->confirmExpire->type == ContractConfirmExpire::NO_CONFIRM_CONTRACT) {
                                                    $dataComfirm = [
                                                        $item->id,
                                                        ContractConfirmExpire::EXTEND_CONTRACT,
                                                        $item->confirmExpire->note,
                                                        "<p>$labelInfoContract</p>
                                                                <table class='table table-bordered modal_table'>
                                                                    <thead>
                                                                    <tr>
                                                                        <th>$labelContractType</th>
                                                                        <th>$labelStartDate</th>
                                                                        <th>$labelEndDate</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <td>$textType</td>
                                                                        <td>$startTime</td>
                                                                        <td>$endTime</td>
                                                                    </tbody>
                                                                </table>
                                                                <hr>"
                                                    ];
                                                }
                                            ?>
                                            <button type="button" class="btn btn-primary btn-update" data-note="{{ $item->confirmExpire->note }}" data-type="{{$item->confirmExpire->type}}">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        @endif
                                    </td>
                                @else
                                    <td>&nbsp</td>
                                    <td>&nbsp</td>
                                    <td class="set">&nbsp</td>
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
    <button type="button" class="btn btn-primary hidden" data-toggle="modal" data-target="#myModal">
        <i class="fa fa-edit"></i>
    </button>
    <!-- Modal -->
    <div class="modal fade-scale" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('contract::contract.update-confirm') }}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h3 class="modal-title text-center" id="myModalLabel">{{trans('contract::vi.Confirm contract extension')}}</h3>
                    </div>
                    <div class="modal-body">
                        <div class="add_table"></div>
                        <p>{{trans('contract::vi.You please confirm the contract.')}}</p>
                        <div class="radio">
                            <label><input type="radio" id="{{ ContractConfirmExpire::EXTEND_CONTRACT }}" name="cat" checked value="{{ ContractConfirmExpire::EXTEND_CONTRACT }}">{{ trans('contract::view.Extend contract') }}</label>
                        </div>
                        <div class="radio">
                            <label><input type="radio" id="{{ ContractConfirmExpire::END_CONTRACT }}" name="cat" value="{{ ContractConfirmExpire::END_CONTRACT }}">{{ trans('contract::view.End contract') }}</label>
                        </div>
                        <div class="form-group">
                            <label for="comment">Comment:</label>
                            <textarea class="form-control" rows="4" id="note" name="note"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default float-left" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let noConfirm = "{{ContractConfirmExpire::NO_CONFIRM_CONTRACT}}";
        let extend = "{{ContractConfirmExpire::EXTEND_CONTRACT}}";

        $(document).on('click', '.btn-update', function () {
            let row = $(this).closest("tr");
            let type = $(this).attr('data-type');
            if (type == noConfirm) {
                type = extend;
            }
            showModal(row.attr('data-id'), type, $(this).attr('data-note'), '')

        });

        $(document).ready(function () {
            let dataConfirm = <?php echo json_encode($dataComfirm) ?>;
            if (dataConfirm.length) {
                showModal(dataConfirm[0], dataConfirm[1], dataConfirm[2], dataConfirm[3]);
            }
            let set = "{{ $set }}";
            if (!set) {
                $('.set').css('display', 'none');
            }
        });

        function showModal(id, type, text, info) {
            $('.add_table').html(info);
            $('input[name="cat"]').removeAttr('checked');
            $('input[type=radio][id="' + type + '"]').prop('checked', true);
            $('.modal-dialog').find('input[name="id"]').val(id);
            $('#note').text(text);
            $('button[data-toggle="modal"]').click();
        }
    </script>
@endsection