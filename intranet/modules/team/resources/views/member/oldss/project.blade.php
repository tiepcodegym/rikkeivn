<?php
use Rikkei\Core\View\View as Coreview;
use Rikkei\Team\Model\EmployeeProjExper;
$projTag = []; ?>
<table class="table dataTable tbl-cv tbl-proj-exper" data-tbl-cv="proj">
    <colgroup>
        <col style="width: 25%; max-width: 400px;">
        <col style="width: 10%; min-width: 120px;">
        <col style="width: 12%;">
        <col style="width: 12%;">
        <col style="width: 12%;">
        <col style="width: 10%; max-width: 120px;">
        <col style="width: 10%; min-width: 100px;">
        <col style="width: 5%; min-width: 100px;">
        <col style="width: 5%; min-width: 100px;">
    </colgroup>
    <tbody>
        <tr class="heading-proj-exper">
            <td data-lang-r="project name"></td>
            <td>OS</td>
            <td data-lang-r="env"></td>
            <td data-lang-r="program"></td>
            <td data-lang-r="responsible"></td>
            <td data-lang-r="start at"></td>
            <td data-lang-r="end at"></td>
            <td data-lang-r="period" colspan="2"></td>
        </tr>
        @foreach ($projsExper as $projItem)
            @include('team::member.ss.project_item')
        @endforeach
        @if ($isAccess)
            <tr data-btn-last="before" class="tr-no-border hidden" data-access-active="hidden">
                <td>
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
                                @include('team::member.ss.project_item', ['projItem' => $newProjEx])
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        @endif
    </tbody>
</table>
<script>
    var varProjTag = {!!json_encode($projTag)!!};
</script>