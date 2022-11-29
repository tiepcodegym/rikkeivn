<?php

namespace Rikkei\Document\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Document\View\DocConst;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

class DocHistory extends CoreModel
{
    protected $table = 'doc_histories';
    protected $fillable = ['doc_id', 'request_id', 'content', 'created_by'];

    /**
     * get column change store history
     * @return type
     */
    public static function colsData()
    {
        $changed = trans('doc::view.Changed');
        $added = trans('doc::view.Add');
        $deleted = trans('doc::view.Delete');
        return [
            'title' =>              '- '. $changed .' '. trans('doc::view.Title') .': %s ==> %s',
            'status' =>             '- %s',
            'file_name' =>          '- '. $changed .' file: %s ==> %s',
            'publisher_id' =>       '- '. $changed .' '. trans('doc::view.Publisher') .': %s ==> %s',
            'attach_file_ids' =>    '- '. $changed .' '. trans('doc::view.Attach file') .': %s ==> %s',
            'type_ids' =>           '- '. $changed .' '. trans('doc::view.Document types') .': %s ==> %s',
            'team_ids' =>           '- '. $changed .' '. trans('doc::view.Group') .': %s ==> %s',
            'description' =>        '- '. $changed .' '. trans('doc::view.Description') .': %s ==> %s',
            'request_id' =>         '- '. $changed .' '. trans('doc::view.Document request') .': %s ==> %s',
            'add_reviewer' =>       '- '. $added .' reviewer: %s',
            'add_approver' =>       '- '. $added .' approver: %s',
            'delete_reviewer' =>    '- '. $deleted .' reviewer: %s',
            'delete_approver' =>    '- '. $deleted .' approver: %s',
            'editor_ids' =>         '- '. $changed .' editor: %s ==> %s',
            'code' =>               '- '. $changed .' ' . trans('doc::view.Document code') . ': %s ==> %s',
            'reviewers_name' =>     '- '. $changed .' reviewer: %s ==> %s', 
        ];
    }

