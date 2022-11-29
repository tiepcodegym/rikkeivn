<?php

namespace Rikkei\Ticket\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Ticket\Model\Ticket;

class TicketRead extends CoreModel
{
    const IS_READ = 1;
    const IS_NOT_READ = 0;
    
    protected $table = 'ticket_reads';
    public $timestamps = false;
    protected $primaryKey = ['ticket_id', 'reader_id'];
    public $incrementing = false;

    /**
     * [findTicketRead: find ticket has read by employee]
     * @param  [int] $ticketId   
     * @param  [int] $employeeId 
     * @return              
     */
    public static function findTicketRead($ticketId, $employeeId)
    {
        $ticketRead = self::where('ticket_id', $ticketId)
                    ->where('reader_id', $employeeId)
                    ->first();

        return $ticketRead;
    }

    /**
     * [updateTicketRead: update status of ticket_reads table]
     * @param  [int] $ticketId   
     * @param  [int] $status     
     * @param  [int] $employeeId 
     * @return 
     */
    public static function updateTicketRead($ticketId, $status, $employeeId)
    {
        $ticketRead = self::where('ticket_id', $ticketId);

        if(!empty($employeeId))
        {
            $ticketRead = $ticketRead->where('reader_id', $employeeId);
        } 

        $ticketRead->update(['status' => $status]);
    }

    /**
     * [isNotRead: check employee has read or not read ticket]
     * @param  [int]  $ticketId   [id of ticket]
     * @param  [int]  $employeeId [id of employee]
     * @return boolean            [true is read else not read]
     */
    public static function isRead($ticketId, $employeeId)
    {
        $isRead = false;
        $hasEmployeeInTicketRead = self::hasEmployeeInTicketRead($ticketId, $employeeId);
        if(! $hasEmployeeInTicketRead)
        {
            return false;
        } else {
            $isRead = self::where('ticket_id', $ticketId)
                    ->where('reader_id', $employeeId)
                    ->where('status', self::IS_READ)
                    ->count() ? true : false;
        }

    	return $isRead;
    }

    /**
     * [hasEmployeeInTicketRead: check employee and ticket has in ticket_reads table]
     * @param  [int]  $ticketId   [id of ticket]
     * @param  [int]  $employeeId [id of employee]
     * @return boolean            [true has in ticket_reads table else not in]
     */
    public static function hasEmployeeInTicketRead($ticketId, $employeeId)
    {
        return self::where('ticket_id', $ticketId)
                    ->where('reader_id', $employeeId)
                    ->count() ? true : false;
    }

    /**
     * [countTicketsCreatedByIsRead: count tickets of creator has read or not read]
     * @param  [int] $employeeId 
     * @param  [int] $status     
     * @return [int]             
     */
    public static function countTicketsCreatedByIsRead($employeeId, $status = null)
    {
        $countTicketIsRead = self::join('tickets as ticket_table', 'ticket_table.id', '=', 'ticket_reads.ticket_id')
                                ->where('ticket_reads.reader_id', $employeeId)
                                ->where('ticket_reads.status', self::IS_READ)
                                ->where('ticket_table.created_by', $employeeId);
        if(is_null($status))
        {
            return $countTicketIsRead->count();
        } elseif ($status == Ticket::STATUS_OVERDUE) {
            $countTicketIsRead = $countTicketIsRead->whereIn('ticket_table.status', Ticket::ARRAY_STATUS)
                                ->where('ticket_table.deadline', '<', Carbon::now());
        } else {
            $countTicketIsRead = $countTicketIsRead->where('ticket_table.status', $status);
        } 

        return $countTicketIsRead->count();
    }

