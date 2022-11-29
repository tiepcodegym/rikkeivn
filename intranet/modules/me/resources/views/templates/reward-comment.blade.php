<div class="panel panel-primary hidden" id="me_comment_modal">
    <div class="panel-heading">
        <button type="button" class="close">
            <span aria-hidden="true">×</span></button>
        <h4 class="panel-title">{{ trans('me::view.Comment') }}</h4>
    </div>
    <div class="panel-body" style="padding-bottom: 5px;">
        <div class="me_comment_form">
            <div class="me_comments_list me_new" style="margin-bottom: 5px;"></div>
            <p class="_no_comment text-center hidden">{{ trans('me::view.No comments') }}</p>
            <p class="_error error hidden"></p>
            <div class="_loading text-center hidden" style="margin-bottom: 15px;"><i class="fa fa-spin fa-refresh"></i></div>
        </div>
        <div class="text-right">
            <button type="button" class="cancel-btn btn btn-default">{{ trans('me::view.Cancel') }}</button>
        </div>
    </div>
</div>

<div class="hidden" id="rw_comment_item_tpl">
    <div class="media comment_item">
        <div class="media-left pull-left">
            <img class="_comment_avatar" src={avatar} alt="" />
        </div>
        <div class="media-body">
            <h4 class="media-heading">
                <b class="_comment_name"></b><span class="white-space-nowrap"> at <span class="date"></span></span>
                <div><span class="comment-attr text text-black"></span></div>
            </h4>
            <div class="comment_content text-blue"></div>
        </div>
    </div>
</div>

<div class="panel panel-primary hidden" id="me_allowance_onsites_modal" style="width: 380px">
    <div class="panel-heading">
        <button type="button" class="close">
            <span aria-hidden="true">×</span></button>
        <h4 class="panel-title">Số tiền thưởng và phụ cấp nhân viên onsite</h4>
    </div>
    <div class="panel-body" style="padding-bottom: 5px;">
        <div class="me_comment_form">
            <div class="me_comments_list me_new" style="margin-bottom: 5px;"></div>
            <p class="_no_comment text-center hidden">{{ trans('me::view.No comments') }}</p>
            <p class="_error error hidden"></p>
            <div class="_loading text-center hidden" style="margin-bottom: 15px;"><i class="fa fa-spin fa-refresh"></i></div>
        </div>
        <hr style="margin-bottom:5px">
        <div class="text-right close" style="opacity: 1">
            <button type="button" class="cancel-btn btn btn-default">Close</button>
        </div>
    </div>
</div>