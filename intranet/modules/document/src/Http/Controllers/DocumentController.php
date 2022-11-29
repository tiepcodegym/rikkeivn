<?php

namespace Rikkei\Document\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Document\Models\Document;
use Rikkei\Document\Models\Type;
use Rikkei\Document\View\DocConst;
use Rikkei\Document\Models\DocHistory;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Document\Models\File;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Document\Models\DocComment;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\DB;
use Rikkei\Document\Models\DocRequest;
use Rikkei\Team\Model\Employee;
use Rikkei\Document\Models\DocPublish;
use Rikkei\Team\Model\Permission as PermissModel;
use Rikkei\Team\Model\Team;
use Validator;
use Storage;
use Rikkei\Magazine\Model\Magazine;

class DocumentController extends Controller
{
    public function _construct()
    {
        Menu::setActive('document');
        Breadcrumb::add(trans('doc::view.Document'), route('doc::admin.index'));
    }

    /**
     * list document
     * @return view
     */
    public function index()
    {
        $collectionModel = Document::getGridData();
        $listTypes = Type::getList();
        $permissType = Permission::getInstance()->isAllow('doc::admin.type.index');
        return view('doc::doc.index', compact('collectionModel', 'listTypes', 'permissType'));
    }

    /**
     * edit/create document
     * @param type $id
     * @return type
     */
    public function edit(Request $request, $id = null)
    {
        $title = trans('doc::view.Create');
        $item = null;
        if ($id) {
            $item = Document::findOrFail($id);
        }

        $requestId = $request->get('request_id');
        $docPermiss = Document::getDocPermission($item, $requestId);
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }

        $listFiles = collect();
        $selectedTeams = collect();
        $commentsList = collect();
        $attachFiles = collect();
        $accoutsPublish = collect();
        $histories = collect();
        $listPublished = collect();
        if ($item) {
            $title = trans('doc::view.Edit');
            $listFiles = $item->listFiles();
            $selectedTeams = $item->teams;
            $commentsList = DocComment::getData($id);
            $attachFiles = $item->attachFiles;
            $accoutsPublish = Document::getAccountPublish($id);
            if ($item->status == DocConst::STT_PUBLISH) {
                $listPublished = DocPublish::getByDocId($id);
            }
            $histories = DocHistory::getByDocument($id);
        }
        Breadcrumb::add($title);

