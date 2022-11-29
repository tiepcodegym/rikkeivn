<?php

namespace Rikkei\Ticket\Model;

use Rikkei\Core\Model\CoreModel;

class TicketImage extends CoreModel
{
    protected $table = 'ticket_images';

    /**
     * Purpose : get ticket detail
     *
     * @param $ticketId
     */
    public static function getTicketImagesByTicketId($ticketId)
    {   
        $ticketImages = self::select('ticket_images.url_image', 'tickets.subject')
                    ->join('tickets', 'tickets.id', '=', 'ticket_images.id_ticket')                  
                    ->where('tickets.id', $ticketId)            
                    ->get();

        return $ticketImages;
    }
}