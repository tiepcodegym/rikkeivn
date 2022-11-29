<?php
use Rikkei\Core\View\View as Coreview;
use Rikkei\Team\Model\EmployeeProjExper;
$projTag = []; ?>
<tr class="heading-proj-exper">
    <td data-lang-r="project name" colspan="2"></td>
    <td>OS</td>
    <td data-lang-r="env"></td>
    <td data-lang-r="language"></td>
    <td data-lang-r="responsible"></td>
    <td data-lang-r="start at"></td>
    <td data-lang-r="end at"></td>
    <td data-lang-r="period" colspan="2"></td>
</tr>
@foreach ($projsExper as $projItem)
    @include('team::member.synthesis.project_item')
@endforeach
@if ($isAccess)
<tr data-row-add="proj" class="tr-no-border">
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
                    @include('team::member.synthesis.project_item', ['projItem' => $newProjEx])
                </tbody>
            </table>
        </div>
    </td>
</tr>
@endif
<script>
    var varProjTag = {!!json_encode($projTag)!!};
</script>