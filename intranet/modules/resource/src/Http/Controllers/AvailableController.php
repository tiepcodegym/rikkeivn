<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Resource\View\FreeEffort;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\EmployeeNote;
use Rikkei\Resource\Model\EmplAvailTask;
use Rikkei\Resource\Model\EmpAvailableData;
use Rikkei\Core\View\CacheHelper;
use Excel;

class AvailableController extends Controller
{
    public function _construct() {
        Breadcrumb::add(trans('resource::view.Employees available'), route('resource::available.index'));
        Menu::setActive('resource');
    }

    /*
     * render index
     */
    public function index(Request $request)
    {
        $urlFilter = $request->url() . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        //check first view
        $keyView = auth()->id() . '_view_' . $urlFilter;
        $dataSearch['has_search'] = CacheHelper::get($keyView);
        if (!$dataSearch['has_search']) {
            CacheHelper::put($keyView, 1, null, true, 1 * 60 * 60); //store one hour
        }
        $collectionModel = FreeEffort::getGridData($dataSearch);
        $employeeIds = $collectionModel->lists('id')->toArray();
        return view('resource::available.index', [
            'collectionModel' => $collectionModel,
            'languages' => Tag::getAllTagByCodes(['language']),
            'frameworks' => Tag::getAllTagByCodes(['framework']),
            'compares' => FreeEffort::compareFilters(),
            'rangeYears' => FreeEffort::rangeYears(),
            'teamList' => TeamList::toOption(null, true, false),
            'dataSearch' => $dataSearch,
            'currentUser' => Permission::getInstance()->getEmployee(),
            'isScopeCompany' => Permission::getInstance()->isScopeCompany(),
            'arrayNotes' => EmployeeNote::getNoteEmployees($employeeIds),
            'arrayTaskIds' => EmplAvailTask::getTaskEmployees($employeeIds),
            'permissExport' => Permission::getInstance()->isAllow(FreeEffort::ROUTE_EXPORT)
        ]);
    }

    /*
     * list project in time of employee
     */
    public function projectInTime(Request $request)
    {
        $employeeId = $request->get('employee_id');
        if (!$employeeId) {
            return response()->json('Invalid data!', 422);
        }
        $urlIndex = route('resource::available.index') . '/';
        $fromDate = CoreForm::getFilterData('search', 'from_date', $urlIndex);
        $toDate = CoreForm::getFilterData('search', 'to_date', $urlIndex);
        return FreeEffort::getProjectsInTime($employeeId, $fromDate, $toDate);
    }

    /*
     * save employee note
     */
    public function saveNote(Request $request)
    {
        $employeeId = $request->get('employee_id');
        if (!$employeeId) {
            return response()->json(trans('resource::message.Invalid input data'), 422);
        }
        $result = EmployeeNote::insertOrUpdate(
            $employeeId,
            $request->get('note')
        );
        if (isset($result['error'])) {
            return response()->json($result['message'], 500);
        }
        return $result;
    }

    /*
     * export results
     */
    public function export(Request $request)
    {
        if (!Permission::getInstance()->isAllow(FreeEffort::ROUTE_EXPORT)) {
            CoreView::viewErrorPermission();
        }

        $dataSearch = CoreForm::getFilterData('search', null, route('resource::available.index') . '/');
        $strEmployeeIds = $request->get('employee_ids');
        $employeeIds = $strEmployeeIds ? explode('-', $strEmployeeIds) : [];
        $dataSearch['employee_ids'] = $employeeIds;
        $dataSearch['has_search'] = 1;
        $collection = FreeEffort::getGridData($dataSearch, true);
        $rangeDate = self::strRangeDate($dataSearch);

        //export files
        $strRangeDate = '';
        if ($rangeDate) {
            $strRangeDate = str_replace('-', '', $rangeDate);
            $strRangeDate = str_replace('-->', '-', $strRangeDate);
        }
        $fileName = ($strRangeDate ? $strRangeDate . '_' : '') . 'Employees_Available_' . \Carbon\Carbon::now()->format('Ymd');
        Excel::create($fileName, function ($excel) use ($collection, $rangeDate, $dataSearch) {
            $excel->sheet('Available', function ($sheet) use ($collection, $rangeDate, $dataSearch) {
                $employeeIds = $collection->lists('id')->toArray();
                $arrayNotes = EmployeeNote::getNoteEmployees($employeeIds);

                $sheet->mergeCells('A1:J1');
                $rowHeader = [
                    'No.',
                    trans('resource::view.Employee code'),
                    trans('resource::view.Employee name'),
                    trans('resource::view.Email'),
                    trans('resource::view.Foreign language'),
                    trans('resource::view.Programing language'),
                    trans('resource::view.Project'),
                    trans('resource::view.Experience'),
                    trans('resource::view.Division'),
                    trans('resource::view.Note')
                ];

                $sheetData = [
                    [trans('resource::view.Employees available') . ' '. $rangeDate]
                ];
                array_push($sheetData, $rowHeader);

                foreach ($collection as $order => $item) {
                    $rowData = [
                        $order + 1,
                        FreeEffort::replaceSymbolExcel($item->employee_code),
                        FreeEffort::replaceSymbolExcel($item->name),
                        FreeEffort::replaceSymbolExcel($item->email),
                        FreeEffort::replaceSymbolExcel($item->lang_level),
                        FreeEffort::replaceSymbolExcel(FreeEffort::sepSkillLangs($item->str_langs, true)),
                        FreeEffort::replaceSymbolExcel(FreeEffort::getProjsExport($item, $dataSearch)),
                        FreeEffort::replaceSymbolExcel($item->exper_year),
                        FreeEffort::replaceSymbolExcel($item->team_names),
                        FreeEffort::replaceSymbolExcel((isset($arrayNotes[$item->id]) ? FreeEffort::renderNotes($arrayNotes[$item->id]) : ''))
                    ];
                    $sheetData[] = $rowData;
                }
                $sheet->getStyle('A2:J' . ($collection->count() + 2))->getAlignment()->applyFromArray([
                    'horizontal' => 'left',
                    'vertical' => 'top',
                    'wrap' => true
                ]);
                $sheet->fromArray($sheetData, null, 'A1', false, false);
                $sheet->setHeight([
                    1 => 30,
                    2 => 30
                ]);
                $sheet->cells('A1:J1', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setFontSize('18');
                    $cells->setValignment('center');
                });
                $sheet->setBorder('A2:J2', 'thin');
                $sheet->cells('A2:J2', function ($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#fcf8e3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
            });
        })->export('xlsx');
    }

    public static function strRangeDate($dataSearch = [])
    {
        $fromDate = isset($dataSearch['from_date']) ? $dataSearch['from_date'] : null;
        $toDate = isset($dataSearch['to_date']) ? $dataSearch['to_date'] : null;
        if ($fromDate && $toDate) {
            return $fromDate . ' --> ' . $toDate;
        }
        if ($fromDate && !$toDate) {
            return 'From-' . $fromDate;
        }
        if (!$fromDate && $toDate) {
            return 'To-' . $toDate;
        }
        return '';
    }

    /*
     * update data ajax
     */
    public function updateData()
    {
        $isUpdate = EmpAvailableData::cronUpdate();
        if (!$isUpdate) {
            return response()->json(trans('resource::message.An error occurred'), 500);
        }
        return response()->json(1);
    }
}

