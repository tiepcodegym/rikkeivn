<?php

namespace Rikkei\Education\Http\Services;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Tag\Input;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View;
use Rikkei\Education\Http\Requests\EducationRequestsRequest;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Model\EducationRequestHistory;
use Rikkei\Education\Model\EducationRequestObject;
use Rikkei\Education\Model\EducationTag;
use Rikkei\Education\Model\EducationType;
use Rikkei\Education\Model\SettingAddressMail;
use Rikkei\Education\Model\EducationRequest;
use Rikkei\Education\Http\Helper\CommonHelper;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\SubscriberNotify\Model\EmailQueue;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeRole;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Permission;
use Rikkei\Team\View\Permission as PermissonView;
use Session;

class EducationRequestService
{
    protected $route;
    protected $isScopeHrOrCompany = false;
    protected $isHrCreate = false;
    protected $isAvailableTeamId = false;
    protected $isSelfCreated = false;
    protected $actionForm = 'create';
    protected $permissionEmpId = null;
    protected $routeEditHr = 'education::education.request.hr.edit';
    protected $routeEdit = 'education::education.request.edit';
    protected $routeStore = 'education::education.request.store';
    protected $routeUpdate = 'education::education.request.update';

    /**
     * construct
     */
    public function __construct()
    {
        $this->route = \Request::route()->getName();
    }

    /**
     * Show list education request for dlead
     */
    public function showListEducationRequest() {
        Breadcrumb::add(Lang::get('education::view.Education.Education request list'));
        $curEmpId = PermissonView::getInstance()->getEmployee()->id;
        $urlFilter = url()->current() . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        $data = [];
        if ($this->isScopeHrOrCompany) {
            $data += [
                'tags' => $this->getTag()
            ];
        }
        if(PermissonView::getInstance()->isScopeCompany()) {
            //
        } else {
            if($teamIds = PermissonView::getInstance()->isScopeTeam()) {
                PermissonView::getInstance()->getTeams();
                // Get $teamIds
            } else {
//                if($teamIds = PermissonView::getInstance()->isScopeSelf()) {
//                    // Get $teamIds
//                } else {
//                    // If hasn't permission
//                    return view('core::errors.permission_denied');
//                }
                return view('core::errors.permission_denied');
            }
            $employeeIds = $this->getLeaderIdFromTeamId($teamIds);
            $dataSearch['employee_id'] = $employeeIds;
        }

        $collectionModel = $this->getEducationRequestGridData($dataSearch, $urlFilter);

        $data += [
            'collectionModel'   => $collectionModel,
            'urlFilter'         => $urlFilter,
            'titlePage'         => Lang::get('education::view.Education.Request training list'),
            'types'             => $this->getType(),
            'objects'           => $this->getRoles(),
            'status'            => $this->getStatus(),
            'teamPath'          => Team::getTeamPathTree(),
            'teamsOptionAll'    => TeamList::toOption(null, true, false),
            'isScopeHrOrCompany'=> $this->isScopeHrOrCompany,
            'employee_branch'   => $this->getBranchByCurrentEmployee($curEmpId),
            'scopeTotal'        => $this->getScopeTotal(),
        ];

        return view('education::education-request.index')->with($data);
    }

    /**
     * Show list education request for hr
     */
    public function showListEducationRequestForHr() {
        $this->isScopeHrOrCompany = true;

        return $this->showListEducationRequest();
    }

    /**
     * Create education request
     */
    public function createEducationRequest() {
        // Check permission create: company and team
        if (PermissonView::getInstance()->isScopeCompany() || PermissonView::getInstance()->isScopeTeam()){
            // Breadcrumb
            if(!$this->isScopeHrOrCompany) {
                Breadcrumb::add(Lang::get('education::view.Create'), URL::route('education::education.request.create'));
            } else {
                Breadcrumb::add(Lang::get('education::view.Create'), URL::route('education::education.request.hr.create'));
            }

            $curEmp = PermissonView::getInstance()->getEmployee();
            $employeeBranch = '';
            if(!$this->isScopeHrOrCompany) {
                $employeeBranch = $this->getBranchByCurrentEmployee($curEmp->id);
            }

            $education = new EducationRequest();
            $data = [
                'titlePage'             => Lang::get('education::view.Education.Create.Create a new training request'),
                'action'                => 'create',
                'education'             => $education,
                'employee'              => $curEmp,
                'employeeBranch'        => $employeeBranch,
                'position'              => $this->getPosition($curEmp->id),
                'division'              => $this->getDivision($curEmp->id),
                'types'                 => $this->getType(),
                'tags'                  => $this->getTag(),
                'scopeTotal'            => $this->getScopeTotal(),
                'objects'               => $this->getRoles(),
                'curDate'               => Carbon::now()->toDateString(),
                'tag_ids'               => [],
                'object_ids'            => [],
                'teachers'              => [],
                'teamPath'              => Team::getTeamPathTree(),
                'teamsOptionAll'        => TeamList::toOption(null, true, false),
                'isScopeHrOrCompany'    => $this->isScopeHrOrCompany,
                'isSelfCreated'         => $this->isSelfCreated,
            ];

            return view('education::education-request.create', $data);
        }

        // If hasn't permission
        return view('core::errors.permission_denied');
    }

