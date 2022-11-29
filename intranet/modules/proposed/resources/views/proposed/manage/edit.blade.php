@extends('layouts.default')

<?php
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Proposed\Model\Proposed;

    $arrStatus = Proposed::getStatus();
    $arrLevelRecognition = Proposed::getLevelRecognition();
    $arrFeedback = Proposed::getFeedback();
?>
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-2">
            @include('proposed::nav_left')
        </div>
        <div class="col-sm-10">
            @include('proposed::message')
            <div class="box box-info">
                <div class="box-body">
                    <h3 style="text-align: center; margin-bottom: 20px">{{ trans('proposed::view.Answered proposed') }}</h3>
                    <div class="col-md-6 col-md-offset-3">
                    <form id="form" method="post" action="{{URL::route('proposed::manage-proposed.update', $proposed->id)}}" autocomplete="off">
                        {!! csrf_field() !!}
                        <input type="hidden" value="{{ $proposed->id}}">
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-sm-2">{{ trans('proposed::view.Title proposed') }}</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" value="{{ $proposed->title }}" disabled="disabled">
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-sm-2">{{ trans('proposed::view.Creator') }}</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" value="{{ $proposed->employees['name'] }}" disabled="disabled">
                                </div>
                            </div>
                        </div><br>
                        {{ trans('proposed::view.List proposed category') }}
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-sm-2 text-right">VI</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" value="{{ $proposed->categories['name_vi'] }}" disabled="disabled">
                                </div>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-sm-2 text-right">EN</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" value="{{ $proposed->categories['name_vi'] }}" disabled="disabled">
                                </div>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-sm-2 text-right">JA</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" value="{{ $proposed->categories['name_ja'] }}" disabled="disabled">
                                </div>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-sm-2">{{ trans('proposed::view.Content proposed') }}</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" name="proposed[proposed_content]" rows="6" disabled="disabled" style="resize: vertical;">{{ $proposed->proposed_content }}</textarea>
                                </div>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="form-group">
                                <label class="control-label col-sm-2 required">{{ trans('proposed::view.Answer proposed') }}
                                    <em id="proposedAnswerContentRequiredLabel"
                                        style="display: {{$proposed->feedback == \Rikkei\Proposed\Model\Proposed::RESPONDED ? 'inline-block' : 'none'}}">*</em>
                                </label>
                                <div class="col-sm-10">
                                    <textarea class="form-control"
                                              name="proposed[answer_content]"
                                              id="proposedAnswerContent"
                                              rows="6"
                                              {{$proposed->feedback == \Rikkei\Proposed\Model\Proposed::RESPONDED ? 'required' : ''}}
                                              style="resize: vertical;">{{ $proposed->answer_content }}</textarea>
                                </div>
                            </div>
                        </div><br>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label required">{{ trans('proposed::view.Status') }}</label>
                                    <div class="">
                                        <select name="proposed[status]" class="form-control" style="width: 100%">
                                        @foreach($arrStatus as $key => $status)
                                            <option value="{{ $key }}"
                                            @if ($key == $proposed->status)
                                                selected
                                            @endif
                                            >{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label required">{{ trans('proposed::view.Level of recognition') }}</label>
                                    <div class="">
                                        <select name="proposed[level]" class="form-control" style="width: 100%" @if($proposed->level != 1) disabled @endif>
                                        @foreach($arrLevelRecognition as $key => $level)
                                            <option value="{{ $key }}"
                                            @if ($key == $proposed->level)
                                                selected
                                            @endif
                                            >{{ $level }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label required">{{ trans('proposed::view.Feedback') }}</label>
                                    <div class="">
                                        <select name="proposed[feedback]" class="form-control" style="width: 100%" id="proposedFeedback">
                                        @foreach($arrFeedback as $key => $feedback)
                                            <option value="{{ $key }}"
                                            @if ($key == $proposed->feedback)
                                                selected
                                            @endif
                                            >{{ $feedback }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 text-center margin-top-20">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o"></i>
                                        {{ trans('proposed::view.Save') }}
                                    </button>
                                </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_managetime/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('proposed\js\proposed_manage.js') }}"></script>

<script>
    $(document).ready(function() {
        $('.select-search-employee').selectSearchEmployee();
    });
</script>
@endsection
