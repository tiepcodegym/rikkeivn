<?php

namespace Rikkei\Document\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Document\View\DocConst;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Team;

class DocRequest extends CoreModel
{
    protected $table = 'documentrequests';
    protected $fillable = ['name', 'content', 'note', 'status', 'created_by'];

    /*
     * get list data
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $empTbl = Employee::getTableName();
        $collection = self::select(
            'docrq.id',
            'docrq.name',
            'docrq.status',
            'author.email as author_email',
            DB::raw('GROUP_CONCAT(DISTINCT(SUBSTRING(creator.email, 1, LOCATE("@", creator.email) - 1)) SEPARATOR ", ") as creator_email'),
            'docrq.created_at'
        )
            ->from(self::getTableName() . ' as docrq')
            ->join($empTbl . ' as author', 'docrq.created_by', '=', 'author.id')
            ->join('doc_request_creator as rq_creator', 'docrq.id', '=', 'rq_creator.doc_req_id')
            ->join($empTbl . ' as creator', 'rq_creator.creator_id', '=', 'creator.id')
            ->groupBy('docrq.id');
        //permisison
        $route = DocConst::ROUTE_MANAGE_REQUEST;
        $scope = Permission::getInstance();
        if ($scope->isScopeCompany(null, $route)) {
            //get all
        } elseif ($scope->isScopeTeam(null, $route)) {
            $currUser = $scope->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $teamIds = Team::teamChildIds($teamIds);
            $collection->join(TeamMember::getTableName().' as tmb', 'tmb.employee_id', '=', 'docrq.created_by')
                ->where(function ($query) use ($teamIds, $currUser) {
                    $query->whereIn('tmb.team_id', $teamIds)
                        ->orWhere('docrq.created_by', $currUser->id);
                });
        } elseif ($scope->isScopeSelf(null, $route)) {
            $collection->where('docrq.created_by', $scope->getEmployee()->id);
        } else {
            CoreView::viewErrorPermission();
        }
        //filter grid
        self::filterGrid($collection);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('docrq.created_at', 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /*
     * get document creator
     */
    public function creators()
    {
        return $this->belongsToMany('\Rikkei\Team\Model\Employee', 'doc_request_creator', 'doc_req_id', 'creator_id');
    }

