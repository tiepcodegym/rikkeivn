<?php

namespace Rikkei\Document\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Document\Models\Type;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Document\View\DocConst;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Document\Models\DocHistory;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Document\Models\File;

class Document extends CoreModel
{
    protected $table = 'documents';
    protected $fillable = ['request_id', 'code', 'url', 'description', 'note', 'author_id', 'publisher_id', 'publish_all', 'status'];

    /**
     * get list data
     * @return type
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $tblTeamDoc = 'doc_team';
        $collection = self::select(
                'doc.id',
                'doc.code',
                'file.id as file_id',
                'file.url',
                'file.name as file_name',
                'file.type as file_type',
                'file.mimetype',
                'doc.status',
                'doc.author_id',
                'emp.email as author_email',
                DB::raw('GROUP_CONCAT(DISTINCT(type.id) SEPARATOR "|") as type_ids'),
                DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names'),
                'doc.created_at'
            )
            ->from(self::getTableName() . ' as doc')
            ->join('doc_file', 'doc.id', '=', 'doc_file.doc_id')
            ->join(File::getTableName() . ' as file', function ($join) {
                $join->on('doc_file.file_id', '=', 'file.id')
                        ->where('is_current', '=', 1);
            })
            ->leftJoin('doc_type', 'doc.id', '=', 'doc_type.doc_id')
            ->leftJoin(Type::getTableName() . ' as type', 'doc_type.type_id', '=', 'type.id')
            ->join(Employee::getTableName() . ' as emp', 'doc.author_id', '=', 'emp.id')
            ->leftJoin($tblTeamDoc . ' as doc_team', 'doc.id', '=', 'doc_team.doc_id')
            ->leftJoin(Team::getTableName() . ' as team', 'doc_team.team_id', '=', 'team.id')
            ->groupBy('doc.id');
        //permisison
        $route = DocConst::ROUTE_MANAGE_DOC;
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany(null, $route)) {
            //get all
        } elseif ($scope->isScopeTeam(null, $route)) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $teamIds = Team::teamChildIds($teamIds);
            $collection->leftJoin('doc_assignee', function ($join) {
                $join->on('doc.id', '=', 'doc_assignee.doc_id')
                        ->where('doc_assignee.type', '=', DocConst::TYPE_ASSIGNE_EDITOR);
            })
            ->where(function ($query) use ($teamIds, $currUser) {
                $query->whereIn('team.id', $teamIds)
                    ->orWhere('doc.author_id', $currUser->id)
                    ->orWhere('doc_assignee.employee_id', $currUser->id);
            });
        } elseif ($scope->isScopeSelf(null, $route)) {
            $collection->leftJoin('doc_assignee', function ($join) {
                $join->on('doc.id', '=', 'doc_assignee.doc_id')
                        ->where('doc_assignee.type', '=', DocConst::TYPE_ASSIGNE_EDITOR);
            })
            ->where(function ($query) use ($scope) {
                $currUserId = $scope->getEmployee()->id;
                $query->where('doc.author_id', $currUserId)
                        ->orWhere('doc_assignee.employee_id', $currUserId);
            });
        } else {
            CoreView::viewErrorPermission();
        }
        //filter grid
        self::filterGrid($collection);
        //filter team
        if ($teamName = Form::getFilterData('excerpt', 'team.name')) {
            $collection->join($tblTeamDoc . ' as teamdoc_search', 'doc.id', '=', 'teamdoc_search.doc_id')
                    ->join(Team::getTableName() . ' as team_search', 'teamdoc_search.team_id', '=', 'team_search.id')
                    ->where('team_search.name', 'like', '%' . $teamName . '%');
        }
        if ($typeId = Form::getFilterData('excerpt', 'type_id')) {
            $typeIds = Type::allIds($typeId);
            $collection->join('doc_type as type_search', 'doc.id', '=', 'type_search.doc_id')
                    ->whereIn('type_search.type_id', $typeIds);
        }
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('doc.created_at', 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get permission of document
     * @param object $document
     * @param string $coordinator
     * @return array
     */
    public static function getDocPermission($document = null, $requestId = null)
    {
        $scope = Permission::getInstance();
        $currentUser = $scope->getEmployee();
        $route = DocConst::ROUTE_MANAGE_DOC;
        $permissView = false; //quền vào xem
        $permissEdit = false; //quyền lưu lại
        $permissSubmit = false; // quyền submit
        $permissReview = false; // quyền review
        $permissPublish = false; // quyền publish
        $permissFeedback = false; // quyền feedback
        $requestPermiss = [
            'create_doc' => false,
        ];
        $permissEditReviewer = false;
        $permissEditPublisher = false;
        $permissCompany = $scope->isScopeCompany(null, $route);

        if ($requestId) {
            $requestPermiss = DocRequest::getPermission($requestId, DocConst::STT_APPROVED);
        } elseif ($document) {
            $requestPermiss = DocRequest::getPermission($document->request_id, DocConst::STT_APPROVED);
        } else {
            //else none
        }
        $reqCreateDocPermiss = $requestPermiss['create_doc'];

        if (!$document) { //không có tài liệu
            $permissEdit = $scope->isAllow($route);
            $permissSubmit = $permissEdit;
            $permissView = $permissEdit;
            $permissEditReviewer = $permissEdit || $reqCreateDocPermiss;
            $permissEditPublisher = $permissEdit;
        } else {
            //permission scope
            if ($scope->isScopeCompany(null, $route)) {
                $permissEdit = true;
            } elseif ($scope->isScopeTeam(null, $route)) {
                $teamIds = TeamMember::where('employee_id', $currentUser->id)
                        ->lists('team_id')
                        ->toArray();
                $teamIds = Team::teamChildIds($teamIds);
                $hasDoc = self::from(self::getTableName() . ' as doc')
                        ->join('doc_team', 'doc.id', '=', 'doc_team.doc_id')
                        ->leftJoin('doc_assignee as editor', function ($join) {
                            $join->on('doc.id', '=', 'editor.doc_id')
                                    ->where('editor.type', '=', DocConst::TYPE_ASSIGNE_EDITOR);
                        })
                        ->where(function ($query) use ($teamIds, $currentUser) {
                            $query->whereIn('doc_team.team_id', $teamIds)
                                    ->orWhere('doc.author_id', '=', $currentUser->id)
                                    ->orWhere('editor.employee_id', '=', $currentUser->id);
                        })
                        ->where('doc.id', $document->id)
                        ->first();
                $permissEdit = $hasDoc != null;
            } elseif ($scope->isScopeSelf(null, $route)) {
                $permissEdit = in_array($currentUser->id, $document->authorAndEditorIds());
            } else {
                $permissEdit = false;
            }

            $arrReviewers = $document->reviewers()->lists('status', 'id')->toArray();
            $permissReview = isset($arrReviewers[$currentUser->id]); //check là người review
            $permissPublish = $document->publisher_id == $currentUser->id || $permissCompany; // check là người publish
            $permissView = $permissEdit || $permissReview || $permissPublish;
            $permissEditReviewer = $permissEdit || $reqCreateDocPermiss;
            $permissEditPublisher = $permissEdit || $reqCreateDocPermiss || $permissReview;
            //set permiss feedback
            $permissFeedback = $permissReview || $permissPublish;

            if ($document->status == DocConst::STT_NEW) {
                $permissReview = false;
                $permissPublish = false;
                $permissFeedback = false;
            }
            if ($document->status == DocConst::STT_SUBMITED) {
                $permissEdit = false;
                $permissPublish = false;
                $reqCreateDocPermiss = false;
            }
            if ($document->status == DocConst::STT_REVIEWED) {
                $permissEdit = false;
                $permissReview = false;
                $reqCreateDocPermiss = false;
                $permissEditReviewer = false;
            }
            if ($document->status == DocConst::STT_FEEDBACK) {
                $permissReview = false;
                $permissPublish = false;
                $permissFeedback = false;
            }
            $permissSubmit = $permissEdit;
        }
        //check after
        if ($document) {
            // nếu đã review rồi
            if ($document->status == DocConst::STT_SUBMITED && $permissReview
                    && $arrReviewers[$currentUser->id] == DocConst::STT_REVIEWED) {
                $permissReview = false;
                $permissFeedback = false;
            }

            // trường hợp tài liệu đã publish, chỉ có người publisher đc feedback
            if ($document->status == DocConst::STT_PUBLISH) {
                if (in_array($currentUser->id, $arrReviewers)) {
                    $permissFeedback = false;
                }
                if ($document->publisher_id == $currentUser->id) {
                    $permissFeedback = true;
                }
            }
        }

        return [
            'view' => $permissView || $requestPermiss['create_doc'],
            'edit' => $permissEdit || $reqCreateDocPermiss,
            'submit' => $permissSubmit || $reqCreateDocPermiss,
            'review' => $permissReview,
            'feedback' => $permissFeedback,
            'publish' => $permissPublish,
            'edit_reviewer' => $permissEditReviewer,
            'edit_publisher' => $permissEditPublisher,
            'company' => $permissCompany
        ];
    }

