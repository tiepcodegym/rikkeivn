<?php

namespace Rikkei\Team\View;

use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeEducation;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\EmployeePolitic;
use Rikkei\Team\Model\EmployeeMilitary;
use Rikkei\Team\Model\EmployeeHealth;
use Rikkei\Team\Model\EmployeeHobby;
use Rikkei\Team\View\EmpLib;
use Rikkei\Team\Model\EmployeeSchool;
use Rikkei\Team\Model\QualityEducation;
use Rikkei\Team\Model\PartyPosition;
use Rikkei\Team\Model\UnionPosition;
use Rikkei\Team\Model\MilitaryPosition;
use Rikkei\Team\Model\MilitaryRank;
use Rikkei\Team\Model\MilitaryArm;
use Rikkei\Team\Model\Country;
use Rikkei\Team\Model\EmplCvAttrValue;
use Illuminate\Support\Facades\DB;
//use Rikkei\Team\Model\EmplCvAttrValueText;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Core\View\Form;
use  Rikkei\Resource\View\getOptions;

class ExportMember
{

    /*
     * define columns export
     */
    public static function columnsExport($options = [])
    {
        $empTbl = Employee::getTableName();
        $contactTbl = EmployeeContact::getTableName();
        $eduTbl = EmployeeEducation::getTableName();
        $teamTbl = 'team_table';
        $workTbl = EmployeeWork::getTableName();
        $politicTbl = EmployeePolitic::getTableName();
        $militaryTbl = EmployeeMilitary::getTableName();
        $healthTbl = EmployeeHealth::getTableName();
        $hobbyTbl = EmployeeHobby::getTableName();
        $roleTbl = 'role_table';
        $attrTbl = EmplCvAttrValue::getTableName();
        $pjmTbl = ProjectMember::getTableName();
        /*
         * column name => [title, is default, is column in db]
         */
        return [
            //employees
            $empTbl . '.employee_card_id' =>        ['tt' => trans('team::export.employee_card_id'), 'df' => 1, 't' => 'n'],
            $empTbl . '.employee_code' =>           ['tt' => trans('team::export.employee_code'), 'df' => 1],
            $empTbl . '.first_name' =>              [
                'tt' => trans('team::export.first_name'),
                'sl' => 'SUBSTRING(TRIM(' . $empTbl . '.name), 1, LOCATE(SUBSTRING_INDEX(TRIM(' . $empTbl . '.name), " ", -1), TRIM(' . $empTbl . '.name)) - 1)',
                'wch' => 20
            ],
            $empTbl . '.last_name' =>               [
                'tt' => trans('team::export.last_name'),
                'sl' => 'SUBSTRING_INDEX(TRIM(' . $empTbl . '.name), " ", -1)'
            ],
            $empTbl . '.name' =>                    ['tt' => trans('team::export.name'), 'df' => 1, 'wch' => 25],
            $empTbl . '.japanese_name' =>           ['tt' => trans('team::export.japanese_name')],
            $empTbl . '.gender' =>                  [
                'tt' => trans('team::export.gender'),
                'df' => 1,
                'sl_fc' => 'selectGender'
            ],
            $empTbl . '.birthday' =>                [
                'tt' => trans('team::export.birthday'),
                'df' => 1,
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            'cv_attrs.value' => [
                'as' => $attrTbl . '_skillsheet_status',
                'tt' => trans('team::export.skillsheet_status'),
                'sl_fc' => 'selectSkillsheetStatus'
            ],
            $pjmTbl . '.type' => [
                'as' => 'project_member_role',
                'tt' => trans('team::export.Project member role'),
                'sl_fc' => 'selectProjectMemberRole'
            ],
            /*'cv_roles.value' => [
                'as' => $attrTbl . '_skillsheet_roles',
                'tt' => trans('team::export.Response for')
            ],*/
            $empTbl . '.id_card_number' =>          ['tt' => trans('team::export.id_card_number'), 'df' => 1, 't' => 'n'],
            $empTbl . '.id_card_date' =>            [
                'tt' => trans('team::export.id_card_date'),
                'df' => 1,
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $empTbl . '.id_card_place' =>           ['tt' => trans('team::export.id_card_place'), 'df' => 1],
            $empTbl . '.passport_number' =>         ['tt' => trans('team::export.passport_number'), 'df' => 1],
            $empTbl . '.passport_date_start' =>     [
                'tt' => trans('team::export.passport_date_start'),
                'df' => 1,
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $empTbl . '.passport_date_exprie' =>    [
                'tt' => trans('team::export.passport_date_exprie'),
                'df' => 1,
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $empTbl . '.passport_addr' =>           ['tt' => trans('team::export.passport_addr'), 'df' => 1],
            $empTbl . '.marital' =>                 ['tt' => trans('team::export.marital'), 'df' => 1],
            $empTbl . '.folk' =>                    [
                'tt' => trans('team::export.folk'),
                'df' => 1,
                'sl_fc' => 'selectFolk'
            ],
            $empTbl . '.religion' =>                [
                'tt' => trans('team::export.religion'),
                'df' => 1,
                'sl_fc' => 'selectReligion'
            ],
            $empTbl . '.marital' =>                 [
                'tt' => trans('team::export.marital'),
                'df' => 1,
                'sl_fc' => 'selectMartial'
            ],

            //education
            $eduTbl . '.school' =>                    ['tt' => trans('team::export.education.school')],
            $eduTbl . '.faculty' =>                   ['tt' => trans('team::export.education.faculty')],
            $eduTbl . '.majors' =>                    ['tt' => trans('team::export.education.majors')],
            $eduTbl . '.end_at' =>                    [
                'tt' => trans('team::export.education.end_at'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $eduTbl . '.degree' =>                    [
                'tt' => trans('team::export.education.degree'),
                'sl_fc' => 'selectDegree',
            ],
            $eduTbl . '.quality' =>                   [
                'tt' => trans('team::export.education.quality'),
                'sl_fc' => 'selectEduQuality'
            ],
            //employee contact
            $contactTbl . '.office_phone' =>        ['tt' => trans('team::export.contact.office_phone'), 't' => 'n'],
            $empTbl . '.email' =>                   ['tt' => trans('team::export.email'), 'df' => 1, 'wch' => 36],
            $contactTbl . '.mobile_phone' =>        ['tt' => trans('team::export.contact.mobile_phone'), 't' => 'n'],
            $contactTbl . '.home_phone' =>          ['tt' => trans('team::export.contact.home_phone'), 't' => 'n'],
            $contactTbl . '.other_phone' =>         ['tt' => trans('team::export.contact.other_phone'), 't' => 'n'],
            $contactTbl . '.personal_email' =>      ['tt' => trans('team::export.contact.personal_email'), 'wch' => 36],
            $contactTbl . '.other_email' =>         ['tt' => trans('team::export.contact.other_email')],
            $contactTbl . '.skype' =>               ['tt' => trans('team::export.contact.skype')],
            $contactTbl . '.native_addr' =>         ['tt' => trans('team::export.contact.native_addr')],
            $contactTbl . '.native_country' =>      [
                'tt' => trans('team::export.contact.native_country'),
                'sl_fc' => 'selectTextCountry'
            ],
            $contactTbl . '.native_province' =>     ['tt' => trans('team::export.contact.native_province')],
            $contactTbl . '.native_district' =>     ['tt' => trans('team::export.contact.native_district')],
            $contactTbl . '.native_ward' =>         ['tt' => trans('team::export.contact.native_ward')],
            $contactTbl . '.tempo_addr' =>          ['tt' => trans('team::export.contact.tempo_addr')],
            $contactTbl . '.tempo_country' =>       [
                'tt' => trans('team::export.contact.tempo_country'),
                'sl_fc' => 'selectTextCountry'
            ],
            $contactTbl . '.tempo_province' =>      ['tt' => trans('team::export.contact.tempo_province')],
            $contactTbl . '.tempo_district' =>      ['tt' => trans('team::export.contact.tempo_district')],
            $contactTbl . '.tempo_ward' =>          ['tt' => trans('team::export.contact.tempo_ward')],
            $contactTbl . '.emergency_contact_name' => ['tt' => trans('team::export.contact.emergency_contact_name')],
            $contactTbl . '.emergency_relationship' => ['tt' => trans('team::export.contact.emergency_relationship'), 't' => 'n'],
            $contactTbl . '.emergency_contact_mobile' => ['tt' => trans('team::export.contact.emergency_contact_mobile'), 't' => 'n'],
            $contactTbl . '.emergency_mobile' =>    ['tt' => trans('team::export.contact.emergency_mobile'), 't' => 'n'],
            $contactTbl . '.emergency_addr' =>      ['tt' => trans('team::export.contact.emergency_addr')],
            // team
            $roleTbl . '.role' =>                     [
                'tt' => trans('team::export.team.position'),
                'sl' => 'GROUP_CONCAT(DISTINCT(' . $roleTbl . '.role) SEPARATOR "; ")'
            ],
            $teamTbl . '.name' =>                     [
                'tt' => trans('team::export.team.team_code'),
                'sl' => 'GROUP_CONCAT(DISTINCT(' . $teamTbl . '.name) SEPARATOR "; ")'
            ],
            $empTbl . '.trial_date' =>              [
                'tt' => trans('team::export.trial_date'),
                'df' => 1,
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $empTbl . '.offcial_date' =>            [
                'tt' => trans('team::export.official_date'),
                'df' => 1,
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $empTbl . '.join_date' =>               [
                'tt' => trans('team::export.join_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            //work
            $workTbl . '.tax_code' =>                 ['tt' => trans('team::export.work.tax_code')],
            $workTbl . '.bank_account' =>             ['tt' => trans('team::export.work.bank_account'), 't' => 'n'],
            $workTbl . '.bank_name' =>                ['tt' => trans('team::export.work.bank_name')],
            $workTbl . '.contract_type' =>            [
                'tt' => trans('team::export.work.contract_type'),
                'sl_fc' => 'selectContractType'
            ],
            $workTbl . '.insurrance_book' =>          ['tt' => trans('team::export.work.insurrance_book'), 't' => 'n'],
            $workTbl . '.insurrance_date' =>          [
                'tt' => trans('team::export.work.insurrance_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $workTbl . '.insurrance_ratio' =>         ['tt' => trans('team::export.work.insurrance_ratio'), 't' => 'n', 'fm' => '0.00'],
            $workTbl . '.insurrance_h_code' =>        ['tt' => trans('team::export.work.insurrance_h_code')],
            $workTbl . '.insurrance_h_expire' =>      [
                'tt' => trans('team::export.work.insurrance_h_expire'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $workTbl . '.register_examination_place' => ['tt' => trans('team::export.work.register_examination_place')],
            $empTbl . '.trial_end_date' =>          [
                'tt' => trans('team::export.trial_end_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $empTbl . '.leave_date' =>              [
                'tt' => trans('team::export.leave_date'),
                'df' => 1,
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $empTbl . '.leave_reason' =>            ['tt' => trans('team::export.leave_reason')],

            //politic
            $politicTbl . '.is_party_member' =>       [
                'tt' => trans('team::export.politic.is_party_member'),
                'sl_fc' => 'selectBoolean'
            ],
            $politicTbl . '.party_join_date' =>       [
                'tt' => trans('team::export.politic.party_join_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $politicTbl . '.party_position' =>        [
                'tt' => trans('team::export.politic.party_position'),
                'sl_fc' => 'selectPartyPos'
            ],
            $politicTbl . '.party_join_place' =>      ['tt' => trans('team::export.politic.party_join_place')],
            $politicTbl . '.is_union_member' =>       [
                'tt' => trans('team::export.politic.is_union_member'),
                'sl_fc' => 'selectBoolean'
            ],
            $politicTbl . '.union_join_date' =>       [
                'tt' => trans('team::export.politic.union_join_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $politicTbl . '.union_poisition' =>       [
                'tt' => trans('team::export.politic.union_poisition'),
                'sl_fc' => 'selectUnionPos'
            ],
            $politicTbl . '.union_join_place' =>      ['tt' => trans('team::export.politic.union_join_place')],
            //military
            $militaryTbl . '.is_service_man' =>       [
                'tt' => trans('team::export.military.is_service_man'),
                'sl_fc' => 'selectBoolean'
            ],
            $militaryTbl . '.join_date' =>            [
                'tt' => trans('team::export.military.join_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $militaryTbl . '.position' =>             [
                'tt' => trans('team::export.military.position'),
                'sl_fc' => 'selectMilitaryPos'
            ],
            $militaryTbl . '.rank' =>                 [
                'tt' => trans('team::export.military.rank'),
                'sl_fc' => 'selectMilitaryRank'
            ],
            $militaryTbl . '.arm' =>                  [
                'tt' => trans('team::export.military.arm'),
                'sl_fc' => 'selectMilitaryArm'
            ],
            $militaryTbl . '.branch' =>               ['tt' => trans('team::export.military.branch')],
            $militaryTbl . '.left_date' =>            [
                'tt' => trans('team::export.military.left_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $militaryTbl . '.left_reason' =>          ['tt' => trans('team::export.military.left_reason')],
            $militaryTbl . '.is_wounded_soldier' =>   [
                'tt' => trans('team::export.military.is_wounded_soldier'),
                'sl_fc' => 'selectBoolean'
            ],
            $militaryTbl . '.revolution_join_date' => [
                'tt' => trans('team::export.military.revolution_join_date'),
                'sl_fc' => 'selectDateFormat',
                't' => 'd'
            ],
            $militaryTbl . '.wounded_soldier_level' => [
                'tt' => trans('team::export.military.wounded_soldier_level'),
                'sl_fc' => 'selectMilitaryLevel'
            ],
            $militaryTbl . '.num_disability_rate' =>  ['tt' => trans('team::export.military.num_disability_rate')],
            $militaryTbl . '.is_martyr_regime' =>     [
                'tt' => trans('team::export.military.is_martyr_regime'),
                'sl_fc' => 'selectBoolean'
            ],
            //health
            $healthTbl . '.blood_type' =>             ['tt' => trans('team::export.health.blood_type')],
            $healthTbl . '.height' =>                 ['tt' => trans('team::export.health.height')],
            $healthTbl . '.weigth' =>                 ['tt' => trans('team::export.health.weigth')],
            $healthTbl . '.health_status' =>          ['tt' => trans('team::export.health.health_status')],
            $healthTbl . '.health_note' =>            ['tt' => trans('team::export.health.health_note')],
            $healthTbl . '.is_disabled' =>            [
                'tt' => trans('team::export.health.is_disabled'),
                'sl_fc' => 'selectBoolean'
            ],
            //hobby
            $hobbyTbl . '.personal_goal' =>           ['tt' => trans('team::export.hobby.personal_goal')],
            $hobbyTbl . '.hobby' =>                   ['tt' => trans('team::export.hobby.hobby')],
            $hobbyTbl . '.forte' =>                   ['tt' => trans('team::export.hobby.forte')],
            $hobbyTbl . '.weakness' =>                ['tt' => trans('team::export.hobby.weakness')],
        ];
    }

    /*
     * get list columns heading
     */
    public static function getColsHeading($columns)
    {
        $results = [];
        $columnsExport = self::columnsExport();
        foreach ($columns as $key) {
            if (!isset($columnsExport[$key])) {
                continue;
            }
            $arrKeys = explode('.', $key);
            $asName = isset($columnsExport[$key]['as']) ? $columnsExport[$key]['as'] : implode('_', $arrKeys);
            $results[$asName] = [
                'tt' => $columnsExport[$key]['tt'], //title
                'wch' => isset($columnsExport[$key]['wch']) ? $columnsExport[$key]['wch'] : strlen($columnsExport[$key]['tt']), //col width (ch)
                't' => isset($columnsExport[$key]['t']) ? $columnsExport[$key]['t'] : 's', // data type,
                'fm' => isset($columnsExport[$key]['fm']) ? $columnsExport[$key]['fm'] : null, // format cell
            ];
        }
        return $results;
    }

    /*
     * filter select post data columns
     */
    public static function filterSelectCols($columns)
    {
        $results = [];
        $colsExport = self::columnsExport();
        foreach ($columns as $col) {
            if (!isset($colsExport[$col])) {
                continue;
            }
            $arrKeys = explode('.', $col);
            $asName = isset($colsExport[$col]['as']) ? $colsExport[$col]['as'] : implode('_', $arrKeys);
            if (isset($colsExport[$col]['sl'])) {
                $results[] = DB::raw($colsExport[$col]['sl'] . ' AS ' . $asName);
            } elseif (isset($colsExport[$col]['sl_fc'])) {
                $results[] = DB::raw(call_user_func(__NAMESPACE__ . '\ExportMember::' . $colsExport[$col]['sl_fc'], $col) . ' AS ' . $asName);
            } else {
                $results[] = $col . ' AS ' . $asName;
            }
        }
        return $results;
    }

    /*
     * get data to export excel
     */
    public static function getDataExport($data)
    {
        $urlFilter = isset($data['urlFilter']) ? $data['urlFilter'] : null;
        $statusWork = isset($data['statusWork']) ? $data['statusWork'] : null;
        $teamId = isset($data['teamIdCurrent']) ? $data['teamIdCurrent'] : null;
        switch ($statusWork) {
            case 'leave':
                $statusWork = Team::END_WORK;
                break;
            case 'all':
                $statusWork = null;
                break;
            default: // work
                $statusWork = Team::WORKING;
                break;
        }
        $colsSelect = self::filterSelectCols($data['columns']);
        //permission
        $teamIds = null;
        $scopeRoute = 'team::team.member.index';
        if (Permission::getInstance()->isScopeCompany(null, $scopeRoute)) {
            $teamIds = $teamId;
        } elseif ($scopeTeamIds = Permission::getInstance()->isScopeTeam(null, $scopeRoute)) {
            $scopeTeamIds = is_array($scopeTeamIds) ? $scopeTeamIds : [];
            if ($teamId) {
                $teamIds = array_map(function ($item) {
                    return trim($item);
                }, explode(',', $teamId));
                $teamIds = array_intersect($teamIds, $scopeTeamIds);
            } else {
                $teamIds = $scopeTeamIds;
            }
            $teamIds = implode(',', $teamIds);
        } else {
            return \Rikkei\Core\View\View::viewErrorPermission();
        }
        $collection = Team::getMemberGridData(
            $teamIds,
            $statusWork,
            $urlFilter,
            ['select' => $colsSelect, 'return_builder' => true, 'isListPage' => true]
        );
        $empTbl = Employee::getTableName();
        $arrTblJoins = [
            EmployeeContact::getTableName(),
            EmployeeEducation::getTableName(),
            EmployeePolitic::getTableName(),
            EmployeeMilitary::getTableName(),
            EmployeeHealth::getTableName(),
            EmployeeHobby::getTableName()
        ];
        foreach ($arrTblJoins as $tbl) {
            $collection->leftJoin($tbl, $empTbl . '.id', '=', $tbl . '.employee_id');
        }
        $attrTbl = EmplCvAttrValue::getTableName();
        if (in_array('cv_attrs.value', $data['columns'])) {
            $collection->leftJoin($attrTbl . ' as cv_attrs', function ($join) use ($empTbl) {
                $join->on($empTbl . '.id', '=', 'cv_attrs.employee_id')
                    ->where('cv_attrs.code', '=', 'status');
            });
        }

        if (in_array('project_members.type', $data['columns'])) {
            $pjmTbl = ProjectMember::getTableName();
            $filterMemberRole = Form::getFilterData('except', 'member_role', $urlFilter);
            $mapProjRoles = ProjectMember::mapProjMemberRoles();
            $collection->leftJoin($pjmTbl, function ($join) use ($pjmTbl, $empTbl, $mapProjRoles, $filterMemberRole) {
                $join->on($pjmTbl . '.employee_id', '=', $empTbl . '.id')
                    ->where($pjmTbl . '.status', '=', ProjectMember::STATUS_APPROVED)
                    ->where($pjmTbl . '.is_disabled', '!=', ProjectMember::STATUS_DISABLED);
                if ($filterMemberRole) {
                    $join->where($pjmTbl . '.type', '=', isset($mapProjRoles[$filterMemberRole]) ? $mapProjRoles[$filterMemberRole] : $filterMemberRole);
                }
            });
            if ($filterMemberRole == getOptions::ROLE_SQA) {
                $collection->leftJoin(\Rikkei\Team\Model\TeamMember::getTableName() . ' as ft_role_tmb', 'ft_role_tmb.employee_id', '=', $empTbl . '.id')
                    ->leftJoin(Team::getTableName() . ' as ft_role_team', function ($join) {
                        $join->on('ft_role_team.id', '=', 'ft_role_tmb.team_id')
                            ->where('ft_role_team.code', '=', TeamConst::CODE_HN_QA);
                    });
            }
            $collection->leftJoin($attrTbl . ' as attr_role', function ($join) use ($empTbl, $filterMemberRole) {
                $join->on('attr_role.employee_id', '=', $empTbl . '.id')
                    ->where('attr_role.code', '=', 'role')
                    ->where('attr_role.value', 'LIKE', '%"' . $filterMemberRole . '"%');
            });
            if ($filterMemberRole) {
                $collection->where(function ($query) use ($pjmTbl, $filterMemberRole) {
                    $query->whereNotNull($pjmTbl . '.employee_id')
                        ->orWhereNotNull('attr_role.employee_id');
                    if ($filterMemberRole == getOptions::ROLE_SQA) {
                        $query->orWhereNotNull('ft_role_team.id');
                    }
                });
            };
        }

        /*if (in_array('cv_roles.value', $data['columns'])) {
            $attrTbl = EmplCvAttrValue::getTableName();
            $collection->leftJoin($attrTbl . ' as cv_roles', function ($join) use ($empTbl) {
                $join->on($empTbl.'.id', '=', 'cv_roles.employee_id')
                        ->where('cv_roles.code', '=', 'role');
            });
        }*/

        //sort order
        $pager = Config::getPagerData($urlFilter);
        if (Form::getFilterPagerData('order', $urlFilter)) {
            $fieldsOrderByEmp = ['employee_code', 'name', 'email', 'join_date', 'offcial_date', 'leave_date'];
            $orderExceptRules = ['role_name' => DB::raw('CONCAT(role_table.role, " - ", team_table.name)')];
            $orderBy = $pager['order'];
            if (isset($orderExceptRules[$orderBy])) {
                $orderBy = $orderExceptRules[$orderBy];
            } elseif (in_array($orderBy, $fieldsOrderByEmp)) {
                $orderBy = $empTbl . '.' . $orderBy;
            } else {
                //
            }
            $collection->orderBy($orderBy, $pager['dir']);
        } else {
            $collection->orderBy("{$empTbl}.created_at", 'desc')
                ->orderBy("{$empTbl}.join_date", 'desc');
        }

        $exportAll = isset($data['export_all']) ? $data['export_all'] : true;
        if (!$exportAll) {
            $itemIds = isset($data['itemsChecked']) ? $data['itemsChecked'] : '';
            $collection->whereIn($empTbl . '.id', explode(',', $itemIds));
        }
        return $collection->get();
    }

    /*
     * label gender
     */
    public static function labelGender()
    {
        return [
            Employee::GENDER_MALE => trans('team::export.gender_male'),
            Employee::GENDER_FEMALE => trans('team::export.gender_female')
        ];
    }

    /*
     * list text to boolean
     */
    public static function textBoolean()
    {
        return [
            0 => trans('team::export.boolean_no'),
            1 => trans('team::export.boolean_yes')
        ];
    }

    /*
     * sql select text gender
     */
    public static function selectGender($col = null)
    {
        if (!$col) {
            $col = Employee::getTableName() . '.gender';
        }
        $listGenders = self::labelGender();
        return self::selectCase($col, $listGenders);
    }

    /*
     * sql select text martial
     */
    public static function selectMartial($col = null)
    {
        if (!$col) {
            $col = Employee::getTableName() . '.martial';
        }
        $listMartials = Employee::labelMarital();
        return self::selectCase($col, $listMartials);
    }

    /*
     * sql select text folk
     */
    public static function selectFolk($col = null)
    {
        if (!$col) {
            $col = Employee::getTableName() . '.folk';
        }
        $listFolks = EmpLib::getInstance()->folk();
        return self::selectCase($col, $listFolks);
    }

    /*
     * sql select text religion
     */
    public static function selectReligion($col = null)
    {
        if (!$col) {
            $col = Employee::getTableName() . '.religion';
        }
        $listReligs = EmpLib::getInstance()->relig();
        return self::selectCase($col, $listReligs);
    }

    /*
     * sql select text contract type
     */
    public static function selectContractType($col = null)
    {
        if (!$col) {
            $col = EmployeeWork::getTableName() . '.contract_type';
        }
        $contractTypes = EmployeeWork::getAllTypeContract();
        return self::selectCase($col, $contractTypes);
    }

    /*
     * sql select text degree
     */
    public static function selectDegree($col = null)
    {
        if (!$col) {
            $col = EmployeeEducation::getTableName() . '.degree';
        }
        $listDegree = EmployeeSchool::listDegree();
        return self::selectCase($col, $listDegree);
    }

    /*
     * sql select text education quality
     */
    public static function selectEduQuality($col = null)
    {
        if (!$col) {
            $col = EmployeeEducation::getTableName() . '.quality';
        }
        $listQuality = QualityEducation::getAll();
        return self::selectCase($col, $listQuality);
    }

    /*
     * sql select text party position
     */
    public static function selectPartyPos($col)
    {
        return self::selectCase($col, PartyPosition::getAll());
    }

    /*
     * sql select text union position
     */
    public static function selectUnionPos($col)
    {
        return self::selectCase($col, UnionPosition::getAll());
    }

    /*
     * sql select text military position
     */
    public static function selectMilitaryPos($col)
    {
        return self::selectCase($col, MilitaryPosition::getAll());
    }

    /*
     * sql select text military rank
     */
    public static function selectMilitaryRank($col)
    {
        return self::selectCase($col, MilitaryRank::getAll());
    }

    /*
     * sql select text military arm
     */
    public static function selectMilitaryArm($col)
    {
        return self::selectCase($col, MilitaryArm::getAll());
    }

    /*
     * sql select text military level
     */
    public static function selectMilitaryLevel($col)
    {
        return self::selectCase($col, EmployeeMilitary::toOptionSoldierLevel());
    }

    /*
     * sql select text country
     */
    public static function selectTextCountry($col)
    {
        return self::selectCase($col, Country::getAll());
    }

    /*
     * sql select date format Y-m-d
     */
    public static function selectDateFormat($col)
    {
        return 'DATE_FORMAT(' . $col . ', "%Y-%m-%d")';
    }

    /*
     * sql select text boolean
     */
    public static function selectBoolean($col)
    {
        return self::selectCase($col, self::textBoolean());
    }

    /*
     * sql select text skillsheet status
     */
    public static function selectSkillsheetStatus($col)
    {
        return self::selectCase($col, EmplCvAttrValue::getValueStatus(), 'UnSubmitted');
    }

    /*
     * sql select text project member type
     */
    public static function selectProjectMemberRole($col)
    {
        $caseQuery = self::selectCase($col, ProjectMember::getTypeMember(true));
        $queryProjectRole = 'CONCAT("p-", GROUP_CONCAT(DISTINCT(' . $caseQuery . ') SEPARATOR "; "))';
        $queryLeaderRole = 'CONCAT("t-", GROUP_CONCAT(DISTINCT(IF(role_table.id = ' . Team::ROLE_MEMBER . ', NULL, role_table.role)) SEPARATOR "; "))';
        $queryAttrRole = 'CONCAT("a-", attr_role.value)';
        return 'CONCAT_WS("; ", ' . $queryLeaderRole . ', ' . $queryProjectRole . ', ' . $queryAttrRole . ')';
    }

    /*
     * sql switch case by array
     */
    public static function selectCase($col, $list, $default = null)
    {
        $sql = 'CASE ';
        foreach ($list as $key => $label) {
            $sql .= 'WHEN ' . $col . ' = "' . $key . '" THEN "' . $label . '" ';
        }
        if ($default) {
            $sql .= ' ELSE "' . $default . '"';
        }
        return $sql . ' END';
    }

    /**
     * set name roles in dataExport
     * @param [collection] $dataExport
     */
    public static function setNameRoles($dataExport)
    {
        $dataRoles = getOptions::getInstance()->getRoles(true);
        $dataExport = $dataExport->map(function ($item) use ($dataRoles) {
            if (!$item->project_member_role) {
                return $item;
            }
            $arrRoles = explode('; ', $item->project_member_role);
            $resultRoles = [];
            foreach ($arrRoles as $strRole) {
                preg_match_all('/p-(.*)/', $strRole, $matchProj); // project member
                if (isset($matchProj[1][0])) {
                    if (!in_array($matchProj[1][0], $resultRoles)) {
                        $resultRoles[] = $matchProj[1][0];
                    }
                    continue;
                }
                preg_match_all('/a-(.*)/', $strRole, $matchAttrRole); // skillsheet role
                if (isset($matchAttrRole[1][0])) {
                    $strAttrRole = $matchAttrRole[1][0];
                    if (is_numeric($strAttrRole)) {
                        $arrAttrRoles = [$strAttrRole];
                    } else {
                        $arrAttrRoles = json_decode($strAttrRole, true);
                    }
                    foreach ($arrAttrRoles as $roleId) {
                        if (isset($dataRoles[$roleId]) && !in_array($dataRoles[$roleId], $resultRoles)) {
                            $resultRoles[] = $dataRoles[$roleId];
                        }
                    }
                    continue;
                }
                preg_match_all('/t-(.*)/', $strRole, $matchTeamRole); // team member
                if (isset($matchTeamRole[1][0])) {
                    if (!in_array($matchTeamRole[1][0], $resultRoles)) {
                        $resultRoles[] = $matchTeamRole[1][0];
                    }
                    continue;
                }
            }
            $item->project_member_role = implode('; ', $resultRoles);
            return $item;
        });
        return $dataExport;
    }
}
