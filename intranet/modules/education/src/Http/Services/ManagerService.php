<?php

namespace Rikkei\Education\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\User;
use Rikkei\Core\View\Breadcrumb;
use Lang;
use Rikkei\Education\Model\EducationClass;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\Education\Model\EducationTeacherWithout;
use Rikkei\Education\Model\EducationClassDetail;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Team;
use Rikkei\Education\Model\EducationRequest;
use Rikkei\Education\Model\EducationType;
use Rikkei\Education\Model\Status;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\TeamList;
use Carbon\Carbon;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\PqaResponsibleTeam;
use DB;
use Excel;
use Rikkei\Core\View\View;
use Auth;

class ManagerService
{

    public function getInfo()
    {
        $urlFilter = 'education::education.list';
        $url = URL::route('education::education.list') . '/';
        if (Permission::getInstance()->isScopeCompany(null, $urlFilter) || Permission::getInstance()->isScopeTeam(null, $urlFilter)) {
            Breadcrumb::add(Lang::get('education::view.list'), route('education::education.list'));
            $dataSearch = CoreForm::getFilterData('search', null, $url);
            $collectionModel = $this->getEducationRequestGridData($dataSearch, $url);
            $employee = Permission::getInstance()->getEmployee();
            $teamPath = Team::getTeamPathTree();
            $location = $this->getLocation();
            $education = new EducationCourse();
            $hrAssign = $this->getHrAssign();
            $teamIdsAvailable = $this->getTeamId();
            $teamSelected = [];
            return view('education::manager-courses.list', [
                'taskStatus' => Status::statusLabel(),
                'employee' => $employee,
                'types' => $this->getType(),
                'teamPath' => $teamPath,
                'education' => $education,
                'teamSelected' => $teamSelected,
                'collectionModel' => $collectionModel,
                'location' => $location,
                'hrAssign' => $hrAssign,
                'teamIdsAvailable' => $teamIdsAvailable,
                'teamsOptionAll' => TeamList::toOption(null, true, false),
            ]);
        }
        return view('core::errors.permission_denied');
    }

    public function getProfileList()
    {
        Breadcrumb::add(Lang::get('education::view.education_profile_list'), route('education::profile.profileList'));
        $urlFilter = URL::route('education::profile.profileList') . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        $collectionModel = $this->getEducationRequestGridDataProfile($urlFilter, $dataSearch);
        $employee = Permission::getInstance()->getEmployee();
        $teamPath = Team::getTeamPathTree();
        $location = $this->getLocation();
        $education = new EducationCourse();
        $teamSelected = [];

        return view('education::manager-courses.profileList', [
            'taskStatus' => Status::statusLabel(),
            'employee' => $employee,
            'types' => $this->getType(),
            'teamPath' => $teamPath,
            'education' => $education,
            'teamSelected' => $teamSelected,
            'collectionModel' => $collectionModel,
            'location' => $location,
            'roles' => Status::roleLabel(),
            'teamIdsAvailable' => true,
            'teamsOptionAll' => TeamList::toOption(null, true, false),
        ]);
    }

    public static function getNameTeacher($related_name, $related_id)
    {
        if ($related_name && $related_id) {
            if ($related_name == EducationCourse::RELATED_ID) {
                $educationClass = EducationClass::getTableName();
                $educationTeacher = EducationTeacherWithout::getTableName();
                $data = EducationTeacherWithout::join("{$educationClass}", "{$educationTeacher}.id", '=', "{$educationClass}.related_id")
                    ->where('education_teacher_withouts.id', (int)$related_id)
                    ->select("{$educationTeacher}.id", "{$educationTeacher}.name")
                    ->first();
                if ($data && count($data)) {
                    return $data->name;
                }
            } else {
                $employee = Employee::getTableName();
                $data = Employee::where('id', (int)$related_id)
                    ->select(
                        "{$employee}.name"
                    )
                    ->first();
                if ($data) {
                    return $data->name;
                }
                return '';
            }
        }
    }

    public static function countEmployee($shift_id)
    {
        $educationClassDetail = EducationClassDetail::getTableName();

        return EducationClassDetail::where('shift_id', $shift_id)
            ->select(
                "{$educationClassDetail}.employee_id"
            )
            ->count('employee_id');
    }

