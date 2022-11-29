<?php
/**
 * Created by PhpStorm.
 * User: quanhv
 * Date: 09/01/20
 * Time: 10:37
 */

namespace Rikkei\Education\Http\Services;

use Excel;
use Rikkei\Core\View\Form as FieldForm;
use Rikkei\Education\Model\EducationClass;
use Rikkei\Education\Model\EducationClassDetail;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;

class EmployeeService
{

    // Minute of one hours
    const HOURS = 60;

    /**
     * Get list employee is working
     *
     * @param $teamId
     * @param bool $isExport
     * @param null $urlFilter
     * @return \Rikkei\Team\Model\collection
     */
    public function getMembers($teamId, $isExport = false, $urlFilter = null)
    {
        $educationClassDetailTable = EducationClassDetail::getTableName();
        $educationClassShiftTable = EducationClassShift::getTableName();

        $dataSearch = FieldForm::getFilterData('search_employee', null, $urlFilter);

        $pager = Config::getPagerData(null);

        $collections = Team::getMemberGridData($teamId, Team::WORKING, null, ['return_builder' => true, 'isListPage' => true]);

        $collections = $collections->with(['getTeamOfEmployee']);

        if (isset($dataSearch['employee_code'])) {
            $collections = $collections->where('employees.employee_code', 'LIKE', '%' . trim($dataSearch['employee_code']) . '%');
        }

        if (isset($dataSearch['employee_name'])) {
            $collections = $collections->where('employees.name', 'LIKE', '%' . trim($dataSearch['employee_name']) . '%');
        }

        if (isset($dataSearch['employee_email'])) {
            $collections = $collections->where('employees.email', 'LIKE', '%' . trim($dataSearch['employee_email']) . '%');
        }

        $collections = $collections->with(['educationClassDetail' => function ($query) use ($dataSearch, $educationClassShiftTable) {
            if (isset($dataSearch['from_date'])) {
                $query->whereDate("{$educationClassShiftTable}.start_date_time", '>=', trim($dataSearch['from_date']));
            }

            // Search by to_date
            if (isset($dataSearch['to_date'])) {
                $query->whereDate("{$educationClassShiftTable}.end_date_time", '<=', trim($dataSearch['to_date']));
            }

        }]);

        if (isset($dataSearch['study_role']) || isset($dataSearch['from_date']) || isset($dataSearch['to_date'])) {
            $collections = $collections->whereHas('educationClassDetail', function ($query) use ($dataSearch, $educationClassDetailTable, $educationClassShiftTable) {
                if (isset($dataSearch['study_role']) && $dataSearch['study_role'] != Employee::ROLE_All) {
                    $query->where("{$educationClassDetailTable}.role", $dataSearch['study_role']);
                }

                // Set join table education_class_shifts
                $isJoinAgain = false;
                // Search by from_date
                if (isset($dataSearch['from_date'])) {
                    $query->join("{$educationClassShiftTable}", "{$educationClassShiftTable}.id", '=', "{$educationClassDetailTable}.shift_id")
                        ->whereDate("{$educationClassShiftTable}.start_date_time", '>=', trim($dataSearch['from_date']));
                    $isJoinAgain = true;
                }

                // Search by to_date
                if (isset($dataSearch['to_date'])) {
                    if (!$isJoinAgain) {
                        $query->join("{$educationClassShiftTable}", "{$educationClassShiftTable}.id", '=', "{$educationClassDetailTable}.shift_id");
                    }
                    $query->whereDate("{$educationClassShiftTable}.end_date_time", '<=', trim($dataSearch['to_date']));
                }
            });
        }

        // Set order by
        if (isset($pager['order'])) {
            $collections = $collections->orderBy('employees.id', $pager['dir']);
        }

        if ($isExport) {
            return $collections->get();
        }

        Team::pagerCollection($collections, $pager['limit'], $pager['page']);

        return $collections;
    }

