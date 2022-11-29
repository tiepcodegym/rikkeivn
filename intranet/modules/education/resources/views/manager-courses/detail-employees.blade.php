@extends('layouts.default')

@section('title')
    <?php if ($flag == 1) { ?>
    {{ trans('education::view.Education.Manager create') }}
    <?php } else { ?>
    {{ trans('education::view.Education.Manager detail') }}
    <?php } ?>
@endsection

@section('content')

    <div class="row list-css-page">
        <div class="col-xs-12">
            <div class="box box-info">
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="box-body">
                                @include('education::manager-courses.includes.manager-detail-employee-infomation')
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.col -->
            </div>
        </div>
    </div>

@endsection

<!-- Styles -->
@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link href="{{ asset('resource/css/candidate/list.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
@endsection

<!-- Script -->
@section('script')
    <?php
    use Rikkei\Core\View\CoreUrl;
    ?>
    <script src="{{ CoreUrl::asset('resource/js/candidate/list.js') }}"></script>
    <script src="{{ CoreUrl::asset('resource/js/request/list.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
@endsection