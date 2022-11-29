@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection

@section('content')
<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Education\Model\SettingEducation;

?>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="box-body">
                    <a class="btn btn-primary btn-create" href="{{ URL::route('education::education.settings.types.create') }}">
                            {{ trans('education::view.Add') }}
                        <i class="fa fa-spin fa-refresh hidden"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                            <tr>
                                <th class="col-id width-10 text-center" style="width: 20px;">{{ trans('education::view.No.') }}</th>
                                <th class="col-title text-center">{{ trans('education::view.Code') }}</th>
                                <th class="col-title text-center">{{ trans('education::view.Status') }}</th>
                                <th class="col-name text-center">{{ trans('education::view.Name') }}</th>
                                <th class="col-status" style="width: 250px;">&ensp;</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td class="text-center">{{ $i }}</td>
                                    <td class="text-center">
                                        {{ $item->code }}
                                    </td>
                                    <td class="text-center">
                                        {{ ($item->status == SettingEducation::STATUS_ENABLE) ? trans('education::view.Status Enable') : trans('education::view.Status Disabled') }}
                                    </td>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <td>
                                        <a class="btn btn-primary btn-create" href="{{ URL::route('education::education.settings.types.show_detail', [ $item->id ]) }}">
                                            {{ trans('education::view.Detail') }}
                                            <i class="fa fa-spin fa-refresh hidden"></i>
                                        </a>
                                        <a class="btn btn-primary btn-create" href="{{ URL::route('education::education.settings.types.show', [ $item->id ]) }}">
                                            {{ trans('education::view.update') }}
                                            <i class="fa fa-spin fa-refresh hidden"></i>
                                        </a>
                                        @if ($item->education_courses_types_count == 0 && $item->education_request_types_count == 0)
                                            <form action="{{URL::route('education::education.settings.types.delete', [ $item->id ])}}" method="POST" class="hidden">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="submit" id="form-{{$i}}" class="btn btn-danger hidden"/>
                                            </form>
                                            <a class="btn btn-primary btn-delete delete-confirm" href="#" data-id="{{$i}}">
                                                {{ trans('education::view.Delete') }}
                                                <i class="fa fa-spin fa-refresh hidden"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('education::view.messages.Data not found') }}</h2>
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="box-body">
                    @include('team::include.pager')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var globalIndex;
        $(document).ready(function () {
            setTimeout(function () {
                $('.flash-message').remove();
            }, 2000);
        });

        $('.btn-delete').on('click', function(e) {
            e.preventDefault();
            globalIndex = $(this).data('id');
        });

        $('.btn-ok').on('click', function(e) {
            e.preventDefault();
            $('#form-'+ globalIndex ).click();
        });
    </script>
@endsection
