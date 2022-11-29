<?php

namespace Rikkei\Sales\Model;

use Rikkei\Core\Model\CoreModel;

class ReqOpporMember extends CoreModel
{
    protected $table = 'request_oppor_members';
    protected $fillable = ['request_oppor_id', 'type', 'member_exp', 'role', 'english_level', 'japanese_level'];
    public $timestamps = false;

    /**
     * get programs that belongs to
     * @return type
     */
    public function programs()
    {
        return $this->belongsToMany('\Rikkei\Resource\Model\Programs', 'request_oppor_member_program', 'req_member_id', 'prog_id');
    }

    /**
     * list programs id
     * @return type
     */
    public function programsIds()
    {
        $programs = $this->programs;
        if ($programs->isEmpty()) {
            return [];
        }
        return $programs->lists('id')->toArray();
    }

    /**
     * update item by request id
     * @param type $requestId
     * @param type $dataMembers
     * @return boolean
     */
    public static function updateByReqId($requestId, $dataMembers = [])
    {
        if (!$dataMembers) {
            return false;
        }
        $keepIds = [];
        $fieldsFill = self::getFillableCols();
        unset($fieldsFill[0]); //unset request_oppor_id
        unset($fieldsFill[1]); //unset type
        foreach ($dataMembers as $member) {
            //$progIds = $member['prog_ids'];
            //$progIds = $progIds ? $progIds : [];
            $dataItem = [
                'request_oppor_id' => $requestId,
                'number' => 0
            ];
            foreach ($fieldsFill as $field) {
                $dataItem[$field] = (isset($member[$field]) && $member[$field]) ? $member[$field] : null;
            }
            $id = $member['id'] ? $member['id'] : null;
            if ($id) {
                $itemMember = self::findOrFail($id);
                $itemMember->update($dataItem);
            } else {
                $itemMember = self::create($dataItem);
            }
            //$itemMember->programs()->sync($progIds);
            $keepIds[] = $itemMember->id;
        }
        if ($keepIds) {
            self::where('request_oppor_id', $requestId)
                    ->whereNotIn('id', $keepIds)
                    ->delete();
        }
        return true;
    }

    public static function updateByReqIdOld($requestId, $dataMembers = [])
    {
        if (!$dataMembers['ids']) {
            return false;
        }
        $keepIds = [];
        $fieldsFill = self::getFillableCols();
        unset($fieldsFill[0]); //unset request_oppor_id
        unset($fieldsFill[1]); //unset type
        $dataProgIds = isset($dataMembers['prog_ids']) ? $dataMembers['prog_ids'] : [];
        foreach ($dataMembers['ids'] as $index => $id) {
            $progIds = isset($dataProgIds[$index]) ? $dataProgIds[$index] : [];
            $progIds = $progIds ? $progIds : [];
            $dataItem = [
                'request_oppor_id' => $requestId
            ];
            foreach ($fieldsFill as $field) {
                $dataItem[$field] = isset($dataMembers[$field .'s'][$index]) ? $dataMembers[$field .'s'][$index] : null;
            }
            if ($id) {
                $itemMember = self::findOrFail($id);
                $itemMember->update($dataItem);
            } else {
                $itemMember = self::create($dataItem);
            }
            $itemMember->programs()->sync($progIds);
            $keepIds[] = $itemMember->id;
        }
        if ($keepIds) {
            self::where('request_oppor_id', $requestId)
                    ->whereNotIn('id', $keepIds)
                    ->delete();
        }
        return true;
    }
}

