<?php

namespace Rikkei\Document\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Document\Models\File;
use Rikkei\Document\View\DocConst;

class DocComment extends CoreModel
{
    protected $table = 'doc_comments';
    protected $fillable = ['doc_id', 'content', 'file_id', 'created_by', 'type'];

    /*
     * get list data
     */
    public static function getData($docId)
    {
        return self::select(
            'comment.id',
            'comment.content',
            'comment.file_id',
            'file.name as file_name',
            'file.url as file_url',
            'emp.email',
            'comment.created_at',
            'comment.type'
        )
            ->from(self::getTableName() . ' as comment')
            ->join(Employee::getTableName() . ' as emp', 'comment.created_by', '=', 'emp.id')
            ->leftJoin(File::getTableName() . ' as file', 'comment.file_id', '=', 'file.id')
            ->where('comment.doc_id', $docId)
            ->orderBy('comment.created_at', 'desc')
            ->paginate(DocConst::COMMENT_PER_PAGE, ['*'], 'comment_page');
    }

    /*
     * insert or update comment
     */
    public static function insertOrUpdate($data = [])
    {
        if (isset($data['id'])) {
            $item = self::findOrFail($data['id']);
        } else {
            $data['created_by'] = auth()->id();
            $item = self::create($data);
        }
        return $item;
    }

    /*
     * delete comment
     */
    public function delete() {
        if ($this->file_id) {
            $file = File::find($this->file_id);
            if ($file) {
                $file->delete();
            }
        }
        return parent::delete();
    }

    public function author()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'created_by', 'id');
    }
}
