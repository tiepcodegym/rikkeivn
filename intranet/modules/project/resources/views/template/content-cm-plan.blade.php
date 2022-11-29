<div class="row margin-bottom-20 div-cm-plan-{{$cm->id}}">
    <div class="col-md-10 padding-25-lr">
        <div class="cm-plan-{{$cm->id}}">
            <span class="content-cm-plan-{{$cm->id}} pev">{!!($cm->content)!!}</span></span>
            <textarea class="flex-text display-none input-cm-plan-{{$cm->id}} input-cm-plan form-control col-md-6">{!! $cm->content !!}</textarea>
        </div>
    </div>
    <div class="col-md-2">
        <span>
            <i class="width-40 fa fa-floppy-o display-none btn-add save-cm-plan save-cm-plan-{{$cm->id}}" data-id="{{$cm->id}}"></i>
            <i class="fa fa-pencil-square-o btn-edit edit-cm-plan edit-cm-plan-{{$cm->id}}" data-id="{{$cm->id}}"></i>
            <i class="fa fa-trash-o btn-delete delete-cm-plan delete-confirm-new delete-cm-plan-{{$cm->id}}" data-id="{{$cm->id}}"></i>
            <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-cm-plan refresh-cm-plan-{{$cm->id}}" data-id="{{$cm->id}}" data-status="{{$cm->status}}"></i>
        </span>
    </div>
</div> 