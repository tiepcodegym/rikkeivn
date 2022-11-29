<?php

namespace Rikkei\ManageTime\Http\Controllers;

use DB;
use Log;
use Lang;
use Exception;
use Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\View\ReportPermission;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Team;

class ReportController extends Controller
{

    public function index($filterDate = null, $country = 0)
    {
        if (!$filterDate) {
            $filterDate = date('Y-m');
        } else {
            $arrFilterDate = explode('-', $filterDate);
            $filterDate = $arrFilterDate[1] . '-' . $arrFilterDate[0];
        }

        Breadcrumb::add('HR');
        Breadcrumb::add('Manage time');
        Breadcrumb::add('Business trip');
        Breadcrumb::add('Report');
        Menu::setActive('HR');

        $teamsAvailable = [];
        if (ReportPermission::isScopeManageOfCompany()) {
            $teamsAvailable = [];
        } elseif (ReportPermission::isScopeManageOfTeam()) {
            $teamsAvailable = $this->getAllTeamsAllow();
            if (count($teamsAvailable) == 0) {
                View::viewErrorPermission();
            }
        } else {
            View::viewErrorPermission();
        }
        $collectionModel = BusinessTripRegister::getListManageReport($filterDate, $country, $teamsAvailable);
        // column 'number_days_business_trip' đang sai nếu dùng lại thì fix lại
        $params = [
            'collectionModel' => (new BusinessTripRegister())->processingBeforeRenderViewBusinessReport($collectionModel, Carbon::parse($filterDate)),
            'filterDate' => $filterDate,
            'selCountry' => $country,
            'roles' => \Rikkei\Team\Model\Role::getAllPosition(),
        ];
        return view('manage_time::report.report_list_report', $params);
    }

    public function export()
    {
        $filterDate = Input::get('filterDate', null);
        $country = Input::get('sel_country', 0);
        if (!$filterDate) {
            Log::error(trans('manage_time::export.Not found param') . ' [File:' . __FILE__ . '  - on line:' . __LINE__ . ']');
            return abort(404);
        }
        $teamsAvailable = [];
        if (ReportPermission::isScopeManageOfCompany()) {
            $teamsAvailable = [];
        } elseif (ReportPermission::isScopeManageOfTeam()) {
            $teamsAvailable = $this->getAllTeamsAllow();
            if (count($teamsAvailable) == 0) {
                View::viewErrorPermission();
            }
        } else {
            View::viewErrorPermission();
        }
        $collectionModel = BusinessTripRegister::getListManageReport($filterDate, $country, $teamsAvailable);
        $collectionModel = (new BusinessTripRegister())->processingBeforeRenderViewBusinessReport($collectionModel, Carbon::parse($filterDate), true);
        $arrFilterDate = explode('-', $filterDate);
        $filterDate = $arrFilterDate[1] . '/' . $arrFilterDate[0];

        $fileName = 'Business_trip_export_' . Carbon::now()->format('Y_m_d');
        Excel::create($fileName, function ($excel) use ($collectionModel, $filterDate) {
            $excel->sheet('Sheet 1', function ($sheet) use ($collectionModel, $filterDate) {
                $sheet->loadView('manage_time::export.report_business_trip', [
                    'collectionModel' => $collectionModel,
                    'filterDate' => $filterDate,
                ]);
            });
        })->download('xlsx');
    }

    /**
     * @return array  array teams
     */
    private function getAllTeamsAllow()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        $teamIdsAvailable = (array) ManageTimeCommon::getTeamIdIsScopeTeam($userCurrent->id, 'manage_time::timekeeping.manage.report');
        $teamIds = [];
        foreach ($teamIdsAvailable as $teamId) {
            ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId);
            $teamIds = array_merge($teamIds, [$teamId]);
        }
        return array_unique($teamIds);
    }

    /**
     * export number leave days of employee onsite
     * @param integer $year
     * @return void
     */
    public function exportLeaveDaysEmployeeOnsite($year) {
        $currentUser = Permission::getInstance()->getEmployee();
        if (!in_array($currentUser->email, ['hungnt2@rikkeisoft.com', 'xuanntl@rikkeisoft.com'])) {
            return redirect('/');
        }
        $dataExport = (new ManageTimeView())->statisticLeaveDaysEmployeeOnsite($year);
        Excel::create("Leave_day_employee_onsite_{$year}", function ($excel) use ($dataExport, $year) {
            $excel->sheet('Sheet 1', function ($sheet) use ($dataExport, $year) {
                $sheetData[] = ['STT', 'Mã NV', "Số ngày phép đi onsite năm {$year}"];
                $order = 0;
                foreach ($dataExport as $employee) {
                    $sheetData[] = [
                        ++$order,
                        $employee['employee_code'],
                        $employee['total_leave_days']
                    ];
                }
                $nEndRowData = count($sheetData);
                $sheet->cells("A1:C{$nEndRowData}", function ($cells) {
                    $cells->setAlignment('left');
                });
                $sheet->fromArray($sheetData, null, 'A1', false, false);
                $sheet->setHeight([1 => 30]);
                $sheet->setBorder('A1:C1', 'thin');
                $sheet->cells('A1:C1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#e06666');
                });
            });
            $excel->sheet('Sheet 2', function ($sheet) use ($dataExport) {
                $rowHeader = ['STT', 'Mã NV'];
                $sheetData[] = array_merge($rowHeader, range(1, 12));
                $order = 0;
                foreach ($dataExport as $employee) {
                    $rowData = [
                        ++$order,
                        $employee['employee_code'],
                    ];
                    foreach ($employee['leave_days'] as $numDayOffs) {
                        $rowData[] = $numDayOffs;
                    }
                    $sheetData[] = $rowData;
                }
                $nEndRowData = count($sheetData);
                $sheet->cells("A1:N{$nEndRowData}", function ($cells) {
                    $cells->setAlignment('left');
                });
                $sheet->fromArray($sheetData, null, 'A1', false, false);
                $sheet->setWidth([
                    'A' => 5, 'B' => 12,
                    'C' => 7, 'D' => 7, 'E' => 7, 'F' => 7, 'G' => 7, 'H' => 7,
                    'I' => 7, 'J' => 7, 'K' => 7, 'L' => 7, 'M' => 7, 'N' => 7,
                ]);
                $sheet->setHeight([1 => 30]);
                $sheet->setBorder('A1:N1', 'thin');
                $sheet->cells('A1:N1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#e06666');
                });
            });
        })->download('xlsx');
    }
}
