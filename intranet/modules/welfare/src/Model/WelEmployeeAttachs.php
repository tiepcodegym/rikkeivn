<?php

namespace Rikkei\Welfare\Model;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;
use Rikkei\Welfare\Model\Event;
use Rikkei\Welfare\Model\WelAttachFee;

class WelEmployeeAttachs extends CoreModel
{
    /*
     * flag value gender
     */
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;
    const LIMIT_AGE = 12;

    /**
     * Table Name
     *
     * @var string
     */
    protected $table = 'wel_relative_attachs';

    /**
     * Column in table
     *
     * @var array
     */
    protected $fillable =['welfare_id', 'employee_id', 'name', 'gender', 'card_id', 'birthday', 'phone', 'is_joined', 'relation_name_id', 'support_cost'];

    public function setBirthdayAttribute($value)
    {
        $this->attributes['birthday'] = $value ? : null;
    }

    /**
     * Get grid data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getGridData($id)
    {
        $collection = DB::table('wel_relative_attachs')
            ->join('employees', 'wel_relative_attachs.employee_id', '=', 'employees.id')
            ->leftjoin('users', 'wel_relative_attachs.employee_id', '=', 'users.employee_id')
            ->join('welfares', 'wel_relative_attachs.welfare_id', '=', 'welfares.id')
            ->join('relation_names', 'wel_relative_attachs.relation_name_id', '=', 'relation_names.id')
            ->select('employees.name as empname','employees.email as email','wel_relative_attachs.name',
                'relation_names.name as relation_name', 'wel_relative_attachs.birthday as birthday',
                'wel_relative_attachs.gender as gender', 'wel_relative_attachs.phone as phone',
                'wel_relative_attachs.is_joined as joined','wel_relative_attachs.welfare_id as wel_id',
                'wel_relative_attachs.employee_id', 'welfares.end_at_exec', 'wel_relative_attachs.card_id',
                'wel_relative_attachs.id', 'wel_relative_attachs.created_at')
            ->where('wel_relative_attachs.welfare_id', $id);
        return $collection;
    }

    /**
     * Option gender
     *
     * @return array
     */
    public static function optionGender()
    {
        return [
            self::GENDER_MALE => Lang::get('team::view.Male'),
            self::GENDER_FEMALE => Lang::get('team::view.Female'),
        ];
    }

    /**
     * Get Welfare Reletive Attachs By welfare_id
     *
     * @param int $welId
     * @param int $emplId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRelativeAttachByWelId($welId, $emplId)
    {
        return self::select('wel_relative_attachs.id as id', 'wel_relative_attachs.name as name', 'wel_relative_attachs.gender',
            'wel_relative_attachs.phone', 'relation_names.name as relation_name', 'wel_relative_attachs.card_id',
            DB::raw("DATE_FORMAT(wel_relative_attachs.birthday, '%d/%m/%Y') as birthday"))
            ->join('relation_names', 'wel_relative_attachs.relation_name_id', '=', 'relation_names.id')
            ->where([
                ['welfare_id', $welId],
                ['employee_id', $emplId]
            ])
            ->get();
    }

    /**
     *
     * @param int $welId
     * @param int $emplId
     * @return array
     */
    public static function getListRelativeAttachByWelEmpl($welId, $emplId)
    {
        return self::where([
                ['welfare_id', $welId],
                ['employee_id', $emplId],
            ])
            ->lists('id')
            ->toArray();
    }

    public static function checkRelativeAttachByWelId($welId)
    {
        return self::where('welfare_id', $welId)
            ->count() ? true : false;
    }

    /**
     * Array list attached
     *
     * @param int $welId
     * @return Array
     */
    public static function exportListAttachByWelId($welId)
    {
        $welfare = Event::find($welId);
        $query = self::join('employees', 'wel_relative_attachs.employee_id', '=', 'employees.id')
            ->join('relation_names', 'wel_relative_attachs.relation_name_id', '=', 'relation_names.id')
            ->select('wel_relative_attachs.id as employee_id', 'employees.employee_code as emplCode',
                'employees.name as emplName', 'wel_relative_attachs.name', 'wel_relative_attachs.birthday as ages',
                DB::raw("DATE_FORMAT(wel_relative_attachs.birthday, '%d/%m/%Y') as birthday"),
                'wel_relative_attachs.gender', 'wel_relative_attachs.card_id','relation_names.name as relation',
                'wel_relative_attachs.support_cost')
            ->where('wel_relative_attachs.welfare_id', $welId)
            ->get();

        $attached = [];
        foreach ($query as $item) {
            $fee = WelAttachFee::getFeeAttached($welId, $item->support_cost);
            $attached[$item->employee_id] = [
                'emplCode' => $item->emplCode,
                'emplName' => $item->emplName,
                'attachedName' => $item->name,
                'birthday' => $item->birthday,
                'ages' => self::ageAttached($welfare->start_at_exec, $item->ages) ? Lang::get('welfare::view.Is over 12 years old') : Lang::get('welfare::view.Is less than 12 years old'),
                'gender' =>  ($item->gender == self::GENDER_MALE) ? Lang::get('team::view.Male') : Lang::get('team::view.Female'),
                'relation' => $item->relation,
                'card_id' =>  $item->card_id,
                'person_fee' => (int) $fee['perseon_fee'],
                'company_fee' => (int) $fee['company_fee'],
                'note' => '',
                'beneficiaries' => Lang::get('welfare::view.Not'),
            ];
        }
        return array_reverse($attached);
    }

