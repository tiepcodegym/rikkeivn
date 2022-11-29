<?php
use Carbon\Carbon;
use Rikkei\Core\View\View as ViewCore;
use Rikkei\Project\Model\ProjDeliverable;
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data input-color-e">
                <thead>
                    <tr>
                        <th style="width: 20px;">{{ trans('project::view.No.') }}</th>
                        <th>{{ trans('project::view.Deliverable') }}</th>
                        <th>{{ trans('project::view.Committed date') }}</th>
                        <th>{{ trans('project::view.Re-Plan Release') }}</th>
                        <th>{{ trans('project::view.Actual date') }}</th>
                        <th>{{ trans('project::view.Change request by') }}</th>
                        <th>{{ trans('project::view.Till now') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php 
                        $i = ViewCore::getNoStartGrid($collectionModel); 
                        $now = Carbon::now();
                        ?>
                        @foreach($collectionModel as $item)
                            <?php
                            $point = null;
                            $commitDate = Carbon::parse($item->committed_date);
                            $actualDate = $item->actual_date ? Carbon::parse($item->actual_date) : null;
                            $expectedDate = $commitDate;
                            if ($item->change_request_by == ProjDeliverable::CHANGE_BY_CUSTOMER && $item->re_commited_date) {
                                $expectedDate = Carbon::parse($item->re_commited_date);
                            }
                            $diff = $expectedDate->diff($now);
                            if ($diff->invert == 0 && $diff->days >= 0) { //commit <= now
                                if ($actualDate) {
                                    $diffActual = $actualDate->diff($expectedDate);
                                    if (!$diffActual->invert || !$diffActual->days) {
                                        $point = 1;
                                    } else {
                                        $point = -1;
                                    }
                                } else {
                                    if ($diff->days > 0) { // commit < now
                                        $point = -1;
                                    }
                                }
                            }
                            ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $item->title }}</td>
                                <td>{{ $commitDate->format('Y-m-d') }}</td>
                                <td>{{ $item->re_commited_date }}</td>
                                <td>
                                    <div class="form-submit-change-ajax form-input-inline"
                                        data-url-ajax="{{ route('project::dashboard.deliver.save', ['id' => $item->id]) }}">
                                        @if (($permissionEditPointPM || $permissionEditSubPM) && $projectIsOpen)
                                            <input type="text" class="form-control date-picker deliver-save" name="actual_date" 
                                                value="{{ $actualDate ? $actualDate->format('Y-m-d') : '' }}" 
                                                data-old-value="{{ $actualDate ? $actualDate->format('Y-m-d') : '' }}"
                                                placeholder="yyyy-mm-dd" />
                                            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i>
                                        @else
                                            {{ $actualDate ? $actualDate->format('Y-m-d') : '' }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ isset($changeRequestList[$item->change_request_by]) ? $changeRequestList[$item->change_request_by] : '' }}</td>
                                <td>{{ $diff->invert == 0 ? 1 : 0 }}</td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center">
                                <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="box-body">
            @include('team::include.pager', ['domainTrans' => 'project'])
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('input.date-picker.deliver-save').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });
        $("input.date-picker.deliver-save").on("dp.change", function () {
            if ($(this).attr('data-old-value') != $(this).val()) {
                RKfuncion.formSubmitAjax._elementSubmit($(this), 3);
            }
            $(this).attr('data-old-value', ($(this).val()));
        });
    });
</script>