    /*
     * get files of document
     */
    public function files()
    {
        return $this->belongsToMany('\Rikkei\Document\Models\File', 'doc_file', 'doc_id', 'file_id')
                ->wherePivot('type', '=', DocConst::TYPE_DOC)
                ->withPivot(['version', 'is_current']);
    }

    /*
     * list files of document
     */
    public function listFiles($excerptCurrent = false)
    {
        $fileTbl = File::getTableName();
        $list = $this->files()
                ->select($fileTbl . '.*', 'emp.email');
        if ($excerptCurrent) {
            $list->wherePivot('is_current', '=', 0);
        }
        $list->leftJoin(Employee::getTableName() . ' as emp', $fileTbl . '.author_id', '=', 'emp.id');
        return $list->orderBy('pivot_is_current', 'desc')
                    ->orderBy('pivot_version', 'desc')
                    ->paginate(DocConst::HISTORY_PER_PAGE, ['*'], 'version_page');
    }

    /*
     * get current file
     */
    public function getCurrentFile()
    {
        return $this->files()->wherePivot('is_current', '=', 1)->first();
    }

    /*
     * attach document file
     */
    public function attachFile($fileId)
    {
        $currentFile = $this->getCurrentFile();
        $version = 1;
        if ($currentFile) {
            $version = $this->files()->max('version') + 1;
        }
        $this->files()->update(['is_current' => 0]);
        $this->files()->attach([$fileId => ['is_current' => 1, 'version' => $version]]);
    }

