@extends('layouts.default')

@section('title')
{{ $titleHeadPage }}
@endsection

@section('css')
<?php
use Rikkei\Core\View\CoreUrl;
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/themes/default/style.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_help/css/style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_help/css/help.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_help/css/seed.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('resource/css/resource.css') }}" />
@endsection

@section('content')
<?php
use Illuminate\Support\Facades\URL;
use Rikkei\Team\View\Permission;

$notification = false;
if ($noti && !$helpItem->id) {
    $notification = $noti;
}
?>
<div class="row view-wraper">           
    @include('help::included.menu')                               

    <div class="col-md-10" id="contentR"> 
        <div class="box box-info">
            <div class="box-body"> 
                {!! csrf_field() !!}                        
                <div id="contentR-title" class="clearfix">    
                    <div id="help-title" class="col-md-9 title-label">
                        {{ $helpItem->title }}
                    </div>                    
                    @if(Permission::getInstance()->isAllow('help::manage.help.edit'))
                    <div class="button-manage" style="visibility: hidden">                
                        <button type="button" id="edit" class="btn btn-primary btn-md help-btn" onclick="edit()">
                            <i class="fa fa-pencil-square-o fa-2" aria-hidden="true"></i> &nbsp;&nbsp;{{ trans('help::view.Edit post help') }}
                        </button>           
                    </div>  
                    @endif                  
                </div>       
                
                <div id="contentR-content">
                    <div id="help-content">
                        {!! $helpItem->content !!}
                    </div>            
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-view-file" id="modal-view-file" tabindex="-1" role="dialog"  data-keyboard="false" >
        <div class="modal-dialog">
            <div class="modal-content"  >
                <div class="modal-body">
                    <div class="disabled-view-full"></div>
                    <iframe src="" frameborder="0"></iframe>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/jstree.min.js"></script>
<script src="{{ URL::asset('asset_help/js/help.js') }}"></script>
<script type="text/javascript"> 
    var getHelpContent = "{{ route('help::display.help.show') }}";
    var searchHelp = "{{ route('help::display.help.search') }}";
    var _token = "{{ csrf_token() }}";
    var pageType = '{{$pageType}}';
    var typeArr = ['create', 'edit', 'view'];
    var searchErr = ["{{ trans('help::view.No input') }}", "{{ trans('help::view.No search keyword') }}"];
    var menu = {!!$menu!!};
    var helpModId = {{$helpItem->id ? $helpItem->id : '0'}};
    var notification = "{{ $notification }}";
    if (notification) {
        $('#modal-warning-notification > .modal-dialog > .modal-content > .modal-body > .text-default').text(notification);
        $('#modal-warning-notification').modal('show');
        setTimeout(function() { 
            window.location.href = "{{ route('help::display.help.view') }}";
        }, 1500);
    }
    waiting();
    jQuery(document).ready(function ($) { 
        //init jstree       
        $('#container').initMenu();     
       
        //listen to jstree menu item action
        $('#container').menuItemViewClickListener();
        
        $('.display-search').pressEnterToSearch();

        $('.btn-search').clickToSearch();        
        
        $('.button-manage > #edit').showEditButton();
        
        enableFixedMenu();
        finish();
    });
</script>
@endsection

