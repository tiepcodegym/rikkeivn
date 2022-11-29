<?php
use Rikkei\Project\Model\Task;
use Illuminate\Support\Str;
?>
<div id="workorder_constraints">
    @if(isset($detail))
        <label class="control-label column-width-12-5-per">{{trans('project::view.Constraints')}}</label>
        <div class="table-responsive table-content-constraints" id="table-constraints">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-15-per">{{trans('project::view.Description')}}</th>
                    <th class="width-15-per">{{trans('project::view.Remark')}}
                        <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{!! trans('project::view.textContraints') !!}"></i>
                    </th>
                    @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                        <th class="width-5-per">&nbsp;</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @if(isset($allConstraints))
                    @foreach($allConstraints as $key => $constraints)
                        <tr class="tr-constraints-{{$constraints->id}} tr-constraints-css">
                            <td>{{$key + 1}}</td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="constraints" data-id="{{$constraints->id}}" name="description_constraints" class="popover-wo-other description-constraints-{{$constraints->id}} white-space">{!!Str::words(nl2br(e($constraints->description)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-description-constraints-{{$constraints->id}} white-space" name="description_constraints" rows="2">{!! $constraints->description !!}</textarea>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="constraints" data-id="{{$constraints->id}}" name="remark_constraints" class="popover-wo-other remark-constraints-{{$constraints->id}} white-space">{!!Str::words(nl2br(e($constraints->remark)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-remark-constraints-{{$constraints->id}} white-space" name="remark_constraints" rows="2">{!! $constraints->remark !!}</textarea>
                            </td>
                            @if(isset($permissionEdit) && $permissionEdit)
                                <td>
                            <span>
                                <i class="fa fa-floppy-o display-none btn-add save-constraints save-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                                <i class="fa fa-pencil-square-o width-38 btn-edit edit-constraints edit-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                                <span class="btn btn-primary display-none loading-item" id="loading-item-constraints-{{$constraints->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-trash-o btn-delete delete-confirm-new delete-constraints delete-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                                <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-constraints refresh-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                            </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-constraints">
                        <td colspan="8" class="slove-constraints">
                            <span href="#" class="btn-add add-constraints"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-constraints tr-constraints-hidden tr-constraints-css">
                        <td></td>
                        <td>
                    <span>
                        <textarea class="form-control width-100 description-constraints" name="description_constraints" rows="2"></textarea>
                    </span>
                        </td>
                        <td>
                    <span>
                        <textarea class="form-control width-100 remark-constraints" name="remark_constraints" rows="2"></textarea>
                    </span>
                        </td>
                        <td>
                    <span>
                        <span class="btn btn-primary display-none loading-items" id="loading-item-constraints"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-constraints"></i>
                        <i class="fa fa-trash-o btn-delete remove-constraints"></i>
                    </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @else
        <label class="control-label column-width-12-5-per">{{trans('project::view.Constraints')}}</label>
        <div class="table-responsive table-content-constraints" id="table-constraints">
            <table class="edit-table table table-bordered table-condensed dataTable">
                <thead>
                <tr>
                    <th class="width-5-per">{{trans('project::view.No')}}</th>
                    <th class="width-15-per">{{trans('project::view.Description')}}</th>
                    <th class="width-15-per">{{trans('project::view.Remark')}}
                        <i class="fa fa-question-circle" data-toggle="tooltip" data-html="true" title="{!! trans('project::view.textContraints') !!}"></i>
                    </th>
                    @if(isset($permissionEdit) && $permissionEdit  && $checkEditWorkOrder)
                        <th class="width-5-per">&nbsp;</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @if(isset($getConstraints))
                    @foreach($getConstraints as $key => $constraints)
                        <tr class="tr-constraints-{{$constraints->id}} tr-constraints-css">
                            <td>{{$key + 1}}</td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="constraints" data-id="{{$constraints->id}}" name="description_constraints" class="popover-wo-other description-constraints-{{$constraints->id}} white-space">{!!Str::words(nl2br(e($constraints->description)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-description-constraints-{{$constraints->id}} white-space" name="description_constraints" rows="2">{!! $constraints->description !!}</textarea>
                            </td>
                            <td>
                                <span data-toggle="popover" data-html='true' data-type="constraints" data-id="{{$constraints->id}}" name="remark_constraints" class="popover-wo-other remark-constraints-{{$constraints->id}} white-space">{!!Str::words(nl2br(e($constraints->remark)), 30, '...')!!}</span>
                                <textarea class="display-none form-control input-remark-constraints-{{$constraints->id}} white-space" name="remark_constraints" rows="2">{!! $constraints->remark !!}</textarea>
                            </td>
                            @if(isset($permissionEdit) && $permissionEdit)
                                <td>
                        <span>
                            <i class="fa fa-floppy-o display-none btn-add save-constraints save-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                            <i class="fa fa-pencil-square-o width-38 btn-edit edit-constraints edit-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                            <span class="btn btn-primary display-none loading-item" id="loading-item-constraints-{{$constraints->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                            <i class="fa fa-trash-o btn-delete delete-confirm-new delete-constraints delete-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                            <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-constraints refresh-constraints-{{$constraints->id}}" data-id="{{$constraints->id}}"></i>
                        </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
                @if(isset($permissionEdit) && $permissionEdit)
                    <tr class="tr-add-constraints">
                        <td colspan="8" class="slove-constraints">
                            <span href="#" class="btn-add add-constraints"><i class="fa fa-plus"></i></span>
                        </td>
                    </tr>
                    <tr class="display-none tr-constraints tr-constraints-hidden tr-constraints-css">
                        <td></td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 description-constraints" name="description_constraints" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <textarea class="form-control width-100 remark-constraints" name="remark_constraints" rows="2"></textarea>
                            </span>
                        </td>
                        <td>
                            <span>
                                <span class="btn btn-primary display-none loading-items" id="loading-item-constraints"><i class="fa fa-refresh fa-spin"></i></span>
                                <i class="fa fa-floppy-o btn-add add-new-constraints"></i>
                                <i class="fa fa-trash-o btn-delete remove-constraints"></i>
                            </span>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
