<?php
use Rikkei\Welfare\Model\WelEmployee;
use Carbon\Carbon;

$countRow = WelEmployee::checkRelativeAttachByWelId($item['id']);
$labelConfirm = WelEmployee::getLabelConfirm();
$labelJoined = WelEmployee::getLabelJoined();

$endAtExec = isset($item->end_at_register) ?  Carbon::createFromFormat('Y-m-d H:i:s', $item->end_at_register)
            : Carbon::createFromFormat('Y-m-d H:i:s', '0000-00-00 00:00:00');
?>
<style type="text/css">
    .employ-context-menu {
        position: absolute;
        display:none;
    }

    .employ-context-menu ul {
        padding: 10px;
    }
    .employ-context-menu ul li {
        margin:5px;
        cursor: pointer;
        padding: 5px;
    }
    .employ-context-menu ul :hover {
        border: 1px solid #fafdbb;
        border-radius: 3px;
        background-color: #fafdbb;
    }
</style>
<div class="row">
    @if(date('Y-m-d H:i:s') > $item->end_at_exec)
    <div class="col-sm-12">
        <button type="button" class="btn btn btn-primary" id="check-all-employee-join">{{trans('welfare::view.All employee joined')}}</button>
    </div>
    @endif
    @if ($item && $endAtExec->gte(\Carbon\Carbon::now()))
        <div class="col-md-12">
            <button type="button" class="btn btn-primary sendMailNotify" data-url="{{ route('welfare::welfare.event.sendMailNotify', ['id' => $item->id ]) }}" data-welId="{{ $item->id }}">{{ trans('welfare::view.Remind event registration') }}</button>
        </div>
    @endif
    <div class="col-sm-12">
        <table link="{!! route('welfare::welfare.datatables.save') !!}" class="table table-bordered" id="users-table-emp" width="100%">
            <thead>
            <tr>
                <th>{{trans('welfare::view.Employee code')}}</th>
                <th>{{trans('welfare::view.Employee name')}}</th>
                <th>{{trans('welfare::view.Job Position')}}</th>
                <th>{{trans('welfare::view.Department job')}}</th>
                <th>{{trans('welfare::view.Confirm')}}</th>
                <th>{{trans('welfare::view.Joined')}}</th>
                <th>{{trans('welfare::view.Employee fee')}}</th>
                <th>{{trans('welfare::view.Company fee')}}</th>
                <th>{{trans('welfare::view.Tatal money')}}</th>
            </tr>
            </thead>
            <tfoot>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            </tfoot>
        </table>

        <table class="hidden" id="users-table-emp_2">
            <thead>
            <tr class="row-filter">
                <th>
                    <input type="text" class="form-control filter-empcCode" />
                </th>
                <th>
                    <input type="text" class="form-control filter-empname" />
                </th>
                <th>
                </th>
                <th>
                </th>
                <th>
                    <select class="form-control filter-confirm">
                        <option>&nbsp;</option>
                        @foreach($labelConfirm as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </th>
                <th>
                    <select class="form-control filter-joined">
                        <option>&nbsp;</option>
                        @foreach($labelConfirm as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
        </table>
    </div>
</div>

<div id="contextMenu" class="dropdown clearfix employ-context-menu" style="">
    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu" style="display:block;position:static;margin-bottom:5px;">
        @if($item->is_allow_attachments == WelEmployee::IS_CONFIRM)
        <li id="add-person"><i class="fa fa-plus"></i> {{trans('welfare::view.Add attach employee')}}</li>
        <li id="review-person"><i class="fa fa-eye"></i> {{trans('welfare::view.View attach employee')}}</li>
        @endif
        <li id="edit-person"><i class="fa fa-edit"></i>{{ trans('welfare::view.Edit infor cost') }}</li>
        <li id="cancel-person"><i class="fa fa-remove"></i> {{trans('welfare::view.Cancel employee join event')}}</li>
    </ul>
</div>
