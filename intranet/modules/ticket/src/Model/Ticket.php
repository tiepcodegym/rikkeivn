<?php

namespace Rikkei\Ticket\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\View\Config;
use Carbon\Carbon;
use DB;

class Ticket extends CoreModel
{
    const STATUS_OVERDUE = 0;
    const STATUS_OPENED = 1;
    const STATUS_INPROGRESS = 2;
    const STATUS_RESOLVED = 3;
    const STATUS_FEEDBACK = 4;
    const STATUS_CLOSED = 5;
    const STATUS_CANCELLED = 6;

    const TEAM_CODE = 'hanoi_it';

    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAl = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_EMERGENCY = 4;

    const RATING_UNSATISFIED = 1;
    const RATING_SATISFIED = 2;

    const IS_NOT_READ = 0;

    const ARRAY_STATUS = array(Ticket::STATUS_OPENED, Ticket::STATUS_INPROGRESS, Ticket::STATUS_FEEDBACK);

    protected $table = 'tickets';

    /**
     * Purpose : get ticket detail
     *
     * @param $ticketId
     */
    public static function getTicketById($ticketId)
    {   
        $ticket = self::select(
                        'tickets.id', 
                        'tickets.subject', 
                        'tickets.content', 
                        'tickets.deadline', 
                        'tickets.team_id', 
                        'tickets.status as ticket_status', 
                        'tickets.priority as ticket_priority', 
                        'tickets.created_at', 
                        'tickets.closed_at', 
                        'tickets.resolved_at', 
                        'tickets.rating', 
                        'ts.status as attribute_status', 
                        'tp.priority as attribute_priority', 
                        'cb.name as created_name', 
                        'cb.id as created_by', 
                        'cb.email as created_email', 
                        'at.id as assigned_to', 
                        'at.name as assigned_name', 
                        'at.email as assigned_email', 
                        'users.avatar_url', 
                        'teams.name as team_name') 
                    ->where('tickets.id', $ticketId)
                    ->join('employees as cb', 'cb.id', '=', 'tickets.created_by')            
                    ->leftJoin('employees as at', 'at.id', '=', 'tickets.assigned_to')
                    ->leftJoin('users', 'users.employee_id', '=', 'tickets.created_by')        
                    ->join('ticket_attributes as ts', 'ts.id', '=', 'tickets.status')           
                    ->join('ticket_attributes as tp', 'tp.id', '=', 'tickets.priority')        
                    ->join('teams', 'teams.id', '=', 'tickets.team_id')           
                    ->first();

        return $ticket;
    }