    /*
     * get attach files of document
     */
    public function attachFiles()
    {
        return $this->belongsToMany('\Rikkei\Document\Models\File', 'doc_file', 'doc_id', 'file_id')
                ->wherePivot('type', '=', DocConst::TYPE_ATTACH)
                ->withPivot(['version', 'is_current']);
    }

    /*
     * set current file
     */
    public function setCurrentFile($fileId)
    {
        $this->files()->update(['is_current' => 0]);
        return $this->files()
                ->updateExistingPivot($fileId, [
                    'is_current' => 1
                ]);
    }

    /*
     * get request of document
     */
    public function request()
    {
        return $this->belongsTo('\Rikkei\Document\Models\DocRequest', 'request_id', 'id');
    }

    /**
     * insert or update data
     * @param type $data
     * @return boolean
     */
    public static function insertOrUpdate($data = [], $item = null, $docPermiss = [])
    {
        DB::beginTransaction();
        try {
            $file = null;
            if ($docPermiss && $docPermiss['submit']) {
                //check if has file upload
                if ((isset($data['file']) && $data['file']) ||
                        (isset($data['file_link']) && $data['file_link'])) {
                    $file = File::insertData(
                        isset($data['file']) ? $data['file'] : null,
                        isset($data['file_link']) ? $data['file_link'] : null
                    );
                }
                if (isset($data['id_magazine']) && $data['id_magazine'] && isset($data['magazine_name']) && $data['magazine_name']) {
                    $file = File::insertData(null, null, true, isset($data['id_magazine']) ? $data['id_magazine'] : null);
                }
                //check if has attachs file
                if (!isset($data['attach_file_ids'])) {
                    $data['attach_file_ids'] = [];
                }
                if (isset($data['attach_files']) && $data['attach_files']) {
                    foreach ($data['attach_files'] as $attachFile) {
                        if (!$attachFile) {
                            continue;
                        }
                        $data['attach_file_ids'][] = File::insertData($attachFile)->id;
                    }
                }
                //update types
                if (!isset($data['type_ids'])) {
                    $data['type_ids'] = [];
                }
                //update teams
                if (!isset($data['team_ids'])) {
                    $data['team_ids'] = [];
                }
                //update teams publish
                if (!isset($data['team_publish_ids'])) {
                    $data['team_publish_ids'] = [];
                }
                //update member publish
                if (!isset($data['account_ids'])) {
                    $data['account_ids'] = [];
                }
            }

            // edit submited document
            if ($docPermiss && ($docPermiss['submit'] || $docPermiss['edit_reviewer'])) {
                if (!isset($data['assignees'][DocConst::TYPE_ASSIGNE_REVIEW])) {
                    $data['reviewer_ids'] = [];
                    $data['reviewers_name'] = '';
                } else {
                    $reviewerIds = $data['assignees'][DocConst::TYPE_ASSIGNE_REVIEW];
                    $data['reviewer_ids'] = $reviewerIds;
                    $newReviewers = Employee::whereIn('id', array_keys($reviewerIds))->get();
                    $data['reviewers_name'] = $newReviewers->isEmpty() ? '' : $newReviewers->implode('name', ', ');
                }
            }

            //check if update data
            if (!$item && isset($data['id'])) {
                $item = self::find($data['id']);
            }
            if ($item) {
                //collect old data
                $oldData = $item->getAttributes();
                $currentFile = $item->getCurrentFile();
                if ($currentFile) {
                    $oldData['file_name'] = $currentFile->name;
                }
                $oldData['type_ids'] = $item->types()->lists('id')->toArray();
                $oldData['team_ids'] = $item->teams()->lists('id')->toArray();
                $oldData['team_publish_ids'] = $data['team_publish_ids'];
                $oldData['account_ids'] = $data['account_ids'];
                $oldData['attach_file_ids'] = $item->attachFiles()->lists('id')->toArray();
                $oldReviewers = $item->reviewers;
                $oldData['reviewers_name'] = $oldReviewers->isEmpty() ? '' : $oldReviewers->implode('name', ', ');
                //update data
                $dataUpdate = array_only($data, $item->getFillable());
                $item->update($dataUpdate);
                //insert histories

                if (!empty($file)) {
                    $data['file_name'] = $file->name;
                }
                unset($oldData['status']);
                DocHistory::insertData($item->id, $oldData, $data);
                if (!isset($data['publish_all'])) {
                    DocPublish::insertData($item->id, $oldData['team_publish_ids'], $oldData['account_ids']);
                } else {
                    DocPublish::insertData($item->id, [], []); //empty teamIds and accountIds = remove
                }
            } else {
                $data['author_id'] = auth()->id();
                $item = self::create($data);
                DocHistory::createItem($item->id);
                if (!isset($data['publish_all'])) {
                    DocPublish::insertData($item->id, $data['team_publish_ids'], $data['account_ids']);
                } else {
                    DocPublish::insertData($item->id, [], []); //empty teamIds and accountIds = remove
                }
            }

            if ($docPermiss && $docPermiss['submit']) {
                if (!empty($file)) {
                    $item->attachFile($file->id);
                }
                $item->types()->sync($data['type_ids']);
                $item->teams()->sync($data['team_ids']);
                $attachIds = [];
                if ($data['attach_file_ids']) {
                    foreach ($data['attach_file_ids'] as $fileId) {
                        $attachIds[$fileId] = ['type' => DocConst::TYPE_ATTACH];
                    }
                }

                $item->attachFiles()->sync($attachIds);
                //delete not attach file
                if (isset($oldData['attach_file_ids']) && isset($data['attach_file_ids'])) {
                    $notAttachFileIds = array_diff($oldData['attach_file_ids'], $data['attach_file_ids']);
                    if ($notAttachFileIds) {
                        File::deleteById($notAttachFileIds);
                    }
                }
            }

            //check status
//            self::actionStatus($item, $data);

            DB::commit();
            return $item;
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
            return false;
        }
    }

