@extends('layouts.default')

<?php 
use Rikkei\Test\Models\Test;
use Rikkei\Test\View\ViewTest;
use Rikkei\Core\View\CoreLang;

$pageTitle = trans('test::test.add_new_test'); 
$formRoute = 'test::admin.test.save';
$formMethod = 'post';

$allLangs = CoreLang::allLang();
$currentLang = request()->get('lang');
if (!$currentLang) {
    $currentLang = Session::get('locale');
}
?>

@section('title', $pageTitle)

@section('css')

@include('test::template.css')

@stop

@section('content')

@if (isset($notEqualQuestions) && $notEqualQuestions)
    <div class="alert alert-warning">
        <ul>
            <li>{{ trans('test::test.total_questions_not_equal') }}</li>
        </ul>
    </div>
@endif

{!! Form::open([
    'method' => $formMethod, 
    'route' => $formRoute, 
    'id' => 'test_form', 
    'class' => 'validate_form',
    'enctype' => 'multipart/form-data',
]) !!}

<div class="row">
    <div class="col-lg-10 col-md-9 col-sm-8">

        <div class="nav-tabs-custom nav-tabs-rikkei test-tabs">
            <div class="right-barbox">
                <a class="link btn btn-default" 
                   href="{{ ViewTest::getHelpLink() }}" target="_blank">
                    {{ trans('test::test.view_help') }}
                </a>
            </div>

            <ul class="nav nav-tabs">
                <li class="active"><a href="#general_tab" data-toggle="tab" aria-expanded="true">{{ trans('test::test.general_infor') }}</a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="general_tab">
                    @include('test::manage.includes.general-tab', ['item' => $testLangClone])
                </div>

                <br />
            </div>
            <!-- /.tab-content -->

            <div id="sorted_question" class="hidden"></div>
            <input type="hidden" name="has_upload" id="has_upload" value="0">
        </div>

    </div>
    <div class="col-lg-2 col-md-3 col-sm-4">
        @include('test::manage.includes.language-edit')

        <div class="box box-rikkei">
            <div class="box-header with-border">
                <h3 class="box-title">{{ trans('test::test.Action') }}</h3>
            </div>
            <div class="box-body">
                <div class="text-center">
                    <a href="{{route('test::admin.test.index')}}" class="btn btn-warning margin-bottom-5">
                        <i class="fa fa-long-arrow-left"></i> {{trans('test::test.back')}}
                    </a>

                    @if ($item)
                        <button type="submit" class="btn-edit margin-bottom-5"
                                data-noti="{{ trans('test::validate.Are you sure want to update') }}">
                            <i class="fa fa-save"></i> {{trans('test::test.update')}}
                        </button>
                    @else
                        <button type="submit" class="btn-add margin-bottom-5" data-noti=""><i class="fa fa-save"></i> {{trans('test::test.add_new')}}</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
{!! Form::close() !!}

@stop

@section('confirm_class', 'modal-warning')
@section('warn_confirn_class', 'modal-danger')

@section('script')
@include('test::template.script')
<script>
    var totalQuestion = null;
    if ($('#created_by').length > 0) {
        setTimeout(function () {
            RKfuncion.select2.elementRemote(
                $('#created_by')
            );
        }, 300);
    }
</script>

@stop