    /**
     * Get data join training and join study of employee
     *
     * @param $request
     * @param $teaching
     * @return mixed
     */
    public function getListDataStudy($request, $teaching)
    {
        $educationClassDetailTable = EducationClassDetail::getTableName();
        $educationClassShiftTable = EducationClassShift::getTableName();
        $educationClassTable = EducationClass::getTableName();
        $educationCourseTable = EducationCourse::getTableName();
        $employeeTable = Employee::getTableName();

        $collection = EducationClassShift::with(['educationClassDetail' => function ($query) use ($teaching, $educationClassDetailTable) {
            $query->where("{$educationClassDetailTable}.role", Employee::ROLE_STUDENT);
        }])->whereHas('educationClassDetail', function ($query) use ($teaching, $request, $educationClassDetailTable) {
            $query->where("{$educationClassDetailTable}.employee_id", $request->employee_id)
                ->where("{$educationClassDetailTable}.role", $teaching);
        });

        $collection = $collection->join("{$educationClassTable} as edcl", 'edcl.id', '=', "{$educationClassShiftTable}.class_id")
            ->join("{$educationCourseTable} as edc", 'edc.id', '=', 'edcl.course_id')
            ->join("{$employeeTable} as emp", "emp.id", '=', 'edc.hr_id')
            ->select("{$educationClassShiftTable}.*",
                'edcl.class_name',
                'edc.name as courses_name',
                'emp.name as employee_name',
                \DB::raw("IF(edcl.start_date = '0000-00-00', NULL, edcl.start_date) as start_date"),
                \DB::raw("IF(edcl.end_date = '0000-00-00', NULL, edcl.end_date) as end_date")
            );


        if (!empty(trim($request->class_name))) {
            $collection = $collection->where('edcl.class_name', 'LIKE', '%' . trim($request->class_name) . '%');
        }

        if (!empty(trim($request->courses_name))) {
            $collection = $collection->where('edc.name', 'LIKE', '%' . trim($request->courses_name) . '%');
        }

        if (!empty(trim($request->from_date))) {
            $collection = $collection->whereDate("{$educationClassShiftTable}.start_date_time", '>=', trim($request->from_date));
        }

        if (!empty(trim($request->to_date))) {
            $collection = $collection->whereDate("{$educationClassShiftTable}.end_date_time", '<=', trim($request->to_date));
        }

        return $collection->get();
    }


    /**
     * Render datatable ajax
     *
     * @param $datatables
     * @param $collection
     * @param bool $isStudy
     * @return mixed
     */
    public function getDataTable($datatables, $collection, $isStudy = true)
    {
        $datatable = $datatables
            ->of($collection)
            ->editColumn('name', function ($model) use ($isStudy) {
                return trans('education::view.manager_employee.header_table.Shift') . $model->name;
            });

        if (json_decode($isStudy)) {
            $datatable = $datatable->editColumn('start_date_study', function ($model) {
                return date('d/m/Y', strtotime($model->start_date_time));
            })
                ->editColumn('start_date', function ($model) {
                    return is_null($model->start_date) ? null : date('d/m/Y', strtotime($model->start_date));
                })
                ->editColumn('end_date', function ($model) {
                    return is_null($model->end_date) ? null : date('d/m/Y', strtotime($model->end_date));
                })
                ->editColumn('count_class_study', function ($model) {
                    $to_time = strtotime($model->end_date_time);
                    $from_time = strtotime($model->start_date_time);
                    return (round(abs($to_time - $from_time) / 60 / 60, 2));
                });
        } else {
            $datatable = $datatable->editColumn('start_date_teaching', function ($model) {
                return date('d/m/Y', strtotime($model->start_date_time));
            })
                ->editColumn('number_teaching', function ($model) {
                    $to_time = strtotime($model->end_date_time);
                    $from_time = strtotime($model->start_date_time);
                    return (round(abs($to_time - $from_time) / 60 / 60, 2));
                })
                ->editColumn('number_student', function ($model) {
                    return count($model->educationClassDetail);
                })
                ->editColumn('point_average', function ($model) {
                    return $model->educationClassDetail->avg('feedback_company_point');
                });
        }

        $datatable = $datatable->make(true);
        return $datatable;
    }

