<?php

namespace Rikkei\Ticket\Model;

use DB;
use Rikkei\Core\Model\CoreModel;

class TicketRelater extends CoreModel
{
    protected $table = 'ticket_relaters';

    /**
     * [isRelatedPerson: check employee has related person]
     * @param  [int]  $ticketId   
     * @param  [int]  $employeeId 
     * @return boolean             
     */
    public static function isRelatedPerson($ticketId, $employeeId)
    {
    	return self::where('ticket_id', $ticketId)
                    ->where('employee_id', $employeeId)
                    ->count() ? true : false;
    }

    /**
     * [getRelatedPersons: get related persons]
     * @param  [int] $ticketId
     * @return [string]           
     */
    public static function getRelatedPersons($ticketId)
    {
        $relatedPersons = self::select('employee_table.id as id', 'employee_table.name as name', 'employee_table.email as email')
                            ->join('tickets as ticket_table', 'ticket_table.id', '=', 'ticket_relaters.ticket_id')
                            ->join('employees as employee_table', 'employee_table.id', '=', 'ticket_relaters.employee_id')
                            ->where('ticket_relaters.ticket_id', $ticketId)
                            ->get();

        return $relatedPersons;
    }

    /**
     * [getRelatedPersons: get related persons name]
     * @param  [int] $ticketId
     * @return [string]           
     */
    public static function getRelatedPersonsName($ticketId)
    {
        $relatedPersons = self::select(DB::raw("GROUP_CONCAT(employee_table.name SEPARATOR ' - ') as related_persons_name"))
                            ->join('tickets as ticket_table', 'ticket_table.id', '=', 'ticket_relaters.ticket_id')
                            ->join('employees as employee_table', 'employee_table.id', '=', 'ticket_relaters.employee_id')
                            ->where('ticket_relaters.ticket_id', $ticketId)
                            ->first();

        $relatedPersonsName = null;
        if(!is_null($relatedPersons->related_persons_name))
        {
            $relatedPersonsName = $relatedPersons->related_persons_name;
        }

        return $relatedPersonsName;
    }

    /**
     * [deleteTicketRelatedPerson: delete ticket related person by ticket id of ticket_reads table]
     * @param  [int] $ticketId 
     * @return 
     */
    public static function deleteTicketRelatedPerson($ticketId)
    {
        self::where('ticket_id', $ticketId)->delete();
    }
}