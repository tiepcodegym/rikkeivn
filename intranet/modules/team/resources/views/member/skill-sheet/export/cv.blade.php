<?php
use Rikkei\Team\View\ExportCv;
use Carbon\Carbon;

$objExportCv = new ExportCv();
$objExportCv->generateEmptyCols();
$ranking = [
    1 => Lang::get('team::cv.note level 1', [], $locale),
    2 => Lang::get('team::cv.note level 2', [], $locale),
    3 => Lang::get('team::cv.note level 3', [], $locale),
    4 => Lang::get('team::cv.note level 4', [], $locale),
    5 => Lang::get('team::cv.note level 5', [], $locale)
];
$countRank = count($ranking);
$leftColspan = 9;
$rightColspan = $countRank + 2;
$allColspan = $leftColspan + 1 + $rightColspan;

$experYear = ExportCv::getEavAttr($cvEav, 'exper_year');
$experJpYear = ExportCv::getEavAttr($cvEav, 'exper_japan_' . $locale);
$keySkill = 0;
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="{{ asset('team/css/export-cv.css') }}" />
</head>
<body>
    <table>
        <thead>
            <tr class="offset">
                <td colspan="{!! $leftColspan !!}"></td>
                <td></td>
                <td colspan="{{ $rightColspan }}"></td>
                <td></td>
            </tr>
            <tr class="offset">
                <td colspan="{!! $leftColspan !!}" class="sheet-title">{{ Lang::get('team::cv.cv title', [], $locale) }}</td>
                <td></td>
                <td colspan="{{ $rightColspan - 1 }}"></td>
                <td><img src="{!! public_path('common/images/logo-cv.png') !!}" /></td>
                <td></td>
            </tr>
            <tr class="offset">
                <td width="7"></td>
                <td width="15"></td>
                <td width="20"></td>
                <td width="14"></td>
                <td width="14"></td>
                <td width="25"></td>
                <td width="{!! $locale === 'en' ? 20 : 30 !!}"></td>
                <td width="15"></td>
                <td width="15"></td>
                <td width="5"></td>
                <!-- right -->
                <td width="15"></td>
                <td width="5"></td>
                <td width="5"></td>
                <td width="5"></td>
                <td width="5"></td>
                <td width="5"></td>
                <td width="20"></td>
                <td></td>
            </tr>
            <tr class="row-sl-bd">
                <td colspan="2" class="title sl-bd">{{ Lang::get('team::cv.full name', [], $locale) }}</td>
                <td>{{ $employee->name }}</td>
                <td colspan="2" class="title">{{ Lang::get('team::cv.kana name', [], $locale) }}</td>
                <td>{{ $employee->japanese_name }}</td>
                <td class="title">{{ Lang::get('team::cv.sex', [], $locale) }}</td>
                <td>{{ Lang::get('team::cv.gender_' . $employee->gender, [], $locale) }}</td>
                <td rowspan="4"></td> <!-- avatar -->
                <td class="sep"></td>
                <!-- skill -->
                <td class="text-center" rowspan="{{ $countRank }}">{{ Lang::get('team::cv.rank description', [], $locale) }}</td>
                <td class="desc-no">1</td>
                <td colspan="5">{{ $ranking[1] }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr class="row-sl-bd">
                <td colspan="2" class="title sl-bd">{{ Lang::get('team::cv.school graduation', [], $locale) }}</td>
                <td colspan="4">{{ ExportCv::getEavAttr($cvEav, 'school_graduation_' . $locale) }}</td>
                <td class="title">{{ Lang::get('team::cv.old', [], $locale) }}</td>
                <td align="left">{{ Carbon::now()->diff(Carbon::parse($employee->birthday))->y }}</td>
                <td>&nbsp;</td>
                <td class="sep">&nbsp;</td>
                <!-- skill -->
                <td>&nbsp;</td>
                <td class="desc-no">2</td>
                <td colspan="5">{{ $ranking[2] }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr class="row-sl-bd">
                <td colspan="2" class="title">{{ Lang::get('team::cv.field develop', [], $locale) }}</td>
                <td colspan="4">{{ ExportCv::getEavAttr($cvEav, 'field_dev_' . $locale) }}</td>
                <td class="title">{{ Lang::get('team::cv.level english', [], $locale) }}</td>
                <td>{{ ExportCv::getEavAttr($cvEav, 'lang_en_level') }}</td>
                <td>&nbsp;</td>
                <td class="sep">&nbsp;</td>
                <!-- skill -->
                <td>&nbsp;</td>
                <td class="desc-no">3</td>
                <td colspan="5">{{ $ranking[3] }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr class="row-sl-bd">
                <td colspan="2" class="title">{{ Lang::get('team::cv.experience year cv', [], $locale) }}</td>
                <td>{{ $experYear }} {{ Lang::get('team::cv.' . ($experYear == 1 ? 'year' : 'years'), [], $locale) }}</td>
                <td colspan="2" class="title">{{ Lang::get('team::cv.international experience', [], $locale) }}</td>
                <td>{{ $experJpYear }} {{ Lang::get('team::cv.' . ($experJpYear == 1 ? 'year' : 'years'), [], $locale) }}</td>
                <td class="title">{{ Lang::get('team::cv.level japanese', [], $locale) }}</td>
                <td>{{ ExportCv::getEavAttr($cvEav, 'lang_ja_level') }}</td>
                <td>&nbsp;</td>
                <td class="sep">&nbsp;</td>
                <!-- skill -->
                <td>&nbsp;</td>
                <td class="desc-no">4</td>
                <td colspan="5">{{ $ranking[4] }}</td>
                <td>&nbsp;</td>
            </tr>

            <!-- offset personal summary -->
            <tr class="row-sl-bd">
                <td colspan="9" class="bd-none">&nbsp;</td>
                <td class="sep">&nbsp;</td>
                <!-- skill -->
                <td>&nbsp;</td>
                <td class="desc-no">5</td>
                <td colspan="5">{{ $ranking[5] }}</td>
                <td>&nbsp;</td>
            </tr>

            <!-- personal summary -->
            <tr>
                <td class="bd-5 title">{!! Lang::get('team::cv.personal summary', [], $locale) !!}</td>
                <td>&nbsp;</td>
                <td class="bd-5">{!! nl2br(ExportCv::getEavAttr($cvEav, 'statement_' . $locale)) !!}</td>
                {!! $objExportCv->renderEmptyCols(6) !!}
                <td class="bd-none">&nbsp;</td>
                <!-- skill -->
                <td class="bd-5 title text-center">{{ Lang::get('team::cv.rank', [], $locale) }}</td>
                @foreach ($ranking as $noRank => $rankName)
                    <td class="bd-5 title text-center rank-no">{{ $noRank }}</td>
                @endforeach
                <td class="bd-5 text-center title">{{ Lang::get('team::cv.year of experience', [], $locale) }}</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                {!! $objExportCv->renderEmptyCols(9) !!}
                <td class="bd-none">&nbsp;</td>
                <!-- skill, start skill -->
                {!! ExportCv::renderSkill($skillPersonIds, $tagData, $ranking, $rightColspan, $locale, $countSkill, $keySkill) !!}
            </tr>
            <tr>
                {!! $objExportCv->renderEmptyCols(9) !!}
                <td class="bd-none">&nbsp;</td>
                <!-- skill -->
                {!! ExportCv::renderSkill($skillPersonIds, $tagData, $ranking, $rightColspan, $locale, $countSkill, ++$keySkill) !!}
            </tr>

            <!-- offset project -->
            <tr>
                <td colspan="10" class="bd-none">&nbsp;</td>
                <!-- skill -->
                {!! ExportCv::renderSkill($skillPersonIds, $tagData, $ranking, $rightColspan, $locale, $countSkill, ++$keySkill) !!}
            </tr>

            <!-- label project -->
            <tr class="main-row">
                <td class="title">{{ Lang::get('team::cv.No.', [], $locale) }}</td>
                <td colspan="2" class="title">{{ Lang::get('team::cv.project name', [], $locale) }}</td>
                <td class="title">
                    {{ Lang::get('team::cv.role', [], $locale) }}<br>
                    {{ Lang::get('team::cv.team size', [], $locale) }}
                </td>
                <td class="title">{{ Lang::get('team::cv.programming languages', [], $locale) }}</td>
                <td class="title">{{ Lang::get('team::cv.env_cv', [], $locale) }}</td>
                <td class="title">{{ Lang::get('team::cv.assigned phases', [], $locale) }}</td>
                <td class="title">{{ Lang::get('team::cv.start-end', [], $locale) }}</td>
                <td class="title">{{ Lang::get('team::cv.period', [], $locale) }}</td>
                <td class="sep">&nbsp;</td>
                <!-- skill -->
                {!! ExportCv::renderSkill($skillPersonIds, $tagData, $ranking, $rightColspan, $locale, $countSkill, ++$keySkill) !!}
            </tr>
        </thead>
        <tbody>
        <?php $keySkill++; ?>
        @for ($i = 0; $i < $maxCount; $i++)
            <?php
            $pKey = $i >> 2;
            $project = isset($projects[$pKey]) ? $projects[$pKey] : null;
            if ($project) {
                $projectId = $project->id;
                $projKeyName = 'proj_' . $projectId . '_name_' . $locale;
                $projKeyDesc = 'proj_' . $projectId . '_description_' . $locale;
                $projTags = isset($skillProjIds[$projectId]) ? $skillProjIds[$projectId] : [];
                $startAt = Carbon::parse($project->start_at);
                $endAt = Carbon::parse($project->end_at)->addMonth();
                $period = $endAt->diff($startAt);
                if ($period->d >= 15) {
                    $period->m += 1;
                }
                if ($period->m >= 12) {
                    $period->y += 1;
                    $period->m = 0;
                }
            }
            $classBdb = '';
            if ($project && $i === $numRows * $countProj - 1) {
                $classBdb = 'md-bdb';
            }
            ?>
            <tr>
                @if ($i < $countProj * $numRows) <!-- project item -->
                    @if ($i % $numRows === 0)
                        <td rowspan="4" class="md-bdl sl-bd text-center {{ $classBdb }}">{{ ($i / 4) + 1 }}</td>
                        <td class="sl-bd">{{ isset($cvEav[$projKeyName]) ? $cvEav[$projKeyName] : null }}</td>
                        <td>&nbsp;</td>
                        <td class="sl-bd">
                            {!! isset($projTags['role']) ? ExportCv::getTagNames($projTags['role'], isset($projRoles[$locale]) ? $projRoles[$locale] : [], true) : null !!}
                        </td>
                        <td rowspan="4" class="sl-bd {{ $classBdb }}">{!! isset($projTags['lang']) ? ExportCv::getTagNames($projTags['lang'], $tagData, true) : null !!}</td>
                        <td rowspan="4" class="sl-bd {{ $classBdb }}">{!! isset($projTags['other']) ? ExportCv::getTagNames($projTags['other'], $tagData, true) : null !!}</td>
                        <td rowspan="4" class="sl-bd {{ $classBdb }}">
                            {!! isset($projTags['res']) ? ExportCv::getTagNames($projTags['res'], isset($projPosition[$locale]) ? $projPosition[$locale] : [], true) : null !!}
                        </td>
                        <td rowspan="2" class="sl-bd text-center">{!! ($locale == 'ja') ? $startAt->format('Y年m月') : $project->start_at !!}</td>
                        <td rowspan="4" class="sl-bd md-bdr text-center">
                            @if ($period->y > 0)
                            {!! $period->y !!} {!! Lang::get($period->y === 1 ? 'team::cv.year' : 'team::cv.years', [], $locale) !!},
                            @endif
                            {!! $period->m !!} {!! Lang::get($period->m <= 1 ? 'team::cv.month' : 'team::cv.months', [], $locale) !!}
                        </td>
                    @elseif ($i % $numRows === 1)
                        <td>&nbsp;</td>
                        <td class="sl-bd {{ $classBdb }}">{!! isset($cvEav[$projKeyDesc]) ? nl2br($cvEav[$projKeyDesc]) : null !!}</td>
                        <td class="sl-bd">&nbsp;</td>
                        <td rowspan="3" class="sl-bd {{ $classBdb }}">
                            <?php
                                $txtTeamMember = '';
                                if ($project->total_member) {
                                    $txtMember = Lang::get((int) $project->total_member <= 1 ? 'team::cv.member' : 'team::cv.members', [], $locale);
                                    $txtTeamMember = Lang::get('team::cv.team', [], $locale) . ": {$project->total_member} " . $txtMember;
                                }
                            ?>
                            {!! $txtTeamMember !!}<br>
                            {!! $project->total_mm ? Lang::get('team::cv.total', [], $locale) . ": {$project->total_mm} " . Lang::get('team::cv.MM', [], $locale) : '' !!}
                        </td>
                        {!! $objExportCv->renderEmptyCols(5) !!}
                    @elseif ($i % $numRows === 2)
                        {!! $objExportCv->renderEmptyCols(7) !!}
                        <td rowspan="2" class="sl-bd text-center">{!! ($locale === 'ja') ? $endAt->addMonth(-1)->format('Y年m月') : $project->end_at !!}</td>
                        <td>&nbsp;</td>
                    @else
                        {!! $objExportCv->renderEmptyCols(9) !!}
                    @endif
                @elseif ($i < $countProj * $numRows + 4) <!-- reference -->
                    @if ($i % $numRows === 0)
                    <td colspan="9" class="bd-none">&nbsp;</td>
                    @elseif ($i % $numRows === 1)
                    <td class="bd-5 title">{!! Lang::get('team::cv.reference', [], $locale) !!}</td>
                    <td>&nbsp;</td>
                    <td class="bd-5">{!! nl2br(ExportCv::getEavAttr($cvEav, 'reference_' . $locale)) !!}</td>
                    {!! $objExportCv->renderEmptyCols(6) !!}
                    @else
                    {!! $objExportCv->renderEmptyCols(9) !!}
                    @endif
                @else
                    {!! $objExportCv->renderEmptyCols(9) !!}
                @endif
                <td>&nbsp;</td>
                <!-- skill -->
                {!! ExportCv::renderSkill($skillPersonIds, $tagData, $ranking, $rightColspan, $locale, $countSkill, $keySkill + $i) !!}
                <td>&nbsp;</td>
            </tr>
        @endfor
        </tbody>
    </table>
</body>
</html>