    /**
     * Get employee detail
     *
     * @param $id
     * @return mixed
     */
    public function getEmployeeWorkingById($id)
    {
        return Employee::getEmployeeWorkingById($id);
    }

    /**
     * Export data
     *
     * @param $teamId
     * @param $urlFilter
     */
    public function exportTraining($teamId, $urlFilter)
    {
        $fileName = 'Rikkeisoft_Education';
        $sheetName = 'EducationSheet';
        $employees = $this->getMembers($teamId, true, $urlFilter);
        Excel::create($fileName, function ($excel) use ($employees, $sheetName, $urlFilter) {
            $excel->sheet($sheetName, function ($sheet) use ($employees, $urlFilter) {
                // Header row
                $rowHeader = [
                    trans('education::view.manager_employee.header_table.Code'),
                    trans('education::view.manager_employee.header_table.Name'),
                    trans('education::view.manager_employee.header_table.Email'),
                    trans('education::view.manager_employee.header_table.Team'),
                    trans('education::view.manager_employee.header_table.Position'),
                    trans('education::view.manager_employee.header_table.Leader'),
                    trans('education::view.manager_employee.header_table.Number class teaching'),
                    trans('education::view.manager_employee.header_table.Number hours teaching'),
                    trans('education::view.manager_employee.header_table.Number class study'),
                    trans('education::view.manager_employee.header_table.Number hours study')
                ];
                $sheet->row(1, $rowHeader);

                // Set width for columns
                $sheet->setWidth('A', 15);
                $sheet->setWidth('B', 30);
                $sheet->setWidth('C', 30);
                $sheet->setWidth('D', 40);
                $sheet->setWidth('E', 40);
                $sheet->setWidth('F', 20);

                foreach ($employees as $key => $employee) {

                    // Get employee team
                    $employeeTeam = $employee->getTeamOfEmployee->pluck('team_name')->toArray();

                    // Get leader of employee
                    $leaderNames = $employee->getLeaderOfTeam->pluck('name')->toArray();
                    $teamNames = count($employeeTeam) > 0 ? implode('; ', $employeeTeam) : null;

                    $dataEducationOfMember = [
                        'number_class_of_teacher' => 0,
                        'number_hours_of_teacher' => 0,
                        'number_class_of_member' => 0,
                        'number_hours_of_member' => 0,
                    ];

                    // Get data joint training and join study of employee
                    foreach ($employee->educationClassDetail as $item) {
                        if ($item->role == Employee::ROLE_STUDENT) {
                            $dataEducationOfMember['number_class_of_member'] = $item->count_study;
                            $dataEducationOfMember['number_hours_of_member'] = $item->sum_time;
                        }
                        if ($item->role == Employee::ROLE_TEACHER) {
                            $dataEducationOfMember['number_class_of_teacher'] = $item->count_study;
                            $dataEducationOfMember['number_hours_of_teacher'] = $item->sum_time;
                        }
                    }

                    // Set data row
                    $rowEmployee = [
                        $employee->employee_code,
                        $employee->name,
                        $employee->email,
                        $teamNames,
                        $employee->role_name,
                        implode('; ', $leaderNames),
                        $dataEducationOfMember['number_class_of_teacher'],
                        round($dataEducationOfMember['number_hours_of_teacher'] / self::HOURS, 2),
                        $dataEducationOfMember['number_class_of_member'],
                        round($dataEducationOfMember['number_hours_of_member'] / self::HOURS, 2)
                    ];

                    $sheet->row($key + 2, $rowEmployee);
                }
            });
        })->download('xlsx');
    }
}