    /**
     * feedback item
     * @param type $item
     * @param type $content
     * @return boolean
     */
    public static function feedbackItem($item, $content)
    {
        if ($item->status == DocConst::STT_FEEDBACK) {
            return false;
        }
        DB::beginTransaction();
        try {
            DocComment::insertOrUpdate([
                'doc_id' => $item->id,
                'content' => $content,
                'type' => DocConst::COMMENT_TYPE_FEEDBACK
            ]);
            if ($item->status == DocConst::STT_PUBLISH) {
                DB::table('doc_assignee')
                        ->where('doc_id', $item->id)
                        ->update(['status' => DocConst::STT_NEW]);
            }
            $item->assignees()->updateExistingPivot(auth()->id(), ['status' => DocConst::STT_FEEDBACK]);
            self::actionStatus($item, ['status' => DocConst::STT_FEEDBACK]);
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /**
     * action after change status
     * @param type $item
     * @return type
     */
    public static function actionStatus($item, $data = [])
    {
        if (!array_key_exists('status', $data)) {
            return false;
        }
        $status = $data['status'];
        $author = Employee::find($item->author_id);
        $authorName = $author ? DocConst::getAccount($author->email) : null;
        $dataMail = [
            'author' => $authorName,
            'detailLink' => route('doc::admin.edit', $item->id),
            'docTitle' => $item->code
        ];
        //update status
        $newStatus = self::updateStatus($item, $status);
        //check send mail + notify
        $notifyToIds = [];
        $mailTo = null;
        //if add reviewers
        if (!$status && $item->status == DocConst::STT_SUBMITED && isset($data['reviewer_ids'])) {
            $newReviewerIds = array_keys($data['reviewer_ids']);
            $oldReviewerIds = $item->reviewers->lists('id')->toArray();
            $addReviewerIds = array_diff($newReviewerIds, $oldReviewerIds);
            if ($addReviewerIds) {
                self::assigneToReviewer($item, $dataMail, Employee::whereIn('id', $addReviewerIds)->get());
            }
        }

        switch ($newStatus){
            case DocConst::STT_SUBMITED: //status submited mail to reviewers
                $reviewers = $item->reviewers()->get();
                if (!$reviewers->isEmpty()) {
                    self::assigneToReviewer($item, $dataMail, $reviewers);
                }
                break;
            case DocConst::STT_REVIEWED: //status reviewed mail to publisher
                if ($item->publisher_id) {
                    self::assigneToPublisher($item, $dataMail);
                }
                break;
            case DocConst::STT_PUBLISH: //status publish mail to author
                if (!$author) {
                    break;
                }
                $mailTo = $author->email;
                $template = 'doc::mails.doc-published';
                $subject = trans('doc::mail.subject_publish', ['title' => $item->code]);
                $dataMail['dearName'] = $author->name;
                $notifyToIds = [$author->id];
                break;
            case DocConst::STT_FEEDBACK: //status feedback mail to author
                if (!$author) {
                    break;
                }
                $feedbacker = DocConst::getAccount(Permission::getInstance()->getEmployee()->email);
                $mailTo = $author->email;
                $template = 'doc::mails.doc-feedbacked';
                $subject = trans('doc::mail.subject_feedback', ['title' => $item->code, 'feedbacker' => $feedbacker]);
                $dataMail['dearName'] = $author->name;
                $dataMail['feedbacker'] = $feedbacker;
                $notifyToIds = [$author->id];
                break;
            default:
                $mailTo = null;
                break;
        }
        //check mail
        if (!$mailTo) {
            return;
        }
        //send mail
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($mailTo)
                ->setSubject($subject)
                ->setTemplate($template, $dataMail);
        if ($notifyToIds) {
            $emailQueue->setNotify($notifyToIds, null, $dataMail['detailLink']);
        }
        $emailQueue->save();
    }

    public static function updateStatus($item, $status)
    {
        $save = false;
        $newStatus = $status;
        switch ($status) {
            case DocConst::STT_FEEDBACK:
            case DocConst::STT_PUBLISH:
                $save = true;
                break;
            case DocConst::STT_SUBMITED:
                //update assignee table set status to new
                DB::table('doc_assignee')
                    ->where('doc_id', $item->id)
                    ->update(['status' => DocConst::STT_NEW]);
                $save = true;
                break;
            case DocConst::STT_REVIEWED:
                $item->reviewers()->updateExistingPivot(auth()->id(), ['status' => $status]);
                $save = $item->reviewers->count() == $item->reviewers()->wherePivot('status', $status)->get()->count();
                break;
            case null:
                if ($item->status == DocConst::STT_SUBMITED &&
                        $item->reviewers()->get()->count() == $item->reviewers()->wherePivot('status', DocConst::STT_REVIEWED)->get()->count()) {
                    $newStatus = DocConst::STT_REVIEWED;
                    $save = true;
                }
                break;
            default:
                break;
        }
        if ($status != DocConst::STT_NEW) {
            DocHistory::insertData($item->id, ['status' => $status], ['status' => false]);
        }
        if ($save) {
            $item->status = $newStatus;
            $item->save();
        }
        return $newStatus;
    }

    /**
     * after change assignee
     * @param object $item
     * @param array $type
     * @return type
     */
    public static function actionAssigne($item, $assignee = null, $type = null)
    {
        $author = Employee::find($item->author_id);
        $authorName = $author ? DocConst::getAccount($author->email) : null;
        $dataMail = [
            'author' => $authorName,
            'detailLink' => route('doc::admin.edit', $item->id),
            'docTitle' => $item->code
        ];

        //assigne to reviewer
        if ($type == DocConst::TYPE_ASSIGNE_REVIEW) {
            self::assigneToReviewer($item, $dataMail, $assignee);
        }
        //assigne to publisher
        if ($type == DocConst::TYPE_ASSIGNE_PUBLISH) {
            self::assigneToPublisher($item, $dataMail, $assignee);
        }
        //assignee to editor
        if ($type == DocConst::TYPE_ASSIGNE_EDITOR) {
            self::assigneToEditor($item, $dataMail, $assignee);
        }
    }

    /*
     * assigne to reiviewer
     */
    public static function assigneToReviewer($item, $dataMail, $reviewers = null)
    {
        if ($reviewers && $reviewers instanceof Employee) {
            $reviewers = collect([$reviewers]);
        }
        $reviewers = ($reviewers && !$reviewers->isEmpty()) ? $reviewers : $item->reviewers;
        if ($reviewers->isEmpty() || $item->status != DocConst::STT_SUBMITED) {
            return;
        }

        $template = 'doc::mails.doc-assigne-reviewer';
        $subject = trans('doc::mail.subject_assigne_reviewer', ['title' => $item->code, 'author' => $dataMail['author']]);
        foreach ($reviewers as $emp) {
            if (isset($emp['pivot']) && $emp->pivot->status == DocConst::STT_REVIEWED) {
                continue;
            }
            $dataMail['dearName'] = $emp->name;
            $emailReviewer = new EmailQueue();
            $emailReviewer->setTo($emp->email)
                    ->setSubject($subject)
                    ->setTemplate($template, $dataMail)
                    ->setNotify($emp->id, null, $dataMail['detailLink'])
                    ->save();
        }
    }

    /*
     * assigne to publisher
     */
    public static function assigneToPublisher($item, $dataMail)
    {
        if (!$item->publisher_id || $item->status != DocConst::STT_REVIEWED) {
            return;
        }
        $publisher = Employee::find($item->publisher_id);
        if (!$publisher) {
            return;
        }
        $template = 'doc::mails.doc-assigne-publisher';
        $subject = trans('doc::mail.subject_assigne_publisher', ['title' => $item->code, 'author' => $dataMail['author']]);
        $dataMail['dearName'] = $publisher->name;
        $emailApprover = new EmailQueue();
        $emailApprover->setTo($publisher->email)
                ->setSubject($subject)
                ->setTemplate($template, $dataMail)
                ->setNotify($publisher->id, null, $dataMail['detailLink'])
                ->save();
    }

     /*
     * assigne to editor
     */
    public static function assigneToEditor($item, $dataMail, $editors)
    {
        if ($editors->isEmpty()) {
            return;
        }

        $firstEditor = $editors->first();
        unset($editors[0]);

        $template = 'doc::mails.doc-assigne-editor';
        $subject = trans('doc::mail.subject_assigne_editor', ['title' => $item->code]);
        $dataMail['dearName'] = $firstEditor->name;

        $emailEditor = new EmailQueue();
        $emailEditor->setTo($firstEditor->email)
                ->setSubject($subject)
                ->setTemplate($template, $dataMail)
                ->setNotify($firstEditor->id, null, $dataMail['detailLink']);

        if (!$editors->isEmpty()) {
            foreach ($editors as $editor) {
                $emailEditor->addCc($editor->email)
                        ->addCcNotify($editor->id);
            }
        }
        $emailEditor->save();
    }

    /**
     * get label status
     * @param type $labels
     * @return type
     */
    public function getLabelStatus($labels = [])
    {
        return DocConst::getLabelStatus($this->status, $labels = []);
    }

    /**
     * render status html style
     * @param type $status
     * @return type
     */
    public function renderStatusHtml($status = null)
    {
        if (!$status) {
            $status = $this->status;
        }
        $statuses = DocConst::listDocStatuses();
        return DocConst::renderStatusHtml($status, $statuses);
    }


    /**
     * delete image
     */
    public function deleteItem()
    {
        DB::beginTransaction();
        try {
            $files = $this->files;
            if (!$files->isEmpty()) {
                foreach ($files as $file) {
                    $file->delete();
                }
            }
            $this->delete();
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /**
     * get types that belongs to document
     * @return type
     */
    public function types(){
        return $this->belongsToMany('\Rikkei\Document\Models\Type', 'doc_type', 'doc_id', 'type_id');
    }

    /**
     * get list type ids
     * @return type
     */
    public function getListTypeIds()
    {
        $types = $this->types;
        if ($types->isEmpty()) {
            return [];
        }
        return $types->lists('id')->toArray();
    }

    /**
     * get author create document
     * @return type
     */
    public function author()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'author_id');
    }

    public function assignees()
    {
        return $this->belongsToMany('\Rikkei\Team\Model\Employee', 'doc_assignee', 'doc_id', 'employee_id')
                ->withPivot('status');
    }

    /*
     * get reviewer
     */
    public function reviewers()
    {
        return $this->assignees()
                ->wherePivot('type', DocConst::TYPE_ASSIGNE_REVIEW);
    }

    /*
     * get publisher
     */
    public function publisher()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'publisher_id');
    }

