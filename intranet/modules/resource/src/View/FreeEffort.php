<?php

namespace Rikkei\Resource\View;

use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Employee;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\Skill;
use Rikkei\Team\Model\EmplCvAttrValue;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\EmpAvailableData;
use Rikkei\ManageTime\Model\BusinessTripRegister;

class FreeEffort
{
    const MIN_DATE = '1970-01-01';
    const MAX_DATE = '9999-12-31';
    const JAPANESE_LANG_ID = 2;
    const ENGLISH_LANG_ID = 1;
    const ROUTE_EXPORT = 'resource::permiss.available.export';

    /*
     * get list data
     */
    public static function getGridData($dataSearch = [], $isExport = false)
    {
        //table name
        $empTbl = Employee::getTableName();
        $projMbTbl = ProjectMember::getTableName();
        $teamTbl = Team::getTableName();
        $teamMbTbl = TeamMember::getTableName();
        $empSkillTbl = EmployeeSkill::getTableName();
        $empCvAttrTbl = EmplCvAttrValue::getTableName();
        $tagTbl = Tag::getTableName();

        $urlFilter = route('resource::available.index') . '/';
        $pager = Config::getPagerData($urlFilter);
        //filter date
        $fromDate = isset($dataSearch['from_date']) && $dataSearch['from_date'] ? $dataSearch['from_date'] : self::MIN_DATE;
        $toDate = isset($dataSearch['to_date']) && $dataSearch['to_date'] ? $dataSearch['to_date'] : self::MAX_DATE;

        $collection = Employee::select(
            $empTbl.'.id',
            $empTbl.'.name',
            $empTbl.'.employee_code',
            $empTbl.'.email',
            'exp.value as exper_year',
            'nopjm.total_join',
            'joined_pjm.id as joined_pj',
            'emp_data.foreign_langs as lang_level',
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names'),
            'emp_data.program_langs as str_langs',
            'emp_data.projects as str_pjms'
        )
            //project member not joined or join 100%
            ->leftJoin(
                DB::raw('(SELECT id, employee_id, status, deleted_at, start_at, end_at, COUNT(id) as total_join '
                    . 'FROM '. $projMbTbl .') as nopjm'),
                function ($join) use ($empTbl) {
                    $join->on('nopjm.employee_id', '=', $empTbl.'.id')
                        ->where('nopjm.status', '=', ProjectMember::STATUS_APPROVED)
                        ->whereNull('nopjm.deleted_at');
                }
            )
            //project full time
            ->leftJoin($projMbTbl . ' as joined_pjm', function ($join) use ($empTbl, $fromDate, $toDate) {
                $join->on('joined_pjm.employee_id', '=', $empTbl.'.id')
                        ->where('joined_pjm.status', '=', ProjectMember::STATUS_APPROVED)
                        ->whereNull('joined_pjm.deleted_at')
                        ->where(function ($query) use ($fromDate, $toDate) {
                            $query->where('joined_pjm.start_at', '<=', $fromDate)
                                    ->where('joined_pjm.end_at', '>=', $toDate);
                        });
            })
            ->leftJoin(EmpAvailableData::getTableName() . ' as emp_data', $empTbl.'.id', '=', 'emp_data.employee_id')
            //skill program
            ->leftJoin($empSkillTbl . ' as lang', function ($join) use ($empTbl) {
                $join->on('lang.employee_id', '=', $empTbl.'.id')
                        ->where('lang.type', '=', Skill::TYPE_PROGRAM);
            })
            //experience year
            ->leftJoin($empCvAttrTbl . ' as exp', function ($join) use ($empTbl) {
                $join->on('exp.employee_id', '=', $empTbl.'.id')
                        ->where('exp.code', '=', 'exper_year');
            })
            ->leftJoin($teamMbTbl . ' as tmb', $empTbl.'.id', '=', 'tmb.employee_id')
            ->leftJoin($teamTbl . ' as team', 'tmb.team_id', '=', 'team.id')
            //check buisiness trip
            ->leftJoin(BusinessTripRegister::getTableName() . ' as bus', function ($join) use ($empTbl, $fromDate, $toDate) {
                $join->on('bus.creator_id', '=', $empTbl.'.id')
                        ->whereNull('bus.deleted_at')
                        ->where('bus.status', '=', BusinessTripRegister::STATUS_APPROVED)
                        ->where('bus.date_start', '<=', $fromDate)
                        ->where('bus.date_end', '>=', $toDate);
            })
            ->whereNull('bus.id')
            //where not in project or in project free time
            ->where(function ($query) {
                $query->whereNull('joined_pjm.id')
                        ->orWhere('nopjm.total_join', 0);
            })
            //check leave date
            ->where(function ($query) use ($fromDate, $empTbl) {
                $query->whereNull($empTbl.'.leave_date')
                        ->orWhere($empTbl.'.leave_date', '>', $fromDate);
            })
            ->groupBy($empTbl.'.id');

        //permission
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany()) {
            //get all
        } elseif ($scope->isScopeTeam()) {
            $collection->whereIn('tmb.team_id', function ($query) use ($teamMbTbl, $scope) {
                $query->select('team_id')
                        ->from($teamMbTbl)
                        ->where('employee_id', $scope->getEmployee()->id);
            });
        } else {
            CoreView::viewErrorPermission();
        }