    /*
     * get author
     */
    public function author()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'created_by', 'id');
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
        $statuses = DocConst::listRequestStatuses();
        return DocConst::renderStatusHtml($status, $statuses);
    }

    /*
     * get label status
     */
    public function getLabelStatus($labels = [])
    {
        if (!$labels) {
            $labels = DocConst::listRequestStatuses();
        }
        if (isset($labels[$this->status])) {
            return $labels[$this->status];
        }
        return null;
    }

    /*
     * get document request permission
     */
    public static function getPermission($item = null, $status = null)
    {
        $currentUser = Permission::getInstance()->getEmployee();
        $scope = Permission::getInstance();
        $route = DocConst::ROUTE_MANAGE_REQUEST;
        $permissView = false; //quyền vào xem
        $permissEdit = false; // quyền (lưu lại)
        $permissCreateDoc = false; // quyền tạo tài liệu (creator_id)
        if ($item && !is_object($item)) {
            $item = self::find($item);
        }

        if (!$item) {
            $permissEdit = $scope->isAllow($route);
            $permissView = $permissEdit;
        } else {
            //permission scope
            if ($scope->isScopeCompany(null, $route)) {
                $permissEdit = true;
            } elseif ($scope->isScopeTeam(null, $route)) {
                $teamIds = TeamMember::where('employee_id', $currentUser->id)
                        ->lists('team_id')
                        ->toArray();
                $teamIds = Team::teamChildIds($teamIds);
                $hasDocReq = self::from(self::getTableName() . ' as docrq')
                        ->join(TeamMember::getTableName() . ' as tmb', 'docrq.created_by', '=', 'tmb.employee_id')
                        ->where(function ($query) use ($teamIds, $currentUser) {
                            $query->whereIn('tmb.team_id', $teamIds)
                                    ->orWhere('docrq.created_by', '=', $currentUser->id);
                        })
                        ->where('docrq.id', $item->id);
                if ($status) {
                    $hasDocReq->where('docrq.status', DocConst::STT_APPROVED);
                }
                if ($hasDocReq->first()) {
                    $permissEdit = true;
                } else {
                    $permissEdit = false;
                }
            } elseif ($scope->isScopeSelf(null, $route)) {
                $permissEdit = $currentUser->id == $item->created_by;
            } else {
                $permissEdit = false;
            }

            $permissCreateDoc = in_array($currentUser->id, $item->creators->lists('id')->toArray());
            $permissView = $permissEdit || $permissCreateDoc;
        }

        return [
            'view' => $permissView,
            'edit' => $permissEdit,
            'create_doc' => $permissCreateDoc
        ];
    }

    /*
     * insert or update document request
     */
    public static function insertOrUpdate($data = [], $item = null)
    {
        DB::beginTransaction();
        try {
            //check if update data
            if (!$item && isset($data['id'])) {
                $item = self::find($data['id']);
            }
            $data['status'] = DocConst::STT_APPROVED;
            if ($item) {
                $oldData = $item->getAttributes();
                $oldData['creator_ids'] = $item->creators->lists('id')->toArray();
                $dataUpdate = array_only($data, $item->getFillable());
                $item->update($dataUpdate);
                DocHistory::insertRequestData($item->id, $oldData, $data);
            } else {
                $data['created_by'] = auth()->id();
                $item = self::create($data);
                DocHistory::createRequestItem($item->id);
            }
            $creatorIds = isset($data['creator_ids']) ? $data['creator_ids'] : [];
            $item->creators()->sync($creatorIds);

            //action assigne
            self::actionAssigne($item, isset($oldData) ? $oldData : []);

            DB::commit();
            return $item;
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return false;
        }
    }

    /*
     * feedback item
     */
    public static function feedbackItem($item, $content)
    {
        if ($item->status == DocConst::STT_FEEDBACK) {
            return false;
        }
        DB::beginTransaction();
        try {
            DocHistory::insertRequestData(
                $item->id,
                ['status' => $item->status],
                ['status' => DocConst::STT_FEEDBACK],
                ['status' => $content]
            );
            $item->update(['status' => DocConst::STT_FEEDBACK]);
            self::actionStatus($item);
            DB::commit();
            return true;
        } catch (\Exception $ex) {
            DB::rollback();
            return false;
        }
    }

    /*
     * action after update status
     */
    public static function actionStatus($item)
    {
        $status = $item->status;
        $author = Employee::find($item->created_by);
        $authorName = $author ? DocConst::getAccount($author->email) : null;
        $dataMail = [
            'author' => $authorName,
            'detailLink' => route('doc::admin.request.edit', $item->id),
            'requestName' => $item->name
        ];
        $notifyToIds = [];
        $mailTo = null;
        switch ($status) {
            case DocConst::STT_SUBMITED: //status submited mail to coo
                $mailTo = CoreConfigData::getCOOAccount(2);
                if ($mailTo) {
                    $template = 'doc::mails.request-submited';
                    $subject = trans('doc::mail.subject_request_submit', ['title' => $item->name, 'author' => $authorName]);
                    $toAccount = Employee::where('email', $mailTo)->first();
                    if ($toAccount) {
                        $dataMail['dearName'] = $toAccount->name;
                        $notifyToIds = [$toAccount->id];
                    }
                }
                break;
            case DocConst::STT_APPROVED: //status approved mail to author
                if (!$author) {
                    break;
                }
                $mailTo = $author->email;
                $template = 'doc::mails.request-approved';
                $subject = trans('doc::mail.subject_request_approve', ['title' => $item->name]);
                $dataMail['dearName'] = $author->name;
                $notifyToIds = [$author->id];
                break;
            case DocConst::STT_FEEDBACK:
                $feedbacker = DocConst::getAccount(Permission::getInstance()->getEmployee()->email);
                $mailTo = $author->email;
                $template = 'doc::mails.request-feedbacked';
                $subject = trans('doc::mail.subject_request_feedback', ['title' => $item->name, 'feedbacker' => $feedbacker]);
                $dataMail['dearName'] = $author->name;
                $dataMail['feedbacker'] = $feedbacker;
                $notifyToIds = [$author->id];
                break;
            default:
                $mailTo = null;
                break;
        }
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

    /*
     * action assigne
     */
    public static function actionAssigne($item, $oldData = [])
    {
        if (!isset($oldData['creator_ids'])) {
            $oldData['creator_ids'] = [];
        }
        $newCreatorIds = $item->creators()->lists('id')->toArray();
        $addedCreatorIds = array_diff($newCreatorIds, $oldData['creator_ids']);
        if (!$addedCreatorIds) {
            return;
        }
        $addedCreators = Employee::whereIn('id', $addedCreatorIds)->get();
        if ($addedCreators->isEmpty()) {
            return;
        }

        $author = Employee::find($item->created_by);
        $authorName = $author ? DocConst::getAccount($author->email) : null;
        $dataMail = [
            'author' => $authorName,
            'detailLink' => route('doc::admin.request.edit', $item->id),
            'requestName' => $item->name
        ];

        $template = 'doc::mails.request-assigne-creator';
        $subject = trans('doc::mail.subject_request_assigne_creator', ['title' => $item->name]);

        foreach ($addedCreators as $creator) {
            $dataMail['dearName'] = $creator->name;

            $emailCreator = new EmailQueue();
            $emailCreator->setTo($creator->email)
                    ->setSubject($subject)
                    ->setTemplate($template, $dataMail)
                    ->setNotify($creator->id, null, $dataMail['detailLink'])
                    ->save();
        }
    }

    /**
     * get list request
     * @param string $search
     * @param boolean $returnBulder
     * @return collection/bulider
     */
    public static function getList($search = null, $returnBulder = true)
    {
        $items = self::select('docrq.id', 'docrq.name as text', DB::raw('1 as loading'))
                ->from(self::getTableName().' as docrq')
                ->where('docrq.status', DocConst::STT_APPROVED)
                ->groupBy('docrq.id');

        if ($search) {
            $items->where('docrq.name', 'like', '%' . $search . '%');
        }
        //check permission
        $route = DocConst::ROUTE_MANAGE_DOC;
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            //get all
        } elseif (Permission::getInstance()->isScopeTeam(null, $route)) {
            $currUser = Permission::getInstance()->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $items->join(TeamMember::getTableName() . ' as tmb', 'tmb.employee_id', '=', 'docrq.created_by')
                    ->where(function ($query) use ($teamIds, $currUser) {
                        $query->whereIn('tmb.team_id', $teamIds)
                                ->orWhere('docrq.creator_id', $currUser->id);
                    });
        } else {
             $items->where('docrq.creator_id', Permission::getInstance()->getEmployee()->id);
        }
        if (!$returnBulder) {
            return $items->get();
        }
        return $items;
    }

    /*
     * search request ajax select2
     */
    public static function searchAjax($search, $config = [])
    {
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
        ];
        $config = array_merge($arrayDefault, $config);
        $items = self::getList($search, true);
        self::pagerCollection($items, $config['limit'], $config['page']);
        return [
            'incomplete_results' => true,
            'items' => $items->items(),
            'total_count' => $items->total()
        ];
    }
}
