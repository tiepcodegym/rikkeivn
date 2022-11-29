<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Lang;
use Carbon\Carbon;
use Rikkei\Welfare\Model\WelfareFee;
use Rikkei\Welfare\Model\RelationName;
use Illuminate\Support\Facades\DB;
use Rikkei\Welfare\Model\Event;

class WelAttachFee extends CoreModel
{
    const Fee_0 = 1;
    const Fee_50 = 2;
    const Fee_100 = 3;
    const Old_free = 12;
    const Name_rilative_free = "Con";

    protected $table = 'wel_attach_fee';
    protected $primaryKey = 'id';

    /**
     * Column in table
     *
     * @var array
     */
    protected $fillable =[' fee_free_count', 'fee_free_relative', 'fee50_count',
                        'fee50_relative', 'fee100_count', 'fee100_relative'];

    /**
     * Check favorable attach
     *
     * @param array $data
     * $return boolean
     */
    public  static function checkFavorable($data) {
        
        $dataAttachEmployee = WelAttachFee::where('wel_id',$data['wel_id'])->first();
        switch ($data['favorable']) {
            case self::Fee_0 :
                return $dataAttachEmployee->fee_free_relative;
                break;
            case self::Fee_50 :
                return $dataAttachEmployee->fee50_relative;
                break;
            case self::Fee_100 :
                return $dataAttachEmployee->fee100_relative;
                break;
            default:
                return false;
        }
    }

    /**
     * Get list fee wel attach employee
     *
     * @return array
     */
    public static function getLisfeeWellAttach()
    {
        return [
            self::Fee_0   => Lang::get('welfare::view.Fee_free'),
            self::Fee_50  => Lang::get('welfare::view.Fee_free_50'),
            self::Fee_100 => Lang::get('welfare::view.Fee_free_100'),
        ];
    }

    /**
     * Save fee attach employee
     *
     * @param array $data
     * @param int $idEvent
     */
    public static function FeeAttach($data, $idEvent)
    {
        $dataFee = self::where('wel_id', $idEvent)->select('id')->first();

        if (count($dataFee) > 0) {
            $wel_update                    = self::find($dataFee->id);
            if(isset($data['fee_free_relative']) && $data['fee_free_relative']) {
                $wel_update->fee_free_relative = implode(',', $data['fee_free_relative']);
            } else {
                $wel_update->fee_free_relative = NULL;  
            }
            if(isset($data['fee_free_count']) && $data['fee_free_count']) {
                $wel_update->fee_free_count    = $data['fee_free_count'];
            } else {
                $wel_update->fee_free_count = NULL;  
            }
            if(isset($data['fee50_relative']) && $data['fee50_relative']) {
                $wel_update->fee50_relative    = implode(',', $data['fee50_relative']);
            } else {
                $wel_update->fee50_relative = NULL;  
            }
            if(isset($data['fee50_count']) && $data['fee50_count']) {
                $wel_update->fee50_count       = $data['fee50_count'];
            } else {
                $wel_update->fee50_count = NULL;  
            }
            if(isset($data['fee100_relative']) && $data['fee100_relative']) {
                $wel_update->fee100_relative   = implode(',', $data['fee100_relative']);
            } else {
                $wel_update->fee100_relative = NULL;  
            }
            if(trim($data['fee100_count']) == null) {
                $wel_update->fee100_count  = null;
            } else {    
                $wel_update->fee100_count  = $data['fee100_count'];
            }
            $wel_update->save();
        } else {

            $wel_new                    = new self();
            $wel_new->wel_id            = $idEvent;
            if(isset($data['fee_free_relative']) && $data['fee_free_relative']) {
                $wel_new->fee_free_relative = implode(',', $data['fee_free_relative']);
            } else {
                $wel_new->fee_free_relative = NULL;  
            }
            if(isset($data['fee50_relative']) && $data['fee50_relative']) {
                $wel_new->fee50_relative    = implode(',', $data['fee50_relative']);
            } else {
                $wel_new->fee50_relative = NULL;  
            }
            if(isset($data['fee100_relative']) && $data['fee100_relative']) {
                $wel_new->fee100_relative   = implode(',', $data['fee100_relative']);
            } else {
                $wel_new->fee100_relative = NULL;  
            }
            if(isset($data['fee_free_count']) && $data['fee_free_count']) {
                $wel_new->fee_free_count    = $data['fee_free_count'];
            } else {
                $wel_new->fee_free_count = NULL;  
            }
            if(isset($data['fee50_count']) && $data['fee50_count']) {
                $wel_new->fee50_count       = $data['fee50_count'];
            } else {
                $wel_new->fee50_count = NULL;  
            }
            if(trim($data['fee100_count']) != null) {
                $wel_new->fee100_count  = $data['fee100_count'];
            }
            $wel_new->save();
        }
    }

