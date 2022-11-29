<?php

namespace Rikkei\Ticket\View;

use Rikkei\Ticket\Model\Ticket;
use Rikkei\Ticket\Model\TicketRelater;
use Rikkei\Team\View\Permission;

class TicketPermission
{
    /**
     * [isAllowManageMyRequest check permission of assigned person]
     * @return boolean [true is allow]
     */
    public static function isAllowManageMyRequest()
    {
        $isAllowManageMyRequest = false;

        if(Permission::getInstance()->isAllow('ticket::it.manage.request.team'))
        {
            $isAllowManageMyRequest = true;
        }

        return $isAllowManageMyRequest;
    }

    /**
     * [isAllowManageRequestOfTeam check permission of team leader]
     * @return boolean [true is allow]
     */
    public static function isAllowManageRequestOfTeam()
    {
        $isAllowManageRequestOfTeam = false;

        if(Permission::getInstance()->isScopeTeam(null, 'ticket::it.manage.request.team'))
        {
            $isAllowManageRequestOfTeam = true;
        }
        
        return $isAllowManageRequestOfTeam;
    }

    /**
     * [isAllowManageRequestOfDepartmentIT check permission of manager department IT]
     * @return boolean [true is allow]
     */
    public static function isAllowManageRequestOfDepartmentIT()
    {
        $isAllowManageRequestOfDepartmentIT = false;

        if(Permission::getInstance()->isScopeCompany(null, 'ticket::it.manage.request.team'))
        {
            $isAllowManageRequestOfDepartmentIT = true;
        }

        return $isAllowManageRequestOfDepartmentIT;
    }

    /**
     * [isAllowViewRequest check who can view request of team]
     * @return boolean [true is allow]
     */
    public static function isAllowViewRequestOfTeam()
    {
        $isAllowViewRequest = false;

        if(Permission::getInstance()->isScopeTeam(null, 'ticket::it.view.request.team'))
        {
            $isAllowViewRequest = true;
        }

        return $isAllowViewRequest;
    }

    /**
     * [isAllowViewRequestOfDepartmentIT check who can view request of department IT]
     * @return boolean [true is allow]
     */
    public static function isAllowViewRequestOfDepartmentIT()
    {
        $isAllowViewRequestOfDepartmentIT = false;

        if(Permission::getInstance()->isScopeCompany(null, 'ticket::it.view.request.team'))
        {
            $isAllowViewRequestOfDepartmentIT = true;
        }

        return $isAllowViewRequestOfDepartmentIT;
    }

    /**
     * [isAllowEditRequest check who can edit request]
     * @return boolean [true is allow]
     */
    public static function isAllowEditRequest()
    {
        $isAllowEditRequest = false;

        if(self::isAllowManageRequestOfTeam() || self::isAllowManageRequestOfDepartmentIT())
        {
            $isAllowEditRequest = true;
        }

        return $isAllowEditRequest;
    }

    /**
     * [checkShowAssignAndChangeStatus check when can assinge or change status]
     * @param  [object] $ticket     
     * @param  [int] $employeeId 
     * @return boolean [true is allow]             
     */
    public static function checkShowAssignAndChangeStatus($ticket, $employeeId)
    {
        $checkShowAssignAndChangeStatus = false;
        $isAllowAssignAndChangeStatus = false;

        $isMemberOfTeam = Ticket::isMemberOfTeam($ticket->team_id, $employeeId);

        if((self::isAllowManageRequestOfTeam() && $isMemberOfTeam) || self::isAllowManageRequestOfDepartmentIT() || $employeeId == $ticket->created_by || $employeeId == $ticket->assigned_to)
        {
            $isAllowAssignAndChangeStatus = true;
        }

        if(($ticket->ticket_status != Ticket::STATUS_CLOSED) && ($ticket->ticket_status != Ticket::STATUS_CANCELLED) && $isAllowAssignAndChangeStatus)
        {
            $checkShowAssignAndChangeStatus = true;
        }

        return $checkShowAssignAndChangeStatus;
    }

    /**
     * [isAllowComment check who can comment]
     * @param  [object]  $ticket     
     * @param  [int]  $employeeId 
     * @return boolean [true is allow]       
     */
    public static function isAllowComment($ticket, $employeeId)
    {
        $isAllowComment = false;

        $isMemberOfTeam = Ticket::isMemberOfTeam($ticket->team_id, $employeeId);

        if((self::isAllowManageRequestOfTeam() && $isMemberOfTeam) || self::isAllowManageRequestOfDepartmentIT())
        {
            $isAllowComment = true;
        } elseif ($employeeId == $ticket->created_by || $employeeId == $ticket->assigned_to) {
            $isAllowComment = true;
        } elseif (TicketRelater::isRelatedPerson($ticket->id, $employeeId)) {
            $isAllowComment = true;
        }

        return $isAllowComment;
    }
}
