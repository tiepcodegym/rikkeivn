@extends('layouts.default')

@section('title', trans('test::test.upload_files'))

<?php
use Rikkei\Magazine\Model\ImageModel;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Core\View\CoreUrl;

$imageTbl = ImageModel::getTableName();
?>
@section('css')
<link rel="stylesheet" href="{{ CoreUrl::asset('tests/css/main.css') }}">
<link rel="stylesheet" href="{{ CoreUrl::asset('tests/css/upload.css') }}">
@endsection

@section('content')

<div class="box box-info">

    <div class="box-body">
        <form id="upload_form" action="{{ route('test::admin.upload_images') }}">
            <div class="form-group row">
                <label class="col-sm-3 col-md-2">
                    {{ trans('test::test.select_file_upload') }}
                    <p><i>(jpeg, jpg, png, gif, mp3, wav, wma)</i></p>
                </label>
                <div class="col-sm-9 col-md-10">
                    <input type="file" name="images" multiple id="upload_field" accept="image/*,audio/*">
                </div>
            </div>

            <div class="form-group row" id="uploaded_box">
                <label class="col-sm-3 col-md-2"></label>
                <div class="col-sm-9 col-md-10">
                    <div class="list-images">

                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-3 col-md-2"></div>
                <div class="col-sm-9 col-md-10">
                    <button id="upload_btn" type="submit" class="btn-add"><i class="fa fa-upload"></i> {{ trans('test::test.upload') }} <i class="fa fa-spin fa-refresh uploading hidden"></i></button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="img-preview media hidden" id="preview_item">
        <div class="media-left pull-left thumb">
            <img src="" alt="No image">
            <button type="button" class="del-btn" title="Delete item"><span class="fa fa-close"></span></button>
        </div>
        <div class="media-body">
            <h4 class="media-heading filename"></h4>
        </div>
    </div>

</div>

<h3>{{ trans('test::test.image_uploaded') }}</h3>
<div class="box box-info">
    <div class="box-body">
        <div class="btn_actions pull-left">
            @if(!$collectionModel->isEmpty())
            <a href="{{route('test::admin.image.multi_actions')}}" action="delete" id="m_action_delete" data-noti="{{ trans('core::view.Are you sure delete item(s)?') }}" 
               class="m_action_btn btn btn-danger">
                <i class="fa fa-trash"></i> <span class="">{{trans('test::test.delete')}}</span>
            </a>
            @endif
        </div>
        @include('team::include.filter')
        <div class="clearfix"></div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><input type="checkbox" class="check_all"></th>
                    <th>ID</th>
                    <th>{{ trans('test::test.Thumbnail') }}</th>
                    <th>{{ trans('test::test.Title') }}</th>
                    <th>{{ trans('test::test.URL') }}</th>
                    <th>{{ trans('test::test.Copy') }}</th>
                    <th>{{ trans('test::test.Uploaded by') }}</th>
                    <th>{{ trans('test::test.Date') }}</th>
                    <th>{{ trans('test::test.Remove') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <input type="text" name="filter[{{ $imageTbl }}.title]" value="{{ FormView::getFilterData("{$imageTbl}.title") }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" />
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @if (!$collectionModel->isEmpty())
                    @foreach($collectionModel as $item)
                    <?php $imgSrc = $item->getSrc('full', 'tests/') ?>
                    <tr id="image_{{ $item->id }}" inuse="0">
                        <td><input type="checkbox" class="check_item" value="{{ $item->id }}"></td>
                        <td>{{ $item->id }}</td>
                        <td>
                            @if ($item->isImageFile())
                            <img width="50" src="{{ $imgSrc }}" alt="No image">
                            @elseif ($item->isAudioFile())
                            <img width="50" src="{{ asset('tests/images/audio-icon.png') }}">
                            @endif
                        </td>
                        <td class="img-title">{{ $item->title }}</td>
                        <td class="url-col">
                            @if ($imgSrc)
                                <span class="text">{{ url($imgSrc) }}</span>
                            @else
                                NULL
                            @endif
                        </td>
                        <td class="copy-col">
                            <button type="button" class="copy-btn btn btn-sm btn-primary">{{ trans('test::test.Copy') }}</button>
                        </td>
                        <td>{{ $item->employee ? $item->employee->name : null }}</td>
                        <td>{{ $item->created_at->format('H:i d-m-Y') }}</td>
                        <td>
                            {!! Form::open(['method' => 'delete', 'route' => ['test::admin.delete_image', $item->id]]) !!}
                            <button type="submit" class="btn-delete delete-confirm" data-noti="{{ trans('core::view.Are you sure delete item(s)?') }}" title="{{ trans('test::test.delete') }}"><i class="fa fa-trash"></i></button>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center">{{ trans('test::test.no_item') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    
    <div class="box-body">
         @include('team::include.pager')
    </div>
    
    <div id="copy_tooltip" class="hidden">{{ trans('test::test.copy_to_success') }}</div>
</div>

@stop

@section('script')

<script>
    var globMess = {
        file_max_size: '<?php echo trans('test::validate.file_max_size', ['attribute' => 'File', 'max' => 5]) ?>',
        file_mimes: '<?php echo trans('test::validate.file_mimes', ['attribute' => 'File', 'types' => 'jpeg, jpg, png, gif, mp3, wma, wav']) ?>',
        no_file_selected: '<?php echo trans('test::validate.no_file_selected', ['attribute' => 'file']) ?>',
        continue_delete: '<?php echo trans('test::validate.continue_delete') ?>',
        image_has_in_test: '<?php echo trans('test::validate.this_image_has_in_test') ?>',
    };
    var checkImageInUseUrl = '{{ route('test::admin.image.check_in_use') }}';
    var urlAudioIcon = '{{ asset("tests/images/audio-icon.png") }}';
</script>
@include('test::template.script')
<script src="/lib/js/exif.js"></script>
<script src="{{ CoreUrl::asset('tests/js/upload_image.js') }}"></script>

@stop

