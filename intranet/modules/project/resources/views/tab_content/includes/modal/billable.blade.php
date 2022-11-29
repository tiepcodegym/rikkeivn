<div class="modal fade" id="modal-billable-detail" style="display: none;">
    <div class="modal-dialog modal-lg" style="width: 60%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span></span></button>
                <h4 class="modal-title">{{ trans('project::view.Billable Effort') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="box box-info">
                                <div class="box-body scrolls">
                                    <div class="row">
                                        <div class="col-sm-2">&ensp;</div>
                                        <div class="col-sm-8 group-error hidden">
                                            <label class="error-input-mess labl-error error" ></label>
                                        </div>
                                        <div class="col-sm-2">&ensp;</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-2">&ensp;</div>
                                        <div class="col-sm-8">
                                            <span>{{ trans('project::view.Total Billable cost') }} : <span class="js-total-billable">0</span> MM</span>
                                            <br>
                                            <span class="hidden error" id="jsBillableCostMsg">  ({{ trans('project::view.Total Billable cost not matched') }})</span>
                                        </div>
                                        <div class="col-sm-2">&ensp;</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">&ensp;</div>
                                        <div class="col-md-8 billable-wrapper" >
                                            <table id="js-table-billable" class="table dataTable table-bordered  table-grid-data">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 50px;" class="col-month required">{{ trans('project::me.Month') }}<em>*</em></th>
                                                        <th style="width: 50px" class="col-cost required">{{ trans('project::view.Billable Effort') }}<em>*</em></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if( isset($billableCosts))
                                                        @if(count($billableCosts) > 0)
                                                            @foreach( $billableCosts as $billableCost)
                                                                <tr>
                                                                    <td><span class="apc-month label-month">{{\Carbon\Carbon::parse($billableCost->month)->format('Y-m')}}</span></td>
                                                                    <input type="hidden" name="billable[][month]" class="input-month" value="{{\Carbon\Carbon::parse($billableCost->month)->format('Y-m')}}">
                                                                    <td class="approved-cost-wrapper"><input type="number" min="0" step=".01" name="billable[][price]" class="form-control billable-cost-detail input-price" value="{{$billableCost->price}}"></td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-sm-2">&ensp;</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="align-center">
                        @if( isset($billableCosts))
                        <button type="submit" class="btn-add js-btn-billable-submit btn-common-submit">
                            {{trans('project::view.Save')}}
                            <i class="fa fa-spin hidden "></i></button>
                        @else
                            <button type="submit" class="btn-add js-btn-billable-save btn-common-submit">
                                {{trans('project::view.Save')}}
                                <i class="fa fa-spin hidden "></i></button>
                        @endif
                        <button type="button" id="billable-close-modal" class="btn btn-close margin-left-10" data-dismiss="modal" >{{trans('project::view.Close')}}</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->

    <div class="js-clone" style="display: none">
       <table>
           <tbody>
               <tr>
                   <input type="hidden" name="billable[][month]" class="input-month">
                   <td><span class="apc-month label-month"></span></td>
                   <td class="approved-cost-wrapper"><input type="number" min="0" step=".01" name="billable[][price]" class="form-control billable-cost-detail input-price"></td>
               </tr>
           </tbody>
       </table>
    </div>
</div>

