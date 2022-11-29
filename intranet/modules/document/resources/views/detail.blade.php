<?php
use Rikkei\Document\View\DocConst;
use Rikkei\Core\View\CoreUrl;

$routeSearch = route('doc::list');
?>

@extends('layouts.blog')

@section('title', trans('doc::view.Document'))

@section('css')

@include('doc::includes.css')

@stop

@section('content')

<div class="container">
    <h2 class="page-title">{{ $document->code }}</h2>

    <div class="row">
        <div class="col-md-8">
            <div class="box box-info">

                @include('messages.success')
                @include('messages.errors')

                <div class="box-body">

                    @if ($document->description)
                    <div class="form-group">
                        <label>{{ trans('doc::view.Description') }}: </label>
                        <div class="ws-pre-line">{{ $document->description }}</div>
                    </div>
                    @endif
                    
                    <?php
                    $currentFile = $document->getCurrentFile();
                    ?>
                    <div class="form-group">
                        <label><i class="fa fa-file-o"></i> {{ trans('doc::view.File document') }}: </label>
                        <div>
                            <a @if($currentFile->magazine_id != null) target="_blank" @endif  href="{{ $currentFile->frontDownloadLink($document->id) }}">
                                {{ $currentFile->name }}
                            </a>
                        </div>
                    </div>
                    
                    <?php
                    $attachFiles = $document->attachFiles;
                    ?>
                    @if (!$attachFiles->isEmpty())
                    <div class="form-group">
                        <label><i class="fa fa-files-o"></i> {{ trans('doc::view.Attach file') }}: </label>
                        <ul class="list-unstyled">
                            @foreach ($attachFiles as $file)
                            <li>
                                <a href="{{ $file->frontDownloadLink($document->id) }}">{{ $file->name }}</a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <?php
                    $types = $document->types;
                    ?>
                    @if (!$types->isEmpty())
                    <div class="form-group">
                        <label><i class="fa fa-folder-o"></i> {{ trans('doc::view.Document types') }}: </label>
                        <ul>
                            @foreach ($types as $type)
                            <li><a href="{{ $type->getViewLink() }}">{{ $type->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box box-info">
                @include('doc::includes.doc-sidebar-types', ['typeDoc' => null])
            </div>
            <div class="box box-info">
                @include('doc::includes.doc-sidebar-teams', ['teamDoc' => null])
            </div>
        </div>
    </div>
</div>

@stop

@section('script')

<script>
    var textShowLess = '<?php echo trans('doc::view.show less') ?>';
    var textShowMore = '<?php echo trans('doc::view.show more') ?>';
</script>
<script src="{{ CoreUrl::asset('asset_doc/js/main.js') }}"></script>

@stop