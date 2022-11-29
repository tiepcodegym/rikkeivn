<?php 
use Rikkei\Team\View\Permission;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;

if (Permission::getInstance()->isAllow('resource::request.addChannelRequest')) {
    $permissionEditChannelRequest = true;
} else {
    $permissionEditChannelRequest = false;
}
if ($permissionEditChannelRequest 
    && $request->status == getOptions::STATUS_INPROGRESS
    && $request->approve == getOptions::APPROVE_ON) {
    $edit = true;
} else {
    $edit = false;
}
?>
<div class="fetch-content workorder-channel" id="workorder-content-99">
<div class="table-responsive table-content-1" id="table-channel">
    <table class="edit-table table table-hover table-bordered table-condensed dataTable">
        <thead>
            <tr>
                <th class="col-md-1">{{ trans('resource::view.Request.Detail.No') }}</th>
                <th class="col-md-3">{{ trans('resource::view.Request.Detail.Channel name') }}</th>
                <th class="col-md-5">{{ trans('resource::view.Request.Detail.Channel url of request') }}</th>
                <th class="col-md-2">{{ trans('resource::view.Request.Detail.Cost') }}</th>
                @if ($edit)
                <th class="col-md-1">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php $totalCost = 0; ?>
            @foreach($channelsOfRequest as $key => $channel)
            <?php $totalCost += (int)str_replace(',', '', $channel->cost); ?>
            <tr class="tr-channel-{{$channel->id}} tr-channel-css">
                <td>{{$key + 1}}</td>
                <td>
                    <input type="hidden" class="input-rc_id-channel-{{$channel->id}}" value="{{$channel->rc_id}}" >
                    <input type="hidden" class="input-channel_id-channel-{{$channel->id}}" value="{{$channel->id}}" >
                    <span class="channel_id-channel-{{$channel->id}} white-space">{{$channel->name}}</span>
                    <select name="channel_id" class="display-none form-control width-200 select-channel_id-channel-{{$channel->id}} channel-member-select2-new" >
                    @foreach($channels as $item)
                        <option value="{{$item->id}}" class="form-control width-200"
                            @if ($item->id == $channel->id)  
                                selected
                            @endif
                        >{{$item->name}}</option>
                    @endforeach                        
                    </select>
                </td>
                <td>
                    <span class="url-channel-{{$channel->id}} white-space">{{$channel->url}}</span>
                
                    <input type="text" class="display-none width-300 form-control input-url-channel-{{$channel->id}} white-space" name="url" value="{{$channel->url}}" rows="2">
                </td>
                
                <td>
                    <span class="cost-channel-{{$channel->id}}" data-value="{{$channel->cost}}">{{View::getInstance()->priceFormat($channel->cost)}}</span>
                    <input type="text" class="display-none width-100 num form-control width-100 input-cost-channel-{{$channel->id}}" name="cost" value="{{View::getInstance()->priceFormat($channel->cost)}}" >
                </td>
                @if ($edit)
                <td>
                    <span>
                        <i class="fa fa-floppy-o display-none btn-add save-channel save-channel-{{$channel->id}}" data-id="{{$channel->id}}"></i>
                        <i class="fa fa-pencil-square-o width-38 btn-edit edit-channel edit-channel-{{$channel->id}}" data-id="{{$channel->id}}"></i>
                        <span class="btn btn-primary display-none loading-item" id="loading-item-{{$channel->id}}"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-trash-o btn-delete delete-channel delete-confirm-new delete-channel-{{$channel->id}}" data-id="{{$channel->id}}"></i>
                        <i class="display-none fa fa-arrow-left btn-refresh btn-primary refresh-channel refresh-channel-{{$channel->id}}" data-id="{{$channel->id}}" data-status="{{$channel->status}}"></i>
                    </span>
                </td>
                @endif
            </tr>
            @endforeach
            @if ($edit)
            <tr class="display-none tr-channel tr-channel-hidden tr-channel-css">
                <td></td>
                
                <td class="td-channel-member">
                    <select name="channel_id" class="form-control width-200 select-channel_id-channel channel-member-select2-new" >
                        <option value="">{{trans('resource::view.Candiadte.Create.Select channel')}}</option>
                        @foreach($channels as $item)
                        <option value="{{$item->id}}" class="form-control width-100">{{$item->name}}</option>
                        @endforeach                        
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control width-300 input-url-channel" name="url">
                </td>
                <td>
                    <input type="text" class="form-control num width-100 input-cost-channel" name="cost" >
                </td>
                
                <td>
                    <span>
                        <span class="btn btn-primary display-none loading-item" id="loading-item"><i class="fa fa-refresh fa-spin"></i></span>
                        <i class="fa fa-floppy-o btn-add add-new-channel"></i>
                        <i class="fa fa-trash-o btn-delete remove-channel"></i>
                    </span>
                </td>
                
            </tr>
            @endif
            <tr>
                <td colspan="2"></td>
                <td class="text-align-right">{{trans('resource::view.Request.Detail.Total cost')}}</td>
                <td ><span class="value new label label-warning">{{ number_format($totalCost, 0, ',', '.') }}</span></td>
                @if ($permissionEditChannelRequest 
                    || $request->status != getOptions::STATUS_INPROGRESS
                    || $request->approve != getOptions::APPROVE_ON)
                <td></td>
                @endif
            </tr>
            @if ($edit)
            <tr class="tr-add-channel">
                <td colspan="8" >
                  <span href="#" class="btn-add add-channel"><i class="fa fa-plus"></i></span>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
</div>