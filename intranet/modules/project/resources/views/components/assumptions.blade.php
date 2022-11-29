<?php
use Rikkei\Project\Model\Task;
use Illuminate\Support\Str;
?>
<div id="workorder_assumptions">
@if(isset($detail))
<label class="control-label column-width-12-5-per">{{trans('project::view.Assumptions')}}</label>
<div class="table-responsive table-content-assumptions" id="table-assumptions">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
        <tr>
            <th class="width-5-per">{{trans('project::view.No')}}</th>
            <th class="width-15-per">{{trans('project::view.Description')}}</th>
            <th class="width-15-per">{{trans('project::view.Remark')}}
                <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{!! trans('project::view.textAssumptions') !!}"></i>
            </th>
            @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                <th class="width-5-per">&nbsp;</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @if(isset($allAssumptions))
            @foreach($allAssumptions as $key => $assumption)
                <tr class="tr-assumptions-{{$assumption->id}} tr-assumptions-css">
                    <td>{{$key + 1}}</td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumptions" data-id="{{$assumption->id}}" name="description_assumptions" class="popover-wo-other description-assumptions-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->description)), 30, '...')!!}</span>
                        <textarea class="display-none form-control input-description-assumptions-{{$assumption->id}} white-space" name="description_assumptions" rows="2">{!! $assumption->description !!}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumptions" data-id="{{$assumption->id}}" name="remark_assumptions" class="popover-wo-other remark-assumptions-{{$assumption->id}} white-space"></span>
                        <textarea class="display-none form-control input-remark-assumptions-{{$assumption->id}} white-space" name="remark_assumptions" rows="2">{!! $assumption->remark !!}</textarea>
                    </td>
                    @if(isset($permissionEdit) && $permissionEdit)
                        <td>
                            <span>
                                <i class="fa fa-floppy-o display-none btn-add save-assumptions save-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                                <i class="fa fa-pencil-square-o width-38 btn-edit edit-assumptions edit-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                                <span class="btn btn-primary display-none loading-item" id="loading-item-assumptions-{{$assumption->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-trash-o btn-delete delete-confirm-new delete-assumptions delete-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                                <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-assumptions refresh-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                            </span>
                        </td>
                    @endif
                </tr>
            @endforeach
        @endif
        @if(isset($permissionEdit) && $permissionEdit)
            <tr class="tr-add-assumptions">
                <td colspan="8" class="slove-assumptions">
                    <span href="#" class="btn-add add-assumptions"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-assumptions tr-assumptions-hidden tr-assumptions-css">
                <td></td>
                <td>
                    <span>
                        <textarea class="form-control width-100 description-assumptions" name="description_assumptions" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <textarea class="form-control width-100 remark-assumptions" name="remark_assumptions" rows="2"></textarea>
                    </span>
                </td>
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-items" id="loading-item-assumptions"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-assumptions"></i>
                        <i class="fa fa-trash-o btn-delete remove-assumptions"></i>
                    </span>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
@else
<label class="control-label column-width-12-5-per">{{trans('project::view.Assumptions')}}</label>
<div class="table-responsive table-content-assumptions" id="table-assumptions">
    <table class="edit-table table table-bordered table-condensed dataTable">
        <thead>
        <tr>
            <th class="width-5-per">{{trans('project::view.No')}}</th>
            <th class="width-15-per">{{trans('project::view.Description')}}</th>
            <th class="width-15-per">{{trans('project::view.Remark')}}
                <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{!! trans('project::view.textAssumptions') !!}"></i>
            </th>
            @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                <th class="width-5-per">&nbsp;</th>
            @endif
        </tr>
        </thead>
        <tbody>
        @if(isset($getAssumptions))
            @foreach($getAssumptions as $key => $assumption)
                <tr class="tr-assumptions-{{$assumption->id}} tr-assumptions-css">
                    <td>{{$key + 1}}</td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumptions" data-id="{{$assumption->id}}" name="description_assumptions" class="popover-wo-other description-assumptions-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->description)), 30, '...')!!}</span>
                        <textarea class="display-none form-control input-description-assumptions-{{$assumption->id}} white-space" name="description_assumptions" rows="2">{!! $assumption->description !!}</textarea>
                    </td>
                    <td>
                        <span data-toggle="popover" data-html='true' data-type="assumptions" data-id="{{$assumption->id}}" name="remark_assumptions" class="popover-wo-other remark-assumptions-{{$assumption->id}} white-space">{!!Str::words(nl2br(e($assumption->remark)), 30, '...')!!}</span>
                        <textarea class="display-none form-control input-remark-assumptions-{{$assumption->id}} white-space" name="remark_assumptions" rows="2">{!! $assumption->remark !!}</textarea>
                    </td>
                    @if(isset($permissionEdit) && $permissionEdit)
                        <td>
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-assumptions save-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-assumptions edit-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-assumptions-{{$assumption->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-trash-o btn-delete delete-confirm-new delete-assumptions delete-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                            <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-assumptions refresh-assumptions-{{$assumption->id}}" data-id="{{$assumption->id}}"></i>
                        </span>
                        </td>
                    @endif
                </tr>
            @endforeach
        @endif
        @if(isset($permissionEdit) && $permissionEdit)
            <tr class="tr-add-assumptions">
                <td colspan="8" class="slove-assumptions">
                    <span href="#" class="btn-add add-assumptions"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            <tr class="display-none tr-assumptions tr-assumptions-hidden tr-assumptions-css">
                <td></td>
                <td>
                <span>
                    <textarea class="form-control width-100 description-assumptions" name="description_assumptions" rows="2"></textarea>
                </span>
                </td>
                <td>
                <span>
                    <textarea class="form-control width-100 remark-assumptions" name="remark_assumptions" rows="2"></textarea>
                </span>
                </td>
                <td>
                <span>
                    <span class="btn btn-primary display-none loading-items" id="loading-item-assumptions"><i class="fa fa-refresh fa-spin"></i></span>
                    <i class="fa fa-floppy-o btn-add add-new-assumptions"></i>
                    <i class="fa fa-trash-o btn-delete remove-assumptions"></i>
                </span>
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
@endif
</div>
