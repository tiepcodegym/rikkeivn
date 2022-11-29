<?php
use Rikkei\Project\Model\Project;
?>
<div class="modal fade" id="taskModal" style="display: none;">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>x</span></button>
                <h4 class="modal-title" style="float: left;">{{ trans('project::view.Operation project') }}</h4>
                @if($hasPermissionViewCostPriceDetail)
                <div class="col-sm-2" style="float:right;">
                    <a type="button" id="button-purchase-order" class="btn btn-primary">{{ trans('project::view.Price list for reference') }}</a>
                    <a type="button" href="{{ route('project::project.export.productionCostExport',$projectId) }}" class="btn btn-success"><i class="fa fa-download"></i>{{ trans('project::view.Export') }}</a>
                </div>
                @endif
                <div style="clear: both"></div>
                <div class="row submit-successful">
                    <div class="col-sm-12">
                        <div>Cập nhật thành công</div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="box box-info">
                                <div class="box-body scrolls">
                                    <div class="row">
                                        <div class="col-sm-12 group-error hidden">
                                            <label class="error-input-mess labl-error error"></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <span>{{ trans('project::view.Total approved production cost') }} : <span class="total-app-pro-cost">0</span> MM</span>
                                            <br>
                                            <span class="hidden error" id="jsApproveMappingMsg">  {{ trans('project::view.Total approved production cost not matched') }}</span>
                                            <span class="error" id="jsErrorCostApprovedAroduction"></span>
                                            <span class="error" id="jsApproveCost"></span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        {{-- <div class="col-md-2">&ensp;</div> --}}
                                        <div class="col-md-12" style="overflow-x: scroll;">
                                            <table id="tblOperationBody" class="table dataTable table-bordered  table-grid-data">
                                                <thead>
                                                    <tr>
                                                        <th style="min-width: 100px;" class="col-month required">{{ trans('project::me.Month') }}<em>*</em></th>
                                                        <th style="min-width: 50px" class="col-cost required">{{ trans('project::view.Approved production cost') }}<em>*</em></th>
                                                        <th style="min-width: 150px;" class="col-team required">{{trans('project::view.Group')}}<em>*</em></th>
                                                        <th style="min-width: 150px;" class="col-team required">Role</th>
                                                        <th style="min-width: 150px;" class="col-team required">Level</th>
                                                        <th style="min-width: 150px;" class="col-team">{{trans('project::view.Note')}}</th>
                                                        @if ($hasPermissionViewCostPriceDetail)
                                                        <th style="min-width: 150px;" class="col-price required">{{trans('project::view.Approved production price')}}<em>*</em></th>
                                                        <th style="min-width: 100px;" class="col-price required">{{trans('project::view.Approved production unit price')}}<em>*</em></th>
                                                        <th style="min-width: 10px;"><input type="checkbox" id="js-is-approve-all" name="is_approve_all" value="1"></th>
                                                        @endif
                                                        <th style="min-width: 10px;">&ensp;</th>
                                                        <th style="min-width: 10px;">&ensp;</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="js-tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="align-center">
                        @if ($hasPerApproveProductionCost)
                        <button type="submit" class="btn-add btn-submit btn-approve js-btn-approve" data-submit="is_coo">Duyệt</button>
                        @endif
                        <button type="submit" class="btn-add btn-submit" data-submit="is_pm">
                            {{trans('project::view.Save')}}
                            <i class="fa fa-spin hidden "></i></button>
                        <button type="button" id="close-modal" class="btn btn-close margin-left-10" data-dismiss="modal" >{{trans('project::view.Close')}}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@if ($hasPermissionViewCostPriceDetail)
    @include('project::tab_content.includes.modal.modal-purchase-order')
@endif