    /**
     * Create education request for Hr
     */
    public function createEducationRequestForHr()
    {
        $this->isScopeHrOrCompany = true;

        return $this->createEducationRequest();
    }

    /**
     * Store education request
     */
    public function storeEducationRequest(EducationRequestsRequest $request)
    {
        return $this->insertOrUpdate($request, null);
    }

    /**
     * Store education request for Hr
     */
    public function storeEducationRequestForHr(EducationRequestsRequest $request)
    {
        $this->isScopeHrOrCompany = true;

        return $this->insertOrUpdate($request, null);
    }

    /**
     * Edit education request
     */
    public function editEducationRequest($id)
    {
        if (PermissonView::getInstance()->isScopeCompany() || ($teamIds = PermissonView::getInstance()->isScopeTeam())) {
            $this->actionForm = 'update';
            $curEmp = PermissonView::getInstance()->getEmployee();

            if(isset($teamIds)) {
                $leaderIds = $this->getLeaderIdFromTeamId($teamIds);
            }

            // Check exists and get employee create this request
            if(isset($leaderIds)) {
                $empRequest = $this->getEducationRequestById($id, $leaderIds);
            } else {
                $empRequest = $this->getEducationRequestById($id);
            }
            if (!$empRequest) {
                return redirect()->route('education::education.request.list')->withErrors(Lang::get('core::message.Not found item'));
            }

            // Check self created
            if ($curEmp->id == $empRequest->employee_id) {
                $this->isSelfCreated = true;
            }

            // Check permission team id available
            $this->isAvailableTeamId = $this->checkTeamIdAvailable();

            if (!$this->isScopeHrOrCompany) {
                Breadcrumb::add(Lang::get('education::view.Education.Update.Detail training request'), URL::route($this->routeEdit, [$id]));
            } else {
                Breadcrumb::add(Lang::get('education::view.Education.Update.Detail training request'), URL::route($this->routeEditHr, [$id]));
            }

            // Get education request has relationship
            $educationRequest = $this->getEducationRequestByIdWithRelated($id);
            $employee = Employee::find($empRequest->employee_id);

            // Team selected
            $allTeamSelectedArr = $educationRequest->scopes->toArray();
            $teamSelected = array_reduce($allTeamSelectedArr, function ($carry, $item) {
                $carry[] = $item['id'];
                return $carry;
            }, []);

            // Tag selected
            $tagArr = $educationRequest->tags->toArray();
            $tag_ids = array_reduce($tagArr, function ($carry, $item) {
                $carry[] = $item['id'];
                return $carry;
            }, []);

            // Object selected
            $objectArr = $educationRequest->objects->toArray();
            $object_ids = array_reduce($objectArr, function ($carry, $item) {
                $carry[] = $item['education_object_id'];
                return $carry;
            }, []);

            // Get branch employee request
            $employeeBranch = '';
            if (!$this->isScopeHrOrCompany) {
                $employeeBranch = $this->getBranchByCurrentEmployee($curEmp->id);
            }

            // Data
            $data = [
                'titlePage' => Lang::get('education::view.Education.Update.Detail training request'),
                'action' => $this->actionForm,
                'education' => $educationRequest,
                'employee' => $employee,
                'employee_branch' => $employeeBranch,
                'position' => $this->getPosition($employee->id),
                'division' => $this->getDivision($employee->id),
                'types' => $this->getType(),
                'status' => $this->getStatus(),
                'tags' => $this->getTag(),
                'scopeTotal' => $this->getScopeTotal(),
                'curDate' => Carbon::now()->format('d-m-Y'),
                'objects' => $this->getRoles(),
                'tag_ids' => $tag_ids,
                'object_ids' => $object_ids,
                'teamSelected' => $teamSelected,
                'teamPath' => Team::getTeamPathTree(),
                'teamsOptionAll' => TeamList::toOption(null, true, false),
                'isScopeHrOrCompany' => $this->isScopeHrOrCompany,
                'isAvailableTeamId' => $this->isAvailableTeamId,
                'isSelfCreated' => $this->isSelfCreated,
            ];

            return view('education::education-request.update', $data);
        }

        // If hasn't permission
        return view('core::errors.permission_denied');
    }

    /**
     * Edit education request for Hr
     */
    public function editEducationRequestForHr($id)
    {
        $this->isScopeHrOrCompany = true;

        return $this->editEducationRequest($id);
    }

    /**
     * Update education request
     */
    public function updateEducationRequest(EducationRequestsRequest $request, $id)
    {
        return $this->insertOrUpdate($request, $id);
    }

    /**
     * Update education request for hr
     */
    public function updateEducationRequestForHr(EducationRequestsRequest $request, $id)
    {
        $this->isScopeHrOrCompany = true;

        return $this->insertOrUpdate($request, $id);
    }