    /**
     * insert history
     * @param type $docId
     * @param type $oldData
     * @param type $newData
     * @param type $extraContent
     * @return boolean
     */
    public static function insertData($docId, $oldData, $newData, $extraContent = [])
    {
        $colsData = self::colsData();
        $listDocStatus = DocConst::listDocStatuses();
        $contentChange = '';
        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $oldData) || !isset($colsData[$key])) {
                continue;
            }
            if (is_array($value) && DocConst::compareArray($value, $oldData[$key])
                    || (!is_array($value) && trim($value) == trim($oldData[$key]))) {
                continue;
            }
            if ($key === 'status') {
                $oldData[$key] = isset($listDocStatus[$oldData[$key]]) ? $listDocStatus[$oldData[$key]] : null;
            }
            if (in_array($key, ['publisher_id'])) {
                $value = Employee::find($value);
                $value = $value ? DocConst::getAccount($value->email) : 'NULL';
                $oldData[$key] = Employee::find($oldData[$key]);
                $oldData[$key] = $oldData[$key] ? DocConst::getAccount($oldData[$key]->email) : 'NULL';
            }
            if ($key === 'type_ids') {
                $value = Type::whereIn('id', $value)
                        ->lists('name')
                        ->implode(', ');
                $value = ($value == null) ? 'NULL' : $value;
                $oldData[$key] = Type::whereIn('id', $oldData[$key])
                        ->lists('name')
                        ->implode(', ');
                $oldData[$key] = ($oldData[$key] == null) ? 'NULL' : $oldData[$key];
            }
            if ($key === 'team_ids') {
                $value = Team::whereIn('id', $value)
                        ->lists('name')
                        ->implode(', ');
                $value = ($value == null) ? 'NULL' : $value;
                $oldData[$key] = Team::whereIn('id', $oldData[$key])
                        ->lists('name')
                        ->implode(', ');
                $oldData[$key] = ($oldData[$key] == null) ? 'NULL' : $oldData[$key];
            }
            if ($key === 'attach_file_ids') {
                $value = File::whereIn('id', $value)
                        ->lists('name')
                        ->implode(', ');
                $value = ($value == null) ? 'NULL' : $value;
                $oldData[$key] = File::whereIn('id', $oldData[$key])
                        ->lists('name')
                        ->implode(', ');
                $oldData[$key] = ($oldData[$key] == null) ? 'NULL' : $oldData[$key];
            }
            if ($key === 'request_id') {
                $value = DocRequest::find($value);
                $value = ($value == null) ? 'NULL' : $value->name;
                $oldData[$key] = DocRequest::find($oldData[$key]);
                $oldData[$key] = ($oldData[$key] == null) ? 'NULL' : $oldData[$key]->name;
            }
            if ($key === 'editor_ids') {
                $oldEditors = Employee::whereIn('id', $value)->select('email')->get();
                $oldEditorNames = [];
                if (!$oldEditors->isEmpty()) {
                    foreach ($oldEditors as $emp) {
                        $oldEditorNames[] = $emp->getNickName();
                    }
                }
                $value = (!$oldEditorNames) ? 'NULL' : implode(', ', $oldEditorNames);
                $newEditors = Employee::whereIn('id', $oldData[$key])->select('email')->get();
                $newEditorNames = [];
                if (!$newEditors->isEmpty()) {
                    foreach ($newEditors as $emp) {
                        $newEditorNames[] = $emp->getNickName();
                    }
                }
                $oldData[$key] = (!$newEditorNames) ? 'NULL' : implode(', ', $newEditorNames);
            }
            if ($value === null) {
                $value = 'NULL';
            }
            if ($oldData[$key] === null) {
                $oldData[$key] = 'NULL';
            }
            $contentChange .= sprintf($colsData[$key], $oldData[$key], $value);
            if (isset($extraContent[$key])) {
                $contentChange .= ': ' . $extraContent[$key];
            }
            $contentChange .= "\r\n";
        }
        if (!$contentChange) {
            return false;
        }
        return self::create([
            'doc_id' => $docId,
            'content' => trim($contentChange),
            'created_by' => auth()->id()
        ]);
    }

    /**
     * insert history item
     */
    public static function createItem($docId)
    {
        return self::create([
            'doc_id' => $docId,
            'content' => '- ' . trans('doc::view.Created document'),
            'created_by' => auth()->id()
        ]);
    }

    public static function colsRequestData()
    {
        $changed = trans('doc::view.Changed');
        return [
            'name' => '- '. $changed .' '. trans('doc::view.Name') .': %s ==> %s',
            'status' => '- '. $changed .' '. trans('doc::view.Status') .': %s ==> %s',
            'creator_ids' => '- '. $changed .' '. trans('doc::view.Creator') .': %s ==> %s',
            'content' => '- '. $changed .' '. trans('doc::view.Content'),
            'note' => '- '. $changed .' '. trans('doc::view.Note') .': %s ==> %s',
        ];
    }
    /**
     * insert history
     * @param type $requestId
     * @param type $oldData
     * @param type $newData
     * @param type $extraContent
     * @return boolean
     */
    public static function insertRequestData($requestId, $oldData, $newData, $extraContent = [])
    {
        $colsData = self::colsRequestData();
        $listRequestStatus = DocConst::listRequestStatuses();
        $contentChange = '';
        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $oldData) || !isset($colsData[$key])) {
                continue;
            }
            if (is_array($value) && DocConst::compareArray($value, $oldData[$key])
                    || (!is_array($value) && trim($value) == trim($oldData[$key]))) {
                continue;
            }
            if ($key === 'status') {
                $value = isset($listRequestStatus[$value]) ? $listRequestStatus[$value] : null;
                $oldData[$key] = isset($listRequestStatus[$oldData[$key]]) ? $listRequestStatus[$oldData[$key]] : null;
            }
            if (in_array($key, ['creator_ids'])) {
                $newCreators = Employee::whereIn('id', $value)->get();
                $value = 'NULL';
                if (!$newCreators->isEmpty()) {
                    $newCreators = $newCreators->map(function ($crItem) {
                        $crItem->account = DocConst::getAccount($crItem->email);
                        return $crItem;
                    });
                    $value = $newCreators->implode('account', ', ');
                }
                $oldCreators = Employee::WhereIn('id', $oldData[$key])->get();
                $oldData[$key] = 'NULL';
                if (!$oldCreators->isEmpty()) {
                    $oldCreators = $oldCreators->map(function ($crItem) {
                        $crItem->account = DocConst::getAccount($crItem->email);
                        return $crItem;
                    });
                    $oldData[$key] = $oldCreators->implode('account', ', ');
                }
            }
            if ($value === null) {
                $value = 'NULL';
            }
            if ($oldData[$key] === null) {
                $oldData[$key] = 'NULL';
            }
            $contentChange .= sprintf($colsData[$key], $oldData[$key], $value);
            if (isset($extraContent[$key])) {
                $contentChange .= ': ' . $extraContent[$key];
            }
            $contentChange .= "\r\n";
        }
        if (!$contentChange) {
            return false;
        }
        return self::create([
            'request_id' => $requestId,
            'content' => trim($contentChange),
            'created_by' => auth()->id()
        ]);
    }

    /**
     * insert history item
     */
    public static function createRequestItem($requestId)
    {
        return self::create([
            'request_id' => $requestId,
            'content' => '- ' . trans('doc::view.Created document request'),
            'created_by' => auth()->id()
        ]);
    }

    /**
     * get history by document id
     * @param type $docId
     * @return type
     */
    public static function getByDocument($docId = null, $requestId = null)
    {
        $collect = self::select('dhr.id', 'dhr.content', 'emp.name', 'emp.email', 'dhr.created_at')
                ->from(self::getTableName() . ' as dhr')
                ->join(Employee::getTableName() . ' as emp', 'dhr.created_by', '=', 'emp.id');
        if ($docId) {
            $collect->where('dhr.doc_id', $docId);
        }
        if ($requestId) {
            $collect->where('dhr.request_id', $requestId);
        }
        return $collect->orderBy('dhr.created_at', 'desc')
                ->orderBy('dhr.content', 'asc')
                ->paginate(DocConst::HISTORY_PER_PAGE, ['*'], 'history_page');
    }
}
