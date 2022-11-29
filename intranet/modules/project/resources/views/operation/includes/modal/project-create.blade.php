<div class="modal fade" id="taskModal" style="display: none;">
    <div class="modal-dialog modal-lg" style="width: 60%;">
        <div class="modal-content">
            <form id="form-create-operation" method="post" action="{{route('project::operation.create')}}" class="form-horizontal has-valid" autocomplete="off">
                {!! csrf_field() !!}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ trans('project::view.Operation project') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-sm-12">
                                <p class="error operation-error">{!!trans('project::view.note operation future')!!}</p>
                                <div class="box box-info">
                                    <div class="box-body scrolls">
                                        <div class="row" style="margin: 0px !important;">
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label class="col-sm-4 control-label required">{{trans('project::view.Project Name')}} <em>*</em></label>
                                                <div class="col-sm-8">
                                                    <?php
                                                    $oldName = old('name');
                                                    if (isset($oldName)) {
                                                        $oldName = true;
                                                    } else {
                                                        $oldName = false;
                                                    }
                                                    ?>
                                                    <input type="text" class="form-control" id="name" name="name" placeholder="{{trans('project::view.Project name')}}" value="{{$oldName ? old('name') : ''}}">
                                                    <label class="name-error labl-error error" for="name"></label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label class="col-sm-4 control-label required">{{trans('project::view.Project kind')}} <em>*</em></label>
                                                <div class="col-sm-8">
                                                    <select name="type" class="form-control kind_id" id="kind_id">
                                                        @foreach($labelKindProject as $key => $value)
                                                            <option value="{{$key}}" {{old('kind_id') == $key ? 'selected' : '' }}>{{$value}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 col-md-4">
                                                <label class="col-sm-4 control-label required">{{trans('project::view.Project Type')}} <em>*</em></label>
                                                <div class="col-sm-8">
                                                    <select name="type" class="form-control type" id="type">
                                                    @foreach($labelTypeProject as $key => $value)
                                                            <option value="{{$key}}" {{old('state') == $key ? 'selected' : '' }}>{{$value}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" style="margin: 0px !important;">
                                            <div class="col-sm-2">&ensp;</div>
                                            <div class="col-sm-8 group-error">
                                                <label class="error-input-mess labl-error error" ></label>
                                            </div>
                                            <div class="col-md-2">&ensp;</div>
                                        </div>
                                        <div class="row" style="margin: 0px !important;">
                                            <div class="col-sm-12">
                                                <table id="tblOperationBody" class="table  dataTable table-bordered  table-grid-data">
                                                    <thead>
                                                        <tr>
                                                            <th style="min-width: 100px;" class="col-month required">{{ trans('project::me.Month') }}<em>*</em></th>
                                                            <th style="min-width: 50px" class="col-cost required">{{ trans('project::view.Approved production cost') }}<em>*</em></th>
                                                            <th style="min-width: 150px;" class="col-team required">{{trans('project::view.Group')}}<em>*</em></th>
                                                            <th style="min-width: 150px;" class="col-team ">{{trans('project::view.Note')}}</th>
                                                            <th style="min-width: 150px;" class="col-price required">{{trans('project::view.Approved production price')}}<em>*</em></th>
                                                            <th style="min-width: 100px;" class="col-price required">{{trans('project::view.Approved production unit price')}}<em>*</em></th>
                                                            <th style="min-width: 10px;">&ensp;</th>
                                                            <th style="min-width: 10px;">&ensp;</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row" style="position: relative; margin: 0px">
                                            <div class="col-sm-8" style="height: 50px;">
                                                <div class="button-add">
                                                    <span href="#" class="btn-add btn-operation-project add-operation-project"><i class="fa fa-plus"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="align-center">
                            <button type="submit" class="btn-add btn-submit">
                                {{trans('project::view.Save')}}
                                <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            <button type="button" id="close-modal" class="btn btn-close margin-left-10" data-dismiss="modal">{{trans('project::view.Close')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
