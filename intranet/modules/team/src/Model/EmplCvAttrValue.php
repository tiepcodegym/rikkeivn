<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;

class EmplCvAttrValue extends CoreModel
{
    const STATUS_NONE = 0;
    const STATUS_SAVE = 1;
    const STATUS_SUBMIT = 2;
    const STATUS_APPROVE = 3;
    const STATUS_FEEDBACK = 4;
    
    protected $table = 'empl_cv_attr_values';
    protected $fillable = ['employee_id', 'code', 'value'];

    /**
     * defined col and language
     */
    public static function lang()
    {
        return [
            'ja',
            'en',
        ];
    }

    /**
     * get all value attribute of employee - varchar and text
     *
     * @param int $employeeId
     * @return array
     */
    public static function getAllValueCV($employeeId)
    {
        $collection = self::select(['code', 'value'])
            ->where('employee_id', $employeeId)
            ->get();
        $collectionText = EmplCvAttrValueText::select(['code', 'value'])
            ->where('employee_id', $employeeId)
            ->get();
        $attr = new self;
        $attr->eav = [];
        if (!$collection && !$collectionText) {
            return $attr;
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->code] = $item->value;
        }
        foreach ($collectionText as $item) {
            $result[$item->code] = $item->value;
        }
        $attr->eav = $result;
        return $attr;
    }

    /**
     * get value of attribute
     *
     * @param string $col
     * @return string
     */
    public function getVal($col)
    {
        return isset($this->eav[$col]) ? $this->eav[$col] : null;
    }

    /**
     * insert update eav array
     *
     * @param int $employeeId
     * @param array $datasEav
     * @param string $lang
     * @return boolean
     * @throws \Rikkei\Team\Model\Exception
     */
    public static function insertEav($employeeId, $datasEav, $lang, $typeSave = null)
    {
        $now = Carbon::now()->__toString();
        $inserts = [];
        $codesArray = [];
        foreach ($datasEav as $code => $value) {
            if (is_array($value)) {
                $value = json_encode(array_filter($value));
            }
            if ($lang) {
                $code = $code . '_' . $lang;
            }
            $insert = self::insertEavItem($employeeId, $code, $value, $now);
            if ($insert) {
                $inserts[] = $insert;
            }
            $codesArray[] = $code;
        }
        if ($typeSave && in_array($typeSave, self::getStatusSave())) {
            $insert = self::insertEavItem($employeeId, 'status', $typeSave, $now);
            if ($insert) {
                $inserts[] = $insert;
            }
        }
        if ($inserts) {
            self::insert($inserts);
        }
        if (get_called_class() === 'Rikkei\Team\Model\EmplCvAttrValue') {
            $classRemoveCode = EmplCvAttrValueText::class;
        } else {
            $classRemoveCode = EmplCvAttrValue::class;
        }
        if ($codesArray) {
            $classRemoveCode::where('employee_id', $employeeId)
                ->whereIn('code', $codesArray)
                ->delete();
        }
        return true;
    }

    /**
     * insert update eav single
     *
     * @param int $employeeId
     * @param string $code
     * @param string $value
     * @return boolean
     * @throws \Rikkei\Team\Model\Exception
     */
    public static function insertOneEav($employeeId, $code, $value)
    {
        $now = Carbon::now()->__toString();
        if (!$code) {
            return true;
        }
        $insert = self::insertEavItem($employeeId, $code, $value, $now);
        if ($insert) {
            self::insert($insert);
        }
        return true;
    }

    /**
     * insert or update item
     *
     * @param int $employeeId
     * @param string $code
     * @param srting $value
     * @param string $time
     * @return boolean|array
     */
    public static function insertEavItem($employeeId, $code, $value, $time)
    {
        $item = self::where('code', $code)
            ->where('employee_id', $employeeId)
            ->first();
        //insert
        if (!$item) {
            return [
                'employee_id' => $employeeId,
                'code' => $code,
                'value' => $value,
                'created_at' => $time,
                'updated_at' => $time
            ];
        }
        // update
        self::where('code', $code)
            ->where('employee_id', $employeeId)
            ->update([
                'value' => $value,
                'updated_at' => $time
            ]);
        return false;
    }

    /**
     * get all status save
     *
     * @return array
     */
    public static function getStatusSave()
    {
        return [
            self::STATUS_SAVE,
            self::STATUS_SUBMIT,
            self::STATUS_FEEDBACK,
            self::STATUS_APPROVE,
        ];
    }

    /**
     * remove project attribute
     *
     * @param type $projIds
     */
    public static function removeProj($projIds)
    {
        if (!$projIds) {
            return true;
        }
        $deleteMModel = self::orWhereNull('code');
        foreach ($projIds as $id) {
            $deleteMModel->orWhere('code', 'like', "%proj_{$id}_%");
        }
        $deleteMModel->delete();
    }

    /**
     * find approver of skillsheet employee
     *
     * @param type $employeeId
     * @return type
     */
    public static function findApproverSS($employeeId)
    {
        $item = self::select(['value'])
            ->where('employee_id', $employeeId)
            ->where('code', 'ss_approver')
            ->first();
        if (!$item) {
            return null;
        }
        $item = $item->value;
        if (!is_numeric($item)) {
            return null;
        }
        return Employee::find($item);
    }

    /**
     * save approver skillsheet
     *
     * @param int $employeeId
     * @param int $assignId
     */
    public static function saveApproverSS($employeeId, $assignId)
    {
        $insert = self::insertEavItem($employeeId, 'ss_approver', $assignId, Carbon::now()->__toString());
        if ($insert) {
            self::create($insert);
        }
    }

    /**
     * get a value attribute of employee - varchar and text
     *
     * @param int $employeeId
     * @return array
     */
    public static function getValueSingleAttr($employeeId, $code)
    {
        $item = self::select(['value'])
            ->where('employee_id', $employeeId)
            ->where('code', $code)
            ->first();
        if (!$item) {
            return null;
        }
        return $item->value;
    }

    /**
     * get all value status skillsheet
     *
     * @return array
     */
    public static function getValueStatus()
    {
        return [
            self::STATUS_SAVE => 'UnSubmitted',
            self::STATUS_SUBMIT => 'Submitted',
            self::STATUS_APPROVE => 'Approved',
            self::STATUS_FEEDBACK => 'Feedback',
        ];
    }

    /**
     * get label by status skillsheet
     *
     * @param int $status
     * @return string
     */
    public static function getLabelByStatus($status)
    {
        switch ($status) {
            case self::STATUS_SAVE:
                return 'label-default';
            case self::STATUS_SUBMIT:
                return 'label-primary';
            case self::STATUS_APPROVE:
                return 'label-success';
            case self::STATUS_FEEDBACK:
                return 'label-danger';
            default:
                return 'label-default';
        }
    }

    /**
     * Get value skillsheet of emp
     *
     * @param object $item
     * @return string
     */
    public static function getValueOfEmp($item)
    {
        return ($item->status &&
            $item->valSkillSheet && self::getValueStatus()[$item->valSkillSheet]
            ) ? self::getValueStatus()[$item->valSkillSheet] : 'UnSubmitted';
    }
}
