<?php

namespace Rikkei\Team\Model;
use Illuminate\Support\Facades\Validator;
use Exception;

class EmployeeContact extends EmployeeItemRelate
{
    protected $table = 'employee_contact';

    const NOT_SHOW_PHONE = 0;
    const SHOW_PHONE = 1;
    const NOT_SHOW_BIRTHDAY = 0;
    const SHOW_BIRTHDAY = 1;
    const SHOW_ONLY_YEAR = 2;
    const DONT_RECEIVE_SYSTEM_MAIL = 1;
    const RECEIVE_SYSTEM_MAIL = 0;

    /**
     * save employee contact follow employeeId
     * @param type $employeeId
     * @param type $data
     * @return type
     * @throws Exception
     */
    public static function saveItems($employeeId ,$data= [])
    {
        
        if(! $data ) {
            return;
        }
        try {
            $model = self::where('employee_id', $employeeId)
                    ->first();
            if(!$model){
                $model = new self;
            }
            $data['employee_id']  = $employeeId;

            $validator = Validator::make($data, [
                'employee_id' => 'required|integer',
                'other_email' => 'max:100|email',
                'personal_email' => 'max:100|email',
            ]);
            $model->setData($data);

            if ($validator->fails()) {
                return redirect()->route('team::member.profile.index')
                    ->withErrors($validator)->send();
            }

            return $model->save();
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * get birthday and phone options
     * @param type $employeeId
     * @return array $contactOption
     */
    public static function getContactOption($employeeId)
    {
        $contactOption = [];
        $employeeContact = self::where('employee_id', $employeeId)
                                ->first();
        if ($employeeContact) {
            $contactOption['can_show_phone'] = (int)$employeeContact->can_show_phone;
            $contactOption['can_show_birthday'] = (int)$employeeContact->can_show_birthday;
            $contactOption['dont_receive_system_mail'] = (int)$employeeContact->dont_receive_system_mail;
        } else {
            $contactOption['can_show_phone'] = self::SHOW_PHONE;
            $contactOption['can_show_birthday'] = self::SHOW_BIRTHDAY;
            $contactOption['dont_receive_system_mail'] = self::DONT_RECEIVE_SYSTEM_MAIL;
        }
        return $contactOption;
    }
    
    /**
     * get list relationship
     * @return array
    public static function toOptionRelationship()
    {
        $relations = [
                0   => Lang::get('team::profile.Grandfather'),
                1   => Lang::get('team::profile.Grandmother'),
                2   => Lang::get('team::profile.Father'),
                3   => Lang::get('team::profile.Mother'),
                4   => Lang::get('team::profile.Wife'),
                5   => Lang::get('team::profile.Elder Brother'),
                6   => Lang::get('team::profile.Elder Sister'),
                7   => Lang::get('team::profile.Brother'),
                8   => Lang::get('team::profile.Sister'),
                9   => Lang::get('team::profile.Son'),
                10  => Lang::get('team::profile.Daughter'),
                11  => Lang::get('team::profile.Mother in law'),
                12  => Lang::get('team::profile.Other'),
        ];
        
        return $relations;
    }*/
    
    /**
     * get Social address (skype , yahoo)
     * @return string $str
     */
    public function getSocial()
    {
        $str = "";
        $yahoo = $this->yahoo;
        $skype = $this->skype;
        $str = $yahoo ? 'Yahoo : '. $yahoo : "";
        $str .= $skype ? "<br> Skype : " . $skype : "";        
        
        return $str;
    }
    
    /**
     * get string contact address
     * @return string $str
     */
    public function getContactAddress()
    {
        return $this->native_addr ? $this->native_addr : $this->tempo_addr;
    }

    public static function getByEmp($empId)
    {
        return self::where('employee_id', $empId)->first();
    }

    public static function updateSkype($empId, $newSkype)
    {
        self::where('employee_id', $empId)
            ->update([
                'skype' => $newSkype,
            ]);
    }

    public static function getEmployeeNotNoti()
    {
        return self::where('dont_receive_system_mail', 1)
            ->get();
    }
}
