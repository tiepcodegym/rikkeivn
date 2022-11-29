<?php

namespace Rikkei\Education\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Rikkei\Education\Http\Services\EducationCourseService;
use Rikkei\Education\Http\Services\RegisterTeachingService;
use Rikkei\Education\Model\SettingEducation;
use Rikkei\Education\Model\EducationTeacher;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Education\Http\Requests\StoreRegisterTeachingRequests;
use Rikkei\Education\Http\Requests\UpdateRegisterTeachingRequest;
use Lang, URL;
use Illuminate\Support\Facades\Session;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\CookieCore;
use Rikkei\Team\View\TeamList;

class RegisterTeachingController extends Controller
{
    protected $registerTeachingService;

    public function __construct(RegisterTeachingService $registerTeachingService)
    {
        $this->registerTeachingService = $registerTeachingService;
    }

    /**
     * Show list items register teachings
     *
     * @return mixed
     */
    public function index()
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.List of Teaching Registration'));
        $items = $this->registerTeachingService->listItem();
        $listUserAssignee = $this->registerTeachingService->listUserAssignee(EducationTeacher::ROUTER_REGISTER_HR, null, null);

        return view('education::register-teaching.index', [
            'titleHeadPage' => Lang::get('education::view.List of Teaching Registration'),
            'scopes' => EducationTeacher::getLableScope(),
            'registerType' => EducationTeacher::getLableRegisterType(),
            'status' => EducationTeacher::getLableStatus(),
            'listUserAssignee' => $listUserAssignee,
            'collectionModel' => $items
        ]);
    }

    /**
     * show view form create register teaching
     *
     * @return mixed
     */
    public function create()
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Register of teaching'));
        $educationTypes = SettingEducation::where('status', SettingEducation::STATUS_ENABLE)
            ->pluck('name', 'id');
        $item = new EducationTeacher();

        return view('education::register-teaching.create', [
            'collectionModel' => $item,
            'isShow' => false,
            'educationTypes' => $educationTypes,
            'scopes' => EducationTeacher::getLableScope()
        ]);
    }

    /**
     * show detail register teaching by id and disable input
     *
     * @param $id
     * @return mixed
     */
    public function showDetail($id)
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Teaching registration details'));
        $item = $this->registerTeachingService->findEducationTeacher($id);
        $educationTypes = SettingEducation::all()->pluck('name', 'id');
        $isShow = true;
        if (!$item) {
            return redirect()->route('education::education.teaching.teachings.index')->withErrors(Lang::get('education::view.messages.Data not found'));
        }
        // show teams name for HR
        $teamsSelected = null;
        if (isset($item['teams']) && $item['teams']) {
            $teamIdSelected = explode(',', $item['teams']);
            $teamModel = new Team();
            $teams = $teamModel->getTeamsByTeamIds($teamIdSelected)->pluck('name')->toArray();
            $teamsSelected = implode(', ', $teams);
        }

        return view('education::register-teaching.show', [
            'collectionModel' => $item,
            'isShow' => $isShow,
            'teamsSelected' => $teamsSelected,
            'educationTypes' => $educationTypes,
            'scopes' => EducationTeacher::getLableScope()
        ]);
    }

    /**
     * show detail register teaching by id and enable input
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.Teaching registration details'));

        $item = $this->registerTeachingService->findEducationTeacher($id);
        $educationTypes = SettingEducation::where('status', SettingEducation::STATUS_ENABLE)
            ->pluck('name', 'id');
        $isShow = false;

        if (!$item) {
            return redirect()->route('education::education.teaching.teachings.index')->withErrors(Lang::get('education::view.messages.Data not found'));
        }
        if ($item->status == EducationTeacher::STATUS_ARRANGEMENT || $item->status == EducationTeacher::STATUS_SEND) {
            return redirect()->route('education::education.teaching.teachings.show_detail',['id' => $item->id]);
        }
        // show teams selected to edit
        $teamsSelected = null;
        if (isset($item['teams']) && $item['teams']) {
            $teamIdSelected = explode(',', $item['teams']);
            $teamModel = new Team();
            $teamsSelected = $teamModel->getTeamsByTeamIds($teamIdSelected)->pluck('id')->toArray();
        }
        return view('education::register-teaching.create', [
            'collectionModel' => $item,
            'isShow' => $isShow,
            'teamsSelected' => $teamsSelected,
            'teamsOptionAll' => TeamList::toOption(null, true, false),
            'educationTypes' => $educationTypes,
            'scopes' => EducationTeacher::getLableScope()
        ]);

    }

    /**
     * submit create register teaching
     *
     * @param Request $request
     * @return mixed
     */
    public function store(StoreRegisterTeachingRequests $request)
    {
        if (isset($request['teams']) && $request['teams']) {
            $request['teams'] = implode(',', $request['teams']);
        }
        $item = $this->registerTeachingService->insert($request);
        CookieCore::setRaw('dataCourse', $request->all());
        if (!$item) {
            return redirect()->back()->withErrors(Lang::get('education::view.messages.Data not found'));
        }
        $this->registerTeachingService->send($request, $item);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Create register teaching success')
                ]
            ]
        );

        return redirect()->route('education::education.teaching.teachings.index');
    }

    /**
     * update register teaching
     *
     * @param Request $request, $id
     * @return mixed
     */
    public function update(UpdateRegisterTeachingRequest $request, $id)
    {
        if (isset($request['teams']) && $request['teams']) {
            $request['teams'] = implode(',', $request['teams']);
        }
        $this->registerTeachingService->update($request, $id);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Update register teaching success')
                ]
            ]
        );
        return redirect()->back()->withInput();
    }

    /**
     * get course by type course id
     *
     * @param $typeId
     * @return response
     */
    public function getCourseTypeId($typeId)
    {
        $idClass = [];
        $itemCourse = $this->registerTeachingService->getCourseByCourseTypeId($typeId);
        $itemsClass = $this->registerTeachingService->getClassByCourseId($typeId);
        foreach ($itemsClass as $key => $value) {
            $idClass[] = $key;
            break;
        }
        $contentCours = $this->registerTeachingService->getCourseContent($typeId);
        $classDetail = $this->registerTeachingService->getClassDetailId(count($idClass) ? $idClass[0] : '');

        $data = [
            'itemCourse' => $itemCourse,
            'itemsClass' =>$itemsClass,
            'contentCours' => $contentCours,
            'classDetail' => $classDetail
        ];

        return response()->json([
            'data' => $data,
            'status' => true
        ]);
    }

    /**
     * get class by course id
     *
     * @param $courseId
     * @return response
     */
    public function getClassByCourseId($courseId)
    {
        $idClass = [];
        $itemsClass = $this->registerTeachingService->getClassByCourseId($courseId);
        foreach ($itemsClass as $key => $value) {
            $idClass[] = $key;
            break;
        }
        $contentCours = $this->registerTeachingService->getCourseContent($courseId);
        $classDetail = $this->registerTeachingService->getClassDetailId(count($idClass) ? $idClass[0] : '');

        $data = [
            'itemsClass' =>$itemsClass,
            'contentCours' => $contentCours,
            'classDetail' => $classDetail
        ];

        return response()->json([
            'data' => $data,
            'status' => true
        ]);
    }

    /**
     * get detail class by class id
     *
     * @param $classId
     * @return response
     */
    public function getClassDetailById($classId)
    {
        $items = $this->registerTeachingService->getClassDetailId($classId);

        return response()->json([
            'data' => $items,
            'status' => true
        ]);
    }

    /**
     * get list register teachings (Hr)
     *
     */
    public function managerTeachings()
    {
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('education::view.List of Teaching Registration'));
        $items = $this->registerTeachingService->listHrManaTeachings();
        $teamIds = [];
        if (Permission::getInstance()->isScopeTeam(null, 'education::education.teaching.hr.index')) {
            $teamIds = Permission::getInstance()->getTeams();
        }
        $listUserAssignee = $this->registerTeachingService->listUserAssignee(EducationTeacher::ROUTER_REGISTER_HR, $teamIds, null);

        return view('education::register-teaching.hr.index', [
            'titleHeadPage' => Lang::get('education::view.List of Teaching Registration'),
            'scopes' => EducationTeacher::getLableScope(),
            'registerType' => EducationTeacher::getLableRegisterType(),
            'collectionModel' => $items,
            'listUserAssignee' => $listUserAssignee
        ]);
    }

    /**
     * update curator in register teaching
     *
     */
    public function updateCurator($id)
    {
        $this->registerTeachingService->updateIdCurator($id);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Update register teaching success')
                ]
            ]
        );

        return redirect()->route('education::education.teaching.hr.index');
    }

    /**
     * update Reject in register teaching
     *
     * @param $request, $id
     * @return mixed
     */
    public function updateReject(Request $request, $id)
    {
        $this->registerTeachingService->updateStatusReject($request, $id);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Update register teaching success')
                ]
            ]
        );

        return redirect()->route('education::education.teaching.hr.index');
    }

    /**
     * update Reject in register teaching
     *
     * @param $request, $id
     * @return response
     */
    public function getCourse($id)
    {
        $items = $this->registerTeachingService->getCourseContent($id);

        return response()->json([
            'data' => $items,
            'status' => true
        ]);
    }

    /**
     * lấy chi tiết đăng ký giảng dạy
     *
     * @return response
     */
    public function getRegisterTeaching($course_type_id, $course_id, $class_id)
    {

        $itemCourse = $this->registerTeachingService->getCourseByCourseTypeId($course_type_id);
        $itemsClass = $this->registerTeachingService->getClassByCourseId($course_id);
        $contentCours = $this->registerTeachingService->getCourseContent($course_id);
        $classDetail = $this->registerTeachingService->getClassDetailId($class_id);

        $data = [
            'itemCourse' => $itemCourse,
            'itemsClass' =>$itemsClass,
            'contentCours' => $contentCours,
            'classDetail' => $classDetail
        ];

        return response()->json([
            'data' => $data,
            'status' => true
        ]);
    }

    public function getCourseId()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        return response()->json(
            $this->registerTeachingService->getCourseId([
                'page' => Input::get('page'),
                'query' => trim(Input::get('q'), ' '),
            ])
        );
    }

    public function send(Request $request, $id)
    {
        $this->registerTeachingService->send($request, $id);
        Session::flash(
            'messages', [
                'success'=> [
                    Lang::get('education::view.Send register teaching success')
                ]
            ]
        );

        return redirect()->route('education::education.teaching.teachings.index');
    }

    public function register($courseId)
    {
        $userId = Auth::user()->employee_id;
        $data = EducationCourseService::getClassByCourseAndEmpIdClass($courseId);
        $dataShift = EducationCourseController::checkRegister($data, $userId);
        return view('education::manager-courses.includes.register', compact('dataShift'));
    }
}
