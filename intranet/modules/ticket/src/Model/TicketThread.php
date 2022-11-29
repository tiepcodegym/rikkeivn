<?php

namespace Rikkei\Ticket\Model;

use Rikkei\Core\Model\CoreModel;

class TicketThread extends CoreModel
{
    const COMMENT_NORMAl = 0;
    const COMMENT_RATING = 1;
    const COMMENT_PRIORITY = 2;
    const COMMENT_DEADLINE = 3;

    protected $table = 'ticket_thread';

    protected $fillable = [
        'ticket_id',
        'employee_id',
        'content',
    ];

    /**
     * get ticket comment by ticket_id
     * 
     * @param array $ticket_id
     */
    public static function getCommentByTicketId($ticket_id)
    {
    	return self::where('ticket_thread.ticket_id', $ticket_id)
    		->join('tickets', 'tickets.id', '=', 'ticket_thread.ticket_id')
    		->join('employees', 'employees.id', '=', 'ticket_thread.employee_id')
            ->leftJoin('users', 'users.employee_id', '=', 'ticket_thread.employee_id')
            ->select('ticket_thread.content', 'ticket_thread.type', 'ticket_thread.note', 'ticket_thread.created_at', 'employees.name as created_by', 'users.avatar_url')
            ->orderBy('ticket_thread.created_at', 'ASC')
            ->get();
    }
}