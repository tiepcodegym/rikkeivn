<?php
use Rikkei\Core\View\View as Coreview;
use Rikkei\Team\Model\EmployeeProjExper;
use Rikkei\Team\View\Config as TeamConfig;
$countEN = $countJP = 0;
?>
<div class="table-responsive">
<table class="table table-striped table-bordered table-condensed dataTabletable dataTable tbl-cv tbl-proj-exper"
    data-tbl-cv="proj">
    <colgroup>
        <col style="width: 45px;">
        <col style="width: 45px;" data-col-fix="proj_number">
        <col data-col-remain data-col-fix="name">
        <col data-priority="3" data-percent="13" data-col-fix="lang">
        <col data-priority="2" data-percent="13" data-col-fix="env">
        <col data-priority="1" data-percent="10">
        <col style="width: 115px">
        <col style="width: 90px;">
        @if (!$disabledInput)
            <col style="width: 80px;" data-col-fix="action">
        @endif
    </colgroup>
    <thead class="not-padding">
        <tr>
            <th data-lang-r="No."></th>
            <th data-lang-r="Ref Index" class="ref_index" title="{{ trans('team::cv.Ref Index tooltips') }}"></th>
            <th data-lang-r="project name" width="420">
            <th width="230">
                <div data-lang-r="role"></div>
                <div data-lang-r="team size"></div>
            </th>
            <th data-lang-r="programming languages" width="150"></th>
            <th data-lang-r="environment" width="150"></th>
            <th data-lang-r="assigned phases" width="300"></th>
            <th id="order" class="sorting {!!TeamConfig::getDirClass('start_end')!!}" data-sort="{!!TeamConfig::getDirClass('start_end')!!}" data-order="start_end" data-dir="{!!TeamConfig::getDirOrder('start_end')!!}" data-lang-r="start-end" width="100"></th>
            <th data-lang-r="period" width="80"></th>
            @if (!$disabledInput)
            <th width="60">&nbsp;</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($projsExper as $key => $projItem)
            <?php
            if ($projItem->lang_code == 'en') {
                $countEN += 1;
            } else if ($projItem->lang_code == 'ja') {
                $countJP += 1;
            }
            ?>
            @include('team::member.skill-sheet.project_item', ['number' => $projItem->lang_code == 'en' ? $countEN : $countJP])
        @endforeach
        @if (!$disabledInput)
            <tr data-btn-last="before" class="no-border">
                <td colspan="10">
                    <button type="button" class="btn btn-primary" data-btn-row-add="proj">
                        <i class="fa fa-plus"></i>
                    </button>
                    <div class="hidden" data-row-edit="proj">
                        <table>
                            <tbody>
                                <?php
                                $newProjEx = new EmployeeProjExper();
                                $newProjEx->id = '-9999';
                                ?>
                                @include('team::member.skill-sheet.project_item', ['projItem' => $newProjEx])
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>
</div>
