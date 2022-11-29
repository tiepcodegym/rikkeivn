<?php
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\EmployeeSkill;

$typesSkill = [
    'language' => [
        'label' => 'program language',
        'ph' => 'pl',
    ],
    'frame' => [
        'label' => 'framework / ide',
        'ph' => 'framework / ide',
    ],
    'database' => [
        'label' => 'db',
        'ph' => 'db',
    ],
    'os' => [
        'label' => 'os',
        'ph' => 'os',
    ],
    'english' => [
        'label' => 'English',
        'ph' => 'english',
    ],
];
$colSpanDesc = 6;
$colSpanBtn = 7;
?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-condensed dataTabletable dataTable tbl-cv tbl-skill"
        data-tbl-cv="skill">
        <colgroup>
            <col style="width: 200px">
            <col style="width: 40px;">
            <col style="width: 40px;">
            <col style="width: 40px;">
            <col style="width: 40px;">
            <col style="width: 40px;">
            <col style="width: 164px">
            @if (!$disabledInput)
                <col style="width: 86px">
            @endif
        </colgroup>
        <tbody>
            <tr>
                <td data-lang-r="rank description" rowspan="5" style="vertical-align: middle;"></td>
                <td><strong>1</strong></td>
                <td data-lang-r="note level 1" colspan="{!!$colSpanDesc!!}"></td>
            </tr>
            @for ($i = 2; $i < 6; $i++)
            <tr>
                <td><strong>{!!$i!!}</strong></td>
                <td data-lang-r="note level {!!$i!!}" colspan="{!!$colSpanDesc!!}"></td>
            </tr>
            @endfor
            <tr class="thead" data-skill-dom="head">
                <td data-lang-r="rank"></td>
                @for ($i = 1; $i < 6; $i++)
                    <td>{!!$i!!}</td>
                @endfor
                <td data-lang-r="skill experience"></td>
                @if (!$disabledInput)
                    <td>&nbsp;</td>
                @endif
            </tr>
            @foreach ($typesSkill as $key => $label)
                <tr class="t-subhead" data-btn-last="after" d-skill-title="{!!$key!!}">
                    <td colspan="{!!$colSpanBtn!!}" class="tr-no-border td-head" d-skill-title="text">
                        <strong data-lang-r="{!!$label['label']!!}"></strong>
                    </td>
                    @if (!$disabledInput)
                        <td class="td-add-subhead">
                            <button type="button" class="btn btn-primary" data-btn-row-add="{!!$key!!}">
                                <i class="fa fa-plus"></i>
                            </button>
                            <div class="hidden" data-row-edit="{!!$key!!}">
                                <table>
                                    <tbody>
                                        <?php
                                        $newProjEx = new EmployeeSkill();
                                        $newProjEx->id = '-9999';
                                        ?>
                                        @include('team::member.skill-sheet.skill_item', ['skillData' => $newProjEx])
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    @endif
                </tr>
                @if (isset($skillsPerson[$key]) && count($skillsPerson[$key]))
                    @foreach ($skillsPerson[$key] as $skillData)
                        @include('team::member.skill-sheet.skill_item')
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</div>