        //check if search
        if ($dataSearch['has_search']) {
            //filter team
            if ($teamId = CoreForm::getFilterData('search', 'team_id', $urlFilter)) {
                $collection->join($teamMbTbl . ' as tmb_filter', $empTbl.'.id', '=', 'tmb_filter.employee_id')
                        ->join($teamTbl . ' as team_filter', 'tmb_filter.team_id', '=', 'team_filter.id')
                        ->whereIn('team_filter.id', $teamId);
            }
            //data search
            //filter programing language and framework
            $filterFrameLangs = [];
            if (isset($dataSearch['lang']) && $dataSearch['lang']) {
                $filterFrameLangs = array_merge($filterFrameLangs, $dataSearch['lang']);
            }
            if (isset($dataSearch['framework']) && $dataSearch['framework']) {
                $filterFrameLangs = array_merge($filterFrameLangs, $dataSearch['framework']);
            }
            if ($filterFrameLangs) {
                //skill (tags)
                foreach ($filterFrameLangs as $tagData) {
                    if (!isset($tagData['tag_id']) || !$tagData['tag_id']) {
                        continue;
                    }
                    $collection->whereIn($empTbl.'.id', function ($subQuery) use ($empSkillTbl, $tagData) {
                        $subQuery->select('employee_id')
                            ->from($empSkillTbl)
                            ->where('tag_id', $tagData['tag_id'])
                            ->where(DB::raw('exp_y + exp_m / 12'), $tagData['compare'], $tagData['year']);
                    });
                }
            }
            //filter input language name
            if (isset($dataSearch['lang_input']['name']) && $dataSearch['lang_input']['name']) {
                $dataLangInput = $dataSearch['lang_input'];
                $collection->whereIn($empTbl.'.id', function ($subQuery) use ($empSkillTbl, $tagTbl, $dataLangInput) {
                        $subQuery->select('i_skill.employee_id')
                            ->from($empSkillTbl . ' as i_skill')
                            ->join($tagTbl . ' as i_tag', 'i_tag.id', '=', 'i_skill.tag_id')
                            ->where('i_skill.type', Skill::TYPE_PROGRAM)
                            ->where('i_tag.value', 'like', $dataLangInput['name'] . '%')
                            ->where(DB::raw('(i_skill.exp_y + i_skill.exp_m / 12)'), $dataLangInput['compare'], $dataLangInput['year'])
                            ->groupBy('i_skill.employee_id');
                    });
            }
            //filter foreign language
            if (isset($dataSearch['foreign']) && ($dataForeign = $dataSearch['foreign'])) {
                //filter japan
                if (isset($dataForeign['ja']['level']) && ($jaLevel = $dataForeign['ja']['level'])) {
                    $aryJaLevel = str_split($jaLevel);
                    if (isset($aryJaLevel[1])) {
                        //join employee cv attr
                        $collection->join($empCvAttrTbl.' as emp_attr_ja', function ($join) use ($empTbl) {
                            $join->on('emp_attr_ja.employee_id', '=', $empTbl.'.id')
                                    ->where('emp_attr_ja.code', '=', 'lang_ja_level');
                        })
                        ->where(function ($query) {
                            $query->whereNotNull('emp_attr_ja.value')
                                    ->where(DB::raw('TRIM(emp_attr_ja.value)'), '!=', '');
                        })
                        //N1 -> 4, N2 -> 3, N3 -> 2, N4 -> 1, N5 -> 0
                        ->where(DB::raw('0 - SUBSTRING(TRIM(emp_attr_ja.value), 2, 1)'), $dataForeign['ja']['compare'], 0 - (int) $aryJaLevel[1]);
                    }
                }
                //filter english
                if (isset($dataForeign['en']['level']) && ($enLevel = $dataForeign['en']['level'])) {
                    $aryEnLevel = explode(" ", $enLevel);
                    if (count($aryEnLevel) == 2) {
                        $enLevelType = $aryEnLevel[0]; //TOEIC, IELTS
                        $enLevelPoint = $aryEnLevel[1];//990, 9.0
                        //join employee cv attr
                        $collection->join($empCvAttrTbl.' as emp_attr_en', function ($join) use ($empTbl) {
                            $join->on('emp_attr_en.employee_id', '=', $empTbl.'.id')
                                    ->where('emp_attr_en.code', '=', 'lang_en_level');
                        })
                        ->where(function ($query) {
                            $query->whereNotNull('emp_attr_en.value')
                                    ->where(DB::raw('TRIM(emp_attr_en.value)'), '!=', '');
                        })
                        ->where(DB::raw('SUBSTRING_INDEX(TRIM(emp_attr_en.value), " ", 1)'), $enLevelType)
                        ->where(DB::raw('CAST(SUBSTRING_INDEX(TRIM(emp_attr_en.value), " ", -1) AS DECIMAL(4,1))'), $dataForeign['en']['compare'], $enLevelPoint);
                    }
                }
            }
            //filter name
            if (isset($dataSearch['name']) && ($filterName = $dataSearch['name'])) {
                $collection->where(function ($query) use ($empTbl, $filterName) {
                    $query->where($empTbl.'.name', 'like', '%' . $filterName . '%')
                            ->orWhere($empTbl.'.email', 'like', '%' . $filterName . '%');
                });
            }
            //filter employeeids
            if (isset($dataSearch['employee_ids']) && ($employeeIds = $dataSearch['employee_ids'])) {
                $collection->whereIn($empTbl.'.id', $employeeIds);
            }
            //filter default
            Employee::filterGrid($collection, [], $urlFilter);

            if (CoreForm::getFilterPagerData('order', $urlFilter)) {
                if ($pager['order'] == 'exper_year') {
                    $collection->orderBy(DB::raw('CAST(exp.value as DECIMAL(4,1))'), $pager['dir']);
                } else {
                    $collection->orderBy($pager['order'], $pager['dir']);
                }
            } else {
                $collection->orderBy('lang.exp_y', 'desc')
                        ->orderBy('lang.exp_m', 'desc');
            }
        } else {
            $collection->where($empTbl.'.id', -1);
        }
        //check is export return all
        if ($isExport) {
            return $collection->get();
        }
        Employee::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * separate skill language string to array
     * @param string $strLangs
     * @return string
     */
    public static function sepSkillLangs($strLangs = null, $isExport = false)
    {
        if (!$strLangs) {
            return '';
        }
        $collectLangs = self::getJsonAttribute($strLangs);
        if ($collectLangs->isEmpty()) {
            return '';
        }
        $outHtml = '<ul class="td-lists padding-left-15">';
        if ($isExport) {
            $outHtml = '';
        }
        foreach ($collectLangs as $index => $arrItem) {
            if ($isExport) {
                $outHtml .= $arrItem['name'] . ': ' . $arrItem['exp_ym'] . ' year' . "\r\n";
            } else {
                $outHtml .= '<li class="white-space-nowrap '. ($index == 0 ? 'text-red' : '') .'">'
                        . '<strong>'. e($arrItem['name']) .'</strong>: '
                        . '<span>'. $arrItem['exp_ym'] .' year</span>'
                        . '</li>';
            }
        }
        if ($isExport) {
            return trim($outHtml, "\r\n");
        }
        return $outHtml . '</ul>';
    }

