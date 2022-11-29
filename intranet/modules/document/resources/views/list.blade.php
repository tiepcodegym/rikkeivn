<?php
use Rikkei\Document\View\DocConst;
use Rikkei\Core\View\CoreUrl;

$arrayTypes = $listTypes->lists('name', 'id')->toArray();
$pageTitle = trans('doc::view.Document');
$routeSearch = route('doc::list');
if ($typeDoc) {
    $pageTitle .= ' - ' . trans('doc::view.Type') . ': ' . $typeDoc->name;
    $routeSearch = $typeDoc->getViewLink();
}
if ($teamDoc) {
    $pageTitle .= ' - ' . trans('doc::view.Team') . ': ' . $teamDoc->name;
}
?>

@extends('layouts.blog')

@section('title', trans('doc::view.Document'))

@section('css')

@include('doc::includes.css')

@stop

@section('content')

<div class="container">
    <?php
    $search = request()->get('search');
    if ($search) {
        $pageTitle .= ' - ' . trans('doc::view.search for') . ' "'. $search .'"';
    }
    ?>
    <h2 class="page-title">{{ $pageTitle }}</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="box box-rikkei">
                @include('doc::includes.doc-sidebar-types')
            </div>
            <div class="box box-rikkei">
                @include('doc::includes.doc-sidebar-teams')
            </div>
        </div>
        <div class="col-md-8">
            <div class="box box-rikkei">

                @include('messages.success')
                @include('messages.errors')

                <div class="box-body">
                    @if (!$collection->isEmpty())
                        <div class="doc-list">
                            @foreach ($collection as $doc)
                                <div class="doc-item">
                                    <h3 class="doc-title"><a href="{{ $doc->getViewLink() }}">{{ $doc->code }}</a></h3>
                                    <div class="doc-desc-box">
                                        @if ($doc->description)
                                            <div class="doc-desc ws-pre-line el-short-content" data-showchars="200">{{ $doc->description }}</div>
                                        @endif
                                        @if ($doc->file_id)
                                            <a target="_blank"
                                               href="{{ $doc->file_type == 'link' ? $doc->file_url : route('doc::file.download', ['docId' => $doc->id, 'fileId' => $doc->file_id]) }}">
                                                {{ $doc->file_name }}
                                            </a>
                                        @endif
                                    </div>
                                    <div class="doc-meta">
                                        <span class="date"><i class="fa fa-calendar"></i> {{ $doc->created_at->format('Y-m-d') }}</span>
                                        <?php $typeListName = DocConst::getListTypeName($doc->type_ids, $arrayTypes, false); ?>
                                        @if ($typeListName)
                                            | <ul class="meta-types">
                                                <i class="fa fa-folder"></i> {!! $typeListName !!}
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="doc-paginate text-center">
                            {!! $collection->appends(request()->all())->links() !!}
                        </div>

                    @else
                        <h4>{{ trans('doc::message.Document not found') }}</h4>
                    @endif
                </div>
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