    /**
     * get employee attach event
     * @param employee id event id
     */
    public static function getEmployeeAttach($idEmployee,$event) {
        $data = self::where('employee_id',$idEmployee)
            ->where('welfare_id',$event)
            ->leftjoin('relation_names','wel_relative_attachs.relation_name_id','=','relation_names.id')
            ->select('relation_names.name as nameRelation',
                'wel_relative_attachs.id',
                'wel_relative_attachs.name',
                'wel_relative_attachs.gender',
                'wel_relative_attachs.birthday',
                'wel_relative_attachs.phone')
            ->get();
        if ($data) {
            return $data;
        }
        return null;
    }

    /**
     * get employee attach event
     * @param employee id event id
     */
    public static function getEmployeeAttachById($id) {
        $data = self::where('wel_relative_attachs.id',$id)
            ->leftjoin('relation_names','wel_relative_attachs.relation_name_id','=','relation_names.id')
            ->leftjoin('employees','wel_relative_attachs.employee_id','=','employees.id')
            ->select('relation_names.name as nameRelation',
                'wel_relative_attachs.id',
                'wel_relative_attachs.name',
                'wel_relative_attachs.gender',
                'wel_relative_attachs.birthday',
                'wel_relative_attachs.phone',
                'employees.name as employeeName',
                'employees.id as employeeId',
                'wel_relative_attachs.card_id',
                'wel_relative_attachs.relation_name_id',
                'wel_relative_attachs.welfare_id',
                'wel_relative_attachs.support_cost')
            ->first();
        if ($data) {
            return $data;
        }
        return null;
    }

    /**
     *
     * @param int $id
     * @return WelEmployeeAttachs $welEmployeeAttach
     */
    public static function infoAttached($id)
    {
        return self::join('employees', 'wel_relative_attachs.employee_id', '=', 'employees.id')
            ->join('relation_names', 'relation_names.id', '=','wel_relative_attachs.relation_name_id')
            ->where('wel_relative_attachs.id', $id)
            ->select('employees.name as employee_name', 'wel_relative_attachs.employee_id as employee_id',
                'wel_relative_attachs.birthday as birthday', 'wel_relative_attachs.gender',
                'wel_relative_attachs.phone', 'wel_relative_attachs.welfare_id', 'wel_relative_attachs.support_cost',
                'wel_relative_attachs.card_id', 'wel_relative_attachs.name as name', 'wel_relative_attachs.id',
                'wel_relative_attachs.relation_name_id', 'relation_names.name as relation_name')
            ->first();

    }

    /**
     * get favorable employee attach
     */
    public static function getFavorable() {
        return [
            ["id"=>1,'value'=>"đối tượng được miễn phí"],
            ["id"=>2,'value'=>"Đối tương được giảm 50%"],
            ["id"=>3,'value'=>"Đối tượng đóng 100%"],
        ];
    }

    /**
     * Comparison of the two dates is 12 years apart
     *
     * @param date $dateFirst
     * @param date $dateSecond
     * @return \phpDocumentor\Reflection\Types\Boolean
     */
    public static function ageAttached($dateFirst, $dateSecond)
    {
        $dtFirst = new Carbon($dateFirst);
        $dtSecond = new Carbon($dateSecond);

        $diff = $dtFirst->diffInYears($dtSecond);

        return $diff >= self::LIMIT_AGE;
    }

    /**
     * Save information of person attached
     *
     * @param array $attachs
     * @return boolean
     * @throws \Rikkei\Welfare\Model\Exception
     */
    public static function saveAttachedWithSession($attachs)
    {
        try {
            DB::beginTransaction();
            foreach ($attachs as $attach) {
                if (isset($attach['id']) && $attach['id'] != "") {
                    $relativeAttach = self::find($attach['id']);
                } else {
                    $relativeAttach = new self();
                }
                $relativeAttach->fill($attach);
                $relativeAttach->save();
            }
            DB::commit();
            return true;
        } catch (Exception $ex) {
            throw $ex;
            DB::rollback();
            return false;
        }
    }

    /**
     * Get the number of people attached to support cost
     *
     * @param int $emplId
     * @param int $welId
     * @param int $supportCost
     * @return int
     */
    public static function numberFeeRelativeOfEmployee($emplId, $welId, $supportCost)
    {
        return self::where([
                ['welfare_id', $welId],
                ['employee_id', $emplId],
                ['support_cost', $supportCost],
            ])->count();
    }

}