    /*
     * get editors
     */
    public function editors()
    {
        return $this->assignees()
                ->wherePivot('type', DocConst::TYPE_ASSIGNE_EDITOR);
    }

    public function isAuthor()
    {
        return $this->author_id == auth()->id();
    }

    /*
     * get author and editor ids
     */
    public function authorAndEditorIds()
    {
        $editors = $this->editors()->lists('id')->toArray();
        $editors[] = $this->author_id;
        return $editors;
    }

    /**
     * get author name
     * @return type
     */
    public function getAuthorName()
    {
        $author = $this->author;
        if ($author) {
            return DocConst::getAccount($author->email);
        }
        return null;
    }

    /*
     * get teams of document
     */
    public function teams()
    {
        return $this->belongsToMany('\Rikkei\Team\Model\Team', 'doc_team', 'doc_id', 'team_id');
    }

    /**
     * get view link
     * @return type
     */
    public function getViewLink()
    {
        return route('doc::view', ['id' => $this->id, 'slug' => str_slug($this->code)]);
    }

    public function canEdit()
    {
        return in_array($this->status, [DocConst::STT_NEW, DocConst::STT_FEEDBACK]);
    }

    /**
     * get list employee by team id or employee id
     * @param type $teamIds
     * @param type $accountIds
     */
    public static function getListEmployee($teamIds = [], $accountIds = [])
    {
        $empTbl = Employee::getTableName();
        return Employee::select($empTbl.'.id', $empTbl.'.name', $empTbl.'.email')
                ->leftJoin(TeamMember::getTableName() . ' as tmb', $empTbl . '.id', '=', 'tmb.employee_id')
                ->where(function ($query) use ($teamIds, $accountIds, $empTbl) {
                    if ($teamIds) {
                        $query->whereIn('tmb.team_id', $teamIds);
                    }
                    if ($accountIds) {
                        $query->orWhereIn($empTbl.'.id', $accountIds);
                    }
                })
                ->where(function ($query) use ($empTbl) {
                    $query->whereNull($empTbl.'.leave_date')
                        ->orWhereRaw('DATE('. $empTbl .'.leave_date) > CURDATE()');
                })
                ->groupBy($empTbl.'.id')
                ->get();
    }

