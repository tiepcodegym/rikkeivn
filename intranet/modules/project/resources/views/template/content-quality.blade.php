@if(isset($permissionEdit) && $permissionEdit && $checkEditWorkOrder)
<span class="content-{{$type}}-approved">{{$valueApproved}}</span>
<span class="value new label label-warning {{!$qualityDraft ? 'display-none' : ''}} edit-draft-{{$type}}">{{$valueNotApproved}}</span>
@if ($qualityDraft)
<input type="text" class="form-control width-100 input-add-quality display-none input-{{$type}} input-{{$type}}_effort-approved-{{$qualityDraft->id}} {{$type}}" name="{{$type}}" value="{{$valueNotApproved}}" data-id="{{$qualityDraft->id}}">
@else
<input type="text" class="form-control width-100 input-add-quality display-none input-{{$type}} input-{{$type}}_effort-approved {{$type}}" name="{{$type}}" value="{{$valueApproved}}">
@endif
<span class="btn-add btn-save-{{$type}} btn-save-quality display-none" data-name="{{$type}}"><i class="fa fa-floppy-o margin-right-0"></i></span>
<span class="btn-primary btn-edit btn-edit-{{$type}} width-38"><i class="fa fa-pencil-square-o margin-right-0"></i></span>
<span class="btn btn-default btn-cancel btn-cancel-{{$type}} display-none"><i class="fa fa-ban"></i></span>
@if($qualityDraft)
<span class="btn-delete delete-{{$type}} delete-quality delete-confirm-new delete-{{$type}}-{{$qualityDraft->id}}" data-id="{{$qualityDraft->id}}""><i class="fa fa-trash-o"></i></span>
@endif
@else
<span>{{$valueApproved}}</span>
@endif