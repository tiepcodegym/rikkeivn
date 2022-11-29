<?php 
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\View;
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <!-- Bootstrap 3.3.6 -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ URL::asset('resource/css/resource.css') }}" />
    </head>
    <body class="invite-letter-body" style="margin-top: 155px;">
        <div class="header">
            <div class="row container">
                <div class="col-xs-3 logo" style="padding-top: 20px">
                    <img src="{{asset('common/images/logo-rikkei.png')}}" />
                </div>
                <div class="col-xs-9 company-info">
                    <div class="text-danger"><b>{{ trans('resource::view.RIKKEISOFT COMPANY') }}</b></div>
                    <div>{{ trans('resource::view.Address: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi') }}</div>
                    <div>{{ trans('resource::view.Tel: (+84) 243 623 1685') }} </div>
                    <div>{!! trans('resource::view.Page: https://www.facebook.com/rikkeisoft?fref=ts') !!} </div>
                    <div>{!! trans('resource::view.Website: https://tuyendung.rikkeisoft.com/') !!} </div>
                </div>
            </div>
        </div>
        <div class="body" style="margin-top: -100px">
            {!! $content !!}
        </div>
    </body>
</html>

