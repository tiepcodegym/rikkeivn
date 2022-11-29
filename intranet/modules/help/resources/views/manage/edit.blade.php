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
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_help/css/help.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_help/css/style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_help/css/seed.css') }}" />
@endsection

@section('content')
<?php
use Illuminate\Support\Facades\URL;
?>
<div id="overlay"></div>
<div class="row">
    @include('help::included.menu')
    <div class="col-sm-9" id="contentR">
        <div class="box box-info">
            <div class="box-body">             
                <form id="form-post-edit" class="form-submit-ajax has-valid" autocomplete="off">    
                    {!! csrf_field() !!}   
                    <div id="contentR-title" class="clearfix">           
                        <div class="button-manage" style="float: left">                          
                            <input type="hidden" name="id" id="help-id" value="{{ $helpItem->id }}"/>                       
                            <button type="submit" id="save" class="btn btn-success btn-md btn-submit-ckeditor help-btn"> 
                                <i class="fa fa-floppy-o fa-2" aria-hidden="true"></i> &nbsp;&nbsp;{{ trans('help::view.Save') }}
                            </button>
                            &nbsp;&nbsp;
                            <input type="hidden" id="action" name="action" value=""/>                                
                            <button type="button" id="delete" class="btn btn-delete btn-md help-btn" onclick="deleteHelp()" style="visibility: hidden">                             
                                <i class="fa fa-trash-o fa-2" aria-hidden="true"></i> &nbsp;&nbsp;{{ trans('help::view.Delete') }}
                            </button>
                            &nbsp;&nbsp;
                            <button type="button" id="cancel" class="btn btn-primary btn-md help-btn" onclick="window.location='{{ URL::route('help::display.help.view') }}'">
                                <i class="fa fa-close" aria-hidden="true"></i> &nbsp;&nbsp;{{ trans('help::view.Cancel') }}
                            </button>
                        </div>                   
                    </div>                      
                    <div id="contentR-content">                          
                        <div class="row">
                            <div class="col-sm-12 form-group row-input">                    
                                <label for="title" class="col-sm-3 control-label required">
                                    {!! trans('help::view.Title *') !!}
                                </label>
                                <div class="col-sm-5" >
                                    <input name="help[title]" class="form-control select2-input select2-default" id="help-title" type="text"
                                           value="{{ $helpItem->title }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group row-input">                    
                                <label for="parent" class="col-sm-3 control-label">{{ trans('help::view.Parent') }}</label>
                                <div class="col-sm-5">
                                    <select name="help[parent]" id="help-parent" class="select2-selection form-control">  
                                        <?php
                                        $str = '';
                                        $level = 0;
                                        ?>
                                        <option value="#">&nbsp;</option>                              
                                        @if (count($helpOption) > 0)
                                            @foreach ($helpOption as $option)                                                 
                                                @include('help::included.parent_combobox', $option)                                                                                                                                         
                                            @endforeach                                                
                                        @endif                                            
                                    </select>
                                </div>                         
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group row-input">                    
                                <label for="slug" class="col-sm-3 control-label">{{ trans('help::view.Slug') }}</label>
                                <div class="col-sm-5">
                                    <input name="help[slug]" class="form-control" id="help-slug" type="text"
                                        value="{{ $helpItem->slug}}"/>
                                </div>                           
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group row-input">                    
                                <label for="active" class="col-sm-3 control-label required">{!! trans('help::view.Active *') !!}</label>
                                <div class="col-sm-3">
                                    <select name="help[active]" id="help-active" class="select2-selection form-control">                                      
                                        @foreach ($optionStatus as $key => $value)
                                        <option value="{{ $key }}"{{ $helpItem->active == $key ? ' selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>                       
                            </div>
                        </div>                        
                        <div class="row">
                            <div class="col-sm-12 form-group row-input">                    
                                <label for="order" class="col-sm-3 control-label">
                                    {{ trans('help::view.Order') }}
                                </label>
                                <div class="col-sm-3" id="help-order-input">
                                    <input name="help[order]" class="form-control input-field" type="number" id="help-order" 
                                           data-toggle="tooltip" data-placement="right" title="Refresh trang để menu và option hiển thị đúng thứ tự"  
                                           value="{{ $helpItem->order }}" />
                                </div>                          
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 form-group row-input">  
                                <label for="content" class="control-label col-sm-3">
                                    {{ trans('help::view.Description') }}
                                    <button class="btn btn-primary btn-sm" data-ckeditor-attach="help-content">
                                        <i class="fa fa-paperclip" aria-hidden="true"></i>
                                    </button>
                                </label>
                            </div>
                        </div> 
                        <div class="row">
                            <div class="col-sm-12 form-group row-input">                                 
                                <div class="col-sm-12">
                                    <textarea name="help[content]" class="ckedittor-text" id="help-content">{{ htmlspecialchars($helpItem->content) }}</textarea>
                                </div>                                        
                            </div>
                        </div>  
                    </div>  
                </form>                        
            </div>
        </div>
    </div>
</div>

<!-- modal delete cofirm -->
<div class="modal fade modal-danger" id="help-confirm-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">{{ trans('help::view.Confirm') }}</h4>
            </div>
            <div class="modal-body">
                <p class="text-default"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-close" data-dismiss="modal">{{ trans('help::view.Close') }}</button>
                <button type="button" class="btn btn-outline btn-ok">{{ trans('help::view.Yes') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div> <!-- modal delete cofirm -->
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.min.js"></script>
<script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script src="{{ URL::asset('lib/ckfinder/ckfinder.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/jstree.min.js"></script>
<script src="{{ URL::asset('asset_help/js/help.js') }}"></script>
<script type="text/javascript">   
    var getHelp = "{{ route('help::display.help.edit') }}";
    var searchHelp = "{{ route('help::display.help.search') }}";
    var saveHelpRoute = "{{ route('help::manage.help.save') }}";
    var deleteHelpRoute = "{{ route('help::manage.help.delete') }}";
    var _token = "{{ csrf_token() }}";
    var pageType = '{{$pageType}}';
    var typeArr = ['create', 'edit', 'view'];
    var searchErr = ["{{ trans('help::view.No input') }}", "{{ trans('help::view.No search keyword') }}"];
    var validateErr = ["{{ trans('help::view.Required input') }}",
                       "{{ trans('help::view.Input limit', ['limit' => '255']) }}",
                       "{{ trans('help::view.Positive number') }}"];
    var menu = {!!$menu!!};    
    var noti = "{{ $noti }}";
    if (noti) {
        $('#modal-warning-notification > .modal-dialog > .modal-content > .modal-body > .text-default').text(noti);
        $('#modal-warning-notification').modal('show');
        setTimeout(function() { 
            window.location.href = "{{ route('help::display.help.view') }}";
        }, 1500);
    }
    waiting();
    jQuery(document).ready(function ($) {
        //init jstree       
        $('#container').initMenu();
        
        //enable tooltip
        $('#help-order[data-toggle="tooltip"]').tooltip(); 
        
        //customize help parent option
        $("#help-parent").select2({
            dropdownCssClass : 'bigdrop',
        });         
        
        //init manage button
        initManageButton();
        
        //init ckeditor
        var ckEditorReturn = RKfuncion.CKEditor.init([
            'help-content'
        ], true, {attach: true});
        CKEDITOR.config.height = 350;
        CKEDITOR.config.entities_latin = false;
        //listen to jstree menu item action
        $('#container').menuItemClickListener();
        
        $('.display-search').pressEnterToSearch();

        $('.btn-search').clickToSearch();
        
        if (pageType == typeArr[1]){
            setParentHelp('{{ $helpItem->id }}');         
        }
        
        //validate form
        validateForm(); 
        
        finish();
    });
</script>
@endsection
