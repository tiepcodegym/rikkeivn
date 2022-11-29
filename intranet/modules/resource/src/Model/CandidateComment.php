<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Exception;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\CandidatePermission;
use Rikkei\Core\Model\EmailQueue;
use DB;

class CandidateComment extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'candidate_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'candidate_id'];
    /**
     * overwrite save model
     * @param array $options
     */
    public function save(array $options = array())
    {
        try {
            $this->created_by = Permission::getInstance()->getEmployee()->id;
            return parent::save($options);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * get grid data of candidate_comment
     * @return collection
     */
    public static function getGridData($id)
    {
        $pager = Config::getPagerDataQuery();
        $tableCandidateComment = self::getTableName();
        $tableEmployee = Employee::getTableName();

        $collection = DB::table("{$tableCandidateComment}")
                    ->join("{$tableEmployee}", "{$tableCandidateComment}.created_by", "=", "{$tableEmployee}.id")
                    ->select("{$tableEmployee}.name", "{$tableEmployee}.email", "{$tableCandidateComment}.*")
                    ->where("{$tableCandidateComment}.candidate_id", "=", $id)
                    ->orderBy("{$tableCandidateComment}.created_at", "desc");
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /*
     * get autho commented
     */
    public function author()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'created_by', 'id');
    }

    /*
     * send mail to related employee
     */
    public function sendMailRelated($candidate = null)
    {
        if (!$candidate) {
            $candidate = Candidate::find($this->candidate_id);
        }
        $receives = CandidatePermission::getRelatedInterview($candidate, $this->created_by);
        if (!$receives) {
            return;
        }
        $author = $this->author;
        $authorName = $author ? View::getNickName($author->email) : null;
        $subject = trans('resource::view.candidate_new_comment', ['accout' => $authorName, 'candidate' => $candidate->fullname]);
        foreach ($receives as $receive) {
            $data = [
                'author' => $authorName,
                'name' => $receive['name'],
                'candidateName' => $candidate->fullname,
                'urlToCandidate' => route('resource::candidate.detail', $candidate->id),
                'comment' => $this->content,
                'subject' => $subject
            ];
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($receive['email'])
                ->setSubject($subject)
                ->setTemplate('resource::candidate.mail.new-comment', $data)
                ->setNotify($receive['id'], null, route('resource::candidate.detail', $candidate->id), [
                    'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE,
                    'content_detail' => RkNotify::renderSections('resource::candidate.mail.new-comment', $data)
                ])
                ->save();
        }
    }
}
