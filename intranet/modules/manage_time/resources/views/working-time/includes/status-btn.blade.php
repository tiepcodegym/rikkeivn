<?php
    use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
    use Rikkei\ManageTime\Model\WorkingTimeRegister;
?>

@if ($permiss['edit'] || $permiss['update_approved'])
    @if ($isPageDetail)
        <a type="button" class="btn btn-primary btn-lg" href="{!! route('manage_time::wktime.edit', ['id' => $workingTime->id]) !!}">
            <i class="fa fa-edit"></i> {{ trans('manage_time::view.Edit') }}
        </a>
    @else
        <button type="button" class="btn btn-primary btn-lg btn-submit-form" id="btn-submit-form">
            <i class="fa fa-floppy-o"></i> {{ trans('manage_time::view.Update') }} <i class="fa fa-spin fa-refresh hidden"></i>
        </button>
    @endif
@endif

@if ($workingTime && $isPageDetail)
        <?php
        if ($workingTime->status == WorkingTimeRegister::STATUS_REJECT) {
            $permiss['not_approve'] = false;
        }
        if ($workingTime->status == WorkingTimeRegister::STATUS_APPROVE) {
            $permiss['approve'] = false;
        }
    ?>

    @if ($permiss['approve'])
    <button type="button" class="btn btn-success btn-lg status-submit" data-status="{{ WorkingTimeRegister::STATUS_APPROVE }}"
            data-url="{{ route('manage_time::wktime.approve_register') }}" data-id="{{ $item->id }}"
            data-noti="{{ trans('manage_time::message.confirm_do_action', ['action' => trans('manage_time::view.Approve')]) }}">
        <i class="fa fa-check"></i> {{ trans('manage_time::view.Approve') }}
    </button>
    @endif

    @if ($permiss['not_approve'])
    <button type="button" class="btn btn-danger btn-lg" id="btn_time_modal_reject">
        <i class="fa fa-minus-circle"></i> {{ trans('manage_time::view.Not approve') }}
    </button>
    @endif
@endif