    /**
     * Get data by event id
     *
     * @param int $eventId
     * @return WelAttachFee $welAttachFee
     */
    public static function getDataByEventId($eventId)
    {
        return self::where('wel_id', $eventId)->first();
    }

    /* Get fee of person attached follow support_cost
     *
     * @param int $welId
     * @param int $idSP support_cost
     * @return array
     */
    public static function getFeeAttached($welId, $idSP)
    {
        $data       = WelfareFee::where('wel_id', $welId)->first();
        $personFee  = (int) $data->attachments_first_fee;
        $companyFee = (int) $data->attachments_first_company_fee;
        switch ($idSP) {
            case self::Fee_0 :
                $fee = [
                    'perseon_fee' => 0,
                    'company_fee' => $personFee,
                ];
                break;
            case self::Fee_50 :
                $fee = [
                    'perseon_fee' => (int) ($personFee / 2),
                    'company_fee' => (int) ($companyFee + $personFee / 2),
                ];
                break;
            case self::Fee_100 :
                $fee = [
                    'perseon_fee' => $personFee,
                    'company_fee' => $companyFee,
                ];
                break;
            default:
                $fee = [
                    'perseon_fee' => $personFee,
                    'company_fee' => $companyFee,
                ];
        }
        return $fee;
    }

    /**
     * Get information of Welfare Fee follow welId
     *
     * @param int $welId
     * @return array
     */
    public static function infoWelAttachFee($welId)
    {
        $attachFee = WelAttachFee::where('wel_id', $welId)->first();
        if (!$attachFee) {
            return null;
        }

        $arrayAttachFee[self::Fee_0]   = [
            'relation' => RelationName::listNameByListId($attachFee->fee_free_relative),
            'number'   => $attachFee->fee_free_count,
        ];
        $arrayAttachFee[self::Fee_50]  = [
            'relation' => RelationName::listNameByListId($attachFee->fee50_relative),
            'number'   => $attachFee->fee50_count,
        ];
        $arrayAttachFee[self::Fee_100] = [
            'relation' => RelationName::listNameByListId($attachFee->fee100_relative),
            'number'   => $attachFee->fee100_count,
        ];

        return $arrayAttachFee;
    }

    /**
     * Check date retilave free
     *
     * @param int $idRelative
     * @param date $birthday
     * @return boolean
     */
    public static function checkDateRelativeFree($idRelative,$birthday,$checkDate) {
        return $birthday > $checkDate && in_array($idRelative , RelationName::getIdByName(self::Name_rilative_free));
    }