    /**
     * [countTicketsRelatedPersonIsRead: count tickets of related person has read or not read]
     * @param  [int] $employeeId 
     * @param  [int] $status     
     * @return [int]             
     */
    public static function countTicketsRelatedPersonIsRead($employeeId, $status = null)
    {
        $countTicketIsRead = self::join('tickets as ticket_table', 'ticket_table.id', '=', 'ticket_reads.ticket_id')
                                ->join('ticket_relaters as ticket_relater_table', 'ticket_relater_table.ticket_id', '=', 'ticket_table.id')
                                ->where('ticket_reads.reader_id', $employeeId)
                                ->where('ticket_reads.status', self::IS_READ)
                                ->where('ticket_relater_table.employee_id', $employeeId);
        if(is_null($status))
        {
            return $countTicketIsRead->count();
        } elseif ($status == Ticket::STATUS_OVERDUE) {
            $countTicketIsRead = $countTicketIsRead->whereIn('ticket_table.status', Ticket::ARRAY_STATUS)
                                ->where('ticket_table.deadline', '<', Carbon::now());
        } else {
            $countTicketIsRead = $countTicketIsRead->where('ticket_table.status', $status);
        } 

        return $countTicketIsRead->count();
    }

    /**
     * [countTicketsAssignedToIsRead: count tickets of assigned person has read or not read]
     * @param  [int] $employeeId 
     * @param  [int] $status     
     * @return [int]             
     */
    public static function countTicketsAssignedToIsRead($employeeId, $status = null)
    {
        $countTicketIsRead = self::join('tickets as ticket_table', 'ticket_table.id', '=', 'ticket_reads.ticket_id')
                                ->where('ticket_reads.reader_id', $employeeId)
                                ->where('ticket_reads.status', self::IS_READ)
                                ->where('ticket_table.assigned_to', $employeeId);
        if(is_null($status))
        {
            return $countTicketIsRead->count();
        } elseif ($status == Ticket::STATUS_OVERDUE) {
            $countTicketIsRead = $countTicketIsRead->whereIn('ticket_table.status', Ticket::ARRAY_STATUS)
                                ->where('ticket_table.deadline', '<', Carbon::now());
        } else {
            $countTicketIsRead = $countTicketIsRead->where('ticket_table.status', $status);
        } 

        return $countTicketIsRead->count();
    }

    /**
     * [countTicketsOfTeamIsRead: count tickets of team has read or not read by employee]
     * @param  [int] $teamId     
     * @param  [int] $employeeId 
     * @param  [int] $status     
     * @return [int]             
     */
    public static function countTicketsOfTeamIsRead($teamId, $employeeId, $status = null)
    {
        $countTicketIsRead = self::join('tickets as ticket_table', 'ticket_table.id', '=', 'ticket_reads.ticket_id')
                                ->where('ticket_reads.reader_id', $employeeId)
                                ->where('ticket_reads.status', self::IS_READ)
                                ->where('ticket_table.team_id', $teamId);
        if(is_null($status))
        {
            return $countTicketIsRead->count();
        } elseif ($status == Ticket::STATUS_OVERDUE) {
            $countTicketIsRead = $countTicketIsRead->whereIn('ticket_table.status', Ticket::ARRAY_STATUS)
                                ->where('ticket_table.deadline', '<', Carbon::now());
        } else {
            $countTicketIsRead = $countTicketIsRead->where('ticket_table.status', $status);
        } 

        return $countTicketIsRead->count();
    }

    /**
     * [countTicketsOfDepartmentITIsRead: count tickets of department IT has read or not read by employee]
     * @param  [int] $employeeId 
     * @param  [int] $status     
     * @return [int]             
     */
    public static function countTicketsOfDepartmentITIsRead($employeeId, $status = null)
    {
        $countTicketIsRead = self::join('tickets as ticket_table', 'ticket_table.id', '=', 'ticket_reads.ticket_id')
                                ->where('ticket_reads.reader_id', $employeeId)
                                ->where('ticket_reads.status', self::IS_READ);
        if(is_null($status))
        {
            return $countTicketIsRead->count();
        } elseif ($status == Ticket::STATUS_OVERDUE) {
            $countTicketIsRead = $countTicketIsRead->whereIn('ticket_table.status', Ticket::ARRAY_STATUS)
                                ->where('ticket_table.deadline', '<', Carbon::now());
        } else {
            $countTicketIsRead = $countTicketIsRead->where('ticket_table.status', $status);
        } 

        return $countTicketIsRead->count();
    }
}