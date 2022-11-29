<?php
/**
 * Created by PhpStorm.
 * User: quanhv
 * Date: 08/01/20
 * Time: 13:39
 */

namespace Rikkei\Education\Http\Services;

use Rikkei\Education\Http\Requests\Request;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Core\View\View;
use Excel;
use Carbon\Carbon;
use Lang;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Team;
class CertificateService
{
    const NO_EXPORT = false;
    const EXPORT = true;
    /**
     * Get list team
     *
     * @param $id
     * @return array
     */
    public static function getTeamTree($id)
    {

        if ($id === null) {
            $id = 0;
        }

        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'education::education.certificates.index';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            if (Permission::getInstance()->isScopeTeam(null, $route)) {
                $teamIdsAvailable = (array) Permission::getInstance()->getTeams();
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResonsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if ($teamIdsResonsibleByPqa) {
                foreach ($teamIdsResonsibleByPqa as $teamId) {
                    $teamIdsAvailable[] = $teamId->team_id;
                }
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            if (!$id) {
                $id = end($teamIdsAvailable);
            }
            //get team and all child avaliable
            $teamIdsChildAvailable = [];
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamPathTree = Team::getTeamPath();
                foreach ($teamIdsAvailable as $teamId) {
                    if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                        if (isset($teamPathTree[$teamId]['child'])) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]['child']);
                            $teamIdsChildAvailable = array_merge($teamIdsChildAvailable, $teamPathTree[$teamId]['child']);
                            unset($teamPathTree[$teamId]['child']);
                        }
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }
            if (!in_array($id, $teamIdsAvailable)) {
                View::viewErrorPermission();
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::find($id);
            }
        }

        return ['teamIdsAvailable' => $teamIdsAvailable, 'teamTreeAvailable' => $teamTreeAvailable, 'teamIdActive' => $id];
    }

    /**
     * @param bool $isExport
     * @param null $urlFilter
     * @return collections
     */
    public static function getEmployees($teamId, $isExport = false, $urlFilter = null){
        $pager = Config::getPagerData(null);
        $dataSearch = Form::getFilterData('search', null, $urlFilter);

        $collection = Employee::with(['employeeCerties' => function ($query) use ($dataSearch) {

            // Search by certificate name
            if (isset($dataSearch['name'])) {
                $query->where('employee_certies.name', 'LIKE', '%' . trim($dataSearch['name']) . '%');
            }

            // Search by status
            if (isset($dataSearch['status'])) {
                if ($dataSearch['status'] == Employee::STATUS_VALIDITY) {
                    $query->where(function ($query_child){
                        $query_child->where('employee_certies.end_at', '>=', Carbon::now())->orWhere('employee_certies.end_at', null);
                    });
                } elseif ($dataSearch['status'] == Employee::STATUS_INVALIDITY) {
                    $query->where('employee_certies.end_at', '<', Carbon::now());
                } elseif ($dataSearch['status'] == Employee::STATUS_NOT_INPUT) {
                    $query->where('employee_certies.end_at', null);
                }
            }

            // Search by certificate start_at
            if (isset($dataSearch['start_at'])) {
                $query->where('employee_certies.start_at', '>=', trim($dataSearch['start_at']));
            }
            // Search by certificate end_at
            if (isset($dataSearch['end_at'])) {
                $query->where('employee_certies.end_at', '<=', trim($dataSearch['end_at']));
            }

        }])->whereHas('employeeCerties', function ($query) use ($dataSearch){

            // Search by certificate name
            if (isset($dataSearch['name'])) {
                $query->where('employee_certies.name', 'LIKE', '%' . trim($dataSearch['name']) . '%');
            }

            // Search by status
            if (isset($dataSearch['status'])) {
                if ($dataSearch['status'] == Employee::STATUS_VALIDITY) {
                    $query->where(function ($query_child){
                        $query_child->where('employee_certies.end_at', '>=', Carbon::now())->orWhere('employee_certies.end_at', null);
                    });
                } elseif ($dataSearch['status'] == Employee::STATUS_INVALIDITY) {
                    $query->where('employee_certies.end_at', '<', Carbon::now());
                } elseif ($dataSearch['status'] == Employee::STATUS_NOT_INPUT) {
                    $query->where('employee_certies.end_at', null);
                }
            }

            // Search by certificate start_at
            if (isset($dataSearch['start_at'])) {
                $query->where('employee_certies.start_at', '>=', trim($dataSearch['start_at']));
            }
            // Search by certificate end_at
            if (isset($dataSearch['end_at'])) {
                $query->where('employee_certies.end_at', '<=', trim($dataSearch['end_at']));
            }
        })->whereHas('getTeamMember', function($query) use($dataSearch, $teamId) {

            // Search by team id
            if ($teamId != 0) {
                $query->where('team_id', $teamId);
            }
        });

        // is Export file
        if ($isExport){
            return $collection->get();
        }

        Employee::filterGrid($collection);
        Employee::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Export certificates
     *
     * @param $request
     * @param $urlFilter
     *
     * @return file
     */
    public static function exportCertificate($urlFilter, $dataSearch){

        $fileName = 'Rikkeisoft_Certificates';
        $sheetName = 'CertificateSheet';
        $id = $dataSearch['team_id'];

        $arrTeams = self::getTeamTree($id);

        $users = self::getEmployees( $arrTeams['teamIdActive'], self::EXPORT, $urlFilter);
        $employeeModel = new Employee();
        Excel::create($fileName, function ($excel) use ($users, $sheetName, $employeeModel, $urlFilter) {
            $excel->sheet($sheetName, function ($sheet) use ($users, $employeeModel, $urlFilter) {
                // Header row
                $rowHeader = [
                    trans('education::view.export.certificate.header.Certificate'),
                    trans('education::view.export.certificate.header.Level'),
                    trans('education::view.export.certificate.header.From'),
                    trans('education::view.export.certificate.header.To'),
                    trans('education::view.export.certificate.header.Link image'),
                ];
                $sheet->row(1, $rowHeader);
                // Set width for columns
                $sheet->setWidth('A', 50);
                $sheet->setWidth('B', 20);
                $sheet->setWidth('C', 20);
                $sheet->setWidth('D', 20);
                $sheet->setWidth('E', 40);
                $linkWeb = $_SERVER['HTTP_ORIGIN'];
                $indexStart = 2;
                foreach ($users as $key => $user){
                    $teams = $user->getTeamMember->pluck('name')->toArray();

                    $rowName = [
                        $user->name . ' - ' . implode(',', $teams)
                    ];

                    $sheet->mergeCells("A{$indexStart}:E{$indexStart}");
                    $sheet->cells("A{$indexStart}:E{$indexStart}", function ($cells) {
                        $cells->setBackground('#00c0ef');
                        $cells->setAlignment('left');
                        $cells->setValignment('center');
                    });/**/

                    // Set name employee to excel
                    $sheet->row($indexStart, $rowName);
                    // Set index row
                    $indexStart += 1;

                    $certificates = $user->employeeCerties;

                    foreach ($certificates as $index => $item){
                        $rowContent = [
                            $item->name,
                            $item->level,
                            $item->start_at,
                            $item->end_at,
                        ];
                        if ($item->image) {
                            $rowContent[5] = $linkWeb . "/storage" . $item->image;
                        }

                        $sheet->cells("B{$indexStart}", function ($cells) {
                            $cells->setAlignment('left');
                            $cells->setValignment('center');
                        });

                        // Set certificates to excel
                        $sheet->row($indexStart, $rowContent);
                        // Set index row
                        $indexStart += 1;
                    }
                }
            });
        })->download('xlsx');;
    }

}
