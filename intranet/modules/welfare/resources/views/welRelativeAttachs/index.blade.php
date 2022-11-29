<?php
use Rikkei\Welfare\Model\RelationName;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Carbon\Carbon;

$endExec = Carbon::createFromFormat('Y-m-d H:i:s', $item['end_at_exec']);
$countRow = WelEmployeeAttachs::checkRelativeAttachByWelId($item['id']);
?>
<div class="row">
    <div class="col-md-12">
        <button id="add-new-relative" class="btn-add add-relative" type="button"  data-toggle="tooltip"
                data-placement="bottom" title="{{ trans('welfare::view.Add New') }}">
            <span class="glyphicon glyphicon-plus"></span>
        </button>
    </div>
    <div class="col-sm-12">
        <table class="table table-bordered text-left" id="users-table-emp-att" link="{!! route('welfare::welfare.RelativeAttach.save') !!}"
               data-list="{!! route('welfare::welfare.RelativeAttach.data') !!}/{{$item['id']}}">
            <thead>
            <tr>
                <th>{{trans('welfare::view.Employee name')}}</th>
                <th>{{trans('welfare::view.Email')}}</th>
                <th>{{trans('welfare::view.Relation_attack')}}</th>
                <th>{{trans('welfare::view.Relation')}}</th>
                <th>{{trans('welfare::view.Birthday')}}</th>
                <th>{{trans('welfare::view.Gender')}}</th>
                <th>{{trans('welfare::view.Phone')}}</th>
                <th>{{trans('welfare::view.Joined')}}</th>
            </tr>
            </thead>
        </table>
    </div>
</div>
<div class="radmenu radcontext">
    <ul>
        <li class="add-relative">
            <i class="fa fa-plus"></i> {{ trans('welfare::view.Add New') }}
        </li>

        <li class="edit-relative <?php if (!$countRow) : ?> hidden <?php endif; ?>" data-url="{{ route('welfare::welfare.relative.attach.edit') }}">
            <i class="fa fa-edit"></i> {{ trans('welfare::view.Edit') }}
        </li>
        <li class="remove-relative <?php if (!$countRow) : ?> hidden <?php endif; ?>">
            <i class="fa fa-trash"></i> {{ trans('welfare::view.Delete') }}
        </li>

        @if ($endExec->lte(Carbon::now()))
        <li class="confirm-relative-participation <?php if (!$countRow) : ?> hidden <?php endif; ?>" data-welId="" data-emplId="" data-value="">
            <p class="text-default"><i class="fa fa-check"></i> {{ trans('welfare::view.Confirm participation') }}</p>
            <p class="text-change hidden"><i class="fa fa-times"></i> {{ trans('welfare::view.Status_canceled') }}</p>
        </li>
        @endif
    </ul>
</div>
