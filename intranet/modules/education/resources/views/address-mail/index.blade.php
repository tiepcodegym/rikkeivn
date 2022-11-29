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

?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                        <thead>
                        <tr>
                            <th class="col-id width-10 text-center" style="width: 20px;">{{ trans('education::view.No.') }}</th>
                            <th class="col-title text-center">{{ trans('education::view.Branch name') }}</th>
                            <th class="col-name text-center">{{ trans('education::view.Branch code') }}</th>
                            <th class="col-name text-center">{{ trans('education::view.Mailing address') }}</th>
                            <th class="col-status" style="width: 250px;">&ensp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = CoreView::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td class="text-center">{{ $i }}</td>
                                    <td class="text-center">{{ $item->name }}</td>
                                    <td class="text-center">{{ $item->branch_code }}</td>
                                    <td class="text-center">
                                        @if($item->addressMail)
                                            {{ $item->addressMail->email }}
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-primary btn-create" href="{{ URL::route('education::education.settings.show-mail', [ $item->id ]) }}">
                                            {{ trans('education::view.update') }}
                                            <i class="fa fa-spin fa-refresh hidden"></i>
                                        </a>
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
            </div>
            <div class="box-body">
                @include('team::include.pager')
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function () {
            setTimeout(function () {
                $('.flash-message').remove();
            }, 2000);
        });
    </script>
@endsection