    /**
     * get list account to mail/notify publish document
     * @param type $docId
     * @return type
     */
    public static function getAccountPublish($docId)
    {
        $collect = self::select('emp.id', 'emp.email')
                ->from(self::getTableName().' as doc')
                ->leftJoin('doc_team', 'doc.id', '=', 'doc_team.doc_id')
                ->leftJoin(TeamMember::getTableName(). ' as tmb', 'doc_team.team_id', '=', 'tmb.team_id')
                ->leftJoin(Employee::getTableName() . ' as emp', 'tmb.employee_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at')
                ->where('doc.id', $docId)
                ->where('tmb.role_id', Team::ROLE_TEAM_LEADER)
                ->groupBy('emp.id')
                ->get();

        return $collect;
    }

    /*
     * get front data
     */
    public static function getData($filter = [])
    {
        $defaultFilter = [
            'fields' => [
                'doc.*',
                'author.email',
                'file.id as file_id',
                'file.name as file_name',
                'file.type as file_type',
                'file.url as file_url',
                DB::raw('GROUP_CONCAT(DISTINCT(type.id) SEPARATOR "|") as type_ids')
            ],
            'orderby' => 'doc.created_at',
            'order' => 'desc',
            'per_page' => DocConst::DOC_PER_PAGE,
            'type_id' => null,
            'team_id' => null,
            'search' => null,
            'status' => [DocConst::STT_PUBLISH]
        ];
        $filter = array_merge($defaultFilter, $filter);

        $currentUser = Permission::getInstance()->getEmployee();
        $teamIds = TeamMember::where('employee_id', $currentUser->id)
                ->lists('team_id')
                ->toArray();
        $teamIds = Team::teamChildIds($teamIds);
        $permissCompany = DocConst::isPermissCompany();
        $collection = self::select($filter['fields'])
                ->from(self::getTableName() . ' as doc')
                ->leftJoin('doc_file', function ($join) {
                    $join->on('doc.id', '=', 'doc_file.doc_id')
                            ->where('doc_file.is_current', '=', 1);
                })
                ->leftJoin(File::getTableName() . ' as file', 'doc_file.file_id', '=', 'file.id')
                ->leftJoin('doc_type', 'doc.id', '=', 'doc_type.doc_id')
                ->leftJoin(Type::getTableName() . ' as type', function ($join) {
                    $join->on('doc_type.type_id', '=', 'type.id')
                            ->where('type.status', '=', DocConst::STT_ENABLE);
                })
                ->leftJoin('doc_type as doc_type_s', 'doc.id', '=', 'doc_type_s.doc_id')
                ->leftJoin(Employee::getTableName() . ' as author', 'doc.author_id', '=', 'author.id')
                ->leftJoin('doc_assignee', 'doc.id', '=', 'doc_assignee.doc_id')
                ->leftJoin(DocPublish::getTableName() . ' as publish', 'doc.id', '=', 'publish.doc_id');
        if (!$permissCompany) {
            $collection->where(function ($query) use ($teamIds, $currentUser) {
                $query->whereIn('publish.team_id', $teamIds)
                        ->orWhere('doc.publish_all', 1)
                        ->orWhere('publish.employee_id', $currentUser->id)
                        ->orWhere('doc.author_id', $currentUser->id)
                        ->orWhere('doc.publisher_id', $currentUser->id)
                        ->orWhere('doc_assignee.employee_id', $currentUser->id);
            });
        }
        $collection->orderBy($filter['orderby'], $filter['order'])
                ->groupBy('doc.id');
        //filter
        if ($filter['status']) {
            $collection->whereIn('doc.status', $filter['status']);
        }
        if ($filter['type_id']) {
            $allTypeIds = Type::allIds($filter['type_id']);
            $collection->whereIn('doc_type_s.type_id', $allTypeIds);
        }
        if ($filter['team_id']) {
            $allTeamIds = Team::teamChildIds($filter['team_id']);
            $collection->leftJoin('doc_team', 'doc_team.doc_id', '=', 'doc.id')
                    ->whereIn('doc_team.team_id', $allTeamIds);
        }
        if ($filter['search']) {
            $collection->leftJoin(Type::getTableName() . ' as type_s', 'doc_type_s.type_id', '=', 'type_s.id')
                ->where(function ($query) use ($filter) {
                    $query->where('doc.code', 'like', '%'. $filter['search'] .'%')
                        ->orWhere('doc.description', 'like', '%'. $filter['search'] .'%')
                        ->orWhere('file.name', 'like', '%' . $filter['search'] .'%')
                        ->orWhere('type_s.name', 'like', '%' . $filter['search'] .'%');
                });
        }
        return $collection->paginate($filter['per_page']);
    }

