@extends('layouts.default')

@section('title')

@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css"/>
    <link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('lib/css/jquery.datetimepicker.css')}}"/>
@endsection

@section('content')
    <?php
    use Rikkei\Team\View\Config as TeamConfig;
    use Rikkei\Core\View\View as CoreView;
    use Rikkei\Core\View\Form as CoreForm;
    ?>
    <div class="container">
        <form action="{{ URL::route('welfare::welfare.group.save') }}" method="post" id="form-evnet-info"
              enctype="multipart/form-data" autocomplete="off" novalidate="novalidate">
            <input type="submit" class="btn-add" name="submit" value="Save">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="event[id]" @if(isset($item)) value="{{$item['id']}}" @endif>
            <div class="box box-info">
                <div class="box-body">
                    <div class="form-horizontal form-label-left">
                        <div class="form-group">
                            <label class="col-md-2 control-label required"
                                   aria-required="true">{{ trans('welfare::view.Group_name') }}<em>*</em></label>
                            <div class="input-box col-md-10">
                                <input type="text" name="group[name]" class="form-control"
                                       placeholder="{{trans('welfare::view.Group_name')}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.full.min.js"></script>
    <script src="{{ URL::asset('lib/js/jquery.datetimepicker.full.min.js') }}"></script>
    <script src="{{ URL::asset('lib/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
@endsection