    /**
     * Purpose : get list tickets of creator
     *
     * @param $createdBy, $status
     *
     * @return collection model
     */
    public static function getTicketsCreatedBy($createdBy, $status = null)
    {
        $collection = self::select('tickets.id', 
                            'tickets.subject', 
                            'tickets.deadline',  
                            'ts.status' , 
                            'tickets.status as status_id', 
                            'tp.priority', 
                            'tickets.priority as priority_id', 
                            'cb.id as created_by', 
                            'cb.name as created_name', 
                            'at.id as assigned_to', 
                            'at.name as assigned_name', 
                            'team_table.name as team_name')
            ->join('employees as cb', 'cb.id', '=', 'tickets.created_by')
            ->leftJoin('employees as at', 'at.id', '=', 'tickets.assigned_to')
            ->leftJoin('teams as team_table', 'team_table.id', '=', 'tickets.team_id')
            ->join('ticket_attributes as ts', 'ts.id', '=', 'tickets.status')
            ->join('ticket_attributes as tp', 'tp.id', '=', 'tickets.priority')
            ->where('tickets.created_by', $createdBy);

        if(!is_null($status) && $status == self::STATUS_OVERDUE)
        {
            // Kiểm tra ticket đã hết hạn hay chưa
            $collection = $collection->whereIn('tickets.status', self::ARRAY_STATUS)
                ->where('tickets.deadline', '<', Carbon::now());
        } elseif(!is_null($status)) {
            $collection = $collection->where('tickets.status', $status);
        }

        $pager = Config::getPagerData(null, ['order' => 'tickets.priority', 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy('tickets.status', 'ASC')->orderBy('tickets.deadline', 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;    
    }

    /**
     * Purpose : get list tickets of related person
     *
     * @param $relatedPersonId, $status
     *
     * @return collection model
     */
    public static function getTicketsOfRelatedPerson($relatedPersonId, $status = null)
    {
        $collection = self::select('tickets.id', 
                            'tickets.subject', 
                            'tickets.deadline',  
                            'ts.status' , 
                            'tickets.status as status_id', 
                            'tp.priority', 
                            'tickets.priority as priority_id', 
                            'cb.id as created_by', 
                            'cb.name as created_name', 
                            'at.id as assigned_to', 
                            'at.name as assigned_name', 
                            'team_table.name as team_name')
            ->join('employees as cb', 'cb.id', '=', 'tickets.created_by')
            ->join('employees as at', 'at.id', '=', 'tickets.assigned_to')
            ->join('teams as team_table', 'team_table.id', '=', 'tickets.team_id')
            ->join('ticket_relaters as ticket_relater_table', 'ticket_relater_table.ticket_id', '=', 'tickets.id')
            ->join('ticket_attributes as ts', 'ts.id', '=', 'tickets.status')
            ->join('ticket_attributes as tp', 'tp.id', '=', 'tickets.priority')
            ->where('ticket_relater_table.employee_id', $relatedPersonId);

        if(!is_null($status) && $status == self::STATUS_OVERDUE)
        {
            // Kiểm tra ticket đã hết hạn hay chưa
            $collection = $collection->whereIn('tickets.status', self::ARRAY_STATUS)
                ->where('tickets.deadline', '<', Carbon::now());
        } elseif(!is_null($status)) {
            $collection = $collection->where('tickets.status', $status);
        }

        $pager = Config::getPagerData(null, ['order' => 'tickets.priority', 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy('tickets.status', 'ASC')->orderBy('tickets.deadline', 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Purpose : get list tickets of assigned
     *
     * @param $assignedTo, $status
     *
     * @return collection model
     */
    public static function getTicketsAssignedTo($assignedTo, $status = null)
    {
        $collection = self::select('tickets.id', 
                            'tickets.subject', 
                            'tickets.deadline',  
                            'ts.status' , 
                            'tickets.status as status_id', 
                            'tp.priority', 
                            'tickets.priority as priority_id', 
                            'cb.id as created_by', 
                            'cb.name as created_name', 
                            'at.id as assigned_to', 
                            'at.name as assigned_name', 
                            'team_table.name as team_name')
            ->join('employees as cb', 'cb.id', '=', 'tickets.created_by')
            ->join('employees as at', 'at.id', '=', 'tickets.assigned_to')
            ->join('teams as team_table', 'team_table.id', '=', 'tickets.team_id')
            ->join('ticket_attributes as ts', 'ts.id', '=', 'tickets.status')
            ->join('ticket_attributes as tp', 'tp.id', '=', 'tickets.priority')
            ->where('tickets.assigned_to', $assignedTo);

        if(!is_null($status) && $status == self::STATUS_OVERDUE)
        {
            // Kiểm tra ticket đã hết hạn hay chưa
            $collection = $collection->whereIn('tickets.status', self::ARRAY_STATUS)
                ->where('tickets.deadline', '<', Carbon::now());
        } elseif(!is_null($status)) {
            $collection = $collection->where('tickets.status', $status);
        }
        
        $pager = Config::getPagerData(null, ['order' => 'tickets.priority', 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy('tickets.status', 'ASC')->orderBy('tickets.deadline', 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Purpose : get list tickets of team
     *
     * @param $teamId, $status
     *
     * @return collection model
     */
    public static function getTicketsOfTeam($teamId, $status = null)
    {
        $collection = self::select('tickets.id', 
                            'tickets.subject', 
                            'tickets.deadline',  
                            'ts.status' , 
                            'tickets.status as status_id', 
                            'tp.priority', 
                            'tickets.priority as priority_id', 
                            'cb.id as created_by', 
                            'cb.name as created_name', 
                            'at.id as assigned_to', 
                            'at.name as assigned_name', 
                            'team_table.name as team_name')
            ->join('employees as cb', 'cb.id', '=', 'tickets.created_by')
            ->join('employees as at', 'at.id', '=', 'tickets.assigned_to')
            ->join('teams as team_table', 'team_table.id', '=', 'tickets.team_id')
            ->join('ticket_attributes as ts', 'ts.id', '=', 'tickets.status')
            ->join('ticket_attributes as tp', 'tp.id', '=', 'tickets.priority')
            ->where('tickets.team_id', $teamId);

        if(!is_null($status) && $status == self::STATUS_OVERDUE)
        {
            // Kiểm tra ticket đã hết hạn hay chưa
            $collection = $collection->whereIn('tickets.status', self::ARRAY_STATUS)
                ->where('tickets.deadline', '<', Carbon::now());
        } elseif(!is_null($status)) {
            $collection = $collection->where('tickets.status', $status);
        }

        $pager = Config::getPagerData(null, ['order' => 'tickets.priority', 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy('tickets.status', 'ASC')->orderBy('tickets.deadline', 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Purpose : get list tickets of department IT
     *
     * @param $status
     *
     * @return collection model
     */
    public static function getTicketsOfDepartmentIT($status = null)
    {
        $collection = self::select('tickets.id', 
                            'tickets.subject', 
                            'tickets.deadline',  
                            'ts.status' , 
                            'tickets.status as status_id', 
                            'tp.priority', 
                            'tickets.priority as priority_id', 
                            'cb.id as created_by', 
                            'cb.name as created_name', 
                            'at.id as assigned_to', 
                            'at.name as assigned_name', 
                            'team_table.name as team_name')
            ->join('employees as cb', 'cb.id', '=', 'tickets.created_by')
            ->join('employees as at', 'at.id', '=', 'tickets.assigned_to')
            ->join('teams as team_table', 'team_table.id', '=', 'tickets.team_id')
            ->join('ticket_attributes as ts', 'ts.id', '=', 'tickets.status')
            ->join('ticket_attributes as tp', 'tp.id', '=', 'tickets.priority');

        if(!is_null($status) && $status == self::STATUS_OVERDUE)
        {
            // Kiểm tra ticket đã hết hạn hay chưa
            $collection = $collection->whereIn('tickets.status', self::ARRAY_STATUS)
                ->where('tickets.deadline', '<', Carbon::now());
        } elseif(!is_null($status)) {
            $collection = $collection->where('tickets.status', $status);
        }

        $pager = Config::getPagerData(null, ['order' => 'tickets.priority', 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy('tickets.status', 'ASC')->orderBy('tickets.deadline', 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    /**
     * Purpose : count tickets of created by
     *
     * @param $employeeId, $status
     *
     * @return int
     */
    public static function countTicketsCreatedBy($employeeId, $status = null)
    {
        $countTickets = self::where('created_by', $employeeId);
        if(is_null($status))
        {
            return $countTickets->count();
        } elseif ($status == self::STATUS_OVERDUE) {
            $countTickets = $countTickets->whereIn('status', self::ARRAY_STATUS)
                                ->where('deadline', '<', Carbon::now());
        } else {
            $countTickets = $countTickets->where('status', $status);
        } 

        return $countTickets->count();
    }

    /**
     * Purpose : count tickets of related person
     *
     * @param $relatedPersonId, $status
     *
     * @return int
     */
    public static function countTicketsOfRelatedPerson($relatedPersonId, $status = null)
    {
        $countTickets = self::join('ticket_relaters as ticket_relater_table', 'ticket_relater_table.ticket_id', '=', 'tickets.id')
                            ->where('ticket_relater_table.employee_id', $relatedPersonId);
        if(is_null($status))
        {
            return $countTickets->count();
        } elseif ($status == self::STATUS_OVERDUE) {
            $countTickets = $countTickets->whereIn('status', Ticket::ARRAY_STATUS)
                                ->where('deadline', '<', Carbon::now());
        } else {
            $countTickets = $countTickets->where('status', $status);
        } 

        return $countTickets->count();
    }

    /**
     * Purpose : count tickets of assigned to
     *
     * @param $employeeId, $status
     *
     * @return int
     */
    public static function countTicketsAssignedTo($employeeId, $status = null)
    {
        $countTickets = self::where('assigned_to', $employeeId);
        if(is_null($status))
        {
            return $countTickets->count();
        } elseif ($status == self::STATUS_OVERDUE) {
            $countTickets = $countTickets->whereIn('tickets.status', self::ARRAY_STATUS)
                                ->where('deadline', '<', Carbon::now());
        } else {
            $countTickets = $countTickets->where('status', $status);
        } 

        return $countTickets->count();
    }

    /**
     * Purpose : count tickets of team
     *
     * @param $status
     *
     * @return int
     */
    public static function countTicketsOfTeam($teamId, $status = null)
    {
        $countTickets = self::where('team_id', $teamId);
        if(is_null($status))
        {
            return $countTickets = $countTickets->count();
        } elseif ($status == self::STATUS_OVERDUE) {
            $countTickets = $countTickets->whereIn('status', Ticket::ARRAY_STATUS)
                                ->where('deadline', '<', Carbon::now());
        } else {
            $countTickets = $countTickets->where('status', $status);
        } 

        return $countTickets->count();
    }

    /**
     * Purpose : count tickets of department IT
     *
     * @param $status
     *
     * @return int
     */
    public static function countTicketsOfDepartmentIT($status = null)
    {
        $countTickets = self::select('id');
        if(is_null($status))
        {
            return $countTickets = $countTickets->count();
        } elseif ($status == self::STATUS_OVERDUE) {
            $countTickets = $countTickets->whereIn('status', Ticket::ARRAY_STATUS)
                                ->where('deadline', '<', Carbon::now());
        } else {
            $countTickets = $countTickets->where('status', $status);
        } 

        return $countTickets->count();
    }

    /**
    * Purpose : Lấy ticket hết hạn để gửi đi
    *
    * @param 
    *
    * @return array
    */
    public static function getTicketOver() {
        //1 , 2, 4
        $return =  DB::select( DB::raw (
        "SELECT
        `tickets`.`id`, 
        `tickets`.`subject`, 
        `tickets`.`assigned_to`, 
        `tickets`.`deadline` AS deadline,
        `cb`.`email` AS `cb`,
        `cb`.`id` AS `cb_id`,
        `cb`.`name` AS `name_cb`,
        `as`.`email` AS `as`,
        `as`.`id` AS `as_id`,
        `as`.`name` AS `name_as`,
        `tickets`.`created_at` AS `timecreate` 
        FROM `tickets`
        LEFT JOIN `employees` AS `cb` ON `cb`.`id` = `tickets`.`created_by`
        LEFT JOIN `employees` AS `as` ON `as`.`id` = `tickets`.`assigned_to`
        WHERE`tickets`.`status` IN (:a, :b, :c) AND `tickets`.`deadline` < :now"), 
        array( 'now' => Carbon::now() ,'a' => self::STATUS_OPENED,'b' =>  self::STATUS_INPROGRESS, 'c' => self::STATUS_FEEDBACK ) );

        return $return;
    }

    /**
    * Purpose : Lấy ticket sắp hết hạn để gửi đi
    *
    * @param 
    *
    * @return array
    */
    public static function getTicketExpire() {
        //1 , 2, 4
        $return =  DB::select( DB::raw (
        " SELECT
              `tickets`.`id`, 
              `tickets`.`subject`, 
              `tickets`.`assigned_to`,
              `tickets`.`deadline` AS deadline,
              `cb`.`email` AS `cb`,
              `cb`.`name`  AS `name_cb`,
              `cb`.`id` AS `cb_id`,
              `as`.`email` AS `as` ,
              `as`.`id` AS `as_id`,
              `as`.`name` AS `name_as`,
              `tickets`.`created_at` AS `timecreate` 
              FROM `tickets`
              LEFT JOIN `employees` AS `as` ON `as`.`id` = `tickets`.`assigned_to`
              LEFT JOIN `employees` AS `cb` ON `cb`.`id` = `tickets`.`created_by`
              WHERE`tickets`.`status` IN (:a, :b, :c) AND `tickets`.`deadline` >= :now "), 
        array( 'now' => Carbon::now() ,'a' => self::STATUS_OPENED,'b' =>  self::STATUS_INPROGRESS, 'c' => self::STATUS_FEEDBACK ) );
        return $return;
    }

    /**
    * Purpose : Lấy ticket đã xong nhưng chưa được close
    *
    * @param 
    *
    * @return array
    */
    public static function getTicketCloseResoled() {
        $arrayStatus = array( self::STATUS_RESOLVED );

        return self::select(
            'tickets.id',
            'tickets.subject',
            'tickets.assigned_to',
            'tickets.deadline',
            'cb.email as cb',
            'cb.id as cb_id',
            'cb.name as name_cb',
            'ai.email as ai',
            'ai.name as name_as',
            'tickets.created_at'
        )
        ->join( 'employees as cb', 'cb.id', '=', 'tickets.created_by' )
        ->join( 'employees as ai', 'ai.id', '=', 'tickets.assigned_to' )
        ->whereIn( 'tickets.status' , $arrayStatus )
        ->get();
    }

    /**
     * [getTeamsOfDeparmentIT: get teams of department IT]
     * 
     * @return [collection]            
     */
    public static function getTeamsOfDeparmentIT()
    {
        $arrayTeamCode = array(TeamConst::CODE_HN_IT, TeamConst::CODE_DN_IT, TeamConst::CODE_HCM_IT);
        if(!$arrayTeamCode)
        {
            return;
        }

        $teams = Team::whereIn('code', $arrayTeamCode)->select('id', 'name', 'leader_id', 'code')
                ->orderBy('id', 'ASC')
                ->get();
        
        return $teams;
    }

    /**
     * check team has member
     * @param int
     * @param int 
     * @return boolean
     */
    public static function isMemberOfTeam($teamId, $employeeId)
    {
        return TeamMember::where('team_id', $teamId)
                    ->where('employee_id', $employeeId)
                    ->count() ? true : false;
    }

    /**
     * [getTeamIdOfDepartmentIT: get team id of a department IT by employee]
     * @param  [int] $employeeId 
     * @return [int]             
     */
    public static function getTeamIdOfDepartmentIT($employeeId)
    {
        $teamsIT = self::getTeamsOfDeparmentIT();

        foreach ($teamsIT as $item) 
        {
            $isMemberOfTeam = self::isMemberOfTeam($item->id, $employeeId);
            if($isMemberOfTeam)
            {
                return $item->id;
            }
        }

        return null;
    }

    /**
     * [isLeaderOfTeamIT: check leader of team IT by employee]
     * @param  [int]  $employee 
     * @return boolean           
     */
    public static function isLeaderOfTeamIT($employee)
    {
        $teamsIT = self::getTeamsOfDeparmentIT();

        if($employee->isLeader())
        {
            foreach ($teamsIT as $item) 
            {
                if($employee->id == $item->leader_id)
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * get all memeber of team by team id
     * @param string
     * @return array
     */
    public static function getMemberOfTeamById($teamId)
    {
        return TeamMember::where('teams.id', $teamId)
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->join('employees', 'employees.id', '=', 'team_members.employee_id')
            ->where(function ($query) {
                $query->whereDate("employees.leave_date", ">=", date('Y-m-d'))
                    ->orWhereNull("employees.leave_date");
            })
            ->whereNull("employees.deleted_at")
            ->select('employee_id', 'employees.name', 'employees.email')
            ->get();
    }
}