    /**
     * Insert Or Update education request
     */
    public function insertOrUpdate(EducationRequestsRequest $request, $id = null) {
        $oldData = false;
        $curEmp = PermissonView::getInstance()->getEmployee();
        $this->actionForm = 'create';
        $this->isAvailableTeamId = $this->checkTeamIdAvailable();
        $input = $request;

        // Check Permission
        if ($this->isAvailableTeamId) {
            // Check Update
            if ($id) {
                $this->actionForm = 'update';
                $request = $this->getEducationRequestById($id);
                if (!$request) {
                    return redirect()->route('education::education.request.list')->withErrors(Lang::get('core::message.Not found item'));
                } else {
                    // Get old data
                    $oldData = $request;
                }

                if ($curEmp->id == $request->employee_id) {
                    $this->isSelfCreated = true;
                }

                // Check scope hr but not self created
                if ($this->isScopeHrOrCompany && !$this->isSelfCreated) {
                    $request = EducationRequest::select(['id', 'employee_id', 'course_id', 'status'])->where('id', $id)->first();
                    $request->status = $input->status;
                }
                $empRequestId = $request->employee_id;
            }

            // Check scope not Hr ( case is Dlead )
            if (!$this->isScopeHrOrCompany) {
                // Check status not 3
                if ($input->status != EducationRequest::STATUS_REQUESTING) {
                    return redirect()->route('education::education.request.list')->withErrors(Lang::get('education::view.message.No permission update!'));
                }
            }

            // Check action with Dlead
            if ($this->actionForm == 'create' || !$this->isScopeHrOrCompany || $this->isSelfCreated) {
                // Check create form
                if ($this->actionForm == 'create') {
                    $request = new EducationRequest();
                }
                $request->employee_id   = $this->actionForm == 'update' ? $empRequestId : $curEmp->id;
                $request->title         = $input->title;
                $request->description   = $input->description;
                $request->type_id       = $input->type_id;
                $request->status        = $input->status;
                $request->teacher_id    = $input->teacher_id;
                $request->assign_id     = $input->assign_id;
                $request->scope_total   = $input->scope_total;
                $request->start_date    = !empty($input->start_date) ? Carbon::parse($input->start_date)->format('Y-m-d 00:00:00') : null;
            }

            // Check hr
            if ($this->isScopeHrOrCompany) {
                $request->course_id = $input->course_id;
            }

            // Transaction
            DB::beginTransaction();
            try {
                if ($request->save()) {
                    $scopeArr   = array_values($input->team_id);
                    $tagArr     = $input->tag;
                    $objectArr  = $input->object;

                    // When action create or is Dlead update or Hr update with hr is self create
                    if ($this->actionForm == 'create' || !$this->isScopeHrOrCompany || $this->isScopeHrOrCompany && $this->isSelfCreated) {
                        // Save scope
                        $request->scopes()->sync($scopeArr);

                        // Save tag
                        $tagIdArr = [];
                        foreach ($tagArr as $key => $value) {
                            $find = EducationTag::where('name', 'like', $value)->first();
                            if (!$find) {
                                $idTags = EducationTag::create([
                                    'name' => $value
                                ])->id;
                            } else {
                                $idTags = $find->id;
                            }
                            $tagIdArr[] = $idTags;
                        }

                        if (!empty($tagIdArr)) {
                            $request->tags()->sync($tagIdArr);
                        }

                        // Save object
                        if (EducationRequestObject::first()) {
                            EducationRequestObject::destroy($id);
                        }
                        foreach ($objectArr as $key => $value) {
                            $arr['education_object_id'] = (int)$value;
                            $request->objects()->save(new EducationRequestObject($arr));
                        }
                    }

                    // Save reason history when scope hr and status is pending or reject
                    if ($this->isScopeHrOrCompany && in_array($input->status, [EducationRequest::STATUS_PENDING, EducationRequest::STATUS_REJECT])) {
                        $arrReason = [
                            'hr_id'        => $curEmp->id,
                            'status'       => $input->status,
                            'description'  => $input->reason,
                        ];
                        $request->reason()->save(new EducationRequestHistory($arrReason));
                    }

                    // Push Mail or Notification
                    $data = [];
                    $data['global_item'] = [];
                    $data['global_link'] = Url::route($this->routeEdit, ['id' => $request->id]);

                    // Check mail for scope company or team with store or update or self created
                    if (PermissonView::getInstance()->isScopeCompany() || PermissonView::getInstance()->isScopeTeam(null, $this->routeStore)
                        || PermissonView::getInstance()->isScopeTeam(null, $this->routeUpdate)
                        || $curEmp->id == $request->employee_id) {
                        if ($this->actionForm == 'create') {
                            $subject = ['subject' => trans('education::mail.Training request new')];
                            $data['global_view'] =  'education::template-mail.education-request-leader-create';
                        } else {
                            $subject = ['subject' => trans('education::mail.Training request update')];
                            $data['global_view'] =  'education::template-mail.education-request-leader-update';
                        }

                        // Check scope company
                        if ($input->scope_total == EducationRequest::SCOPE_COMPANY) {
                            $globalItem = $this->getEmployeeWithScopeCompany();
                        }

                        // Check scope branch
                        if ($input->scope_total == EducationRequest::SCOPE_BRANCH) {
                            $globalItem = $this->getEmployeeWithScopeBranch($scopeArr, true);
                        }

                        // Check scope division
                        if ($input->scope_total == EducationRequest::SCOPE_DIVISION) {
                            if (count($scopeArr) == 1 && $this->checkSelfScopeDivision($scopeArr)) {
                                // Check private division
                                $globalItem = $this->getEmployeeWithScopeBranch($scopeArr);
                            } else {
                                // Check multi division
                                $globalItem1 = $this->getEmployeeWithScopeBranch($scopeArr);

                                // Check exists admin of division
                                $globalItem2 = $this->getEmployeeWithScopeDivision($scopeArr, 'education.request.team.hr');
                                $globalItem = array_merge($globalItem1, $globalItem2);
                            }
                        }
                        if (isset($globalItem) && !empty($globalItem)) {
                            $data['global_item'] = array_reduce($globalItem, function ($carry, $item) use ($subject) {
                                $carry[] = array_merge($item, $subject);
                                return $carry;
                            });
                        }
                        if ($this->isScopeHrOrCompany) {
                            $data['global_link'] = Url::route($this->routeEditHr, ['id' => $request->id]);
                        }
                    }

                    // Check Hr update status or info request
                    if ($this->isScopeHrOrCompany) {
                        $globalItem = $this->getEmployeeSentRequest($request->employee_id);
                        if ($oldData && $oldData->status != $input->status) {
                            $subject = ['subject' => trans('education::mail.Update status')];
                        } else {
                            $subject = ['subject' => trans('education::mail.Update information')];
                        }

                        if (isset($globalItem) && !empty($globalItem)) {
                            $dataHr = array_reduce($globalItem, function ($carry, $item) use ($subject) {
                                $carry[] = array_merge($item, $subject);
                                return $carry;
                            });
                            $data['global_item'] = (isset($data['global_item'])) ? array_merge($data['global_item'], $dataHr) : $dataHr;
                        }
                    }

                    if (isset($data['global_item']) && !empty($data['global_item'])) {
                        $data += [
                            'global_creator' => $curEmp->name,
                            'global_title' => $input->title,
                        ];
                        // Define {{ name }} or {{ title }}
                        $patternsArr = ['/\{\{\sname\s\}\}/', '/\{\{\stitle\s\}\}/'];
                        $replacesArr = ['global_creator', 'global_title'];
                        $this->pushNotificationAndEmail($data, $patternsArr, $replacesArr);
                    }

                    DB::commit();

                    // Return to request list Hr
                    if ($this->isScopeHrOrCompany) {
                        return redirect()->route($this->routeEditHr, $request->id)->with('flash_success', Lang::get('core::message.Save success'));
                    }

                    // Return to request list D-Lead
                    return redirect()->route($this->routeEdit, $request->id)->with('flash_success', Lang::get('core::message.Save success'));
                }
            } catch (Exception $ex) {
                DB::rollback();
                throw $ex;
            }
        }

        // If hasn't permission
        return view('core::errors.permission_denied');
    }

