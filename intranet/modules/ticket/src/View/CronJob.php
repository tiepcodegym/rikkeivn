<?php

namespace Rikkei\Ticket\View;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Ticket\Model\Ticket;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\View;
use Lang;


class CronJob extends Controller
{
    /*    
     * Lấy ticket hết hạn và sắp hết hạn để gửi mail đi và những mail đã resoled nhưng người tạo chưa close
     * @param  [type]
     * @return [type]
     */
    public static function getTicketCronjob() {

        $ticketOver         = Ticket::getTicketOver();  
        
        $ticketExpire       = Ticket::getTicketExpire();

        $ticketCloseResoled = Ticket::getTicketCloseResoled();

        $data = array();
        $dataEmail = array();

        if( count( $ticketOver ) > 0 ) {
            foreach ( $ticketOver as $key => $value ) {
                $data['id']           = $value->id;
                $data['deadline']     = $value->deadline ;
                $data['subject']      = "[Request-IT][".$value->id."]".ucfirst($value->subject); 
                $data['content']      = $value->subject;
                $data['sent_to']      = $value->as;
                $data['name_sent_to'] = $value->name_as;
                $data['created_by']   = $value->cb;
                $data['name_created_by'] = $value->name_cb;
                $data['timecreate'] = $value->timecreate;
                $data['link']       = route( 'ticket::it.request.check', [ 'id' => $value->id ] );
                $data['template']   = 'ticket::template.mailoverdue';
                $dataEmail[] = self::pushEmailToArray($data);
                //set notify
                \RkNotify::put(
                    [$value->as_id, $value->cb_id],
                    trans('ticket::view.Currently the request below is about to overdue but deadline has not been finalized.'),
                    $data['link'],
                    ['actor_id' => null, 'icon' => 'fill.png', 'category_id' => RkNotify::CATEGORY_PROJECT]
                );
            }
        }
       
        if( count( $ticketExpire ) > 0 ){
           unset($data);
           foreach ( $ticketExpire as $key => $value ) {
                $data['id']       = $value->id;
                $data['deadline'] = $value->deadline ;
                $data['subject']  = "[Request-IT][".$value->id."]".ucfirst($value->subject);
                $data['content']  = $value->subject;
                $data['sent_to']  = $value->as;
                $data['name_sent_to'] = $value->name_as;
                $data['timecreate'] = $value->timecreate;
                $data['name_created_by'] = $value->name_cb;
                $data['link']     = route( 'ticket::it.request.check', [ 'id' => $value->id ] );
                $data['template']   = 'ticket::template.mailexpire';
                $tmp = self::pushEmailToArray($data);
                if(!isset($tmp['option'])){
                    $tmp['option'] = null ;
                }
                $dataEmail[] = $tmp;
                //set notify
                \RkNotify::put(
                    $value->as_id,
                    trans('ticket::view.Currently the request below is about to expire but deadline has not been finalized.'),
                    $data['link'],
                    ['actor_id' => null, 'icon' => 'fill.png', 'category_id' => RkNotify::CATEGORY_ADMIN]
                );
            }
        }

        if( count( $ticketCloseResoled ) > 0 ){
           unset($data);
           foreach ( $ticketCloseResoled as $key => $value ) {
                $data['id']       = $value->id;
                $data['deadline'] = $value->deadline ;
                $data['subject']  = "[Request-IT][".$value->id."]".' '.ucfirst($value->subject);
                $data['content']  = $value->subject;
                $data['sent_to']  = $value->cb;
                $data['link']     = route( 'ticket::it.request.check', [ 'id' => $value->id ] );
                $data['name_created_by'] = $value->name_cb;
                $data['created_at'] = $value->created_at;
                $data['name_as'] = $value->name_as;
                $data['template']   = 'ticket::template.mailclose';
                $tmp = self::pushEmailToArray($data);
                if(!isset($tmp['option'])){
                    $tmp['option'] = null ;
                }
                $dataEmail[] = $tmp;
                //set notify
                \RkNotify::put(
                    $value->cb_id,
                    trans('ticket::view.Currently there is a request below that you created '
                            . 'IT has completed but not switch status to Closed.'),
                    $data['link'],
                    ['actor_id' => null, 'icon' => 'fill.png', 'category_id' => RkNotify::CATEGORY_PROJECT]
                );
            }
        }
      
        EmailQueue::insert($dataEmail);
    }

    /**
     * [pushEmailToArray lấy giá trị của các trường để insert vào bảng email_queues
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function pushEmailToArray( $data ){
        $template   = $data['template'];
        $subject    = $data['subject'];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo( $data['sent_to'] )
                   ->setSubject( $subject )
                   ->setTemplate( $template, $data );
        
        if ( isset( $data['created_by'] ) ) {
            $emailQueue->addCc($data['created_by']);
        }
        
        return $emailQueue->getValue();
    }
}
