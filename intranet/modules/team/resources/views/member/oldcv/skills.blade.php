<?php 
    use Rikkei\Team\Model\EmployeeSkill;
    use Rikkei\Team\Model\Skill;
    use Rikkei\Resource\Model\Programs;
    use Rikkei\Core\View\View;
    
    $programs = EmployeeSkill::getItemsFollowEmployee($employee->id, Skill::TYPE_PROGRAM);
    $oss = EmployeeSkill::getItemsFollowEmployee($employee->id, Skill::TYPE_OS);
    $db = EmployeeSkill::getItemsFollowEmployee($employee->id, Skill::TYPE_DATABASE);
    $types = Skill::typeLabel();
?>

<?php if (!function_exists('getHtmlSkill')) {
    function getHtmlSkill($skills, $skillType, $types)
    {?>
        <div class="row">
            <div class="cvo-skillgroup-area ">
                <span class="default_min_width">{{ isset($types[$skillType]) ? $types[$skillType]: ''}}</span>
            </div>
            <div class="cvo-skillgroup-skill-description">
                @foreach($skills as $skill)
                <span class="cvo-activity-position default_min_width">
                    - {{ $skillType == Skill::TYPE_PROGRAM ? Programs::getNameById((int)$skill->name) : $skill->name }}
                    ~ {{ trans('team::view.Experience') }} : {{ $skill->experience }}
                    ; {{ trans('team::view.Level') }} : {{ View::getLabelNormalLevel($skill->level) }}
                </span>
                <div style="clear: both"></div>
                @endforeach
                <div style="clear: both"></div>
            </div>
            <div style="clear: both"></div>
        </div>
        <div style="clear: both"></div>
<?php 
    }
}?>
<!--/. Start skills -->
<div class="cvo-block" id="cvo-skillgroup">
    <h3 class="cvo-block-title">
        <span id="cvo-skillgroup-blocktitle" class="default_min_width">Các kỹ năng</span>
    </h3>
    <div id="skill-table">
        @if ($programs && count($programs))
        {{ getHtmlSkill($programs, Skill::TYPE_PROGRAM, $types) }}
        @endif
        @if ($oss && count($oss))
        {{ getHtmlSkill($oss, Skill::TYPE_OS, $types) }}
        @endif
        @if ($db && count($db))
        {{ getHtmlSkill($db, Skill::TYPE_DATABASE, $types) }}
        @endif
    </div>
</div>
<!--/. End skills -->