    public function getEducationRequestGridData($dataSearch = [], $urlFilter, $isExport = false)
    {
        $pager = Config::getPagerData($urlFilter, ['limit' => 20]);
        $collection = EducationCourse::with(['employee', 'classes', 'classes.classDetails', 'classes.classDetails.shift', 'classes.classShift']);
        $collection = $this->_filterDataTable($dataSearch, $collection);
        // check location
        if (!empty($dataSearch['location']) && isset($dataSearch['location'])) {
            $collection = $collection->whereHas('classes.classShift', function ($query) use ($dataSearch) {
                $query->where("location_name", "like", "%{$dataSearch['location']}%");
            });
        }
        // search giang vien
        if (!empty($dataSearch['giangvien']) && isset($dataSearch['giangvien'])) {
            $collection = $collection->whereHas('classes', function ($query) use ($dataSearch) {
                $query->whereHas('employee', function ($query) use ($dataSearch) {
                    $query->where('name', 'like', "%{$dataSearch['giangvien']}%");
                })
                    ->orWhereHas('teacher', function ($query) use ($dataSearch) {
                        $query->where('name', 'like', "%{$dataSearch['giangvien']}%");
                    });
            });
        }
        //check nguoi phu trach
        if (!empty($dataSearch['hr_id']) && isset($dataSearch['hr_id'])) {
            $collection->where("hr_id", (int)$dataSearch['hr_id']);
        }
        $collection = $this->_getInFoDataSearch($dataSearch, $collection);

        $collection->orderBy('id', 'desc');
        EducationCourse::filterGrid($collection);

        // Apply pagination
        EducationCourse::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * @param array|null $dataSearch
     * @param $urlFilter
     * @return object
     */
    public function getEducationRequestGridDataProfile($urlFilter, array $dataSearch = null)
    {
        $pager = Config::getPagerData($urlFilter, ['limit' => 20]);
        $collection = EducationCourse::with(['employee', 'classes', 'classes.classDetails', 'classes.classDetails.shift', 'classes.classShift']);

        $collection = $this->_filterDataTable($dataSearch, $collection);

        if (!empty($dataSearch['role_profile']) && isset($dataSearch['role_profile'])) {
            $collection = $collection->whereHas('classes.classDetails', function ($query) use ($dataSearch) {
                $query->where("role", (int)$dataSearch['role_profile'])->where('employee_id', (int)Permission::getInstance()->getEmployee()->id);
            })->with(['classes.classShift' => function ($query) use ($dataSearch) {
                $query->whereHas('classDetails', function ($query) use ($dataSearch) {
                    $query->where("role", (int)$dataSearch['role_profile'])->where('employee_id', (int)Permission::getInstance()->getEmployee()->id);
                });
            }]);
        }

        $collection = $this->_getInFoDataSearch($dataSearch, $collection);
        $collection->join("education_course_teams", "education_course_teams.course_id", '=', "education_courses.id");
        $collection->join("team_members", "team_members.team_id", '=', "education_course_teams.team_id");
        $collection->join("employees", "employees.id", '=', "team_members.employee_id");

        // get user id
        $employeeId = Auth::user()->employee_id;

        $dataWhereIn = DB::table('education_courses')
            ->join('education_class', 'education_class.course_id', '=', 'education_courses.id')
            ->join('education_class_details', 'education_class_details.class_id', '=', 'education_class.id')
            ->where('education_class_details.employee_id', $employeeId)
            ->distinct()->get(['education_courses.id']);

        $arrayWhereIn = array();
        if (count($dataWhereIn) > 0) {
            foreach ($dataWhereIn as $item => $value) {
                array_push($arrayWhereIn, $value->id);
            }
        }

        $collection->where('employees.id', $employeeId)
            ->orWhereIn('education_courses.id', $arrayWhereIn);

        $collection->groupBy('education_courses.id');
        $collection->orderBy('education_courses.id', 'desc');
        $collection->select('education_courses.id as id', "course_code", "education_courses.name as name", "status", "hours", "type", "description", "target", "hr_feedback", "teacher_feedback", "education_cost", "teacher_cost", "is_mail", "education_courses.created_at as created_at", "education_courses.updated_at as updated_at", "hr_id", "scope_total", "course_form", "is_mail_list", "course_id", "education_course_teams.team_id", "employee_id", "role_id");

        EducationCourse::filterGrid($collection);
        // Apply pagination
        EducationCourse::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    private function _filterDataTable($dataSearch = [], $collection)
    {
        // Check search course_code
        if (!empty($dataSearch['course_code']) && isset($dataSearch['course_code'])) {
            $collection->where(function ($q) use ($dataSearch) {
                $q->where("course_code", "like", "%{$dataSearch['course_code']}%")
                    ->orWhereHas('classes', function ($query) use ($dataSearch) {
                        $query->where('class_code', 'like', "%{$dataSearch['course_code']}%");
                    });
            });
        }

        // Check search name
        if (!empty($dataSearch['name']) && isset($dataSearch['name'])) {
            $collection->where(function ($q) use ($dataSearch) {
                $q->where("name", "like", "%{$dataSearch['name']}%")
                    ->orWhereHas('classes', function ($query) use ($dataSearch) {
                        $query->where('class_name', 'like', "%{$dataSearch['name']}%");
                    })->orWhereHas('classes.classDetails.shift', function ($query) use ($dataSearch) {
                        $query->where('name', 'like', "%{$dataSearch['name']}%");
                    });
            });
        }

        if (!empty($dataSearch['status']) && isset($dataSearch['status'])) {
            $collection->where("status", (int)$dataSearch['status']);
        } else {
            $collection->whereNotIn("status", [Status::STATUS_CLOSED, Status::STATUS_PENDING]);
        }

        return $collection;
    }

    private function _getInFoDataSearch($dataSearch = [], $collection)
    {
        // Check datetime from - to
        if ((!empty($dataSearch['from_date']) && isset($dataSearch['from_date'])) || (!empty($dataSearch['to_date']) && isset($dataSearch['to_date']))) {
            $collection = $collection->whereHas('classes.classDetails.shift', function ($query) use ($dataSearch) {
                $fromDate = isset($dataSearch['from_date']) && $dataSearch['from_date'] ? Carbon::createFromFormat('d/m/Y', $dataSearch['from_date'])->format('Y-m-d 00:00:00') : EducationCourse::MIN_DATE;
                $toDate = isset($dataSearch['to_date']) && $dataSearch['to_date'] ? Carbon::createFromFormat('d/m/Y', $dataSearch['to_date'])->format('Y-m-d 23:59:59') : EducationCourse::MAX_DATE;
                $query->whereBetween("start_date_time", [$fromDate, $toDate])->whereBetween("end_date_time", [$fromDate, $toDate]);
            });
        }
        //  search education_type
        if (!empty($dataSearch['type_id']) && isset($dataSearch['type_id'])) {
            $collection = $collection->whereHas('typeEducation', function ($query) use ($dataSearch) {
                $query->where("type", (int)$dataSearch['type_id']);
            });
        }

        // search education_type
        if (!empty($dataSearch['division']) && isset($dataSearch['division'])) {
            $collection = $collection->whereHas('courseTeam.team', function ($query) use ($dataSearch) {
                $query->whereIn("id", $dataSearch['division']);
            });
        }

        return $collection;
    }

    public function getLocation()
    {
        $collection = DB::table('education_class_shifts')->distinct()->get(['location_name']);

        return $collection;
    }

    public function getHrAssign()
    {
        $educationCourse = EducationCourse::getTableName();
        $employee = Employee::getTableName();
        $messages = collect(EducationCourse::join("{$employee}", "{$employee}.id", '=', "{$educationCourse}.hr_id")->select(["{$employee}.id", "{$employee}.name", "{$educationCourse}.hr_id"])->get());
        $collection = $messages->unique('hr_id');
        $collection->values()->all();

        return $collection;

    }

    public function getTeamId()
    {
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'education::education.list';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            if (Permission::getInstance()->isScopeTeam(null, $route)) {
                $teamIdsAvailable = (array)Permission::getInstance()->getTeams();
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
        }
        return $teamIdsAvailable;
    }

    public function registerStudent($idClass, $idShift)
    {
        if (!$idClass || !$idShift) {
            abort(404);
        }
        $employee_id = (int)Permission::getInstance()->getEmployee()->id;
        DB::beginTransaction();
        try {
            $result = EducationClassDetail::create([
                'employee_id' => $employee_id,
                'role' => 1,
                'class_id' => $idClass,
                'shift_id' => $idShift
            ]);
            // push mail or notification
            if ($result) {
                $data['global_item'] = [];
                $educationClass = $this->getEducationClass($result->class_id);
                $educationCourse = $this->getEducationCourse($educationClass->course_id);
                $subject = ['subject' => trans('education::mail.Increase student in class')];
                $data['global_view'] = 'education::template-mail.education-course-register';
                $data['global_link'] = URL::route('education::education-profile.detail', ['id' => $educationClass->course_id, 'flag' => '0#infomation_tab']);
                $globalItem = Employee::select(['id', 'name', 'email'])->where('id', $educationCourse->hr_id)->get()->toArray();
                if (isset($globalItem) && !empty($globalItem)) {
                    $dataHr = array_reduce($globalItem, function ($carry, $item) use ($subject) {
                        $carry[] = array_merge($item, $subject);
                        return $carry;
                    });
                    $data['global_item'] = $dataHr;
                }
                if (isset($data['global_item']) && !empty($data['global_item'])) {
                    $data['global_creator'] = Permission::getInstance()->getEmployee()->name;
                    $data['global_title'] = $educationClass->class_name;
                    $patternsArr = ['/\{\{\stitle\s\}\}/'];
                    $replacesArr = ['global_title'];
                    $this->pushNotificationAndEmail($data, $patternsArr, $replacesArr);
                }
            }
            DB::commit();

            return redirect()->route('education::profile.profileList')->with('flash_success', Lang::get('education::message.Register_successful'));
        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json($ex->getMessage(), 500);
        }
    }

    public function getEducationClass($id)
    {
        $response = EducationClass::find($id);

        return $response;
    }

    public function getEducationCourse($id)
    {
        $response = EducationCourse::find($id);

        return $response;
    }

    public function deleteStudent($idShift)
    {
        $employee_id = (int)Permission::getInstance()->getEmployee()->id;
        $idCa = EducationClassShift::where('id', '=', $idShift)
            ->select(['id', 'class_id'])->first();
        if (!$idCa) {
            abort(404);
        }
        DB::beginTransaction();
        try {
            $result = EducationClassDetail::where('shift_id', '=', $idCa->id)
                ->where('employee_id', '=', $employee_id)
                ->delete();

            // push mail or notification
            if ($result) {
                $data['global_item'] = [];
                $educationClass = $this->getEducationClass($idCa->class_id);
                $educationCourse = $this->getEducationCourse($educationClass->course_id);
                $subject = ['subject' => trans('education::mail.Reduce student in class')];
                $data['global_view'] = 'education::template-mail.education-course-delete';
                $data['global_link'] = URL::route('education::education-profile.detail', ['id' => $educationClass->course_id, 'flag' => '0#infomation_tab']);
                $globalItem = Employee::select(['id', 'name', 'email'])->where('id', $educationCourse->hr_id)->get()->toArray();
                if (isset($globalItem) && !empty($globalItem)) {
                    $dataHr = array_reduce($globalItem, function ($carry, $item) use ($subject) {
                        $carry[] = array_merge($item, $subject);
                        return $carry;
                    });
                    $data['global_item'] = $dataHr;
                }

                if (isset($data['global_item']) && !empty($data['global_item'])) {
                    $data['global_creator'] = Permission::getInstance()->getEmployee()->name;
                    $data['global_title'] = $educationClass->class_name;
                    $patternsArr = ['/\{\{\stitle\s\}\}/'];
                    $replacesArr = ['global_title'];
                    $this->pushNotificationAndEmail($data, $patternsArr, $replacesArr);
                }
            }
            DB::commit();

            return redirect()->route('education::profile.profileList')->with('flash_error', Lang::get('education::message.Delete successful'));
        } catch (\Exception $ex) {
            DB::rollback();

            return response()->json($ex->getMessage(), 500);
        }
    }

    /**
     * Push Notification or Email
     * @param [array] $data
     * @param [array] $patternsArr
     * @param [array] $replacesArr
     * @return boolean
     */
    public function pushNotificationAndEmail(array $data, array $patternsArr, array $replacesArr)
    {
        try {
            $dataInsert = [];
            $receiverIds = [];
            $receiverEmails = [];
            foreach ($data['global_item'] as $item) {
                $receiverIds[] = $item['id'];
                $newReplaceArr = [];
                foreach ($replacesArr as $index) {
                    if (array_key_exists($index, $item)) {
                        $newReplaceArr[] = $item[$index];
                    } else {
                        if (array_key_exists($index, $data)) {
                            $newReplaceArr[] = $data[$index];
                        }
                    }
                }
                $subject = preg_replace($patternsArr, $newReplaceArr, $item['subject']);
                $dataShort = $data;
                unset($dataShort['global_item']);
                // Not send email when define email for employees
                if (isset($item['email']) && !empty($item['email'])) {
                    $receiverEmails[] = $item['email'];
                    $templateData = [
                        'reg_replace' => [
                            'patterns' => $patternsArr,
                            'replaces' => $newReplaceArr
                        ],
                        'data' => $dataShort
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($item['email'], $item['name'])
                        ->setSubject($subject)
                        ->setTemplate($data['global_view'], $templateData);
                    $dataInsert[] = $emailQueue->getValue();
                }

                // Send notification
                \Rikkei\Notify\Facade\RkNotify::put(
                    $item['id'],
                    $subject,
                    $data['global_link'],
                    ['actor_id' => null, 'icon' => 'reward.png']
                );
            }
            EmailQueue::insert($dataInsert);

            return true;
        } catch (\Exception $ex) {
            \Log::info($ex);
        }

        return false;
    }

    public static function getFeedBack($id)
    {
        $collection = EducationClassDetail::join("education_class_shifts", "education_class_shifts.id", '=', 'education_class_details.shift_id')
            ->where('employee_id', (int)Permission::getInstance()->getEmployee()->id)
            ->where('education_class_details.id', (int)$id)
            ->select(["education_class_details.id", "education_class_details.feedback"])->first();

        if ($collection) {
            return $collection->feedback;
        }
        return '';
    }

    public static function checkStatus($id)
    {
        $collection = EducationCourse::where('education_courses.id', (int)$id)
            ->select(["id", "status"])->first();
        if ($collection) {
            return $collection->status;
        }
        return 0;
    }

    public static function getRoleShift($id)
    {
        $collection = EducationClassDetail:: where('shift_id', (int)$id)
            ->where('employee_id', (int)Permission::getInstance()->getEmployee()->id)
            ->select(["id", "role"])->first();
        if ($collection) {
            return $collection->role;
        }
        return '';
    }

    public function export()
    {
        $urlFilter = URL::route('education::education.list') . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        $collection = $this->getEducationRequestGridData($dataSearch, $urlFilter, true);
        if (!$collection) {
            return redirect()->back()->with(['messages' => ['errors' => [trans('sales::message.None item found')]]]);
        }

        $fileName = Carbon::now()->format('Y-m-d') . '_export_education_manager_list';
        //create excel file
        $status = Status::$STATUS;
        Excel::create($fileName, function ($excel) use ($collection, $status, $dataSearch) {
            $excel->setTitle(trans('education::view.Education_Title'));
            $excel->sheet(trans('education::view.Education_Title'), function ($sheet) use ($collection, $status, $dataSearch) {
                $sheet->mergeCells('A1:K1');
                // show title
                $sheet->cells('A1', function ($cells) {
                    $cells->setValue(trans('education::view.Education_Header'));
                    $cells->setFontWeight('bold');
                    $cells->setFontSize('18');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                // show from date
                $sheet->cells('E2', function ($cells) use ($dataSearch) {
                    if (isset($dataSearch['from_date'])) {
                        $cells->setValue(trans('education::view.Education_From_Date') . $dataSearch['from_date']);
                    } else {
                        $cells->setValue(trans('education::view.Education_From_Date') . '');
                    }
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                // show to date
                $sheet->cells('G2', function ($cells) use ($dataSearch) {
                    if (isset($dataSearch['to_date'])) {
                        $cells->setValue(trans('education::view.Education_To_Date') . $dataSearch['to_date']);
                    } else {
                        $cells->setValue(trans('education::view.Education_To_Date') . '');
                    }

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                // show Quy mô
                $sheet->cells('A4', function ($cells) {
                    $cells->setValue(trans('education::view.Education_Division'));
                });
                if (isset($dataSearch['division'])) {
                    $sheet->cell("B4", $this->_getDivision($dataSearch['division']));
                } else {
                    $sheet->cell("B4", $this->_getDivision(''));
                }


                // show Loại khoá học
                $sheet->cells('A5', function ($cells) use ($dataSearch) {
                    $cells->setValue(trans('education::view.Education_Type_Class'));
                });
                if (isset($dataSearch['type_id'])) {
                    $sheet->cell("B5", $this->_getTypeClass($dataSearch['type_id']));
                } else {
                    $sheet->cell("B5", $this->_getTypeClass(''));
                }


                //set row header
                $rowHeader = [
                    trans('education::view.Education_STT'),
                    trans('education::view.Education_Name'),
                    trans('education::view.Education_Code'),
                    trans('education::view.Education_Student'),
                    trans('education::view.Education_Amount'),
                    trans('education::view.Education_Status'),
                    trans('education::view.Education_From Date'),
                    trans('education::view.Education_Location'),
                    trans('education::view.Education_HR_assign'),
                    trans('education::view.Education_Cost'),
                    trans('education::view.Education_Feedback')];

                $sheet->row(6, $rowHeader);
                //format data type column
                $sheet->setColumnFormat(array(
                    'B' => '@',
                    'C' => '@',
                    'D' => '@',
                    'E' => '@',
                    'F' => '@',
                    'G' => '@',
                    'H' => '@',
                    'I' => '@',
                    'J' => '@',
                    'K' => '@',
                ));
                //set data
                $count = 7;
                foreach ($collection as $order => $item) {
                    $rowData = [
                        $order + 1,
                        $item && $item->course_code ? $item->course_code : '',
                        $item && $item->name ? $item->name : '',
                        '',
                        '',
                        $item && $item->status ? trans($status[$item->status]) : '',
                        '',
                        '',
                        $item && $item->employee && $item->employee->name ? $item->employee->name : '',
                        $item && $item->teacher_cost && $item->education_cost ? (int)($item->teacher_cost) + (int)$item->education_cost : 0,
                        $item && $item->hr_feedback ? $item->hr_feedback : '',
                    ];
                    $sheet->row($count++, $rowData);
                    if ($item && $item->classes && count($item)) {
                        foreach ($item->classes as $i => $object) {
                            if ($object && $object->classShift && count($object)) {
                                foreach ($object->classShift as $key => $data) {
                                    $nameTeacher = ManagerService::getNameTeacher($object->related_name, $object->related_id);
                                    $countStudent = ManagerService::countEmployee($data->id);
                                    $dateStart = Carbon::parse($data->start_date_time)->format('d-m-Y');
                                    $location = $data->location_name;
                                    $class_code = $data->class_code;
                                    $class_name = 'Lớp ' . $data->class_name . '- Ca ' . $data->name;
                                    $rowData = [
                                        '',
                                        $class_code ? $class_code : '',
                                        $class_name ? $class_name : '',
                                        $nameTeacher ? $nameTeacher : '',
                                        $countStudent ? $countStudent : '',
                                        '',
                                        $dateStart ? $dateStart : '',
                                        $location ? $location : '',
                                        '',
                                        '',
                                        '',
                                    ];
                                    $sheet->row($count++, $rowData);
                                }
                            }
                        }
                    }
                }

                //set customize style
                $sheet->getStyle('A6:K6')->applyFromArray([
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => '004e00']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'ffffff'],
                            'bold' => true
                        ]
                    ]
                );
                //set wrap text
                $sheet->getStyle('A7:K' . ($collection->count() + 1))->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');
    }

    private function _getTypeClass($idClass)
    {
        if (!empty($idClass) && isset($idClass)) {
            $type = EducationType::where('id', (int)$idClass)
                ->select('id', 'name')->first();
            if ($type) {
                return $type->name;
            }
        }

        return null;
    }

    private function _getDivision($id)
    {
        if (!empty($id) && isset($id)) {
            $division = Team::whereIn("id", $id)
                ->select('id', 'name')->first();
            if ($division) {
                return $division->name;
            }
        }

        return null;
    }


    public function exportListManager($id, $flag)
    {
        //        $route = 'education::education.export';
        $urlFilter = URL::route('education::education.detail', ['id' => $id, 'flag' => $flag]) . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        if (!$dataSearch) {
            $dataSearch = [
                'course_id' => $id
            ];
        }

        $collection = EducationCourseService::getDetailByCourseId($dataSearch, $urlFilter);
        if (!$collection) {
            return redirect()->back()->with(['messages' => ['errors' => [trans('sales::message.None item found')]]]);
        }

        $fileName = Carbon::now()->format('Y-m-d') . '_export_education_manager_detail';
        //create excel file
        $status = Status::$STATUS;
        Excel::create($fileName, function ($excel) use ($collection, $status) {

            $excel->setTitle('Education Manager Detail');
            $excel->sheet('Education Manager Detail', function ($sheet) use ($collection, $status) {
                $sheet->mergeCells('A1:H1');
                $sheet->cells('A1', function ($cells) {
                    $cells->setValue('Báo cáo kết quả khóa học');
                    $cells->setFontWeight('bold');
                    $cells->setFontSize('18');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                $sheet->mergeCells('A2:H2');
                $sheet->cells('A2', function ($cells) use ($collection) {
                    $cells->setValue('Khóa học: ' . $collection[0]->course_name);
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                //set row header
                $rowHeader = ['STT.', 'Tên nhân viên', 'Mã nhân viên', 'Bộ phận', 'Lớp đăng kí'];
                $sheet->row(4, $rowHeader);
                //format data type column
                $sheet->setColumnFormat(array(
                    'B' => '@',
                    'C' => '@',
                    'D' => '@',
                    'E' => '@',
                ));
                //set data
                if ($collection) {
                    foreach ($collection as $order => $item) {
                        $rowData = [
                            $order + 1,
                            $item->employees_name . ' (' . $item->nickname . ')',
                            $item->employee_code . ' (' . $item->nickname . ')',
                            $item->team_names,
                            trans('education::view.Education.Class') . ' ' . $item->class_name . ' - ' . trans('education::view.Education.Ca2') . ' ' . $item->shift_name
                        ];
                        $sheet->row($order + 5, $rowData);
                    }
                }
                //set customize style
                $sheet->getStyle('A4:E4')->applyFromArray([
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => '004e00']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'ffffff'],
                            'bold' => true
                        ]
                    ]
                );
                //set wrap text
                $sheet->getStyle('A5:E' . ($collection->count() + 1))->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');
    }

    public function exportResultManager($id, $flag)
    {
        //        $route = 'education::education.export';
        $urlFilter = URL::route('education::education.detail', ['id' => $id, 'flag' => $flag]) . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        if (!$dataSearch) {
            $dataSearch = [
                'course_id' => $id
            ];
        }

        $collection = EducationCourseService::getDetailByCourseId($dataSearch, $urlFilter);
        if (!$collection) {
            return redirect()->back()->with(['messages' => ['errors' => [trans('sales::message.None item found')]]]);
        }

        $fileName = Carbon::now()->format('Y-m-d') . '_export_education_manager_result';
        //create excel file
        $status = Status::$STATUS;
        Excel::create($fileName, function ($excel) use ($collection, $status) {
            $excel->setTitle('Education Manager Result');

            $excel->sheet('Education Manager Result', function ($sheet) use ($collection, $status) {
                $sheet->mergeCells('A1:H1');
                $sheet->cells('A1', function ($cells) {
                    $cells->setValue('Báo cáo kết quả khóa học');
                    $cells->setFontWeight('bold');
                    $cells->setFontSize('18');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                $sheet->mergeCells('A2:H2');
                $sheet->cells('A2', function ($cells) use ($collection) {
                    $cells->setValue('Khóa học: ' . $collection[0]->course_name);
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                //set row header
                $rowHeader = ['STT.', 'Tên nhân viên', 'Mã nhân viên', 'Bộ phận', 'Lớp đăng kí', 'Điểm giảng viên', 'Điểm tổ chức', 'Góp ý'];
                $sheet->row(4, $rowHeader);
                //format data type column
                $sheet->setColumnFormat(array(
                    'B' => '@',
                    'C' => '@',
                    'D' => '@',
                    'E' => '@',
                    'F' => '@',
                    'G' => '@',
                    'H' => '@'
                ));
                //set data
                if ($collection) {
                    foreach ($collection as $order => $item) {
                        $rowData = [
                            $order + 1,
                            $item->employees_name . ' (' . $item->nickname . ')',
                            $item->employee_code . ' (' . $item->nickname . ')',
                            $item->team_names,
                            trans('education::view.Education.Class') . ' ' . $item->class_name . ' - ' . trans('education::view.Education.Ca2') . ' ' . $item->shift_name,
                            $item->feedback_teacher_point,
                            $item->feedback_company_point,
                            $item->feedback
                        ];
                        $sheet->row($order + 5, $rowData);
                    }
                }
                $countData = count($collection);
                $count = $countData + 6;
                foreach ($collection as $order => $item) {

                    $sheet->cell("A{$count}", function ($cell) {
                        $cell->setValue('Lớp: ');

                    });
                    $sheet->cell("B{$count}", trans('education::view.Education.Class') . ' ' . $item->class_name . ' - ' . trans('education::view.Education.Ca2') . ' ' . $item->shift_name);

                    $count++;
                    $sheet->cell("A{$count}", function ($cell) {
                        $cell->setValue('Đánh giá giảng viên: ');
                    });
                    $sheet->cell("B{$count}", $item->feedback_teacher_point);

                    $sheet->cell("F{$count}", function ($cell) {
                        $cell->setValue('Đánh giá tổ chức: ');
                    });
                    $sheet->cell("F{$count}", $item->feedback_company_point);
                    $count++;
                    $sheet->cell("A{$count}", function ($cell) {
                        $cell->setValue('Nhận xét của giảng viên: ');
                    });
                    $sheet->cell("F{$count}", $item->feedback);

                    $count += 2;
                    $sheet->cell("A{$count}", function ($cell) {
                        $cell->setValue('Nhận xét của HR phụ trách: ' . '\'đổ data vào đây\'');
                    });

                }


                //set customize style
                $sheet->getStyle('A4:H4')->applyFromArray([
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => '004e00']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'ffffff'],
                            'bold' => true
                        ]
                    ]
                );
                //set wrap text
                $sheet->getStyle('A5:H' . ($collection->count() + 1))->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');
    }

    public function getCourseCode(array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $config = array_merge($configDefault, $config);
        $collection = EducationCourse::with(['employee', 'classes', 'classes.classDetails', 'classes.classDetails.shift', 'classes.classShift']);
        if (!empty($config['query']) && isset($config['query'])) {
            $collection->where(function ($q) use ($config) {
                $q->where("course_code", "like", "%{$config['query']}%")
                    ->orWhereHas('classes', function ($query) use ($config) {
                        $query->where('class_code', 'like', "%{$config['query']}%");
                    });
            });
        }
        EducationCourse::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->course_code,
                'text' => $item->course_code
            ];
            if ($item->classes) {
                foreach ($item->classes as $class) {
                    $result['items'][] = [
                        'id' => $class->class_code,
                        'text' => $class->class_code
                    ];
                }
            }
        }
        return $result;
    }

    public function getGiangVien(array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $teacherWithoutTable = EducationTeacherWithout::getTableName();
        $employeeTable = Employee::getTableName();
        $config = array_merge($configDefault, $config);
        $collection = EducationCourse::with(['employee', 'classes', 'classes.classDetails', 'classes.teacher', 'classes.classDetails.shift', 'classes.classShift']);
        if (!empty($config['query']) && isset($config['query'])) {
            $teachers = DB::table($teacherWithoutTable)->select('name')->where('name', 'like', "%{$config['query']}%");
            $employees = DB::table($employeeTable)->select('name')->where('name', 'like', "%{$config['query']}%");
            $query = $teachers->union($employees);
            $collection = $query->toSql();
            $collection = DB::table(DB::raw("($collection) as a"))->mergeBindings($query);
        }
        EducationCourse::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->name,
                'text' => $item->name
            ];
        }
        return $result;
    }

    public function getHrId(array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $config = array_merge($configDefault, $config);
        $collection = EducationCourse::with(['employee', 'classes', 'classes.classDetails', 'classes.teacher', 'classes.classDetails.shift', 'classes.classShift']);
        if (!empty($config['query']) && isset($config['query'])) {
            $educationCourse = EducationCourse::getTableName();
            $employee = Employee::getTableName();
            $collection = EducationCourse::join("{$employee}", "{$employee}.id", '=', "{$educationCourse}.hr_id")->where("{$employee}.name", "like", "%{$config['query']}%")->groupBy("{$employee}.name");
        }
        EducationCourse::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name
            ];
        }
        return $result;
    }

    public static function getType()
    {
        return EducationType::all()->toArray();
    }

    public function sendEmailAndNotificaion($item)
    {
        $subject = ['subject' => Lang::get('education::view.[Register in teaching] There is a requirement to register for teaching')];
        $data['global_view'] = 'education::template-mail.education-register-teaching-success';
        $data['global_link'] = '';
        $data['global_item'] = [];
        $globalItem = Employee::select(['id', 'name', 'email'])->where('id', $item->employee_id)->get()->toArray();
        if (isset($globalItem) && !empty($globalItem)) {
            $dataHr = array_reduce($globalItem, function ($carry, $item) use ($subject) {
                $carry[] = array_merge($item, $subject);
                return $carry;
            });
            $data['global_item'] = $dataHr;
        }
        if (isset($data['global_item']) && !empty($data['global_item'])) {
            $patternsArr = [];
            $replacesArr = [];
            $this->pushNotificationAndEmail($data, $patternsArr, $replacesArr);
        }
    }
}