    /**
     * Push Notification or Email
     * @param [array] $data
     * @param [array] $patternsArr
     * @param [array] $replacesArr
     * @return boolean
     */
    public function pushNotificationAndEmail(array $data, array $patternsArr, array $replacesArr) {
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
        } catch (Exception $ex) {
            Log::info($ex);
        }

        return false;
    }

    /**
     * Check teamid available
     * @return boolean
     */
    public function checkTeamIdAvailable() {
        if (PermissonView::getInstance()->isScopeCompany()) {
            return true;
        } else {
            if (PermissonView::getInstance()->isScopeTeam()) {
                return true;
            } else {
                if (PermissonView::getInstance()->isScopeSelf()) {
                    return true;
                }
            }
        }

        // If hasn't permission
        return false;
    }

    /**
     * Export education request
     */
    public function exportEducationRequest()
    {
        $urlFilter = URL::route('education::education.request.hr.list') . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        $collection = $this->getEducationRequestGridData($dataSearch, $urlFilter, true);
        if (!$collection) {
            return redirect()->back()->with(['messages' => ['errors' => [trans('sales::message.None item found')]]]);
        }
        $fileName = Carbon::now()->format('Y-m-d') . '_export_education_request';
        //create excel file
        $objects = $this->getRoles();
        $status = $this->getStatus();
        Excel::create($fileName, function ($excel) use ($collection, $objects, $status, $dataSearch) {
            $excel->setTitle('Education request');
            $excel->sheet('Education request', function ($sheet) use ($collection, $objects, $status, $dataSearch) {
                $sheet->mergeCells('A1:K1');
                $sheet->cells('A1', function ($cells) {
                    $cells->setValue('Danh sách yêu cầu đào tạo');
                    $cells->setFontWeight('bold');
                    $cells->setFontSize('18');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                $sheet->cells('D2', function ($cells) {
                    $cells->setValue('Từ ngày');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                if (isset($dataSearch['from_date'])) {
                    $sheet->cell("E2", $dataSearch['from_date']);
                }
                $sheet->cells('F2', function ($cells) {
                    $cells->setValue('Đến ngày');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });
                if (isset($dataSearch['to_date'])) {
                    $sheet->cell("G2", $dataSearch['to_date']);
                }

                //set row header
                $rowHeader = ['No.', 'Title', 'Created date', 'Object', 'Scope', 'Education type', 'Status', 'Title courses', 'Keywords', 'Division', 'Phụ trách'];
                $sheet->row(3, $rowHeader);

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
                foreach ($collection as $order => $item) {
                    $objectStr = '';
                    foreach ($item['objects'] as $object) {
                        $objectStr .= $objects[$object['education_object_id']] . ', ';
                    }

                    $scopeStr = '';
                    foreach ($item['scopes'] as $scope) {
                        $scopeStr .= $scope['name'] . ', ';
                    }

                    $tagStr = '';
                    foreach ($item['tags'] as $tag) {
                        $tagStr .= $tag['name'] . ', ';
                    }

                    $divisionStr = '';
                    foreach ($item['employee']['teams'] as $division) {
                        $divisionStr .= $division['name'] . ', ';
                    }

                    $rowData = [
                        $order + 1,
                        $item->title,
                        \Carbon\Carbon::parse($item->created_at)->format('d-m-Y'),
                        trim($objectStr, ', '),
                        trim($scopeStr, ', '),
                        $item['type']['name'],
                        $status[$item->status],
                        in_array($item->status, [EducationRequest::STATUS_CLOSED, EducationRequest::STATUS_OPENING]) ? $item['course']['name'] : '',
                        trim($tagStr, ', '),
                        trim($divisionStr, ', '),
                        $item['assigned']['name'],
                    ];
                    $sheet->row($order + 4, $rowData);
                }
                $sheet->setWidth([
                    'B' => '25',
                    'C' => '25',
                    'D' => '25',
                    'E' => '25',
                    'F' => '25',
                    'G' => '25',
                    'H' => '25',
                    'I' => '25',
                    'J' => '25',
                    'K' => '25'
                ]);
                //set customize style
                $sheet->getStyle('A3:K3')->applyFromArray([
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
                $sheet->getStyle('A3:K' . ($collection->count() + 1))->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');

        View::viewErrorPermission();
    }

    /**
     * get Roles
     */
    public function getRoles() {
        return \Rikkei\Resource\View\getOptions::getInstance()->getRoles(true);
    }

    /**
     * get education by id
     * @return array
     */
    public function getEducationRequestById($id, $leaderIds = null)
    {
        if ($leaderIds) {
            return EducationRequest::whereIn('employee_id', $leaderIds)->find($id);
        }
        return EducationRequest::find($id);
    }

    /**
     * get education request has relationship
     * @return array
     */
    public function getEducationRequestByIdWithRelated($id)
    {
        $result = EducationRequest::with(['course' => function($query) {
            $query->select(['id', 'name']);
        }])->with(['reason' => function($query) {
            $query->select('id', 'description');
        }])->with(['reason.hr' => function($query) {
            $query->select(['employee_id', 'name', 'avatar_url']);
        }])->with(['teacher' => function($query) {
            $query->select('id', 'name');
        }])->with(['assigned' => function($query) {
            $query->select('id', 'name');
        }])->find($id);

        return $result;
    }

    /**
     * get education status
     * @return array
     */
    public function getStatus()
    {
        return EducationRequest::getInstance()->getStatus();
    }

    /**
     * get education division scope
     * @return array
     */
    public function getScopeTotal()
    {
        return EducationRequest::getInstance()->getScopeTotal();
    }

    /**
     * get type
     * @return array
     */
    public function getType()
    {
        return EducationType::all()->toArray();
    }

    /**
     * get tag
     * @return array
     */
    public function getTag()
    {
        return EducationTag::all()->toArray();
    }



    /**
     * get posision
     * @param  [int] $employeeId
     * @return [array]
     */
    public function getPosition($employeeId)
    {
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';
        $teamMemberTable = TeamMember::getTableName();
        $teamMemberTableAs = $teamMemberTable;

        if (empty($employeeId)) {
            return null;
        }

        $employeePosition = TeamMember::select(
            "{$employeeTableAs}.id as id",
            "{$employeeTableAs}.employee_code as employee_code",
            "{$employeeTableAs}.name as employee_name",
            DB::raw("GROUP_CONCAT(DISTINCT `{$roleTableAs}`.`role` SEPARATOR', ') as position"))
            ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$teamMemberTableAs}.employee_id")
            ->join("{$roleTable} as {$roleTableAs}", "{$roleTableAs}.id", '=', "{$teamMemberTableAs}.role_id")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
            ->where("{$teamMemberTableAs}.employee_id", $employeeId)
            ->first();

        return $employeePosition;
    }

    /**
     * get division
     * @param  [int] $employeeId
     * @return [array]
     */
    public function getDivision($employeeId)
    {
        $roleTable = Role::getTableName();
        $roleTableAs = 'role_table';
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';
        $teamMemberTable = TeamMember::getTableName();
        $teamMemberTableAs = $teamMemberTable;

        if (empty($employeeId)) {
            return null;
        }

        $employeePosition = TeamMember::select(
            "{$employeeTableAs}.id as id",
            "{$employeeTableAs}.employee_code as employee_code",
            "{$employeeTableAs}.name as employee_name",
            DB::raw("GROUP_CONCAT(DISTINCT `{$teamTableAs}`.`name` SEPARATOR', ') as division"))
            ->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$teamMemberTableAs}.employee_id")
            ->join("{$roleTable} as {$roleTableAs}", "{$roleTableAs}.id", '=', "{$teamMemberTableAs}.role_id")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
            ->where("{$teamMemberTableAs}.employee_id", $employeeId)
            ->first();

        return $employeePosition;
    }

    /**
     * get grid data of Education request list
     *
     * @param array $dataSearch
     * @param string $urlFilter
     * @param boolean $isExport
     * @return collection
     */
    public function getEducationRequestGridData($dataSearch = [], $urlFilter, $isExport = false)
    {
        $pager = Config::getPagerData($urlFilter, ['limit' => 20]);

        // Filter date
        $fromDate = isset($dataSearch['from_date']) && $dataSearch['from_date'] ? Carbon::createFromFormat('d/m/Y', $dataSearch['from_date'])->format('Y-m-d 00:00:00') : EducationRequest::MIN_DATE;
        $toDate = isset($dataSearch['to_date']) && $dataSearch['to_date'] ? Carbon::createFromFormat('d/m/Y', $dataSearch['to_date'])->format('Y-m-d 23:59:59') : EducationRequest::MAX_DATE;

        // Collection
        $collection = EducationRequest::with('objects');
        if (isset($dataSearch['objects']) && !empty($dataSearch['objects'])) {
            $collection->whereHas('objects', function($query) use ($dataSearch) {
                $query->whereIn("education_object_id", $dataSearch['objects']);
            });
        }
        $collection->with(['type' => function ($query) {
            $query->select(["id", "code", "name"]);
        }])->with(['scopes' => function ($query) {
            $query->select(["id", "code", "name"]);
        }]);
        $collection->with(['course' => function ($query) {
            $query->select(['id', 'name']);
        }])->with(['tags' => function ($query) {
            $query->select(["id", "name"]);
        }])->with(['assigned' => function($query) {
            $query->select('id', 'name');
        }]);
        if (isset($dataSearch['tags']) && !empty($dataSearch['tags'])) {
            $collection->whereHas('tags', function ($query) use ($dataSearch) {
                // Check search object
                if (isset($dataSearch['tags']) && !empty($dataSearch['tags'])) {
                    $query->select(["id", "name"]);
                    $query->whereIn("tag_id", (array)$dataSearch['tags']);
                }
            });
        }
        $collection->with(['employee', 'employee.teams'])
            ->with(['teams' => function ($query) {
            $query->select(['team_id', 'employee_id']);
        }]);

        // Check search division
        if (isset($dataSearch['division']) && !empty($dataSearch['division'])) {
            $collection->whereHas('teams', function ($query) use ($dataSearch) {
                // Check search object
                if (isset($dataSearch['division']) && !empty($dataSearch['division'])) {
                    $query->where("team_id", (int)$dataSearch['division']);
                }
            });
        }
        $collection->select(['id', 'type_id', 'course_id', 'employee_id', 'assign_id', 'title', 'created_at', 'status', 'scope_total']);
        if (!empty($dataSearch['employee_id']) && isset($dataSearch['employee_id'])) {
            $collection->whereIn('employee_id', $dataSearch['employee_id']);
        }

        // Check scope
        if (isset($dataSearch['scope_total']) && !empty($dataSearch['scope_total'])) {
            $collection->where("scope_total", (int)$dataSearch['scope_total']);
        }

        // Check search title
        if (isset($dataSearch['title']) && !empty($dataSearch['title'])) {
            $collection->where("title", "like", "%{$dataSearch['title']}%");
        }

        // Check search person assigned
        if (!empty($dataSearch['assign_id']) && isset($dataSearch['assign_id'])) {
            $collection->where("assign_id", "like", "%{$dataSearch['assign_id']}%");
        }

        // Check search status
        if (isset($dataSearch['status']) && !empty($dataSearch['status'])) {
            $collection->where("status", (int)$dataSearch['status']);
        }

        // Check search type
        if (isset($dataSearch['type']) && !empty($dataSearch['type'])) {
            $collection->where("type_id", (int)$dataSearch['type']);
        }

        // Check datetime from - to
        if (isset($dataSearch['from_date']) || isset($dataSearch['to_date'])) {
            //$collection->where('created_at', '>=', $fromDate)->where('created_at', '<=', $toDate);
            $collection->whereBetween('created_at', [$fromDate, $toDate]);
        }

        $collection->orderBy("id", "desc");

        // Apply filter
        EducationRequest::filterGrid($collection);

        // Export data
        if ($isExport) {
            return $collection->get();
        }

        // Apply pagination
        EducationRequest::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Get tag with ajax
     */
    public function getTagAjax(array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $config = array_merge($configDefault, $config);
        $collection = EducationTag::select(['id', 'name'])->where("name", 'like', '%'.$config['query'].'%');
        EducationTag::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }

        return $result;
    }

    /**
     * Get title by ajax
     */
    public function getTitleAjax(array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $config = array_merge($configDefault, $config);
        $collection = EducationRequest::select(['title'])
            ->where("title", 'like', '%'.$config['query'].'%');
        EducationRequest::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->title,
                'text' => $item->title,
            ];
        }

        return $result;
    }

    /**
     * Get person assigned by ajax
     */
    public function getPersonAssignedAjax(array $config = [])
    {
        $teamTbl = Team::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        $tblEmp = Employee::getTableName();
        $permissTbl = Permission::getTableName();
        $actionTbl = Action::getTableName();
        $empRoleTbl = EmployeeRole::getTableName();

        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $config = array_merge($configDefault, $config);

        // Special role
        $branchCode = (isset($config['employee_branch']) && !empty($config['employee_branch'])) ? $config['employee_branch'] : false;
        $action = 'education.request.team.hr';
        $collection = Employee::select($tblEmp . '.id', $tblEmp . '.name', $tblEmp . '.email')
            ->where(function ($scopeQuery) use ($action, $tblEmp, $empRoleTbl, $teamMemberTbl, $teamTbl, $permissTbl, $actionTbl, $branchCode) {
                // Get team leader with branch
                $scopeQuery->whereIn($tblEmp . '.id', function ($query) use ($teamMemberTbl, $teamTbl, $permissTbl, $branchCode) {
                    $query->select('tmb.employee_id')
                        ->from($teamMemberTbl . ' as tmb')
                        ->join($teamTbl . ' as team', 'team.id', '=', 'tmb.team_id')
                        ->join($permissTbl . ' as permiss1', function ($join) {
                            $join->on('team.id', '=', 'permiss1.team_id')
                                ->on('tmb.role_id', '=', 'permiss1.role_id');
                        });
                        if ($branchCode) {
                            $query->where('team.branch_code', $branchCode);
                        };
                    $query->where('tmb.role_id', 1)
                        ->where('permiss1.scope', '!=', Permission::SCOPE_NONE);
                })
                // Get team Hr
                ->orWhereIn($tblEmp . '.id', function ($query) use ($teamMemberTbl, $teamTbl, $permissTbl, $tblEmp) {
                    $query->select('tmb2.employee_id')
                        ->from($teamMemberTbl . ' as tmb2')
                        ->join($tblEmp . ' as empl2', 'empl2.id', '=', 'tmb2.employee_id')
                        ->join($teamTbl . ' as team2', 'team2.id', '=', 'tmb2.team_id')
                        ->join($permissTbl . ' as permiss2', function ($join) {
                            $join->on('team2.id', '=', 'permiss2.team_id')
                                ->on('tmb2.role_id', '=', 'permiss2.role_id');
                        })
                        ->where('team2.type', Team::TEAM_TYPE_HR)
                        ->where('permiss2.scope', '!=', Permission::SCOPE_NONE);
                })
                // Get special role with branch
                ->orWhereIn($tblEmp . '.id', function ($query) use ($action, $teamMemberTbl, $teamTbl, $empRoleTbl, $permissTbl, $actionTbl, $branchCode) {
                    $query->select('emp_role.employee_id')
                        ->from($empRoleTbl . ' as emp_role')
                        ->join($teamMemberTbl . ' as tmb3', 'emp_role.employee_id', '=', 'tmb3.employee_id')
                        ->join($teamTbl . ' as team3', 'team3.id', '=', 'tmb3.team_id')
                        ->join($permissTbl . ' as permiss3', 'emp_role.role_id', '=', 'permiss3.role_id')
                        ->join($actionTbl . ' as action3', 'permiss3.action_id', '=', 'action3.id')
                        ->where('action3.name', $action);
                        if($branchCode) {
                            $query->where('team3.branch_code', $branchCode);
                        };
                    $query->where('team3.branch_code', $branchCode)
                        ->where('permiss3.scope', '!=', Permission::SCOPE_NONE);
                });
            })->where($tblEmp . '.name', 'like', '%'.$config['query'].'%');

        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }

        return $result;
    }

    /**
     * Get person assigned by ajax
     */
    public function getCourseAjax(array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $config = array_merge($configDefault, $config);
        $collection = EducationCourse::select(['id', 'name'])
            ->where("name", 'like', '%'.$config['query'].'%')
            ->where("status", 3);
        EducationCourse::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name,
            ];
        }

        return $result;
    }

    /**
     * Get division branch by current employee
     */
    public function getBranchByCurrentEmployee($employeeId)
    {
        $teamTable = Team::getTableName();
        $teamTableAs = 'team_table';
        $teamMemberTable = TeamMember::getTableName();
        $teamMemberTableAs = $teamMemberTable;

        if (empty($employeeId)) {
            return null;
        }

        $result = TeamMember::select(
            "{$teamTableAs}.branch_code as branch")
            ->join("{$teamTable} as {$teamTableAs}", "{$teamTableAs}.id", '=', "{$teamMemberTableAs}.team_id")
            ->where("{$teamMemberTableAs}.employee_id", $employeeId)
            ->groupBy("{$teamTableAs}.branch_code")
            ->first();

        return $result;
    }

    public function getIdBranchCode($branch_code)
    {
        return Team::where('code', $branch_code)->first(['id']);
    }

    public function getEmployeeWithScopeCompany()
    {
        $emailArr = SettingAddressMail::lists('email');
        $result = Employee::whereIn('email', $emailArr)->select(['id', 'name', 'email'])->get()->toArray();

        return $result;
    }

    public function getEmployeeWithScopeBranch($scopeArr, $is_mail = false)
    {
        $selectField = ['id', 'name'];
        if ($is_mail) {
            $selectField = ['id', 'name', 'email'];
        }
        $branchCode = Team::whereIn('id', array_values($scopeArr))->groupBy('branch_code')->lists('branch_code');
        $idBranchCode = Team::whereIn('code', $branchCode)->lists('id');
        $branchMail = SettingAddressMail::whereIn('team_id', $idBranchCode)->lists('email');
        $result = Employee::whereIn('email', $branchMail)->select($selectField)->get()->toArray();

        return $result;
    }

    public function checkSelfScopeDivision($scopeArr)
    {
        $employee = PermissonView::getInstance()->getEmployee();
        $division = self::getDivision($employee->id)['division'];

        return Team::where('id', $scopeArr)->where('name', $division)->first();
    }

    public function getEmployeeWithScopeDivision($scopeArr, $act)
    {
        $employee = Employee::getTableName();
        $empRoleTable = EmployeeRole::getTableName();
        $teamMemTable = TeamMember::getTableName();
        $teamTable = Team::getTableName();
        $permTable = Permission::getTableName();
        $action = Action::getTableName();
        $selectField = ["{$employee}.id", "{$employee}.name", "{$employee}.email"];
        $result = Employee::select($selectField)->join("{$empRoleTable}", "{$empRoleTable}.employee_id", '=', "{$employee}.id")
            ->join("{$teamMemTable}", "{$teamMemTable}.employee_id", '=', "{$empRoleTable}.employee_id")
            ->join("{$teamTable}", "{$teamTable}.id", '=', "{$teamMemTable}.team_id")
            ->join("{$permTable}", "{$permTable}.role_id", '=', "{$empRoleTable}.role_id")
            ->join("{$action}", "{$action}.id", '=', "{$permTable}.action_id")
            ->where("{$action}.name", $act)->where("{$permTable}.scope", '!=', 0)->whereIn("{$teamTable}.id", $scopeArr)->get()->toArray();

        return $result;
    }

    public function getEmployeeWithMultiScopeDivision($scopeArr)
    {
        $branchCode = Team::whereIn('id', array_values($scopeArr))->groupBy('branch_code')->lists('branch_code');
        $idBranchCode = Team::whereIn('code', $branchCode)->lists('id');
        $branchMail = SettingAddressMail::whereIn('team_id', $idBranchCode)->lists('email');

        return Employee::whereIn('email', $branchMail)->select(['id', 'name'])->get()->toArray();
    }

    public function getEmployeeSentRequest($id)
    {
        return Employee::where('id', $id)->select(['id', 'name'])->get()->toArray();
    }

    public function getLeaderIdFromTeamId($teamIds = null)
    {
        $teamArr = Team::select(['id', 'name', 'parent_id', 'leader_id'])->get()->toArray();
        $childTeamIds = [];
        $employeeIds = [];
        if ($teamIds != null) {
            foreach ($teamIds as $teamId) {
                $recursiveArr = CommonHelper::getInstance()->getTeamIdRecursive($teamArr, $teamId);
                if (!empty($recursiveArr)) {
                    $childTeamIds = array_merge($childTeamIds, CommonHelper::getInstance()->getKeyArray($recursiveArr));
                }
            }
            $childTeamIds = array_unique(array_merge($childTeamIds, $teamIds));
            $employeeIds = array_column(TeamMember::whereIn('team_id', $childTeamIds)->where('role_id', 1)->select('employee_id')->get()->toArray(), 'employee_id');
            $employeeIds[] = PermissonView::getInstance()->getEmployee()->id;
            return array_unique($employeeIds);
        }

        return $employeeIds;
    }
}
