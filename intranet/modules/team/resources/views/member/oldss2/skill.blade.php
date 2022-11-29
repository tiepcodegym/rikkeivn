<?php
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\EmployeeSkill;

$typesSkill = [
    'language' => 'program language',
    'database' => 'db',
    'os' => 'os',
];
$tagData = $skillsPerson['tagData'];
?>

    <table class="table dataTable tbl-cv tbl-skill">
        <colgroup>
            <col style="width: 20%;">
            <col style="width: 7%">
            <col style="width: 7%">
            <col style="width: 7%">
            <col style="width: 7%">
            <col style="width: 7%">
            <col style="width: 10%">
            <col style="width: 10%">
        </colgroup>
        <tbody>
            <tr>
                <td data-lang-r="rank description" rowspan="5" style="vertical-align: middle;"></td>
                <td><strong>1</strong></td>
                <td data-lang-r="note level 1" colspan="6"></td>
            </tr>
            @for ($i = 2; $i < 6; $i++)
            <tr>
                <td><strong>{!!$i!!}</strong></td>
                <td data-lang-r="note level {!!$i!!}" colspan="6"></td>
            </tr>
            @endfor
            <tr class="heading-proj-exper">
                <td data-lang-r="rank"></td>
                @for ($i = 1; $i < 6; $i++)
                <td>{!!$i!!}</td>
                @endfor
                <td data-lang-r="skill experience" colspan="2"></td>
            </tr>
            @foreach ($typesSkill as $key => $label)
                <tr class="t-subhead">
                    <td colspan="7" class="tr-no-border"><strong data-lang-r="{!!$label!!}"></strong></td>
                    <td class="tr-no-border">
                        @if ($isAccess)
                        <button type="button" class="btn btn-primary btn-xs" data-btn-row-add="{!!$key!!}">
                            <i class="fa fa-plus"></i>
                        </button>
                        <div class="hidden" data-row-edit="{!!$key!!}">
                            <table>
                                <tbody>
                                    <tr data-id="-9999" data-type="ski">
                                        <?php
                                        $newProjEx = new EmployeeSkill();
                                        $newProjEx->id = '-9999';
                                        ?>
                                        @include('team::member.synthesis.skill_item', ['skillData' => $newProjEx])
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </td>
                </tr>
                <tr data-row-add="{!!$key!!}" class="hidden"><td colspan="8"></td></tr>
                @if ($skillsPerson[$key] && count($skillsPerson[$key]))
                    @foreach ($skillsPerson[$key] as $skillData)
                        @include('team::member.synthesis.skill_item')
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