    /**
     * check employee attach with checkFavorable
     *
     * @param array $data
     * @return int
     */
    public static function checkEmployAttachFavorable($data) {
        $dateRegister=strtotime(self::getDateRegister($data['welfare_id']));
        $checkDate = strtotime( '-'.self::Old_free. 'year' , strtotime (date('Y-m-d',$dateRegister))) ;
        $checkDate = date ( 'Y-m-d' , $checkDate );
        $checkRelative = self::getIdRelativeFavorable($data['welfare_id'],$data['support_cost']);
        if(!in_array($data['relation_name_id'], $checkRelative)) {
            return false;
        }
        if(self::checkDateRelativeFree($data['relation_name_id'],$data['birthday'],$checkDate)) {
            return true;
        }
        $childAttach    = WelEmployeeAttachs::where('welfare_id', $data['welfare_id'])
                ->where('employee_id', $data['employee_id'])->where('birthday', '>', $checkDate)
                ->whereIn('relation_name_id', RelationName::getIdByName(self::Name_rilative_free))
                ->select('id')->pluck('id');
        //check id attach under 12 relative "con" and id attach edit
        $attachEmployee = WelEmployeeAttachs::where('welfare_id', $data['welfare_id'])
                ->where('employee_id', $data['employee_id'])
                ->whereNotIn('id', $childAttach)->whereNotIn('id', [$data['id']]);
        $countActure    = self::where('wel_id', $data['welfare_id'])->first();
        if ($data['support_cost'] == self::Fee_0) {
            return count($attachEmployee
                        ->where('support_cost', self::Fee_0)->get()) < $countActure->fee_free_count;
        }
        if ($data['support_cost'] == self::Fee_50) {
            return count($attachEmployee
                        ->where('support_cost', self::Fee_50)->get()) < $countActure->fee50_count;
        }
        if ($data['support_cost'] == self::Fee_100) {
            if($countActure->fee100_count == null) {
                return true;
            }
            return count($attachEmployee
                        ->where('support_cost', self::Fee_100)->get()) < $countActure->fee100_count;
        }
    }

    /**
     * Get array id relative by favorable wel_id
     *
     * @param int $wel_id
     * @param int $favorable
     * @return boolean
     *
     */
    public static function getIdRelativeFavorable($wel_id, $favorable)
    {
        $list = self::where('wel_id', $wel_id)->first();
        switch ($favorable) {
            case self::Fee_0 :
                return explode(',', $list->fee_free_relative);
                break;
            case self::Fee_50 :
                return explode(',', $list->fee50_relative);
                break;
            case self::Fee_100 :
                return explode(',', $list->fee100_relative);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Check information about the number of staff attachments
     *
     * @param array $data
     * @return boolean
     */
    public static function checkPriority($data)
    {
        $welfare  = Event::find($data['welid']);
        $children = in_array($data['relation_name_id'], RelationName::getIdByName(self::Name_rilative_free));

        // Relationship is 'Con' anh $birthday less than 12
        if (isset($children) && $children != "") {
            $checkAges = WelEmployeeAttachs::ageAttached($welfare->start_at_exec, $data['birthday']);
            if($checkAges) {
                return true;
            }
        }

        $dataAttachEmployee = WelAttachFee::where('wel_id', $data['welid'])->first();
        $check              = 0;

        // Case the cost relationship is free
        if ($data['support_cost'] == self::Fee_0) {
            $count  = (int) WelEmployeeAttachs::numberFeeRelativeOfEmployee($data['employee_id'], $data['welid'], $data['support_cost']);
            if ($count >= $dataAttachEmployee->fee_free_count) {
                $check++;
            }
        }

        // Case the cost relationship must pay 50%
        if ($data['support_cost'] == self::Fee_50) {
            $count  = (int) WelEmployeeAttachs::numberFeeRelativeOfEmployee($data['employee_id'], $data['welid'], $data['support_cost']);
            if ($count >= $dataAttachEmployee->fee50_count) {
                $check++;
            }
        }

        // Case the cost relationship must pay 100%
        if ($data['support_cost'] == self::Fee_100) {
            $count  = (int) WelEmployeeAttachs::numberFeeRelativeOfEmployee($data['employee_id'], $data['welid'], $data['support_cost']);
            if ($count >=  $dataAttachEmployee->fee100_count) {
                $check++;
            }
        }

        return $check == 0;
    }

    /**
     * get date end_at_register event
     * @param id event
     * @return  end_at_register event
     */
    public static function getDateRegister($id) {
        return Event::find($id)->end_at_register;
    }

}