        $docRequest = null;
        if ($requestId) {
            $docRequest = DocRequest::find($requestId);
        } elseif ($item) {
            $docRequest = $item->request;
        } else {
            //else none
        }
        $listTypes = Type::getList();
        $teamList = TeamList::getList();
        $permisEditSetting = Permission::getInstance()->isAllow('core::setting.system.data.index');
        return view('doc::doc.edit', compact(
            'item',
            'listTypes',
            'coordinator',
            'isCoordinator',
            'histories',
            'listFiles',
            'teamList',
            'selectedTeams',
            'docPermiss',
            'permisEditSetting',
            'commentsList',
            'attachFiles',
            'accoutsPublish',
            'docRequest',
            'listPublished'
        ));
    }

    /**
     * save document
     * @param Request $request
     * @return type
     */
    public function save(Request $request)
    {
        $data = $request->except('_token');
        $doc = null;
        if (isset($data['id'])) {
            $doc = Document::findOrFail($data['id']);
        }
        $docPermiss = Document::getDocPermission($doc, $request->get('request_id'));
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        //if has permiss edit
        if ($docPermiss['submit']) {
            $maxSize = DocConst::fileMaxSize();
            if (isset($data['file']) || isset($data['file_link'])) {
                $valid = Validator::make($data, [
                    'code' => 'required|unique:documents,code'. (isset($data['id']) ? ',' . $data['id'] : '') .'|max:255',
                    'file' => 'required_without_all:file_id,file_link|max:' . $maxSize,
                    'team_ids' => 'required',
                ]);
            } else {
                $valid = Validator::make($data, [
                    'code' => 'required|unique:documents,code'. (isset($data['id']) ? ',' . $data['id'] : '') .'|max:255',
                    'magazine_name' => 'required|max:255',
                    'id_magazine' => 'required',
                    'team_ids' => 'required',
                ]);
            }
            $valid->setAttributeNames([
                'code' => trans('doc::view.Document code'),
                'team_ids' => trans('doc::view.Team'),
                'team_publish_ids' => trans('doc::view.Team publish'),
                'account_ids' => trans('doc::view.Account'),
            ]);
            if ($valid->fails()) {
                return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors($valid->errors());
            }
            $excludeExt = DocConst::EXCLUDE_EXT;
            $docFile = $request->file('document');
            if ($docFile && in_array($docFile->getClientOriginalExtension(), $excludeExt)) {
                return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'messages',
                            ['errors' => [trans('doc::message.File upload not allow extension', ['mimes' => implode(', ', $excludeExt)])]]
                        );
            }
        }
        // Set default approved
        $data['status'] = DocConst::STT_PUBLISH;
        $item = Document::insertOrUpdate($data, $doc, $docPermiss);
        if (!$item) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
        }
        $message = trans('doc::message.Create successful');
        if ($doc) {
            $message = trans('doc::message.Update successful');
        }
        return redirect()
                    ->route('doc::admin.edit', $item->id)
                    ->with('messages', ['success' => [$message]]);
    }

    /*
     * check document code exists
     */
    public function checkExists(Request $request)
    {
        $code = $request->get('code');
        if (!$code) {
            return 'false';
        }
        $id = $request->get('id');
        $hasDoc = Document::where('code', $code);
        if ($id) {
            $hasDoc->where('id', '!=', $id);
        }
        return $hasDoc->count() > 0 ? 'false' : 'true';
    }

    /**
     * feedback document
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function feedback($id, Request $request)
    {
        $valid = Validator::make($request->all(), [
            'feedback_reason' => 'required'
        ]);
        if ($valid->fails()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors($valid->errors());
        }
        $item = Document::findOrFail($id);
        $docPermiss = Document::getDocPermission($item);
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        if (Document::feedbackItem($item, $request->get('feedback_reason'))) {
            return redirect()
                    ->back()
                    ->with('messages', ['success' => [trans('doc::message.Feedback successful')]]);
        }
        return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
    }

    /**
     * publis document
     */
    public function publish($id, Request $request)
    {
        $doc = Document::findOrFail($id);
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['publish']) {
            CoreView::viewErrorPermission();
        }
        $publishAll = (int) $request->get('publish_all');
        $isSendMail = $request->get('send_mail');

        if (!$publishAll) {
            $teamIds = $request->get('team_ids');
            $accoutnIds = $request->get('account_ids');
            if (!$teamIds && !$accoutnIds) {
                return redirect()
                        ->back()
                        ->withInput()
                        ->with('messages', ['errors' => [trans('doc::message.Please select team or input account')]]);
            }
            $teamIds = \Rikkei\Team\Model\Team::teamChildIds($teamIds);
            $listEmps = Document::getListEmployee($teamIds, $accoutnIds);

            if ($listEmps->isEmpty()) {
                return redirect()
                        ->back()
                        ->withInput()
                        ->with('messages', ['errors' => [trans('doc::message.None account valid')]]);
            }
        } else {
            if ($isSendMail) {
                $listEmps = Employee::select('id', 'name', 'email')
                        ->where(function ($query) {
                            $query->whereNull('leave_date')
                                ->orWhereRaw('DATE(leave_date) > CURDATE()');
                        })->get();
            }
        }
        if ($isSendMail) {
            $emailSubject = $request->get('email_subject');
            $emailContent = $request->get('email_content');
            $dataEmailQueues = [];
            $dataTemplateEmail = [
                'docTitle' => $doc->code,
                'detailLink' => $doc->getViewLink()
            ];
            foreach ($listEmps as $emp) {
                if (isset($dataEmailQueues[$emp->id])) {
                    continue;
                }
                $dataTemplateEmail += [
                    'dearName' => $emp->name
                ];
                $dataTemplateEmail['content'] = preg_replace(
                    ['/\{\{\sname\s\}\}/'],
                    [$emp->name],
                    $emailContent
                );
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($emp->email, $emp->name)
                    ->setSubject($emailSubject)
                    ->setTemplate('doc::mails.doc-anouce-publish', $dataTemplateEmail);
                $dataEmailQueues[$emp->id] = $emailQueue->getValue();
            }
        }
        DB::beginTransaction();
        try {
            if (!$publishAll) {
                DocPublish::insertData($id, $teamIds, $accoutnIds);
            } else {
                DocPublish::insertData($id, [], []); //empty teamIds and accountIds = remove
            }
            if ($isSendMail) {
                $empIds = $listEmps->lists('id')->toArray();
                $chunk = 500;
                $totalEmp = count($dataEmailQueues);
                $numChunk = ceil($totalEmp / $chunk);
                for ($iChunk = 0; $iChunk < $numChunk; $iChunk++) {
                    $idxStart = $iChunk * $chunk;
                    $length = $chunk;
                    $chunkDataEmailQueues = array_slice($dataEmailQueues, $idxStart, $length);
                    $chunkEmpIds = array_slice($empIds, $idxStart, $length);

                    EmailQueue::insert($chunkDataEmailQueues);
                    \RkNotify::put($chunkEmpIds, $emailSubject, $dataTemplateEmail['detailLink'], ['category_id' => RkNotify::CATEGORY_PROJECT]);
                }
            }
            Document::insertOrUpdate(['status' => DocConst::STT_PUBLISH, 'publish_all' => $publishAll], $doc, $docPermiss);

            DB::commit();
            return redirect()->back()
                    ->with('messages', ['success' => [trans('doc::message.Publish document successful')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()
                    ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
        }
    }

    /**
     * download document
     * @param type $id
     * @return type
     */
    public function download($docId, $id)
    {
        $doc = Document::findOrFail($docId);
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        $file = File::findOrFail($id);
        $fileUrl = trim(DocConst::UPLOAD_DIR, '/') . '/' . $file->url;
        if (!Storage::disk('public')->exists($fileUrl)) {
            return redirect()->back()->with('messages', ['errors' => [trans('doc::message.File does not exist')]]);
        }
        $path = storage_path('app/public/' . $fileUrl);
        return response()->file($path, [
            'Content-Type' => $file->mimetype,
            'Content-Disposition' => 'inline; filename="'. $file->name .'"'
        ]);
    }

    /**
     * delete document
     * @param type $id
     * @return type
     */
    public function delete($id)
    {
        $item = Document::findOrFail($id);
        $docPermiss = Document::getDocPermission($item);
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        if ($item->deleteItem()) {
            return redirect()
                    ->back()
                    ->with('messages', ['success' => [trans('doc::message.Delete successful')]]);
        }
        return redirect()
                ->back()
                ->with('messages', ['errors' => [trans('doc::message.An error occurred')]]);
    }

    /*
     * set current file of document
     */
    public function setCurrentFile($docId, $fileId)
    {
        $doc = Document::findOrFail($docId);
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['submit']) {
            CoreView::viewErrorPermission();
        }
        $doc->setCurrentFile($fileId);
        return redirect()
                ->back()
                ->with('messages', ['success' => [trans('doc::message.Do action successful')]]);
    }

    /*
     * delete file of document
     */
    public function deleteFile($docId, $fileId)
    {
        $doc = Document::findOrFail($docId);
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['submit']) {
            CoreView::viewErrorPermission();
        }
        $file = File::findOrFail($fileId);
        if ($file->magazine_id != null) {
            $magazine = Magazine::where('id', $file->magazine_id)->first();
            $magazine->delete();
        }
        $file->delete();
        return redirect()
                    ->back()
                    ->with('messages', ['success' => [trans('doc::message.Delete successful')]]);
    }

    /*
     * add assignee
     */
    public function addAssignee($docId, Request $reqeust)
    {
        $doc = Document::findOrFail($docId);
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        $valid = Validator::make($reqeust->all(), [
            'type' => 'required',
            'employee_id' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('doc::message.Please input valid data'), 422);
        }
        $type = $reqeust->get('type');
        $employeeId = $reqeust->get('employee_id');
        $employee = Employee::find($employeeId);
        //check valid type and employee
        if (!in_array($type, [DocConst::TYPE_ASSIGNE_REVIEW, DocConst::TYPE_ASSIGNE_APPROVE, DocConst::TYPE_ASSIGNE_PUBLISH, DocConst::TYPE_ASSIGNE_EDITOR])
                && ($employee || $type == DocConst::TYPE_ASSIGNE_EDITOR)) {
            return response()->json(trans('doc::message.Invalid data'), 422);
        }
        DB::beginTransaction();
        try {
            if ($type == DocConst::TYPE_ASSIGNE_PUBLISH) {
                $itemHistory = DocHistory::insertData($docId, ['publisher_id' => $doc->publisher_id], ['publisher_id' => $employeeId]);
                $doc->update(['publisher_id' => $employeeId]);
                if ($itemHistory) {
                    Document::actionAssigne($doc, $employee, $type);
                }
            } elseif ($type == DocConst::TYPE_ASSIGNE_EDITOR) {
                $arrEmpIds = $employeeId;
                $editorIds = [];
                if ($arrEmpIds) {
                    foreach ($arrEmpIds as $empId) {
                        if (!$empId || $empId == 'null') {
                            continue;
                        }
                        $editorIds[$empId] = ['type' => $type];
                    }
                }
                $oldEditors = $doc->editors()->lists('id')->toArray();
                $addedEditors = array_diff($arrEmpIds, $oldEditors);
                $itemHistory = DocHistory::insertData($docId, ['editor_ids' => $oldEditors], ['editor_ids' => $arrEmpIds]);
                $doc->editors()->sync($editorIds);

                if ($addedEditors) {
                    Document::actionAssigne($doc, Employee::whereIn('id', $addedEditors)->get(), DocConst::TYPE_ASSIGNE_EDITOR);
                }
            } else {
                //check exists
                $exists = $doc->assignees()
                        ->wherePivot('type', $type)
                        ->where('id', $employeeId)
                        ->first();
                if ($exists) {
                    return response()->json(trans('doc::message.Employee had already added'), 422);
                }
                //attach
                $doc->assignees()->attach($employeeId, ['type' => $type]);
                //noti to assignee
                Document::actionAssigne($doc, $employee, $type);
                //insert history
                $keyHistory = ($type == DocConst::TYPE_ASSIGNE_REVIEW) ? 'add_reviewer' : 'add_approver';
                $itemHistory = DocHistory::insertData($docId, [$keyHistory => DocConst::getAccount($employee->email)], [$keyHistory => true]);
            }
            if ($itemHistory) {
                $currUser = Permission::getInstance()->getEmployee();
                $itemHistory->name = $currUser->name;
                $itemHistory->email = $currUser->email;
            }
            if ($type == DocConst::TYPE_ASSIGNE_PUBLISH) {
                $displayName = DocConst::getAccount($employee->email);
            } else {
                $listEmployees = $doc->assignees()->where('type', $type)->get();
                $displayName = null;
                if (!$listEmployees->isEmpty()) {
                    $listEmployees = $listEmployees->map(function ($emp) {
                        $emp->account = DocConst::getAccount($emp->email);
                        return $emp;
                    });
                    $displayName = $listEmployees->implode('account', ', ');
                }
            }

            DB::commit();
            return response()->json([
                'message' => trans('doc::message.Do action successful'),
                'itemHistory' => $itemHistory ? view('doc::includes.doc-history-item', ['history' => $itemHistory])->render() : null,
                'displayName' => $displayName
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return response()->json(trans('doc::message.An error occurred'), 500);
        }
    }

    /*
     * delete assignee
     */
    public function deleteAssignee($docId, $empId, Request $request)
    {
        $doc = Document::findOrFail($docId);
        $docPermiss = Document::getDocPermission($doc);
        if (!$docPermiss['view']) {
            CoreView::viewErrorPermission();
        }
        $employee = Employee::findOrFail($empId);
        $type = $request->get('type');
        if (!$type || !in_array($type, [DocConst::TYPE_ASSIGNE_REVIEW, DocConst::TYPE_ASSIGNE_APPROVE])) {
            return response()->json(trans('doc::message.Invalid data'), 422);
        }
        if ($doc->assignees()->wherePivot('type', $type)->get()->count() == 1) {
            return response()->json(trans('doc::message.Can not delete this assignee'), 422);
        }
        DB::beginTransaction();
        try {
            $doc->assignees()
                    ->wherePivot('type', $type)
                    ->detach($empId);
            //check remain assignee reviewed or approved
            $updateStatus = null;
            if ($doc->status == DocConst::STT_SUBMITED &&
                    $doc->reviewers->count() == $doc->reviewers()->wherePivot('status', DocConst::STT_REVIEWED)->get()->count()) {
                $updateStatus = DocConst::STT_REVIEWED;
            }
            if ($updateStatus) {
                Document::actionStatus($doc, ['status' => $updateStatus]);
            }

            //insert history
            $keyHistory = ($type == DocConst::TYPE_ASSIGNE_REVIEW) ? 'delete_reviewer' : 'delete_approver';
            $itemHistory = DocHistory::insertData($docId, [$keyHistory => DocConst::getAccount($employee->email)], [$keyHistory => true]);
            $currUser = Permission::getInstance()->getEmployee();
            $itemHistory->name = $currUser->name;
            $itemHistory->email = $currUser->email;

            DB::commit();
            return response()->json([
                'message' => trans('doc::message.Do action successful'),
                'itemHistory' => view('doc::includes.doc-history-item', ['history' => $itemHistory])->render()
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(trans('doc::message.An error occurred'), 500);
        }
    }

    /**
     * search reviewers
     */
    public function searchAssignees(Request $request)
    {
        $tblEmp = Employee::getTableName();
        $type = $request->get('type');
        if ($type == DocConst::TYPE_ASSIGNE_REVIEW) {
            $action = 'doc.review';
        } elseif ($type == DocConst::TYPE_ASSIGNE_PUBLISH) {
            $action = 'doc.publish';
        } else {
            $action = '';
        }
        $config = [
            'page' => 1,
            'limit' => 20
        ];
        $search = $request->get('q');
        $excludeEmpIds = $request->get('exclude_ids');
        $config = array_merge($config, $request->only('page'));

        $collect = PermissModel::builderEmployeesAllowAction($action)
                ->select(
                    $tblEmp . '.id',
                    DB::raw('SUBSTRING('. $tblEmp .'.email, 1, LOCATE("@", '. $tblEmp .'.email) - 1) as text')
                )
                ->where(function ($query) use ($tblEmp, $search) {
                    $query->where($tblEmp.'.email', 'LIKE', '%' . $search . '%')
                            ->orWhere($tblEmp.'.name', 'LIKE', '%' . $search . '%');
                })
                ->groupBy($tblEmp.'.id');
        if ($excludeEmpIds) {
            $collect->whereNotIn($tblEmp.'.id', $excludeEmpIds);
        }
        $collect = $collect->paginate($config['limit'], ['*'], 'page', $config['page']);

        return [
            'total_count' => $collect->total(),
            'incomplete_results' => true,
            'items' => $collect->items()
        ];
    }

    /**
     * render suggest reviewer html
     * @param Request $request
     * @return string
     */
    public function getSuggestReviewers(Request $request)
    {
        $teamIds = $request->get('team_ids');
        $employeeIds = $request->get('employee_ids');
        $docId = $request->get('doc_id');
        if (!$employeeIds && !$teamIds) {
            return response()->json(trans('doc::message.Please select team or input account'), 422);
        }
        $doc = null;
        if ($docId) {
            $doc = Document::find($docId);
            if (!$doc) {
                return response()->json(trans('doc::message.Document not found'), 404);
            }
        }
        $reviewers = Document::getSuggestReviewerByTeam($teamIds, $docId, $employeeIds);
        if ($reviewers->isEmpty()) {
            return response()->json(trans('doc::message.There are no people'), 422);
        }

        $docPermiss = Document::getDocPermission($doc);
        $docStatuses = DocConst::listDocStatuses();

        $outHtml = '';
        foreach ($reviewers as $reviewer) {
            $reviewer->pivot = new \stdClass();
            $reviewer->pivot->status = $reviewer->review_status;
            $outHtml .= view('doc::includes.assignee-item', [
                'typeAssignee' => DocConst::TYPE_ASSIGNE_REVIEW,
                'emp' => $reviewer,
                'item' => $doc,
                'permiss' => $docPermiss['edit_reviewer'],
                'collect' => $reviewers,
                'docStatuses' => $docStatuses
            ])->render();
        }
        return $outHtml;
    }

    public function help()
    {
        Breadcrumb::add(trans('doc::view.Document help'));
        return view('doc::help');
    }

    /************************************************
     **************** Show in front *****************
     ************************************************/

    /*
     * show list document
     */
    public function showList(Request $request)
    {
        $data = $request->all();
        return $this->viewListDoc($data);
    }

    /*
     * view detail document
     */
    public function view($id)
    {
        $document = Document::findOrFail($id);
        $permissView = DocPublish::permissView($document);
        if (!$permissView) {
            CoreView::viewErrorPermission();
        }
        if ($document->status != DocConst::STT_PUBLISH) {
            abort(404);
        }
        $listTypes = Type::getList([], false, true);
        $listPublished = DocPublish::getByDocId($id);
        $teamList = TeamList::getList();
        return view('doc::detail', compact('document', 'listTypes', 'listPublished', 'teamList'));
    }

    /*
     * view by document type
     */
    public function viewType($id, $slug, Request $request)
    {
        $type = Type::findOrFail($id);
        $data = [
            'type_id' => $id,
            'type_doc' => $type
        ];
        $data = array_merge($data, $request->all());
        return $this->viewListDoc($data);
    }

    /*
     * view by document team
     */
    public function viewTeam($id, $slug, Request $request)
    {
        $team = Team::findOrFail($id);
        $data = [
            'team_id' => $id,
            'team_doc' => $team
        ];
        $data = array_merge($data, $request->all());
        return $this->viewListDoc($data);
    }

    /*
     * return view document list
     */
    public function viewListDoc($data = [])
    {
        $collection = Document::getData($data);
        $listTypes = Type::getList([], false, true);
        $typeDoc = isset($data['type_doc']) ? $data['type_doc'] : null;
        $teamList = TeamList::getList();
        $teamDoc = isset($data['team_doc']) ? $data['team_doc'] : null;
        return view('doc::list', compact('collection', 'listTypes', 'typeDoc', 'teamList', 'teamDoc'));
    }

    /*
     * download document file
     */
    public function frontDownload($docId, $fileId)
    {
        $doc = Document::findOrFail($docId);
        $docPermiss = DocPublish::permissView($doc);
        if (!$docPermiss) {
            CoreView::viewErrorPermission();
        }
        $file = File::findOrFail($fileId);
        $fileUrl = trim(DocConst::UPLOAD_DIR, '/') . '/' . $file->url;
        if (!Storage::disk('public')->exists($fileUrl)) {
            return redirect()->back()->with('messages', ['errors' => [trans('doc::message.File does not exist')]]);
        }
        $path = storage_path('app/public/' . $fileUrl);
        return response()->file($path, [
            'Content-Type' => $file->mimetype,
            'Content-Disposition' => 'inline; filename="'. $file->name .'"'
        ]);
    }

    /*
     * Update file magazine
     */
    public function updateFileMagazine(Request $request)
    {
        $data = $request->all();
        $item = Magazine::find($data['id']);
        if (!$item) {
            abort(404);
        }
        $response = view('doc::includes.doc-file-update', compact('item'))->render();
        return response()->json($response);
    }

    /**
     * Read magazine in new and full window
     *
     * @return \Illuminate\Http\Response
     */
    public function read($id)
    {
        $magazine = Magazine::find($id);
        if (!$magazine) {
            abort(404);
        }
        if ($magazine->type != Magazine::DOCUMENT) {
            abort(404);
        }
        $docFileId = File::where('magazine_id', $magazine->id)->first();
        $docFile = DB::table("doc_file")->where("file_id", $docFileId->id)->first();
        $document = Document::findOrFail($docFile->doc_id);
        $permissView = DocPublish::permissView($document);
        if (!$permissView) {
            CoreView::viewErrorPermission();
        }
        $images = $magazine->images()->orderBy('order', 'ASC')->get();

        return view('doc::doc.view', compact('magazine', 'images'));
    }
}
