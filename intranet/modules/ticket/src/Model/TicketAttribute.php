<?php

namespace Rikkei\Ticket\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Ticket\Model\Ticket;

class TicketAttribute extends CoreModel
{
    protected $table = 'ticket_attributes';

    /**
     * @return attributes list
     */
    public static function getListAttributes()
    {
    	$attributes = self::select('ticket_attributes.*')->get();
    	return $attributes;
    }
}