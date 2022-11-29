<?php

namespace Rikkei\ManageTime\View;

use Carbon\Carbon;
use Lang;
use Rikkei\Core\View\View as HelperView;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\BusinessTripRelater;
use Rikkei\ManageTime\Model\BusinessTripTeam;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;

class ApplicationHelper
{
    /**
     * Check application has changed data after submit
     *
     * @param object $oldObj    BusinessTripRegister | LeaveDayRegister | SupplementRegister | OtRegister
     * @param array $newData    data from form submit
     *
     * @return boolean      true: has changed
     */
    public function applicationFieldsChanged($oldObj, $newData)
    {
        $filedsChanged = [];
        foreach ($newData as $field => $newValue) {
            if (in_array($field, ['table_data_emps', 'approver'])) {
                continue;
            }
            if (in_array($field, ['date_start', 'date_end'])) {
                $newData[$field] = Carbon::parse($newData[$field])->format('Y-m-d H:i:s');
            }
            if ($oldObj->$field != $newData[$field]) {
                $filedsChanged[] = [
                    'field' => $field,
                    'old' => $oldObj->$field,
                    'new' => $newData[$field],
                ];
            }
        }
        return $filedsChanged;
    }

    /**
     * Save business team, business relater, business employee
     *
     * @param BusinessTripRegister $registerRecord
     * @param Request $request
     * @param Employee $userCurrent
     *
     * @return void
     */
    public function saveRelate($registerRecord, $request, $userCurrent = null)
    {
        if (empty($userCurrent)) {
            $userCurrent = Permission::getInstance()->getEmployee();
        }
        BusinessTripTeam::where('register_id', $registerRecord->id)->delete();
        $registerTeam = [];
        $teamsOfRegistrant = ManageTimeCommon::getTeamsOfEmployee($userCurrent->id);
        foreach ($teamsOfRegistrant as $team) {
            $registerTeam[] = array('register_id' => $registerRecord->id, 'team_id'=> $team->id, 'role_id' => $team->role_id);
        }
        BusinessTripTeam::insert($registerTeam);

        $registerRecordNew = BusinessTripRegister::getInformationRegister($registerRecord->id);
        $data['user_mail']       = $userCurrent->email;
        $data['mail_to']         = $registerRecordNew->approver_email;
        $data['mail_title']      = Lang::get('manage_time::view.[Business trip] :name register business trip, from date :start_date to date :end_date', ['name' => $registerRecordNew->creator_name, 'start_date' => Carbon::parse($registerRecordNew->date_start)->format('d/m/Y'), 'end_date' => Carbon::parse($registerRecordNew->date_end)->format('d/m/Y')]);
        $data['status']          = Lang::get('manage_time::view.Unapprove');
        $data['registrant_name'] = $registerRecordNew->creator_name;
        $data['approver_name']   = $registerRecordNew->approver_name;
        $data['team_name']       = $registerRecordNew->role_name;
        $data['start_date']      = Carbon::parse($registerRecordNew->date_start)->format('d/m/Y');
        $data['start_time']      = Carbon::parse($registerRecordNew->date_start)->format('H:i');
        $data['end_date']        = Carbon::parse($registerRecordNew->date_end)->format('d/m/Y');
        $data['end_time']        = Carbon::parse($registerRecordNew->date_end)->format('H:i');
        $data['location']        = $registerRecordNew->location;
        $data['purpose']         = HelperView::nl2br(ManageTimeCommon::limitText($registerRecordNew->purpose, 50));
        $data['link']            = route('manage_time::profile.mission.detail', ['id' => $registerRecordNew->register_id]);
        $data['to_id']           = $registerRecordNew->approver_id;
        $data['noti_content']    = $data['mail_title'];

        $template = 'manage_time::template.mission.mail_register.mail_register_to_approver';
        $notificationData = [
            'category_id' => RkNotify::CATEGORY_TIMEKEEPING
        ];
        ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData); //

        BusinessTripRelater::where('register_id', $registerRecord->id)->delete();
        $relatedPersons = $request->related_persons_list;
        if (!empty($relatedPersons)) {
            $comeLateRelaters = [];
            foreach ($relatedPersons as $key => $value) {
                $registerRelaters [] = array('register_id' => $registerRecord->id, 'relater_id'=> $value);
            }
            BusinessTripRelater::insert($registerRelaters);
        }

        //Insert supplement together
        BusinessTripEmployee::removeAllEmp($registerRecord->id);
        $togethers = json_decode($request->table_data_emps, true);
        $strEmpId = '';
        if (count($togethers)) {
            $insertTogether = [];
            foreach ($togethers as $together) {
                $insertTogether[] = [
                    'register_id' => $registerRecord->id,
                    'employee_id' => $together['empId'],
                    'start_at' => Carbon::createFromFormat('d-m-Y H:i', $together['startAt'])->format('Y-m-d H:i'),
                    'end_at' => Carbon::createFromFormat('d-m-Y H:i', $together['endAt'])->format('Y-m-d H:i'),
                ];
                
                $strEmpId .= ',' . $together['empId'];
            }
            BusinessTripEmployee::insert($insertTogether);
            $strEmpId = trim ($strEmpId, ',');
            BusinessTripEmployee::insertTeamId($strEmpId, $registerRecord->id);
        }
    }
}
