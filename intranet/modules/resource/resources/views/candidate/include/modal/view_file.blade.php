<?php
    use Rikkei\Team\View\Permission;
?>

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
<div class="hidden download-html">
    @if (Permission::getInstance()->isAllow('resource::candidate.downloadcv'))
    <a title="{{ trans('resource::view.Candidate.Detail.Download CV') }}" 
        href="{{route('resource::candidate.downloadcv', ['filename'=>$filename])}}">
        <span><i class="fa fa-download"></i></span>
    </a>
    @endif
</div>