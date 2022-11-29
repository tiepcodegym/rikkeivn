@extends('layouts.guest')
<?php 
use Rikkei\Sales\Model\Css;
use Illuminate\Support\Facades\Config as SupportConfig;

$lang = SupportConfig::get('langs.'.$css->lang_id);
if ($lang == null) {
    $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
}

?>
@section('title')
    {{trans('sales::view.Customer service survey',[],'',$lang)}}
@endsection
@section('content')

<div class="row">
    <div class="col-md-12 welcome-body" style="display:block;" >
        <div class="logo-rikkei">
            <img src="{{ URL::asset('common/images/logo-rikkei.png') }}">
        </div>
        <div class="box-header welcome-header">
            <h2 class="welcome-title <?php if($css->project_type_id === 1 || $css->project_type_id === 5){ echo 'color-blue'; } ?>">{{ trans('sales::view.Welcome title well',[],'',$lang) }}</h2>
        </div>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <div class="<?php if(in_array($lang, ["en", "vi"])) :?> content-well font-viet <?php endif; ?>">
                        {!!trans('sales::view.CSS.Welcome.Content', [],'',$lang)!!}
                    </div>
                </div>
            </div>
            <div class="row-fluid ">
                <form method="post" id="frm_welcome" action="{{ $urlSubmit }}"  >
                    <input type="hidden" name="token" value="{{$token}}" />
                    <input type="hidden" name="id" value="{{$id}}" />
                    <div class="css-make-info">
                        <div>
                            <div class="company-name-title <?php if(in_array($lang, ["en", "vi"])) :?> width-200 <?php endif; ?>">{{ trans('sales::view.Customer company name jp',[],'',$lang)}}</div>
                            <div class="company-name inline-block <?php if(in_array($lang, ["en", "vi"])) :?> width-270 <?php endif; ?>">{{ $css->company_name}} @if($lang == "ja") 様 @endif</div>
                        </div>
                        <div>
                            <div class="project-name-title <?php if(in_array($lang, ["en", "vi"])) :?> width-200 <?php endif; ?>">{{ trans('sales::view.Project name jp well',[],'',$lang)}}</div>
                            <div class="project-name inline-block <?php if(in_array($lang, ["en", "vi"])) :?> width-270 <?php endif; ?>">@if(trim($css->project_name_css) != null) {{$css->project_name_css}}  @else {{ $css->project_name}} @endif</div>
                        </div>
                        <div>
                            <div class="customer-name-title <?php if(in_array($lang, ["en", "vi"])) :?> width-200 <?php endif; ?> margin-top-10">{{ trans('sales::view.Make name jp',[],'',$lang)}}</div>
                            <div class="inline-block <?php if(in_array($lang, ["en", "vi"])) :?> width-270 <?php endif; ?>">
                                <div class="input-group goto-make-parent">

                                    <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                                    <input type="text" class="form-control <?php if($lang == "en") :?>font-viet <?php endif; ?>" id="make_name" name="make_name" value="{{$makeName}}" maxlength="100" />
                                    <span class="input-group-btn">
                                        <button type="submit" class="btn btn-default btn-to-make <?php if($css->project_type_id === 1 || $css->project_type_id === 5){ echo 'bg-color-blue'; } ?>" name="submit"><img src="{{ URL::asset('sales/images/splash.png') }}" /></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        @if($status)
                        <div>
                            <div class="project-name-title margin-top-10" style="cursor: pointer;"><a id="history" data-id = "{{$code}}">{{ trans('sales::view.Review history',[],'',$lang)}}</a></div>
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-warning" id="modal-confirm-name">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span></button>
                <h4 class="modal-title">{{ trans('sales::view.Warning',[],'',$lang) }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ trans('sales::message.Name validate required',[],'',$lang) }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline pull-right" data-dismiss="modal">{{ trans('sales::view.Close jp',[],'',$lang) }}</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- popup history -->
<div class="modal bootstrap-dialog type-primary" id="modal-history">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style=" background-color: <?php if($css->project_type_id === 1 || $css->project_type_id === 5) echo '#2b98d4'; else echo '#43a047'; ?>">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@if($lang == "ja") {{$css->company_name}} @endif {{ trans('sales::view.History Css',[],'',$lang)}}</h4>
            </div>
            <div class="modal-body" style="color: #58595b" id="appden_html">
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

<!-- Styles -->
@section('css')
<link href="{{ asset('sales/css/css_customer.css') }}" rel="stylesheet" type="text/css" >
@endsection

<!-- Script -->
@section('script')
<script>
    idCss = {{$css->id}};
    url = "{{route('sales::historyAjax')}}";
</script>
<script src="{{ asset('lib/js/jquery.visible.js') }}"></script>
<script src="{{ asset('sales/js/css/customer.js') }}"></script>
<script src="{{ asset('sales/js/css/welcome.js') }}"></script>
<script>
    <?php if($nameRequired === 1): ?>
        $('#modal-confirm-name').modal('show');
    <?php elseif($nameRequired === -1): ?>
        $('#modal-confirm-name .modal-body').html('{{trans("sales::message.Check max length name")}}');
        $('#modal-confirm-name').modal('show');
        $('#make_name').val('{{$makeName}}');
    <?php endif; ?>
</script>
@endsection