    /**
     * render note list
     * @param collection $notes
     * @return string
     */
    public static function renderNotes($notes)
    {
        if ($notes->isEmpty()) {
            return '';
        }
        $output = '';
        foreach ($notes as $note) {
            $output .= CoreView::getNickName($note->email) . ': ' . $note->note . "\r\n";
        }
        return trim($output, "\r\n");
    }

    public static function replaceSymbolExcel($string)
    {
        return preg_replace('/\=(.*)/', '$1', $string);
    }

    /*
     * get list compare operators
     */
    public static function compareFilters()
    {
        return [
            '>=' => '>=',
            '>' =>  '>',
            '<=' => '<=',
            '<' =>  '<',
            '=' =>  '=',
            '!=' => '!='
        ];
    }

    /**
     * range years filter
     */
    public static function rangeYears()
    {
        return [
            '0' => '&nbsp;',
            '0.5' => '6 Month',
            '1' => '1 Year',
            '2' => '2 Year',
            '3' => '3 Year',
            '4' => '4 Year',
            '5' => '5 Year',
        ];
    }

    /**
     * list project in time of employee
     * @param integer $employeeId
     * @param string $fromDate
     * @param string $toDate
     */
    public static function getProjectsInTime($employeeId, $fromDate = null, $toDate = null)
    {
        $projTbl = Project::getTableName();
        $fromDate = $fromDate ? $fromDate : self::MIN_DATE;
        $toDate = $toDate ? $toDate : self::MAX_DATE;
        return Project::select(
            $projTbl.'.id',
            $projTbl.'.name',
            DB::raw('DATE(pjm.start_at) as start_date'),
            DB::raw('DATE(pjm.end_at) as end_date'),
            'pjm.effort'
        )
            ->join(ProjectMember::getTableName() . ' as pjm', function ($join) use ($projTbl) {
                $join->on('pjm.project_id', '=', $projTbl.'.id')
                    ->where('pjm.status', '=', ProjectMember::STATUS_APPROVED)
                    ->whereNull('pjm.deleted_at');
            })
            ->where(function ($query) use ($fromDate, $toDate) {
                //case 1: start_at <= fromDate <= end_at < toDate
                $query->where(function ($subQuery) use ($fromDate, $toDate) {
                    $subQuery->where('pjm.start_at', '<=', $fromDate)
                            ->where('pjm.end_at', '>=', $fromDate)
                            ->where('pjm.end_at', '<', $toDate);
                })
                //case 2: fromDate < start_at <= toDate <= end_at
                ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                    $subQuery->where('pjm.start_at', '>', $fromDate)
                            ->where('pjm.start_at', '<=', $toDate)
                            ->where('pjm.end_at', '>=', $toDate);
                })
                //case 3: fromDate < start_at <= end_at < toDate
                ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                    $subQuery->where('pjm.start_at', '>', $fromDate)
                            ->where('pjm.end_at', '<', $toDate);
                });
            })
            ->where('pjm.employee_id', $employeeId)
            ->groupBy($projTbl.'.id')
            ->orderBy('pjm.end_at', 'desc')
            ->get();
    }

    /**
     * filter employee data
     */
    public static function filterDateAttrs($item, $dataSearch = [])
    {
        $fromDate = isset($dataSearch['from_date']) ? $dataSearch['from_date'] : null;
        $toDate = isset($dataSearch['to_date']) ? $dataSearch['to_date'] : null;
        $results = ['projects' => collect()];
        if (!$item) {
            return $results;
        }
        //get json collect
        $collectProjs = self::getJsonAttribute($item->str_pjms);
        //filter dates
        self::filterDates($collectProjs, $fromDate, $toDate);
        $results['projects'] = $collectProjs->take(6);
        return $results;
    }

    /**
     * get array project to export
     */
    public static function getProjsExport($item, $dataSearch = [])
    {
        $resultFilter = self::filterDateAttrs($item, $dataSearch);
        $filterProjects = $resultFilter['projects'];
        if ($filterProjects->isEmpty()) {
            return null;
        }
        $out = '';
        foreach ($filterProjects as $idx => $pjm) {
            if ($idx < 5) {
                $out .= $pjm['name'] . ': ' . $pjm['start_at'] . ' --> ' . $pjm['end_at'] . ' ('. round($pjm['effort']) .'%)' . "\r\n";
            } else {
                $out .= '...';
            }
        }
        return trim($out, "\r\n");
    }

    /*
     * get attribute and convert to array
     */
    public static function getJsonAttribute($values)
    {
        if (!$values) {
            return collect();
        }
        return collect(json_decode($values, true));
    }

    public static function getByEmpIds($employeeIds = [])
    {
        if (!$employeeIds) {
            return [];
        }
        return self::whereIn('employee_id', $employeeIds)
                ->get()
                ->groupBy('employee_id');
    }

    /*
     * filter array collection
     */
    public static function filterDates(&$collect, $fromDate = null, $toDate = null)
    {
        if (!$fromDate && !$toDate) {
            return $collect;
        }
        $fromDate = $fromDate ? $fromDate : FreeEffort::MIN_DATE;
        $toDate = $toDate ? $toDate : FreeEffort::MAX_DATE;

        $collect = $collect->filter(function ($item) use ($fromDate, $toDate) {
            return ($item['start_at'] <= $fromDate && $item['end_at'] >= $fromDate && $item['end_at'] < $toDate) // case1
                || ($item['start_at'] > $fromDate && $item['start_at'] <= $toDate && $item['end_at'] >= $toDate) // case2
                || ($item['start_at'] > $fromDate && $item['end_at'] < $toDate); // case 3
        });
    }
}
