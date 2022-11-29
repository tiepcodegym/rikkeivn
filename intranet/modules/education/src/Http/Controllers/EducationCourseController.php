<?php

namespace Rikkei\Education\Http\Controllers;

use Auth;
use Google_Service_Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Lang;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\Menu;
use Rikkei\Education\Http\Services\EducationCourseService;
use Rikkei\Education\Http\Services\ManagerService;
use Rikkei\Education\Model\EducationClass;
use Rikkei\Education\Model\EducationClassDetail;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Model\EducationRequest;
use Rikkei\Education\Model\EducationTeacher;
use Rikkei\Education\Model\SettingTemplateMail;
use Rikkei\Resource\View\GoogleCalendarHelp;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Rikkei\Test\View\TestValueBinder;
use URL;

class EducationCourseController extends Controller
{
    protected $managerService;

    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
        Menu::setActive('education');
    }

    /**
     * index
     *
     * @param
     *
     * @return object
     */
    public function index()
    {
        Breadcrumb::add('HR');
        $data = $this->managerService->getInfo();
        return $data;
    }

    /**     * Get Max Course Code
     *
     * @param Request $request
     *
     * @return object
     */
    public function getMaxCourseCode(Request $request)
    {
        $dataId = $request->data;
        $dataMultiId = EducationCourseService::checkBranchCompany($dataId);
        return $dataMultiId;
    }

    /**
     * Get Max Class Code
     *
     * @param Request $request
     *
     * @return object
     */
    public function getMaxClassCode(Request $request)
    {
        $courseCode = $request->data;
        $getMaxId = EducationCourseService::getMaxId('education_class', $courseCode);
        if ($getMaxId) {
            $maxClassCode = $getMaxId + 1;
        } else {
            $maxClassCode = 1;
        }
        return $maxClassCode;
    }


    /**
     * Create Course
     *
     * @param $id
     *
     * @return object
     */
    public function create(Request $request, $id = '')
    {
        $urlFilter = 'education::education.new';
        if (Permission::getInstance()->isScopeCompany(null, $urlFilter) || Permission::getInstance()->isScopeTeam(null, $urlFilter)) {
            Breadcrumb::add('HR');
            Breadcrumb::add(trans('education::view.Education.Manager create'));
            $teamPath = Team::getTeamPathTree();
            $allTeamDraft = [];
            $teachers = [];
            $emmployee = new Employee();
            $teamIdsAvailable = null;
            $teamTreeAvailable = [];
            $scopeTotal = EducationCourseService::getScopeTotal();
            $courseForm = EducationCourseService::getCourseForm();
            $route = 'team::team.member.index';
            //scope company => view all team
            if (Permission::getInstance()->isScopeCompany(null, $urlFilter)) {
                $teamIdsAvailable = true;
            } else {// permission team or self profile.
                if (Permission::getInstance()->isScopeTeam(null, $urlFilter)) {
                    $teamIdsAvailable = (array)Permission::getInstance()->getTeams();
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
            // get max id
            $getMaxId = EducationCourseService::getMaxId('education_courses') + 1;
            $educationTypes = EducationCourseService::educationTypes();
            $auth = Auth::user()->getEmployee();
            $attachFiles = collect();
            $listFiles = collect();
            //get start and end day default for calendar
            $dateDefault = GoogleCalendarHelp::getStartEndDateDefault();
            //get calendar id
            $calendarId = Auth::user()->email;
            $templateMailArr = ['education::template-mail.education-invite', 'education::template-mail.education-vocational'];
            $templateMail = SettingTemplateMail::whereIn("template", $templateMailArr)->get();
            if (!$templateMail) {
                $templateMail = [];
            }
            $teachingId = $request->input('teaching_id');
            $collection = null;
            if($teachingId) {
                $collection = EducationTeacher::with('teacherTime')->find($teachingId);
            }

            return view('education::manager-courses.new', [
                'teamPath' => $teamPath,
                'allTeamDraft' => $allTeamDraft,
                'teachers' => $teachers,
                'employeeModelItem' => $emmployee,
                'getMaxId' => $getMaxId,
                'educationTypes' => $educationTypes,
                'auth' => $auth,
                'attachFiles' => $attachFiles,
                'listFiles' => $listFiles,
                'teamIdsAvailable' => $teamIdsAvailable,
                'teamIdCurrent' => 0,
                'minDate' => $dateDefault['startDate'],
                'maxDate' => $dateDefault['endDate'],
                'calendarId' => $calendarId,
                'scopeTotal' => $scopeTotal,
                'courseForm' => $courseForm,
                'templateMail' => $templateMail,
                'collection' => $collection
            ]);
        }
        return view('core::errors.permission_denied');
    }

    /**
     * Profile List
     *
     * @param
     *
     * @return object
     */
    public function profileList()
    {
        return $this->managerService->getProfileList();
    }

    /**
     * Register
     *
     * @param $idClass , $idShift
     *
     * @return object
     */
    public function register($idClass, $idShift)
    {
        $data = $this->managerService->registerStudent($idClass, $idShift);
        return $data;
    }

    /**
     * Delete
     *
     * @param $idShift
     *
     * @return object
     */
    public function delete($idShift)
    {
        $data = $this->managerService->deleteStudent($idShift);
        return $data;
    }

    /**
     * get data of calendar event form
     *
     * @param Request $request
     * @return response json
     */
    public function getFormCalendar(Request $request)
    {
        $urlFilter = 'education::education.getFormCalendar';
        if (Permission::getInstance()->isScopeCompany(null, $urlFilter) || Permission::getInstance()->isScopeTeam(null, $urlFilter)) {
            //Check if session access token has expire then flush session to get new token
            if (GoogleCalendarHelp::isTokenHasExpire($request)) {
                GoogleCalendarHelp::flushSession($request);
            }

            // check session google calendar isset
            if ($request->session()->has(GoogleCalendarHelp::TOKEN_NAME)) {
                $client = GoogleCalendarHelp::initClient();
                $client->setAccessToken($request->session()->get(GoogleCalendarHelp::TOKEN_NAME));

                //get data google calendar
                $service = new Google_Service_Calendar($client);
                $calendarList = $service->calendarList->listCalendarList();

                $calendarListGroup = GoogleCalendarHelp::groupCalendar($calendarList->getItems());

                // get lod data calendar
                $oldCalendarId = $request->get('calendarId');
                $oldEventId = $request->get('eventId');
                $notFound = false;
                // check isset data old google calendar for update
                if (empty($oldCalendarId) || empty($oldEventId)) {
                    $dateDefault = GoogleCalendarHelp::getStartEndDateDefault();
                    $event = null;
                } else {
                    $event = $service->events->get($oldCalendarId, $oldEventId);

                    //Not event creator
                    if ($event && $event->getCreator() && $event->getCreator()->email !== Permission::getInstance()->getEmployee()->email) {
                        return response()->json([
                            'success' => 1,
                            'isCreator' => false,
                        ]);
                    }

                    //Not found event
                    if (!$event || $event->getStatus() === GoogleCalendarHelp::EVENT_CANCELLED) {
                        $dateDefault = GoogleCalendarHelp::getStartEndDateDefault();
                        $event = null;
                        $notFound = true;
                    }
                }

                return response()->json([
                    'success' => 1,
                    'data' => $calendarListGroup,
                    'minDate' => $event ? GoogleCalendarHelp::formatDate($event->getStart()->dateTime, true) : $dateDefault['startDate'],
                    'maxDate' => $event ? GoogleCalendarHelp::formatDate($event->getEnd()->dateTime, true) : $dateDefault['endDate'],
                    'description' => $event ? $event->getDescription() : '',
                    'roomId' => $event ? GoogleCalendarHelp::getRoomOfEvent($event) : 0,
                    'notFound' => $notFound,
                    'isCreator' => true,
                ]);
            } else {
                $redirect_uri = route('resource::candidate.oauth2callback');
                header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL), true, 302);
                exit;
            }
        }
    }

    /**
     * Add Course
     *
     * @param Request $request
     *
     * @return object
     */
    public function addCourse(Request $request)
    {
        // process google calendar
        $dataAdd = $this->saveCalendar($request);
        // process save data
        $addCourse = EducationCourseService::addCourse($dataAdd);

        if (isset($request->teaching_id)) {
            $items = EducationTeacher::find($request->teaching_id);
            if(isset($items) && !empty($items)) {
                $this->managerService->sendEmailAndNotificaion($items);
            }
        }

        return $addCourse;
    }

    /**
     * Save Calendar
     *
     * @param Request $request
     *
     * @return object
     */
    public function saveCalendar($request)
    {
        $dataRequest = $request->all();
        // check data google session
        if ($request->session()->has(GoogleCalendarHelp::TOKEN_NAME)) {
            $client = GoogleCalendarHelp::initClient();
            $client->setAccessToken($request->session()->get(GoogleCalendarHelp::TOKEN_NAME));

            // check request data class isset
            if (count($dataRequest['dataClass']) > 0) {
                $dataClass = $dataRequest['dataClass'];
                foreach ($dataClass as $keyClass => $valueClass) {
                    $dataEventId = array();
                    $dataCalendarId = array();
                    // check data location of google calendar isset
                    if (isset($valueClass['location_id']) && count($valueClass['location_id']) > 0) {
                        foreach ($valueClass['location_id'] as $keyShift => $valueShift) {
                            if ($valueShift != '0') {
                                $dataInsert = [
                                    'title' => $valueClass['class_title'],
                                    'startDate' => $valueClass['startCa'][$keyShift],
                                    'endDate' => $valueClass['endCa'][$keyShift],
                                    'roomId' => $valueShift,
                                    'location' => $valueClass['location_name'][$keyShift],
                                    'description' => '',
                                    'attendeesId' => [0 => $request->get('powerful_id')],
                                ];

                                if (isset($valueClass['calendar_id'][$keyShift])) {
                                    $calendarId = $valueClass['calendar_id'][$keyShift];
                                } else {
                                    $calendarId = '';
                                }

                                if (isset($valueClass['event_id'][$keyShift])) {
                                    $eventId = $valueClass['event_id'][$keyShift];
                                } else {
                                    $eventId = '';
                                }
                                if ($calendarId && $calendarId !=  Auth::user()->email ) continue;
                                //insert or update event
                                $service = new Google_Service_Calendar($client);
                                if (empty($calendarId) || empty($eventId)) {
                                    $calendarId = Auth::user()->email;
                                } else {
                                    $event = $service->events->get($calendarId, $eventId);
                                    //check event has been deleted forever?
                                    $eventId = $event->getCreated() ? $eventId : null;
                                }
                                // save data calender from client for google calendar
                                $event = GoogleCalendarHelp::saveEvent($client, $dataInsert, $calendarId, $eventId);
                                array_push($dataEventId, $event->getId());
                                array_push($dataCalendarId, $calendarId);
                            } else {
                                array_push($dataEventId, '0');
                                array_push($dataCalendarId, '0');
                            }
                            $dataRequest['dataClass'][$keyClass]['event_id'] = $dataEventId;
                            $dataRequest['dataClass'][$keyClass]['calendar_id'] = $dataCalendarId;
                        }
                    } else {
                        array_push($dataEventId, '0');
                        array_push($dataCalendarId, '0');
                        $dataRequest['dataClass'][$keyClass]['event_id'] = $dataEventId;
                        $dataRequest['dataClass'][$keyClass]['calendar_id'] = $dataCalendarId;
                    }
                }
            }
            return $dataRequest;
        } else {
            return false;
        }
    }

    /**
     * Copy Course
     *
     * @param Request $request
     *
     * @return object
     */
    public function copyCourse(Request $request)
    {
        $returnData['message'] = '';
        $returnData['flag'] = false;
        $returnData['data'] = '';
        $returnData['url'] = '';
        // check data session exist
        if (isset($request->data) && $request->data) {
            // clear cache data copy
            CacheBase::forgetFile('Education/', 'dataCourse');
            CacheBase::forgetFile('Education/', 'dataClass');
            CacheBase::forgetFile('Education/', 'teamIdSelected');
            $dataCourse = EducationCourseService::getCourseById($request->data);
            $teamIdSelected = EducationCourseService::getTeamIdSelected($dataCourse[0]->course_id);
            $dataClass = EducationCourseService::getClassByCourseId($dataCourse[0]->course_id);
            if (count($dataClass) > 0) {
                foreach ($dataClass as $key => $class) {
                    $dataShift = EducationCourseService::getShiftbyClassModel($class->id);
                    $dataClass[$key]->data_shift = $dataShift;
                    $classElement = explode("_", $dataClass[$key]->class_code);
                    $dataClass[$key]->class_element = $classElement[1];
                    $dataDocument = EducationCourseService::listImageDetail($class->id);
                    $dataClass[$key]->documents = $dataDocument;
                }
            }
            // set cache and cookie for data copy
            CacheBase::putFile(EducationCourseService::CACHE_FOLDER, 'dataCourse', json_encode($dataCourse));
            CacheBase::putFile(EducationCourseService::CACHE_FOLDER, 'dataClass', json_encode($dataClass));
            CacheBase::putFile(EducationCourseService::CACHE_FOLDER, 'teamIdSelected', json_encode($teamIdSelected));
            $returnData['message'] = '';
            $returnData['flag'] = true;
            $returnData['data'] = '';
            $returnData['url'] = route('education::education.new');
        }
        return $returnData;
    }

    /**
     * Update Course
     *
     * @param Request $request
     *
     * @return object
     */
    public function updateCourse(Request $request)
    {
        $updateCourse = EducationCourseService::updateCourse($request->all());

        if (isset($request->teaching_id)) {
            $items = EducationTeacher::find($request->teaching_id);
            if (isset($items) && !empty($items)) {
                $this->managerService->sendEmailAndNotificaion($items);
            }
        }

        return $updateCourse;
    }

    /**
     * Update Course Info
     *
     * @param Request $request
     *
     * @return object
     */
    public function updateCourseInfo(Request $request)
    {
        // process google calendar
        $dataUpdate = $this->saveCalendar($request);
        // process update data
        $updateCourse = EducationCourseService::updateCourseInfo($dataUpdate);
        return $updateCourse;
    }

    /**
     * Detail
     *
     * @param $id
     * @param $flag
     * @return object
     */
    public function detail(Request $request, $id, $flag)
    {
        // get url route
        if ($request->input('teaching_id')) {
            $urlFilter = 'education::education.detailv2';
        } else {
            $urlFilter = 'education::education.detail';
        }
        if (Permission::getInstance()->isScopeCompany(null, $urlFilter) || Permission::getInstance()->isScopeTeam(null, $urlFilter)) {
            Breadcrumb::add('HR');
            // check course exist with id detail
            $courseExist = EducationCourseService::checkCourseExist($id);
            Breadcrumb::add(trans('education::view.Education.Manager detail'));
            if (count($courseExist) > 0) {
                $teamPath = Team::getTeamPathTree();
                $allTeamDraft = [];
                $teachers = [];
                $emmployee = new Employee();
                $teamIdsAvailable = null;
                $teamTreeAvailable = [];
                $route = 'team::team.member.index';
                $courseForm = EducationCourseService::getCourseForm();
                //scope company => view all team
                if (Permission::getInstance()->isScopeCompany(null, $urlFilter)) {
                    $teamIdsAvailable = true;
                } else {// permission team or self profile.
                    if (Permission::getInstance()->isScopeTeam(null, $urlFilter)) {
                        $teamIdsAvailable = (array)Permission::getInstance()->getTeams();
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
                }
                // get max id course
                $getMaxId = EducationCourseService::getMaxId('education_courses') + 1;
                $educationTypes = EducationCourseService::educationTypes();
                // get data login user
                $auth = Auth::user()->getEmployee();
                $dataCourse = EducationCourseService::getCourseById($id);
                $dataClass = EducationCourseService::getClassByCourseId($id);
                $scopeTotal = EducationCourseService::getScopeTotal();
                // process add shift and image for data class
                if (count($dataClass) > 0) {
                    foreach ($dataClass as $key => $class) {
                        $dataShift = EducationCourseService::getShiftbyClassModel($class->id);
                        $dataClass[$key]->data_shift = $dataShift;
                        $classElement = explode("_", $dataClass[$key]->class_code);
                        $dataClass[$key]->class_element = $classElement[1];
                        $dataDocument = EducationCourseService::listImageDetail($class->id);
                        $dataClass[$key]->documents = $dataDocument;
                    }
                }
                // process get data filter for table list
                $teamIdCurrent = EducationCourseService::getTeamByCourseId($id);
                $teamIdSelected = EducationCourseService::getTeamIdSelected($id);
                $urlFilter = URL::route('education::education.detail', ['id' => $id, 'flag' => $flag]) . '/';
                $dataSearch = CoreForm::getFilterData('search', null);
                if (!$dataSearch) {
                    $dataSearch = [
                        'course_id' => $id
                    ];
                }
                $collectionModel = EducationCourseService::getDetailByCourseId($dataSearch, $urlFilter);
                $getMaxDetailId = EducationCourseService::getMaxId('education_class_details');
                $calendarId = Auth::user()->email;
                $dateDefault = GoogleCalendarHelp::getStartEndDateDefault();

                $templateMailArr = ['education::template-mail.education-invite', 'education::template-mail.education-vocational', 'education::template-mail.education-thank'];
                $templateMail = SettingTemplateMail::whereIn("template", $templateMailArr)->get();
                if (!$templateMail) {
                    $templateMail = [];
                }

                $teachingId = $request->input('teaching_id');
                $collection = null;
                if($teachingId) {
                    $collection = EducationTeacher::with('teacherTime')->find($teachingId);
                }

                return view('education::manager-courses.detail', [
                    'flag' => $flag,
                    'teamPath' => $teamPath,
                    'allTeamDraft' => $allTeamDraft,
                    'teachers' => $teachers,
                    'employeeModelItem' => $emmployee,
                    'getMaxId' => $getMaxId,
                    'educationTypes' => $educationTypes,
                    'auth' => $auth,
                    'dataCourse' => $dataCourse,
                    'dataClass' => $dataClass,
                    'teamIdsAvailable' => $teamIdsAvailable,
                    'teamIdCurrent' => $teamIdCurrent,
                    'teamIdSelected' => $teamIdSelected,
                    'urlFilter' => $urlFilter,
                    'isScopeHrOrCompany' => $this->isScopeHrOrCompany('education::education.detail', ['id' => $id, 'flag' => $flag]),
                    'teamsOptionAll' => TeamList::toOption(null, true, false),
                    'types' => $this->getType(),
                    'collectionModel' => $collectionModel,
                    'objects' => $this->getRoles(),
                    'status' => $this->getStatus(),
                    'id' => $id,
                    'getMaxDetailId' => $getMaxDetailId,
                    'calendarId' => $calendarId,
                    'minDate' => $dateDefault['startDate'],
                    'maxDate' => $dateDefault['endDate'],
                    'scopeTotal' => $scopeTotal,
                    'templateMail' => $templateMail,
                    'collection' => $collection,
                    'courseForm' => $courseForm
                ]);
            } else {
                route('education::education.list');
            }
        }
        return view('core::errors.permission_denied');
    }

    /**
     * Detail Employees
     *
     * @param $id , $flag
     *
     * @return object
     */
    public function detailEmployees($id, $flag)
    {
        Breadcrumb::add('Profile');
        // check course id exist
        $courseExist = EducationCourseService::checkCourseExist($id);
        Breadcrumb::add(trans('education::view.Education.Manager detail'));
        if (count($courseExist) > 0) {
            $teamPath = Team::getTeamPathTree();
            $allTeamDraft = [];
            $teachers = [];
            $emmployee = new Employee();
            $getMaxId = EducationCourseService::getMaxId('education_courses') + 1;
            $educationTypes = EducationCourseService::educationTypes();
            $auth = Auth::user()->getEmployee();
            $dataCourse = EducationCourseService::getCourseById($id);
            $dataClass = EducationCourseService::getClassByCourseId($id);
            $scopeTotal = EducationCourseService::getScopeTotal();
            // process add shift and image for data class
            if (count($dataClass) > 0) {
                foreach ($dataClass as $key => $class) {
                    $dataShift = EducationCourseService::getShiftbyClassModel($class->id);
                    $dataClass[$key]->data_shift = $dataShift;
                    $classElement = explode("_", $dataClass[$key]->class_code);
                    $dataClass[$key]->class_element = $classElement[1];
                    $dataDocument = EducationCourseService::listImageDetail($class->id);
                    $dataClass[$key]->documents = $dataDocument;
                }
            }
            // get user id
            $userId = Auth::user()->employee_id;
            // process for class data employee
            $dataClassEmployeeClass = EducationCourseService::getClassByCourseAndEmpIdClass($id);
            if (count($dataClassEmployeeClass) > 0) {
                self::checkRegister($dataClassEmployeeClass, $userId);
            }
            $dataClassEmployee = EducationCourseService::getClassByCourseAndEmpId($id, $userId);
            $checkClassHasTeacher = false;
            if (count($dataClassEmployee) > 0) {
                foreach ($dataClassEmployee as $key => $class) {
                    $dataShift = EducationCourseService::getShiftbyClassModel($class->class_id);
                    if (isset($dataShift) && $dataShift) {
                        foreach ($dataShift as $keyS => $shift) {
                            $checkRegister = EducationCourseService::checkRegister($userId, $shift->id);
                            if (count($checkRegister) > 0) {
                                $dataShift[$keyS]->check_register = 1;
                                $dataShift[$keyS]->feedback_teacher_point = $checkRegister[0]->feedback_teacher_point;
                                $dataShift[$keyS]->feedback_company_point = $checkRegister[0]->feedback_company_point;
                                $dataShift[$keyS]->feedback = $checkRegister[0]->feedback;
                            } else {
                                $dataShift[$keyS]->check_register = 0;
                            }
                            $getOnlyDate = substr($dataShift[$keyS]->end_date_time, 0, 10);
                            $getOnlyTime = substr($dataShift[$keyS]->end_date_time, -8);
                            $date = date_create($getOnlyDate);
                            date_add($date, date_interval_create_from_date_string('1 days'));
                            $timeEnd = strtotime($dataShift[$keyS]->end_date_time);
                            $timeAdd = strtotime(date_format($date, 'Y-m-d') . ' ' . $getOnlyTime);
                            $timeNow = strtotime(date('Y-m-d H:i:s'));
                            if ($timeEnd < $timeNow && $timeNow < $timeAdd) {
                                $dataShift[$keyS]->check_time_in = 1;
                            } else {
                                $dataShift[$keyS]->check_time_in = 0;
                            }
                        }
                    }
                    if ($class->role == 2) {
                        $checkClassHasTeacher = true;
                    }
                    $dataClassEmployee[$key]->data_shift = $dataShift;
                    $classElement = explode("_", $dataClassEmployee[$key]->class_code);
                    $dataClassEmployee[$key]->class_element = $classElement[1];
                    $dataDocument = EducationCourseService::listImageDetail($class->class_id);
                    $dataClassEmployee[$key]->documents = $dataDocument;
                }
            }
            // process get data filter for table list
            $teamIdCurrent = EducationCourseService::getTeamByCourseId($id);
            $teamIdSelected = EducationCourseService::getTeamIdSelected($id);
            $urlFilter = URL::route('education::education-profile.detail', ['id' => $id, 'flag' => $flag]) . '/';
            $dataSearch = CoreForm::getFilterData('search', null);
            if (!$dataSearch) {
                $dataSearch = [
                    'course_id' => $id
                ];
            }
            $collectionModel = EducationCourseService::getDetailByCourseId($dataSearch, $urlFilter);
            $getMaxDetailId = EducationCourseService::getMaxId('education_class_details');
            $calendarId = Auth::user()->email;
            $dateDefault = GoogleCalendarHelp::getStartEndDateDefault();
            return view('education::manager-courses.detail-employees', [
                'flag' => $flag,
                'teamPath' => $teamPath,
                'allTeamDraft' => $allTeamDraft,
                'teachers' => $teachers,
                'employeeModelItem' => $emmployee,
                'getMaxId' => $getMaxId,
                'educationTypes' => $educationTypes,
                'auth' => $auth,
                'dataCourse' => $dataCourse,
                'dataClass' => $dataClass,
                'dataClassEmployeeClass' => $dataClassEmployeeClass,
                'dataClassEmployee' => $dataClassEmployee,
                'teamIdsAvailable' => true,
                'teamIdCurrent' => $teamIdCurrent,
                'teamIdSelected' => $teamIdSelected,
                'urlFilter' => $urlFilter,
                'isScopeHrOrCompany' => $this->isScopeHrOrCompany('education::education-profile.detail', ['id' => $id, 'flag' => $flag]),
                'teamsOptionAll' => TeamList::toOption(null, true, false),
                'types' => $this->getType(),
                'collectionModel' => $collectionModel,
                'objects' => $this->getRoles(),
                'status' => $this->getStatus(),
                'id' => $id,
                'getMaxDetailId' => $getMaxDetailId,
                'calendarId' => $calendarId,
                'minDate' => $dateDefault['startDate'],
                'maxDate' => $dateDefault['endDate'],
                'userId' => $userId,
                'scopeTotal' => $scopeTotal,
                'checkClassHasTeacher' => $checkClassHasTeacher
            ]);
        } else {
            route('education::education.list_employees');
        }
    }


    /**
     * Get Name Teacher
     *
     * @param $id , $type
     *
     * @return object
     */
    public static function getNameTeacher($id, $type)
    {
        $data = EducationCourseService::getNameTeacherModel($id, $type);
        return $data;
    }

    /**
     * Get Emp Code By Id
     *
     * @param Request $request
     *
     * @return object
     */
    public static function getEmpCodeById(Request $request)
    {
        $data = EducationCourseService::getEmpCodeById($request->data);
        return $data;
    }

    /**
     * Check Email Teacher
     *
     * @param Request $request
     *
     * @return object
     */
    public static function checkEmailTeacher(Request $request)
    {
        $data = EducationCourseService::checkEmailTeacher($request->all());
        return $data;
    }

    /**
     * is Scope Hr Or Company
     *
     * @param $route
     *
     * @return object
     */
    public function isScopeHrOrCompany($route = null)
    {
        $routeArr = ['education::education.request.hr.create', 'education::education.request.hr.edit', 'education::education.request.hr.list'];

        if (Permission::getInstance()->isScopeCompany()) {
            return true;
        }

        if (in_array($route, $routeArr)) {
            return Permission::getInstance()->isScopeTeam(null, $route);
        }

        return false;
    }

    /**
     * get Type
     *
     * @param $route
     *
     */
    public function getType()
    {
        return EducationRequest::getType();
    }

    /**
     * get Roles
     */
    public function getRoles()
    {
        return \Rikkei\Resource\View\getOptions::getInstance()->getRoles(true);
    }

    /**
     * get Status
     */
    public function getStatus()
    {
        return EducationRequest::getStatus();
    }

    public function export()
    {
        $data = $this->managerService->export();
        return $data;
    }

    public function exportListManager($id, $flag)
    {
        $data = $this->managerService->exportListManager($id, $flag);
        return $data;
    }

    public function exportResultManager($id, $flag)
    {
        $data = $this->managerService->exportResultManager($id, $flag);
        return $data;
    }

    /**
     * Register Shift
     *
     * @param Request $request
     *
     * @return object
     */
    public function registerShift(Request $request)
    {
        $userId = Auth::user()->employee_id;
        $status = EducationCourse::getCourseStatus($request->course_id);
        $isStudent = EducationClassDetail::isStudent($request->course_id, $request->shift_id, $userId);
        if ($status != EducationCourseService::STATUS_REGISTER || !$isStudent) {
            $returnData['message'] = trans('education::view.You cannot register for the course');
            $returnData['flag'] = false;
            return $returnData;
        }
        return EducationCourseService::registerShift($request->shift_id, $userId, $request->course_id);
    }

    /**
     * Send Feedback
     *
     * @param Request $request
     *
     * @return object
     */
    public function sendFeedback(Request $request)
    {
        $userId = Auth::user()->employee_id;
        $data = EducationCourseService::sendFeedback($request->all(), $userId);
        return $data;
    }

    /**
     * Add Document From Teacher
     *
     * @param Request $request
     *
     * @return object
     */
    public function addDocumentFromTeacher(Request $request)
    {
        $data['message'] = trans('education::view.Education.Error system');
        $data['flag'] = false;
        if (isset($request->class_data) && $request->class_data) {
            $idCourse = $request->course_id;
            foreach ($request->class_data as $key => $value) {
                EducationCourseService::insertOrUpdate($value['files'], $value['class_id']);

                // push mail/notify for file
                $idClass = $value['class_id'];
                EducationCourseService::pushNotificationAndEmailForFile($idClass, $idCourse);
            }
            $data['message'] = trans('education::view.Education.Save success');
            $data['flag'] = true;
        }
        return $data;
    }

    public function getCourseCode() {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->managerService->getCourseCode([
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    public function getGiangVien() {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->managerService->getGiangVien([
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    public function getHrId() {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->managerService->getHrId([
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    /**
     * Search employee by ajax
     */
    public function ajaxSearchEmployeeEmail()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            EducationCourseService::searchEmployeeAjaxEmail(Input::get('q'), [
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    /**
     * Search employee by ajax
     */
    public function searchEmployeeAjaxEmailList()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            EducationCourseService::searchEmployeeAjaxEmailList(Input::get('q'), [
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    /**
     * Search employee by ajax
     */
    public function searchEmployeeAjaxNameList()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            EducationCourseService::searchEmployeeAjaxNameList(Input::get('q'), [
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    /**
     * Search employee by ajax
     */
    public function searchEmployeeAjaxNameCodeList()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            EducationCourseService::searchEmployeeAjaxNameCodeList(Input::get('q'), [
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    /**
     * Search employee by ajax
     */
    public function searchHrAjaxList()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            EducationCourseService::searchHrAjaxList(Input::get('q'), [
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    public static function checkRegister($dataClassEmployeeClass, $userId)
    {
        foreach ($dataClassEmployeeClass as $key => $class) {
            $dataShift = EducationCourseService::getShiftbyClassModel($class->class_id);
            if (isset($dataShift) && $dataShift) {
                foreach ($dataShift as $keyS => $shift) {
                    $checkRegister = EducationCourseService::checkRegister($userId, $shift->id);
                    if (count($checkRegister) > 0) {
                        $dataShift[$keyS]->check_register = 1;
                    } else {
                        $dataShift[$keyS]->check_register = 0;
                    }
                    $timeNow = date("Y-m-d H:i", time());
                    if ($shift->end_time_register >= $timeNow) {
                        $dataShift[$keyS]->check_end_time_register = 0;
                    } else {
                        $dataShift[$keyS]->check_end_time_register = 1;
                    }
                }
            }
            $dataClassEmployeeClass[$key]->data_shift = $dataShift;
            $classElement = explode("_", $dataClassEmployeeClass[$key]->class_code);
            $dataClassEmployeeClass[$key]->class_element = $classElement[1];
            $dataDocument = EducationCourseService::listImageDetail($class->class_id);
            $dataClassEmployeeClass[$key]->documents = $dataDocument;
        }

        return $dataClassEmployeeClass;
    }

    public function import()
    {
        $bodyData = $this->getBodyData();
        $listEmail = '';
        if (!$bodyData['excel_file']) {
            return redirect()->back()->withErrors('messages', trans('test::test.file_format_error_or_not_read'));
        }
        $file = $bodyData['excel_file'];
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            return response()->json(trans('validation.mimes', ['attribute' => 'import file', 'values' => 'xlsx, xls, csv']), 422);
        }
        DB::beginTransaction();
        try {
            $valueBinder = new TestValueBinder;
            $data = Excel::setValueBinder($valueBinder)->load($file->getRealPath(), function ($reader) {
                $reader->formatDates(false);
                $reader->calculate(false);
            });
            $data = $data->get();
            if ($data) {
                foreach ($data as $value) {
                    if (!isset($value['email']) || !isset($value['lop_dang_ky']) || !isset($value['lop_tham_gia'])) {
                        break;
                    }
                    $caTG = isset($value['ca_tham_gia']) && $value['ca_tham_gia'] ? (int)$value['ca_tham_gia'] : 1;
                    $educationClassDetail = new EducationClassDetail();
                    $employee = Employee::getEmpByEmail($value['email']);
                    if ($employee) {
                        $educationClass = EducationClass::where('class_code', $value['lop_dang_ky'])->first();
                        $classTG = null;
                        if ($value['lop_tham_gia']) {
                            $classTG = EducationClassShift::getClassShift($value['lop_tham_gia'], $caTG);
                        }
                        if (!$educationClass || !count($educationClass)) {
                            continue;
                        }
                        $isTeacher = EducationCourse::isTeacher($employee->id, $educationClass->course_code);
                        if ($isTeacher) {
                            continue;
                        }
                        $dkID = $educationClass->id;
                        $tgID = $classTG ? $classTG->id : null;
                        $educationClassDetail->employee_id = $employee->id;
                        $educationClassDetail->shift_id = $tgID;
                        $educationClassDetail->class_id = $dkID;
                        $checkExists = EducationClassDetail::checkImport($employee->id, $dkID, $tgID);
                        if (!$checkExists) {
                            $listEmail .= $value['email'] . ', ';
                            $educationClassDetail->save();
                        }
                    }
                }
            }
            $messages = trans('education::view.list email import successfully') . trim($listEmail, ', ');
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            throw $ex;
        }

        return redirect()->back()->withErrors($messages);
    }
}