    /**
     * get suggest reviewers by team ids
     * @param array $teamIds
     * @return collection
     */
    public static function getSuggestReviewerByTeam($teamIds = null, $docId = null, $employeeIds = null)
    {
        $roleIds = [Team::ROLE_TEAM_LEADER];
        $teamIds = $teamIds ? $teamIds : [];
        $employeeIds = $employeeIds ? $employeeIds : [];
        $empTbl = Employee::getTableName();
        $docAssigneTbl = 'doc_assignee';

        return Employee::select(
                $empTbl.'.id',
                $empTbl.'.name',
                $empTbl.'.email',
                $docId ? 'doc_ass.status as review_status' : DB::raw(DocConst::STT_NEW . ' as review_status')
            )
            ->join(TeamMember::getTableName() . ' as tmb', 'tmb.employee_id', '=', $empTbl.'.id')
            ->leftJoin($docAssigneTbl . ' as doc_ass', function ($join) use ($empTbl, $docId) {
                $join->on($empTbl . '.id', '=', 'doc_ass.employee_id')
                        ->where('doc_ass.type', '=', DocConst::TYPE_ASSIGNE_REVIEW);
                if ($docId) {
                    $join->where('doc_ass.doc_id', '=', $docId);
                }
            })
            ->where(function ($query) use ($empTbl) {
                $query->whereNull($empTbl.'.leave_date')
                    ->orWhereRaw('DATE('. $empTbl .'.leave_date) > CURDATE()');
            })
            ->where(function ($query) use ($teamIds, $employeeIds, $roleIds) {
                $query->whereIn('tmb.team_id', $teamIds)
                        ->whereIn('tmb.role_id', $roleIds)
                        ->orWhereIn('tmb.employee_id', $employeeIds);
            })
            ->get();
    }
}
