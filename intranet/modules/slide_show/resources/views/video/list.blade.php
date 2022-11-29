@extends('layouts.default')
<?php
use Rikkei\Core\View\Form;
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\SlideShow\Model\VideoDefault;
use Illuminate\Support\Facades\Config as CoreConfig;

$videoTable = Rikkei\SlideShow\Model\VideoDefault::getTableName();
$companyTable = Rikkei\Sales\Model\Company::getTableName();
$collectionModel = $allVideo;
?>
@section('title')
{{ trans('slide_show::view.List video default') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link href="{{ asset('sales/css/customer_index.css') }}" rel="stylesheet" type="text/css" >
<meta name="csrf-token" content="{{ csrf_token() }}">

@endsection

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                @include('team::include.filter', ['isSettingVideo' => true, 'domainTrans' => 'slide_show'])
            </div>
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                    <thead>
                        <tr>
                            <th class="col-id width-5-per">{{ trans('slide_show::view.Numerical order') }}</th>
                            <th class="sorting {{ Config::getDirClass('title') }} col-title" data-order="title" data-dir="{{ Config::getDirOrder('title') }}">{{ trans('slide_show::view.Title') }}</th>
                            <th>{{ trans('slide_show::view.Content') }}</th>
                            <th class="col-action width-10-per">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="filter-input-grid">
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $videoTable }}.title]" value="{{ Form::getFilterData("{$videoTable}.title") }}" placeholder="{{ trans('slide_show::view.Search') }}..." class="filter-grid form-control" style="border-radius: 4px !important" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td>
                                    <?php
                                        $url = 'https://www.youtube.com/watch?v='.$item->file_name;
                                    ?>
                                    <a href="{{$url}}" target="_blank">{{ trans('slide_show::view.Detail') }}</a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('slide_show::video-edit', ['id' => $item->id]) }}" class="btn-edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{route('slide_show::delete-video-default')}}" method="post" class="form-inline">
                                            {!! csrf_field() !!}
                                            {!! method_field('delete') !!}
                                            <input type="hidden" name="id" value="{{ $item->id }}" />
                                            <button href="" class="btn-delete delete-confirm" disabled>
                                                <span><i class="fa fa-trash-o"></i></span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">
                                    <h2 class="no-result-grid">{{trans('project::view.No results found')}}</h2>
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
<div class="modal-slide">
    <div class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span></button>
                <h4 class="modal-title modal-title-success">{{trans('slide_show::view.Success')}}</h4>
                <h4 class="modal-title modal-title-error">{{trans('slide_show::view.Error')}}</h4>
              </div>
              <div class="modal-body">
                <p class="text-message"></p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline" data-dismiss="modal">{{trans('slide_show::view.Close')}}</button>
              </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
</div>
@endsection

@section('script')
<script src="{{ asset('slide_show/js/setting.js') }}"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>

<script>
    var urlChangePassword = '{{route('slide_show::change-paswword')}}';
    var urlChangeBirthday = '{{route('slide_show::change-birthday-company')}}';
</script>
@endsection
