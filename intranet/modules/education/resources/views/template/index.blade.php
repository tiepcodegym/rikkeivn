@extends('layouts.default', ['createProject' => true])

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
    <link href="{{ asset('education/css/setting-education.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-info">
                <div class="row">
                    <div class="col-sm-4"> <!-- required for floating -->
                        <!-- Nav tabs -->
                        <div class="left-tab">
                            <ul class="nav nav-pills tabs-left sideways">
                                @foreach ($typesView as $typeView => $labelTypeView)
                                    <li{{ ($typeView == $typeViewMain) ? ' class=active' : '' }}>
                                        <a href="{{ route('education::education.settings.index-template', ['type' => $typeView]) }}">{{ $labelTypeView }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="col-sm-8">
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div class="tab-pane active" id="student-temp">
                                @if (view()->exists('education::template.includes.' . $typeViewMain))
                                    @include('education::template.includes.' . $typeViewMain)
                                @else
                                    @include('education::template.includes.student')
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        setTimeout(function () {
            $('.flash-message').remove();
        }, 2000);
    });

    jQuery(document).ready(function ($) {
        var desEditor = CKEDITOR.replace( 'description', {
            extraPlugins: 'autogrow,image2,fixed',
            removePlugins: 'justify,colorbutton,indentblock,resize,fixed,resize,autogrow',
            removeButtons: 'About',
            startupFocus: true,
            fullPage : false,
            resize_enabled : false
        });

        CKFinder.setupCKEditor( desEditor, '/lib/ckfinder' );
    });
</script>
@endsection
