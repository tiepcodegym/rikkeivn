<?php

namespace Rikkei\Project\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config as SupportConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View as ViewLaravel;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\ProjectPlanComment;
use Rikkei\Project\Model\ProjectPlanResource;
use Rikkei\Project\Model\RiskAttach;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Permission;
use Rikkei\Welfare\Model\WelfareFormQuestion;
use Illuminate\Support\Facades\Validator;

class ProjectPlanController extends Controller
{
    private $project;

    public function __construct()
    {
        $exceptRoutes = [];
        $projectId = request()->route()->parameter('projectId');
        $projectId = $projectId !== null ? $projectId : request()->get('projectId');
        $curRoute = request()->route()->getName();
        // current route not in except route
        if (!in_array($curRoute, $exceptRoutes)) {
            if (preg_match('/^\d+$/', $projectId)) {
                $this->project = Project::where([['id', $projectId], ['status', Project::STATUS_APPROVED]])->first();
            }
            // check project not exist
            if ($this->project === null) {
                ViewProject::errorNotFound();
            }
            // check access view
            if (!ViewProject::isAccessViewProject($this->project)) {
                ViewProject::errorNotPermission();
            }
        }
    }

    /*
     * Display dashboard page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Breadcrumb::add('Project Dashboard', route('project::dashboard'));
        Breadcrumb::add(Lang::get('project::view.Project Plan'));
        Menu::setFlagActive('project');

        $projectId = $this->project->id;
        $collectionModel = ProjectPlanComment::getCommentData($projectId);
        $resourceList = ProjectPlanResource::getProjectPlanResource($projectId);
        $isPmOfProject = ProjectMember::checkIsPmOfProject($projectId, auth()->id());
        return view('project::plan.comment', [
            'collectionModel' => (new ProjectPlanComment())->processingBeforeRender($collectionModel),
            'resourceList' => $resourceList,
            'isPmOfProject' => $isPmOfProject,
            'projectId' => $projectId,
            'project' => $this->project,
            'pmActive' => $this->project->getPMActive(),
        ]);
    }

    /*
     * show list comment by ajax
     */
    public static function commentListAjax($projectId)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response['success'] = 1;
        $collectionModel = ProjectPlanComment::getCommentData($projectId);
        $response['html'] = ViewLaravel::make('project::plan.comment_list', [
            'collectionModel' => (new ProjectPlanComment())->processingBeforeRender($collectionModel)
        ])->render();
        return response()->json($response);
    }

    /*
     * save comment
    */
    public function saveComment()
    {
        try {
            return DB::transaction(function () {
                $employees = [];
                $content = request()->get('comment');
                $contentMail = $content;
                $project = $this->project;
                $projId = $project->id;

                $content = str_replace("<br></div><div>", "\n", $content);
                $content = str_replace("</div><div>", "\n", $content);
                $content = str_replace("<br>", "\n", $content);
                $content = str_replace("<div>", "\n", $content);
                $content = str_replace("</div>", "", $content);
                $content = str_replace(" ", "&nbsp;", $content);

                $contentMail = str_replace("<br></div><div>", "<br>", $contentMail);
                $contentMail = str_replace("</div><div>", "<br>", $contentMail);
                $contentMail = str_replace("<br>", "<br>", $contentMail);
                $contentMail = str_replace("<div>", "<br>", $contentMail);
                $contentMail = str_replace("</div>", "", $contentMail);

                $membersList = request()->get('membersList');
                $limit = request()->get('limit');
                $collection = ProjectMember::getProjectMemberById($projId);
                $content = strip_tags($content);
                $contentMail = strip_tags($contentMail, '<br>');
                foreach ($collection as $key => $value) {
                    $name = str_replace(" ", "&nbsp;", $value->name);
                    $content = str_replace($name, "<name-tag>".$name."</name-tag>", $content);
                    $contentMail = str_replace($value->name, "<b>".$value->name.', '."</b>", $contentMail);
                }
                $requestData = [
                    'project_id' => $projId,
                    'content' => $content,
                ];

                ProjectPlanComment::saveData($requestData);
                $collectionModel = ProjectPlanComment::getCommentData($projId);
                $collectionModel = (new ProjectPlanComment())->processingBeforeRender($collectionModel);
                $pagerTotal = trans('team::view.Total :itemTotal entries / :pagerTotal page', [
                    'itemTotal' => $collectionModel->total(),
                    'pagerTotal' => ceil($collectionModel->total() / $limit),
                ]);

                //send mail to leader
                if (isset($project)) {
                    $data = [
                        'pm' => Employee::getEmpIsWorking($project->manager_id)->name,
                        'projName' => $project->name,
                        'comment' => $contentMail,
                        'link' => route('project::plan.comment', ['projectId' => $projId])
                    ];
                    $emailQueue = new EmailQueue();
                    $subject = trans('project::email.title', ['projName' => $project->name]);
                    $emailQueue->setTo(Employee::getEmpIsWorking($project->manager_id)->email, Employee::getEmpIsWorking($project->manager_id)->name)
                        ->setTemplate('project::emails.notification_comment_to_pm', $data)
                        ->setSubject($subject)
                        ->save();
                }

                // send mail to member
                $projectMember = ProjectMember::getProjectMemberById($projId);
                if (count($projectMember) > 0) {
                    foreach ($projectMember as $value) {
                        array_push($employees, [
                            'id' => $value->id,
                            'name' => $value->name,
                            'email' => $value->href
                        ]);
                    }
                    if (count($membersList) > 0) {
                        $membersTagList = array_map("unserialize", array_unique(array_map("serialize", $membersList)));
                        foreach ($membersTagList as $emp) {
                            if ($emp['id'] !== $project->manager_id) {
                                $data = [
                                    'member' => $emp['name'],
                                    'projName' => $project->name,
                                    'comment' => $contentMail,
                                    'link' => route('project::plan.comment', ['projectId' => $projId])
                                ];
                                $emailQueue = new EmailQueue();
                                $subject = trans('project::email.title', ['projName' => $project->name]);
                                $emailQueue->setTo($emp['email'], $emp['name'])
                                    ->setTemplate('project::emails.notification_comment_to_member', $data)
                                    ->setSubject($subject)
                                    ->save();
                            }
                        }
                    }
                }
                return response()->json([
                    'success' => 1,
                    'message' => Lang::get('project::view.Save comment success'),
                    'data' => view('project::plan.comment_list', ['collectionModel' => $collectionModel, 'limit' => $limit])->render(),
                    'pagerTotal' => $pagerTotal,
                ]);
            });
        } catch (\Exception $exception) {
            logger(Lang::get('project::view.An error occurred'), ['e' => $exception->getMessage()]);
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::view.Save comment error')
            ]);
        }
    }

    /*
     * upload file
    */
    public function uploadFile()
    {
        // validate file
        if (!request()->hasFile('files')) {
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::view.File Not found'),
            ]);
        }

        // current user is not PM project
        $currentUser = Permission::getInstance()->getEmployee();
        if (ProjectMember::checkIsPmOfProject($this->project->id, $currentUser->id) === null) {
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::message.You do not have permission to upload the file!'),
            ]);
        }

        try {
            return DB::transaction(function () {
                $uploadData = request()->all();
                $projId = $this->project->id;
                $path = ProjectPlanResource::ATTACH_FOLDER . '/';
                $now = Carbon::now();
                $files = $uploadData['files'];
                if (!Storage::exists(ProjectPlanResource::ATTACH_FOLDER)) {
                    Storage::makeDirectory(ProjectPlanResource::ATTACH_FOLDER);
                }
                $collection = [];
                for ($i = 0; $i < count($files); $i++) {
                    $file = $files[$i];
                    $subName = $now->format('ymd') . $now->format('hms');
                    $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
                    $urlPath =  $subName . $fileName;
                    Storage::put($path . $subName . $fileName, file($file));
                    $realFileName = $file->getClientOriginalName();
                    $data = [
                        'project_id' => $projId,
                        'file_url' => $urlPath,
                        'created_by' => auth()->id(),
                        'file_name' => $realFileName
                    ];
                    ProjectPlanResource::saveData($data);
                    $collection[] = ProjectPlanResource::getNewProjectPlanResource($projId, $urlPath);
                }
                return response()->json([
                    'success' => 1,
                    'message' => Lang::get('project::view.Save file success'),
                    'data' => $collection
                ]);
            });
        } catch (\Exception $exception) {
            logger(Lang::get('project::view.An error occurred'), ['e' => $exception->getMessage()]);
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::view.Save file error')
            ]);
        }
    }

    /*
     * download file
    */
    public function downloadFile($filename)
    {
        try {
            $myFile = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".ProjectPlanResource::URL_FOLDER.$filename);
            $file = ProjectPlanResource::where('file_url', $filename)
                ->where('project_id', $this->project->id)
                ->select('file_name')->first();
            if ($file === null || !file_exists($myFile)) {
                return redirect()->back()->with('messages', ['errors' => [Lang::get('project::message.The file does not exist!')]]);
            }
            $headers = ['Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            return response()->download($myFile, $file ? $file->file_name : null, $headers);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            logger(Lang::get('project::view.An error occurred'), ['e' => $exception->getMessage()]);
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::view.Download file error'),
            ]);
        }
    }

    public function saveCommentPlan(Request $request)
    {
        $data = $request->all();
        DB::beginTransaction();
        try {
            if (!empty($data['attach_comment'][0]) && count($data['attach_comment'])) {
                if (isset($data['attach_comment'])) {
                    $valid = Validator::make($data, [
                        'attach_comment.*' => 'file|mimes:doc,docx,xlsx,pdf,png,jpg,gif,jpeg|max:5120',
                    ]);
                    if ($valid->fails()) {
                        $response['message_error'] = $valid->errors();
                        $response['status'] = false;
                        return response()->json($response);
                    }
                    RiskAttach::uploadFiles($data['projectId'], $data['attach_comment'], RiskAttach::TYPE_OTHERS);
                }
            }
            DB::commit();
            $response = RiskAttach::getAttachs($data['projectId'], RiskAttach::TYPE_OTHERS);
            return response()->json($response);
        } catch (Exception $ex) {
            \Log::info($ex);
            DB::rollBack();
            return redirect()->back()->with('messages', ['error' => [trans('project::message.Add comment error.')]]);
        }
    }

    /*
     * get member of Project
    */
    public function getProjectMember()
    {
        return response()->json([
            'success' => 1,
            'data' => ProjectMember::getProjectMemberById($this->project->id),
        ]);
    }

    public function deleteFile($fileName)
    {
        $file = ProjectPlanResource::where('file_url', $fileName)
            ->where('project_id', $this->project->id)
            ->select('id', 'file_name')->first();
        // file not found
        if ($file === null) {
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::message.The file does not exist!'),
            ]);
        }
        // current user is not PM project
        $currentUser = Permission::getInstance()->getEmployee();
        if (ProjectMember::checkIsPmOfProject($this->project->id, $currentUser->id) === null) {
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::message.You do not have permission to delete the file!'),
            ]);
        }
        DB::beginTransaction();
        try {
            $path = "app/" . SupportConfig::get('general.upload_storage_public_folder') . "/" . ProjectPlanResource::URL_FOLDER;
            $myFile = storage_path($path . $fileName);
            $file->delete();
            file_exists($myFile) && unlink($myFile);
            DB::commit();
            return response()->json([
                'success' => 1,
                'message' => Lang::get('project::message.Delete the file successfully.'),
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex->getMessage());
            return response()->json([
                'error' => 1,
                'message' => Lang::get('project::message.Delete the file failed!'),
            ]);
        }
    }
}
