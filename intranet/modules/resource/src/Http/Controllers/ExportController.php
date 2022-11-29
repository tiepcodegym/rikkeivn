<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\Skill;
use Rikkei\Tag\View\TagConst;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Resource\Model\Candidate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Excel;

/**
 * Description of ExportController
 *
 * @author lamnv
 */
class ExportController extends Controller
{
    /**
     * export all dev
     * @return string
     */
    public function exportDevs()
    {
        $empTbl = Employee::getTableName();
        $teamTbl = Team::getTableName();
        $teamMbTbl = TeamMember::getTableName();

        // Collect data
        $collection = Employee::select(
            $empTbl.'.employee_code',
            $empTbl.'.name',
            $empTbl.'.email',
            DB::raw('GROUP_CONCAT(DISTINCT(lang.value) ORDER BY skill.exp_y DESC, skill.exp_m DESC SEPARATOR ", ") as lang_names'),
            DB::raw('SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT(proj.name) SEPARATOR ", "), ", ", 6) as project_names'),
            'cv_attr.value as exper_year',
            DB::raw('GROUP_CONCAT(DISTINCT(team_sl.name) SEPARATOR ", ") as team_names'),
            'cdd.interview_note'
        )
            ->leftJoin(EmployeeSkill::getTableName() . ' as skill', function ($join) use ($empTbl) {
                $join->on('skill.employee_id', '=', $empTbl.'.id')
                        ->where('skill.type', '=', Skill::TYPE_PROGRAM);
            })
            ->leftJoin(Tag::getTableName() . ' as lang', function ($join) {
                $join->on('lang.id', '=', 'skill.tag_id')
                        ->whereNull('lang.deleted_at')
                        ->where('lang.status', '=', TagConst::TAG_STATUS_APPROVE);
            })
            ->leftJoin(EmplCvAttrValue::getTableName() . ' as cv_attr', function ($join) use ($empTbl) {
                $join->on('cv_attr.employee_id', '=', $empTbl.'.id')
                        ->where('code', '=', 'exper_year');
            })
            ->leftJoin($teamMbTbl . ' as tmb', 'tmb.employee_id', '=', $empTbl.'.id')
            ->leftJoin($teamTbl . ' as team', 'tmb.team_id', '=', 'team.id')
            ->leftJoin($teamMbTbl . ' as tmb_sl', 'tmb_sl.employee_id', '=', $empTbl.'.id')
            ->leftJoin($teamTbl . ' as team_sl', 'tmb_sl.team_id', '=', 'team_sl.id')
            ->leftJoin(ProjectMember::getTableName() . ' as pjm', function ($join) use ($empTbl) {
                $timeNow = Carbon::now()->toDateString();
                $join->on('pjm.employee_id', '=', $empTbl.'.id')
                    ->whereNull('pjm.deleted_at')
                    ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED)
                    ->where('pjm.start_at', '<=', $timeNow)
                    ->where('pjm.end_at', '>=', $timeNow);
            })
            ->leftJoin(Project::getTableName() . ' as proj', function ($join) {
                $join->on('proj.id', '=', 'pjm.project_id')
                        ->whereNull('proj.deleted_at');
            })
            ->leftJoin(Candidate::getTableName() . ' as cdd', function ($join) use ($empTbl) {
                $join->on('cdd.employee_id', '=', $empTbl.'.id')
                        ->whereNull('cdd.deleted_at');
            })
            ->where(function ($query) {
                $query->where('team.type', Team::TEAM_TYPE_QA)
                        ->orWhere('team.is_soft_dev', Team::IS_SOFT_DEVELOPMENT);
            })
            ->orderBy($empTbl.'.email', 'asc')
            ->groupBy($empTbl.'.id')
            ->get();
        
        if ($collection->isEmpty()) {
            return 'Empty data!';
        }

        // Export excel
        $fileName = Carbon::now()->format('Ymd') . '_All_Devs';
        Excel::create($fileName, function ($excel) use ($collection) {
            //sheet type 1,3,4
            $excel->sheet('dev', function ($sheet) use ($collection) {
                $rowHeader = [
                    'No.',
                    trans('resource::view.Employee code'),
                    trans('resource::view.Employee name'),
                    trans('resource::view.Email'),
                    trans('resource::view.Programing language'),
                    trans('resource::view.Project'),
                    trans('resource::view.Experience'),
                    trans('resource::view.Division'),
                    trans('resource::view.Interview note')
                ];

                $sheetData = [$rowHeader];

                foreach ($collection as $order => $item) {
                    $projectName = $item->project_names;
                    $arrProject = explode(', ', $projectName);
                    $strProject = implode("\r\n", array_slice($arrProject, 0, 5));
                    if (count($arrProject) > 5) {
                        $strProject .= ', ...';
                    }
                    $rowData = [
                        $order + 1,
                        self::exceptCalculate($item->employee_code),
                        self::exceptCalculate($item->name),
                        self::exceptCalculate($item->email),
                        self::exceptCalculate($item->lang_names),
                        self::exceptCalculate($strProject),
                        self::exceptCalculate($item->exper_year),
                        self::exceptCalculate($item->team_names),
                        self::exceptCalculate($item->interview_note)
                    ];
                    $sheetData[] = $rowData;
                }
                $sheet->getStyle('A2:I1000')->getAlignment()->setWrapText(true);
                $sheet->fromArray($sheetData, null, 'A1', false, false);
                $sheet->setHeight([
                    1 =>  30
                ]);
                $sheet->setBorder('A1:I1', 'thin');
                $sheet->cells('A1:I1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
            });
        })->export('xlsx');
    }

    /**
     * remove first character "="
     */
    public static function exceptCalculate($string)
    {
        return preg_replace('/\=(.*)/', '$1', $string);
    }
}
