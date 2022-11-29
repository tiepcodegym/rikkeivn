<?php

namespace Rikkei\Ticket\Http\Controllers;

use Auth;
use DB;
use Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Ticket\Model\Ticket;
use Rikkei\Ticket\Model\TicketAttribute;
use Rikkei\Ticket\Model\TicketThread;
use Rikkei\Ticket\Model\TicketImage;
use Rikkei\Ticket\Model\TicketRelater;
use Rikkei\Ticket\Model\TicketRead;
use Rikkei\Ticket\View\FileUploader;
use Rikkei\Ticket\View\TicketPermission;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\View;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Http\Controllers\AuthController;
use Rikkei\Team\Model\Team; 
use Rikkei\Team\View\Permission;
use Storage;
use App ;


class TicketClientController extends Controller
{
    const FOLDER_TICKET = 'ticket';
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('IT');
        Breadcrumb::add('Request IT');
        Menu::setActive('it');
    }

    /**
     * view requests list
     * 
     * @return view
     */
    public function listTicketsCreated($status = null)
    {      
        // $input = self::upload();

        $checkLeader = self::checkLeaderIt();
        
        $auth = Permission::getInstance()->getEmployee();
        if ($status >= Ticket::STATUS_OVERDUE && $status <= Ticket::STATUS_CANCELLED) {
            $tickets = Ticket::getTicketsCreatedBy($auth->id, $status);
        } else {
            return redirect()->route('ticket::it.request.status')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $boxTitle = Lang::get('ticket::view.List tickets created');
    
        return view('ticket::ticket.index', [
            'collectionModel' => $tickets, 
            'boxTitle' => $boxTitle,
            'status' => $status,
            'checkLeader' => $checkLeader,
            // 'input' => $input
        ]);
    }

    /**
     * view requests list of related person
     * 
     * @return view
     */
    public function listTicketsOfRelatedPerson($status = null)
    {
        Breadcrumb::add('Related');
        $checkLeader = self::checkLeaderIt();
        
        $auth = Permission::getInstance()->getEmployee();
        if ($status >= Ticket::STATUS_OVERDUE && $status <= Ticket::STATUS_CANCELLED) {
            $tickets = Ticket::getTicketsOfRelatedPerson($auth->id, $status);
        } else {
            return redirect()->route('ticket::it.request.related.status')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $boxTitle = Lang::get('ticket::view.List tickets related');
    
        return view('ticket::ticket.index', [
            'collectionModel' => $tickets, 
            'boxTitle' => $boxTitle,
            'status' => $status,
            'checkLeader' => $checkLeader
        ]);
    }

    /**
     * view requests list of assigned to
     * 
     * @return view
     */
    public function listTicketsAssigned($status = null)
    {
        Breadcrumb::add('Assigned');
        $checkLeader = self::checkLeaderIt();
        $auth = Permission::getInstance()->getEmployee();
        $isAllowManageMyRequest = TicketPermission::isAllowManageMyRequest();

        if($isAllowManageMyRequest)
        {
            if ($status >= Ticket::STATUS_OVERDUE && $status <= Ticket::STATUS_CANCELLED) {
                $tickets = Ticket::getTicketsAssignedTo($auth->id, $status);
            } else {
                return redirect()->route('ticket::it.request.assigned.status')->withErrors(Lang::get('team::messages.Not found item.'));
            }
        } else {
            View::viewErrorPermission();
        }

        $boxTitle = Lang::get('ticket::view.List tickets of me');

        return view('ticket::ticket.index', [
            'collectionModel' => $tickets, 
            'boxTitle' => $boxTitle,
            'status' => $status,
            'checkLeader' => $checkLeader
        ]);
    }

    /**
     * view requests list of team
     * 
     * @return view
     */
    public function listTicketsOfTeam($status = null)
    {   
        Breadcrumb::add('Team IT');

        $auth = Permission::getInstance()->getEmployee();

        $checkLeader = self::checkLeaderIt();

        $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
        $isAllowViewRequestOfTeam = TicketPermission::isAllowViewRequestOfTeam();

        $tickets = null;
        if($isAllowManageRequestOfTeam || $isAllowViewRequestOfTeam)
        {
            $idTeamIT = Ticket::getTeamIdOfDepartmentIT($auth->id);

            if ($status >= Ticket::STATUS_OVERDUE && $status <= Ticket::STATUS_CANCELLED) 
            {
                $tickets = Ticket::getTicketsOfTeam($idTeamIT, $status);
            } else {
                return redirect()->route('ticket::it.request.team.status')->withErrors(Lang::get('team::messages.Not found item.'));
            }
        } else {
            View::viewErrorPermission();
        }

        $boxTitle = Lang::get('ticket::view.List tickets of team');

        return view('ticket::ticket.index', [
            'collectionModel' => $tickets,
            'boxTitle' => $boxTitle,
            'status' => $status,
            'checkLeader' => $checkLeader
        ]);
    }

    /**
     * view requests list of department IT
     * 
     * @return view
     */
    public function listTicketsOfDepartmentIT($status = null)
    {   
        Breadcrumb::add('Department IT');

        $auth = Permission::getInstance()->getEmployee();

        $checkLeader = self::checkLeaderIt();

        $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();
        $isAllowViewRequestOfDepartmentIT = TicketPermission::isAllowViewRequestOfDepartmentIT();

        $tickets = null;
        if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
        {
            if ($status >= Ticket::STATUS_OVERDUE && $status <= Ticket::STATUS_CANCELLED) 
            {
                $tickets = Ticket::getTicketsOfDepartmentIT($status);
            } else {
                return redirect()->route('ticket::it.request.dashboard.status')->withErrors(Lang::get('team::messages.Not found item.'));
            }
        } else {
            View::viewErrorPermission();
        }

        $boxTitle = Lang::get('ticket::view.List tickets of department IT');

        return view('ticket::ticket.index', [
            'collectionModel' => $tickets,
            'boxTitle' => $boxTitle,
            'status' => $status,
            'checkLeader' => $checkLeader
        ]);
    }

    /**
     * view request detail
     * 
     * @return view
     */
    public function checkTicket($ticketId)
    {
        Breadcrumb::add('Check request');
        $checkLeader = self::checkLeaderIt();
        $ticket = Ticket::getTicketById($ticketId);
        $auth = Permission::getInstance()->getEmployee();

        if (! $ticket) {
            return redirect()->route('ticket::it.request.status')->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $isAllowViewRequestOfTeam = TicketPermission::isAllowViewRequestOfTeam();
        $isAllowViewRequestOfDepartmentIT = TicketPermission::isAllowViewRequestOfDepartmentIT();
        $isAllowManageMyRequest = TicketPermission::isAllowManageMyRequest();
        $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
        $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();

        $isRelatedPerson = TicketRelater::isRelatedPerson($ticket->id, $auth->id);

        if($auth->id == $ticket->created_by || ($auth->id == $ticket->assigned_to && $isAllowManageMyRequest) || $isAllowManageRequestOfTeam || $isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfTeam || $isRelatedPerson || $isAllowViewRequestOfDepartmentIT)
        {
            $ticketImages = TicketImage::getTicketImagesByTicketId($ticketId);

            if(TicketRead::hasEmployeeInTicketRead($ticketId, $auth->id))
            {
                TicketRead::updateTicketRead($ticketId, TicketRead::IS_READ, $auth->id);
            } else {
                $ticketRead = new TicketRead;
                $ticketRead->ticket_id = $ticketId;
                $ticketRead->reader_id = $auth->id;
                $ticketRead->status = TicketRead::IS_READ;
                $ticketRead->save();
            }

        } else {
            View::viewErrorPermission();
        }

        $ticketComment = TicketThread::getCommentByTicketId($ticketId);

        $membersOfTeamIT = Ticket::getMemberOfTeamById($ticket->team_id);

        $getTeamsOfDeparmentIT = Ticket::getTeamsOfDeparmentIT();

        $relatedPersons = TicketRelater::getRelatedPersons($ticket->id);

        $params = [
            'ticket'                => $ticket,
            'ticketComment'         => $ticketComment,
            'membersOfTeamIT'       => $membersOfTeamIT,
            'ticketImages'          => $ticketImages,
            'checkLeader'           => $checkLeader,
            'getTeamsOfDeparmentIT' => $getTeamsOfDeparmentIT,
            'relatedPersons'        => $relatedPersons
        ];

        return view('ticket::ticket.check_ticket', $params);
    }

    /**
     * assign ticket
     * 
     */
    public function assignedTo(Request $request)
    {
        $isAllowEditRequest = TicketPermission::isAllowEditRequest();
        if($isAllowEditRequest)
        {
            $auth = Permission::getInstance()->getEmployee(); 
        } else {
            View::viewErrorPermission();
        }

        $assignedToId = $request->assign_to;
        $ticketId = $request->ticket_id;

        $ticket = Ticket::getTicketById($ticketId);

        $ticket->assigned_to = $assignedToId;
        $ticket->save();           
        $assignedTo = Employee::find($ticket->assigned_to);

        $ticketNew = Ticket::getTicketById($ticket->id);

        $data = array();
        $data = self::setDataPushEmail($ticketNew, $auth);

        if(!is_null($ticketNew->assigned_to))
        {           
            if($ticketNew->assigned_to)
            {
                $assignedTo = Employee::find($ticketNew->assigned_to);
                if ($assignedTo) {
                    $data['mail_to'] = $assignedTo->email;
                    $template = 'ticket::template.assign.mail_assign_for_assigned';
                    //set notify data
                    $data['to_id'] = $assignedTo->id;
                    $data['noti_content'] = trans('ticket::view.You received a request from').' '.$data['ticket_created_by'];
                    self::pushEmailTicketToQueue($data, $template);
                }
            } 
            if($ticketNew->created_by)
            {
                $createdBy = Employee::find($ticketNew->created_by);
                if ($createdBy) {
                    $data['mail_to'] = $createdBy->email;
                    $template = 'ticket::template.assign.mail_assign_for_creator';
                    //set notify data
                    $data['to_id'] = $createdBy->id;
                    $data['noti_content'] = trans(
                        'ticket::view.At :ticket_time on :ticket_date, you have made a request to IT as follows:',
                        ['ticket_time' => $data['ticket_time'], 'ticket_date' => $data['ticket_date']]
                    );
                    self::pushEmailTicketToQueue($data, $template);
                }
            }

            $relatedPersons = TicketRelater::getRelatedPersons($ticketNew->id);
            if(count($relatedPersons) > 0)
            {
                foreach ($relatedPersons as $item)
                {
                    $data['mail_to'] = $item->email;
                    $data['ticket_related_person_name'] = $item->name;
                    $template = 'ticket::template.assign.mail_assign_for_related_person';
                    self::pushEmailTicketToQueue($data, $template, false);
                }
                //set notify
                \RkNotify::put(
                    $relatedPersons->lists('id')->toArray(),
                    trans('ticket::view.Work related to you has changed the person who made the request.'),
                    $data['ticket_link'], ['category_id' => RkNotify::CATEGORY_PROJECT]
                );
            }

            TicketRead::updateTicketRead($ticket->id, TicketRead::IS_NOT_READ, null);
        } else {
            return redirect()->route('ticket::it.request.check')->withErrors(Lang::get('ticket::view.Can not assign to member'));
        }

        $messages = [
            'success'=> [
                Lang::get('ticket::view.Assign success'),
            ]
        ];

        return redirect()->route('ticket::it.request.check', ['id' => $ticketId])->with('messages', $messages);
    }

    /**
     * change status of ticket
     * 
     */
    public function changeStatus(Request $request)
    {
        $auth = Permission::getInstance()->getEmployee(); 
        $ticketId = $request->ticket_id;
        $status = $request->status;

        $ticket = Ticket::getTicketById($ticketId);

        if(TicketPermission::isAllowEditRequest() || $auth->id == $ticket->created_by || $auth->id == $ticket->assigned_to)
        {
            $valueStatus = TicketAttribute::find($status);

            if($status == Ticket::STATUS_CLOSED)
            {
                $ticket->closed_at = Carbon::now();
            }
            if($status == Ticket::STATUS_CANCELLED)
            {
                $ticket->delete_at = Carbon::now();
            }
            if($status == Ticket::STATUS_RESOLVED)
            {
                $ticket->resolved_at = Carbon::now();
            }
            if($status == Ticket::STATUS_FEEDBACK)
            {
                $ticket->resolved_at = null;
            }

            $ticket->status = $status;
            $ticket->save();
        } else {
            View::viewErrorPermission();
        }

        $ticketNew = Ticket::getTicketById($ticket->id);

        $data = array();
        $data = self::setDataPushEmail($ticketNew, $auth);

        $createdBy = Employee::find($ticketNew->created_by);
        if($auth->id != $ticketNew->assigned_to && $auth->id != $ticketNew->created_by)
        {
            if(!is_null($ticketNew->assigned_to))
            {
                $assignedTo = Employee::find($ticketNew->assigned_to);
                if ($assignedTo) {
                    $data['mail_to'] = $assignedTo->email;
                    $template = 'ticket::template.change_status.mail_change_status_for_assigned';
                    //set notify data
                    $data['to_id'] = $assignedTo->id;
                    $data['noti_content'] = trans('ticket::view.The work you are doing has changed the status.');
                    self::pushEmailTicketToQueue($data, $template);
                }
            } 
            if(!is_null($ticketNew->created_by) && $createdBy) 
            {
                $data['mail_to'] = $createdBy->email;
                $template = 'ticket::template.change_status.mail_change_status_for_creator';
                //set notify data
                $data['to_id'] = $createdBy->id;
                $data['noti_content'] = trans('ticket::view.The work you requested changed the status.');
                self::pushEmailTicketToQueue($data, $template);
            }
        } elseif($auth->id == $ticketNew->assigned_to && $createdBy) {   
            $data['mail_to'] = $createdBy->email;
            $template = 'ticket::template.change_status.mail_change_status_for_creator';
            //set notify data
            $data['to_id'] = $createdBy->id;
            $data['noti_content'] = trans('ticket::view.The work you requested changed the status.');
            self::pushEmailTicketToQueue($data, $template);
        } elseif($auth->id == $ticketNew->created_by) {   
            $assignedTo = Employee::find($ticketNew->assigned_to);
            if ($assignedTo) {
                $data['mail_to'] = $assignedTo->email;
                $template = 'ticket::template.change_status.mail_change_status_for_assigned';
                //set notify data
                $data['to_id'] = $assignedTo->id;
                $data['noti_content'] = trans('ticket::view.The work you are doing has changed the status.');
                self::pushEmailTicketToQueue($data, $template);
            }
        }

        $relatedPersons = TicketRelater::getRelatedPersons($ticketNew->id);
        if(count($relatedPersons) > 0)
        {
            foreach ($relatedPersons as $item)
            {
                if($item->id != $auth->id)
                {
                    $data['mail_to'] = $item->email;
                    $data['ticket_related_person_name'] = $item->name;
                    $template = 'ticket::template.change_status.mail_change_status_for_related_person';
                    self::pushEmailTicketToQueue($data, $template, false);
                }
            }
            \RkNotify::put(
                $relatedPersons->lists('id')->toArray(),
                trans('ticket::view.The work related to you changed the status.'),
                $data['ticket_link'],
                ['excerpt_current' => true, 'category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }

        TicketRead::updateTicketRead($ticketNew->id, TicketRead::IS_NOT_READ, null);
        
        $messages = [
            'success'=> [
                Lang::get('ticket::view.Status update successful'),
            ]
        ];

        return redirect()->route('ticket::it.request.check', ['id' => $ticket->id])->with('messages', $messages);
    }

    /**
     * change status of ticket resolved
     * 
     */
    public function changeStatusResolved(Request $request)
    {
        $auth = Permission::getInstance()->getEmployee();

        if( isset( $request->ticket_id ) ) {
            $ticketId = $request->ticket_id;
            $ticket = Ticket::getTicketById($ticketId);
        }

        $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();

        if($isAllowManageRequestOfDepartmentIT || $auth->id == $ticket->created_by)
        {
            if(isset($request->rating)){
                $ticket->rating = $request->rating;
            }
       
            if( $request->status == Ticket::STATUS_CLOSED)
            {
                $ticket->closed_at = Carbon::now();
            }
            
            $ticket->status = Ticket::STATUS_CLOSED;
            $ticket->save();

            $ticket_thread = new TicketThread;
            $ticket_thread->ticket_id = $ticket->id;
            $ticket_thread->employee_id = $auth->id;
            $ticket_thread->type = TicketThread::COMMENT_RATING;

            if(isset($request->reason_unsatisfied) && $request->rating == Ticket::RATING_UNSATISFIED)
            {
                $ticket_thread->content = $request->reason_unsatisfied;
                $ticket_thread->note = Lang::get('ticket::view.Unsatisfied');
                $ticket_thread->save();
            } elseif($request->rating == Ticket::RATING_SATISFIED) {
                $ticket_thread->note = Lang::get('ticket::view.Satisfied');
                $ticket_thread->save();
            }
        } else {
            View::viewErrorPermission();
        }

        $ticketNew = Ticket::getTicketById($ticket->id);

        $data = array();
        $data = self::setDataPushEmail($ticketNew, $auth);

        $createdBy = Employee::find($ticketNew->created_by);
        if($auth->id != $ticketNew->assigned_to && $auth->id != $ticketNew->created_by)
        {
            if(!is_null($ticketNew->assigned_to))
            {
                $assignedTo = Employee::find($ticketNew->assigned_to);
                if ($assignedTo) {
                    $data['mail_to'] = $assignedTo->email;
                    $template = 'ticket::template.change_status.mail_change_status_for_assigned';
                    //set notify data
                    $data['to_id'] = $assignedTo->id;
                    $data['noti_content'] = trans('ticket::view.The work you are doing has changed the status.');
                    self::pushEmailTicketToQueue($data, $template);
                }
            } 
            if(!is_null($ticketNew->created_by) && $createdBy) 
            {
                $data['mail_to'] = $createdBy->email;
                $template = 'ticket::template.change_status.mail_change_status_for_creator';
                //set notify data
                $data['to_id'] = $createdBy->id;
                $data['noti_content'] = trans('ticket::view.The work you requested changed the status.');
                self::pushEmailTicketToQueue($data, $template);
            }
        } elseif($auth->id == $ticketNew->assigned_to && $createdBy) {   
            $data['mail_to'] = $createdBy->email;
            $template = 'ticket::template.change_status.mail_change_status_for_creator';
            //set notify data
            $data['to_id'] = $createdBy->id;
            $data['noti_content'] = trans('ticket::view.The work you requested changed the status.');
            self::pushEmailTicketToQueue($data, $template);
        } elseif($auth->id == $ticketNew->created_by) {   
            $assignedTo = Employee::find($ticketNew->assigned_to);
            if ($assignedTo) {
                $data['mail_to'] = $assignedTo->email;
                $template = 'ticket::template.change_status.mail_change_status_for_assigned';
                //set notify data
                $data['to_id'] = $assignedTo->id;
                $data['noti_content'] = trans('ticket::view.The work you are doing has changed the status.');
                self::pushEmailTicketToQueue($data, $template);
            }
        }

        $relatedPersons = TicketRelater::getRelatedPersons($ticketNew->id);
        if(count($relatedPersons) > 0)
        {
            foreach ($relatedPersons as $item)
            {
                if($item->id != $auth->id)
                {
                    $data['mail_to'] = $item->email;
                    $data['ticket_related_person_name'] = $item->name;
                    $template = 'ticket::template.change_status.mail_change_status_for_related_person';
                    self::pushEmailTicketToQueue($data, $template, false);
                }
            }
            //set notify
            \RkNotify::put(
                $relatedPersons->lists('id')->toArray(),
                trans('ticket::view.The work related to you changed the status.'),
                $data['ticket_link'],
                ['excerpt_current' => true, 'category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }

        TicketRead::updateTicketRead($ticket->id, TicketRead::IS_NOT_READ, null);

        $messages = [
                'success'=> [
                    Lang::get('ticket::view.Update status successful , thank you'),
                ]
        ];

        return redirect()->route('ticket::it.request.check', ['id' => $ticket->id])->with('messages', $messages);
    }

    /**
     * save comment
     * 
     */
    public function saveComment(Request $request)
    {
        $auth = Permission::getInstance()->getEmployee();
        $ticketId = $request->input('ticket_id');
        $ticket = Ticket::getTicketById($ticketId);

        $isAllowComment = TicketPermission::isAllowComment($ticket, $auth->id);
        if($isAllowComment)
        {
            TicketThread::create([
                'content' => $request->input('comment_content'),
                'ticket_id' => $ticketId,
                'employee_id' => $auth->id,
            ]);
        } else {
            View::viewErrorPermission();
        }

        $data = array();
        $data = self::setDataPushEmail($ticket, $auth);

        $data['comment_content']  = $request->input('comment_content');
        $data['comment_shorten_content'] = self::limit_text($data['comment_content'],100);
        $data['comment_by']  = $auth->name;

        $createdBy = Employee::find($ticket->created_by);
        if($auth->id != $ticket->assigned_to && $auth->id != $ticket->created_by)
        {
            if(!is_null($ticket->assigned_to))
            {
                $assignedTo = Employee::find($ticket->assigned_to);
                if ($assignedTo) {
                    $data['mail_to'] = $assignedTo->email;
                    $template = 'ticket::template.comment.mail_comment_for_assigned';
                    //set notify data
                    $data['to_id'] = $assignedTo->id;
                    $data['noti_content'] = trans('ticket::view.The work you are doing has a new comment.');
                    self::pushEmailTicketToQueue($data, $template);
                }
            } 
            if(!is_null($ticket->created_by) && $createdBy) 
            {
                $data['mail_to'] = $createdBy->email;
                $template = 'ticket::template.comment.mail_comment_for_creator';
                //set notify data
                $data['to_id'] = $createdBy->id;
                $data['noti_content'] = trans('ticket::view.The work you requested has a new comment.');
                self::pushEmailTicketToQueue($data, $template);
            }
        } elseif($auth->id == $ticket->assigned_to && $createdBy) {   
            $data['mail_to'] = $createdBy->email;
            $template = 'ticket::template.comment.mail_comment_for_creator';
            //set notify data
            $data['to_id'] = $createdBy->id;
            $data['noti_content'] = trans('ticket::view.The work you requested has a new comment.');
            self::pushEmailTicketToQueue($data, $template);
        } elseif($auth->id == $ticket->created_by) {  
            $assignedTo = Employee::find($ticket->assigned_to);
            if ($assignedTo) {
                $data['mail_to'] = $assignedTo->email;
                $template = 'ticket::template.comment.mail_comment_for_assigned';
                //set notify data
                $data['to_id'] = $assignedTo->id;
                $data['noti_content'] = trans('ticket::view.The work you are doing has a new comment.');
                self::pushEmailTicketToQueue($data, $template);
            }
        }

        $relatedPersons = TicketRelater::getRelatedPersons($ticket->id);
        if(count($relatedPersons) > 0)
        {
            foreach ($relatedPersons as $item)
            {
                if($item->id != $auth->id)
                {
                    $data['mail_to'] = $item->email;
                    $data['ticket_related_person_name'] = $item->name;
                    $template = 'ticket::template.comment.mail_comment_for_related_person';
                    self::pushEmailTicketToQueue($data, $template, false);
                }
            }
            //set notify
            \RkNotify::put(
                $relatedPersons->lists('id')->toArray(),
                trans('ticket::view.The work related to you has a new comment.'),
                $data['ticket_link'],
                ['excerpt_current' => true, 'category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }

        TicketRead::updateTicketRead($ticket->id, TicketRead::IS_NOT_READ, null);

        return redirect()->route('ticket::it.request.check', ['id' => $ticketId]);
    }

    /**
     * Lưu ticket và gửi email vào hàng đợi
     * @param  Request
     * @return [type]
     */
    public function saveTicket( Request $request ) 
    {
        //Creat folder ticket
        $structure = base_path('public/storage/ticket');
        @mkdir($structure, 0777, true);

        $fileUploader = new FileUploader('files', array(
            'uploadDir' => base_path('public/storage/ticket/'),
            'title' => 'name'
        ));
        
        // call to upload the files
        $data = $fileUploader->upload();
       
        // if uploaded and success
        if($data['isSuccess'] && count($data['files']) > 0) {
            $uploadedFiles = $data['files'];
        }
       
        @chmod(storage_path('app/' . EmailQueue::ACCESS_FILE . '/' . self::FOLDER_TICKET . '/'), 0777);
        // unlink the files
        // !important only for appended files
        // you will need to give the array with appendend files in 'files' option of the FileUploader
        foreach($fileUploader->getRemovedFiles('file') as $key=>$value) {
            unlink(base_path('public/storage/ticket/') . $value['name']);
        }
      
        // get the fileList
        $fileList = $fileUploader->getFileList();
       
        if( ( Carbon::parse($request->deadline)->timestamp - Carbon::now()->timestamp ) < 3540*2 ){
            $messages = [
                    'errors'=> [
                        'Thời gian ít hơn 2 tiếng !',
                    ]
                ];
            return redirect()->route( 'ticket::it.request.status' )->with('messages', $messages);
        };

        $ticket = new Ticket;
        $auth = Permission::getInstance()->getEmployee();
     
        $data = array();

        $ticket->subject          = $request->subject;
        $data['subject_mail']     = $request->subject;
        $ticket->content          = $data['content']  = $request->input('content');
        $ticket->priority         = $data['priority'] = $request->priority;
        $ticket->deadline         = $data['deadline'] = Carbon::parse($request->deadline);
        $ticket->team_id          = $request->team_id;
        $ticket->created_by       = $auth->id;

        $data['user_email'] = $auth->email;

        $idLeader = Team::getLeaderOfTeam($ticket->team_id);        
        $infoLeater = Employee::find($idLeader);

        if($infoLeater) {
            $data['sent_to'] = $infoLeater->email;
        } else {
            $messages = [
                    'errors'=> [
                        Lang::get('ticket::view.The department has not team leader'),
                    ]
                ];
            return redirect()->route( 'ticket::it.request.status' )->with('messages', $messages);
        }
    
        //upload file cu

        if ( !is_null($request->field[0]) ) {

            $files = $request->field;
             
           $remove =  $request->remove[0];
            if($remove != ""){
                $remove = explode(",",$remove);
                foreach ($remove as $key) {
                    unset($files[$key]);
                }
            }

            if(count($files)>0){
                foreach ($files as $file) {
                    $extension = $file->getClientOriginalExtension();
                    $name = str_random(5) . '_' . time() . '.' . $extension;
                    // public/ticket/name.png
                     // var_dump($file->getRealPath());exit();
                    //public/ticket/
                    Storage::put(EmailQueue::ACCESS_FILE . '/' . self::FOLDER_TICKET . '/' . $name,
                      file_get_contents($file->getRealPath()));
                    // public/storage/name.png
                    $data['url_image'][] = EmailQueue::ACCESS_FILE . '/' . 'storage' . '/' . self::FOLDER_TICKET. '/' . $name ;
                    // storage/public/ticket/$name
                    $url_image[] = '/' . 'storage' . '/' . self::FOLDER_TICKET. '/' . $name ;
                    @chmod(storage_path('app/' . EmailQueue::ACCESS_FILE . '/' . self::FOLDER_TICKET . '/' . $name), 0777);
                }
                @chmod(storage_path('app/' . EmailQueue::ACCESS_FILE . '/' . self::FOLDER_TICKET . '/'), 0777);
            }
        }
        //end upload file cu

        if ( $ticket->save() ) {
            $relatedPersons = $request->related_persons_list;
            if(!empty($relatedPersons))
            {
                $ticketRelaters = [];
                foreach ($relatedPersons as $key => $value) 
                {
                    $ticketRelaters [] = array('ticket_id' => $ticket->id, 'employee_id'=> $value);
                }
                TicketRelater::insert($ticketRelaters);
            }

            $data['link'] = route( 'ticket::it.request.check', [ 'id' => $ticket->id ] );
            $data['id']   = $ticket->id;
            $data['subject']    = "[Request-IT][".$ticket->id."]".' '.ucfirst($request->subject);
 
            if( count( $fileList ) > 0 ) {
               
                $addImage = array();
                foreach ($fileList as $key) {
                    $addImage[] = ['id_ticket'=>$ticket->id,'url_image'=>$key['file']];
                    $data['url_image'][] = 'public/'.$key['file'];
                }
                TicketImage::insert($addImage);
            }

            Ticket::where( 'id', $ticket->id )->update( [ 'status' => 1 ] );
            Ticket::where( 'id', $ticket->id )->update( [ 'assigned_to' => $idLeader ] );

            $data['EmpName'] = Employee::getNameEmpById( $idLeader );
            $data['CreateBy'] = Employee::getNameEmpById(  $ticket->created_by );
            $data['TimeCreate']  = $ticket->created_at;
            // tách timecreated
            $dt = Carbon::parse($data['TimeCreate']);
          
            $data['date'] = $dt->format('d/m/Y');
            $data['time'] = $dt->format('H:i');
            //data notify
            $data['to_id'] = $infoLeater->id;
            $data['noti_content'] = trans('ticket::view.At :ticket_time on :ticket_date, there is a request to IT as follows:', ['ticket_time' => $data['time'], 'ticket_date' => $data['date']]).' '. $data['subject_mail'];

            if( $this->pushEmailToQueue( $data ) ) {
                $ticketNew = Ticket::getTicketById($ticket->id);
                
                $dataSendRelatedPerson = array();
                $dataSendRelatedPerson = self::setDataPushEmail($ticketNew, $auth);

                $relatedPersons = TicketRelater::getRelatedPersons($ticketNew->id);
                if(count($relatedPersons) > 0)
                {
                    foreach ($relatedPersons as $item)
                    {
                        $dataSendRelatedPerson['mail_to'] = $item->email;
                        $dataSendRelatedPerson['ticket_related_person_name'] = $item->name;
                        $template = 'ticket::template.create.mail_create_for_related_person';
                        self::pushEmailTicketToQueue($dataSendRelatedPerson, $template, false);
                    }
                    //set notify
                    \RkNotify::put(
                        $relatedPersons->lists('id')->toArray(),
                        trans('ticket::view.There is a request to resolve work from :ticket_created_by regarding you.', ['ticket_created_by' => $data['CreateBy']]),
                        $data['link'], ['category_id' => RkNotify::CATEGORY_PROJECT]
                    );
                }

                $messages = [
                    'success'=> [
                        Lang::get('ticket::view.Sended request'),
                    ]
                ];
                return redirect()->route( 'ticket::it.request.check', ['id' => $ticket->id] )->with('messages', $messages);
            } else {
                $messages = [
                    'success'=> [
                        Lang::get('ticket::view.Pushed request to queue'),
                    ]
                ];
                return redirect()->route( 'ticket::it.request.check', ['id' => $ticket->id] )->with('messages', $messages);
            }
        } else {
            $messages = [
                'errors'=> [
                    Lang::get('ticket::view.Cannot create request'),
                ]
            ];
            return redirect()->route('ticket::it.request.status')->with('messages', $messages);
        } 
    }

    /**
     * Đưa Email vào hàng đợi
     * @param  [type]
     * @return [type]
     */
    public function pushEmailToQueue( $data ) {
        $template = 'ticket::template.mailnoti';
        $subject = $data['sent_to'];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo( $data['sent_to'] )
                   ->setSubject( $data['subject'] )
                   ->setTemplate( $template, $data );
        // if ( isset( $data['created_by'] ) ) {
        //     $emailQueue->addBcc( $data['created_by'] );
        // }

        if ( isset( $data['url_image'] ) ) {
            foreach ($data['url_image'] as $key) {
                $myFile = base_path($key);
                $emailQueue->addAttachment($myFile, false);     
            }   
        }
        //check send notify
        if (isset($data['to_id'])) {
            $emailQueue->setNotify($data['to_id'], $data['noti_content'], $data['link'], ['category_id' => RkNotify::CATEGORY_ADMIN]);
        }
            
        try{
            $emailQueue->save();
            return true;
        }catch( Exception $e ){
            return false;
        }
    }

    /*
    Check isset leaderTeamIT
    */
    public function checkLeaderIt() {
        if(!Employee::find( Team::getLeaderIdByCode( Team::CODE_TEAM_IT ) )) {
           $check = 1;
        }else{
           $check = 0;
        }
        return $check; 
    }

    /*shorten commennt in mail*/
    public function limit_text($text, $limit) {
      if (str_word_count($text, 0) > $limit) {
          $words = str_word_count($text, 2);
          $pos = array_keys($words);
          $text = substr($text, 0, $pos[$limit]) . '...';
      }
      return $text;
    }

    /**
     * [findEmployee search related persons]
     * @param  Request $request
     * @return [Json]
     */
    public function findEmployee(Request $request)
    {
        $keySearch = trim($request->q);

        if (empty($keySearch)) {
            return \Response::json([]);
        }

        $employees = Employee::whereNull('leave_date')
            ->where(function ($query) use ($keySearch) {
                $query->where('email','like', '%'.$keySearch.'%')->orwhere('name','like', '%'.$keySearch.'%');
            })
            ->get();

        $formatted_employees = [];

        foreach ($employees as $employee) 
        {
            $formatted_employees[] = ['id' => $employee->id, 'text' => $employee->name . ' ('. preg_replace('/@.*/', '',$employee->email) . ')'];
        }

        return \Response::json($formatted_employees);
    }

    /**
     * [changeTeamIT: change team IT]
     * @param  Request $request 
     * @return [view ticket detail]           
     */
    public function changeTeamIT(Request $request)
    {
        $auth = Permission::getInstance()->getEmployee();

        $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
        $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();

        if($isAllowManageRequestOfTeam || $isAllowManageRequestOfDepartmentIT)
        {
            $ticket = Ticket::getTicketById($request->ticket_id);
        } else {
            View::viewErrorPermission();
        }

        $idLeader = Team::getLeaderOfTeam($request->team_id);

        if(!$idLeader) 
        {
            return redirect()->route( 'ticket::it.request.check', ['id' => $ticket->id] )->withErrors('Chưa có team leader');
        }

        $ticket->team_id = $request->team_id;
        $ticket->assigned_to = $idLeader;
        $ticket->status = Ticket::STATUS_OPENED;

        $ticket->save();

        TicketRead::updateTicketRead($ticket->id, TicketRead::IS_NOT_READ, null);

        $ticketNew = Ticket::getTicketById($ticket->id);
        $data = array();
        $data = self::setDataPushEmail($ticketNew, $auth);

        if($ticketNew->assigned_to)
        {
            $assignedTo = Employee::find($ticketNew->assigned_to);
            if ($assignedTo) {
                $data['mail_to'] = $assignedTo->email;
                $template = 'ticket::template.change_team.mail_change_team';
                //set notify data
                $data['to_id'] = $assignedTo->id;
                $data['noti_content'] = trans(
                    'ticket::view.At :ticket_time on :ticket_date, there is a request to IT as follows:',
                    ['ticket_time' => $data['ticket_time'], 'ticket_date' => $data['ticket_date']]
                ).' '.$data['ticket_subject'];
                self::pushEmailTicketToQueue($data, $template);
            }
        } 
        if($ticketNew->created_by)
        {
            $createdBy = Employee::find($ticketNew->created_by);
            if ($createdBy) {
                $data['mail_to'] = $createdBy->email;
                $template = 'ticket::template.change_team.mail_change_team_for_creator';
                //set notify data
                $data['to_id'] = $createdBy->id;
                $data['noti_content'] = trans(
                    'ticket::view.At :ticket_time on :ticket_date, you have made a request to IT as follows:',
                    ['ticket_time' => $data['ticket_time'], 'ticket_date' => $data['ticket_date']]
                ).' '.$data['ticket_subject'];
                self::pushEmailTicketToQueue($data, $template);
            }
        }

        $relatedPersons = TicketRelater::getRelatedPersons($ticketNew->id);
        if(count($relatedPersons) > 0)
        {
            foreach ($relatedPersons as $item)
            {
                $data['mail_to'] = $item->email;
                $data['ticket_related_person_name'] = $item->name;
                $template = 'ticket::template.change_team.mail_change_team_for_related_person';
                self::pushEmailTicketToQueue($data, $template, false);
            }
            \RkNotify::put(
                $relatedPersons->lists('id')->toArray(),
                trans('ticket::view.The work related to you changed the IT department that made the request.'),
                $data['ticket_link'], ['category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }

        $messages = [
            'success'=> [
                Lang::get('ticket::view.Change department IT successful')
            ]
        ];

        return redirect()->route('ticket::it.request.check', ['id' => $ticketNew->id])->with('messages', $messages);
    }

    /**
     * [changePriority: change priority of ticket]
     * @param  Request $request 
     * @return [view ticket detail]           
     */
    public function changePriority(Request $request)
    {
        $auth = Permission::getInstance()->getEmployee();

        $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
        $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();

        if($isAllowManageRequestOfTeam || $isAllowManageRequestOfDepartmentIT)
        {
            $ticket = Ticket::getTicketById($request->ticket_id);
        } else {
            View::viewErrorPermission();
        }
        $priorityOld = $ticket->ticket_priority;
        $priorityNew = $request->priority;
        $ticket->priority = $priorityNew;
        $ticket->save();

        $ticket_thread = new TicketThread;
        $ticket_thread->ticket_id = $ticket->id;
        $ticket_thread->employee_id = $auth->id;
        $ticket_thread->content = $request->reason_change_priority;
        $ticket_thread->type = TicketThread::COMMENT_PRIORITY;

        $valuePriorityOld = '';
        $valuePriorityNew = '';

        if($priorityOld == Ticket::PRIORITY_LOW)
        {
            $valuePriorityOld = 'Thấp';
        } elseif ($priorityOld == Ticket::PRIORITY_NORMAl) {
            $valuePriorityOld = 'Bình thường';
        } elseif ($priorityOld == Ticket::PRIORITY_HIGH) {
            $valuePriorityOld = 'Cao';
        } elseif ($priorityOld == Ticket::PRIORITY_EMERGENCY) {
            $valuePriorityOld = 'Khẩn cấp';
        }

        if($priorityNew == Ticket::PRIORITY_LOW)
        {
            $valuePriorityNew = 'Thấp';
        } elseif ($priorityNew == Ticket::PRIORITY_NORMAl) {
            $valuePriorityNew = 'Bình thường';
        } elseif ($priorityNew == Ticket::PRIORITY_HIGH) {
            $valuePriorityNew = 'Cao';
        } elseif ($priorityNew == Ticket::PRIORITY_EMERGENCY) {
            $valuePriorityNew = 'Khẩn cấp';
        }

        $ticket_thread->note = $valuePriorityOld . ' => ' . $valuePriorityNew;
        $ticket_thread->save();

        $ticketNew = Ticket::getTicketById($ticket->id);
        $data = array();
        $data = self::setDataPushEmail($ticketNew, $auth);

        if($ticketNew->assigned_to)
        {
            $assignedTo = Employee::find($ticketNew->assigned_to);
            if ($assignedTo) {
                $data['mail_to'] = $assignedTo->email;
                $template = 'ticket::template.change_priority.mail_change_priority_for_assigned';
                //set notify
                $data['to_id'] = $assignedTo->id;
                $data['noti_content'] = trans('ticket::view.The work you are doing has changed the priority.');
                self::pushEmailTicketToQueue($data, $template);
            }
        } 
        if($ticketNew->created_by)
        {
            $createdBy = Employee::find($ticketNew->created_by);
            if ($createdBy) {
                $data['mail_to'] = $createdBy->email;
                $template = 'ticket::template.change_priority.mail_change_priority_for_creator';
                //set notify
                $data['to_id'] = $createdBy->id;
                $data['noti_content'] = trans('ticket::view.The work you requested changed the priority.');
                self::pushEmailTicketToQueue($data, $template);
            }
        }

        $relatedPersons = TicketRelater::getRelatedPersons($ticketNew->id);
        if(count($relatedPersons) > 0)
        {
            foreach ($relatedPersons as $item)
            {
                $data['mail_to'] = $item->email;
                $data['ticket_related_person_name'] = $item->name;
                $template = 'ticket::template.change_priority.mail_change_priority_for_related_person';
                self::pushEmailTicketToQueue($data, $template, false);
            }
            //set notify
            \RkNotify::put(
                $relatedPersons->lists('id')->toArray(),
                trans('ticket::view.The work related to you changed the priority.'),
                $data['ticket_link'], ['category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }

        TicketRead::updateTicketRead($ticket->id, TicketRead::IS_NOT_READ, null);

        $messages = [
            'success'=> [
                Lang::get('ticket::view.Change priority successful')
            ]
        ];

        return redirect()->route('ticket::it.request.check', ['id' => $ticket->id])->with('messages', $messages);
    }

    /**
     * [changeDeadline: change deadline of ticket]
     * @param  Request $request 
     * @return [view ticket detail]           
     */
    public function changeDeadline(Request $request)
    {
        $auth = Permission::getInstance()->getEmployee();

        $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
        $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();

        if($isAllowManageRequestOfTeam || $isAllowManageRequestOfDepartmentIT)
        {
            $ticket = Ticket::getTicketById($request->ticket_id);
        } else {
            View::viewErrorPermission();
        }

        $deadlineOld = $ticket->deadline;
        $deadlineNew = Carbon::parse($request->change_deadline);
        $ticket->deadline = $deadlineNew;
        $ticket->save();

        $ticket_thread = new TicketThread;
        $ticket_thread->ticket_id = $ticket->id;
        $ticket_thread->employee_id = $auth->id;
        $ticket_thread->content = $request->reason_change_deadline;
        $ticket_thread->type = TicketThread::COMMENT_DEADLINE;

        $ticket_thread->note = $deadlineOld . ' => ' . $deadlineNew;
        $ticket_thread->save();

        TicketRead::updateTicketRead($ticket->id, TicketRead::IS_NOT_READ, null);

        $ticketNew = Ticket::getTicketById($ticket->id);
        $data = array();
        $data = self::setDataPushEmail($ticketNew, $auth);

        if($ticketNew->assigned_to)
        {
            $assignedTo = Employee::find($ticketNew->assigned_to);
            if ($assignedTo) {
                $data['mail_to'] = $assignedTo->email;
                $template = 'ticket::template.change_deadline.mail_change_deadline_for_assigned';
                //set notify data
                $data['to_id'] = $assignedTo->id;
                $data['noti_content'] = trans('ticket::view.The work you are doing has changed the deadline.');
                self::pushEmailTicketToQueue($data, $template);
            }
        } 
        if($ticketNew->created_by)
        {
            $createdBy = Employee::find($ticketNew->created_by);
            if ($createdBy) {
                $data['mail_to'] = $createdBy->email;
                $template = 'ticket::template.change_deadline.mail_change_deadline_for_creator';
                //set notify
                $data['to_id'] = $createdBy->id;
                $data['noti_content'] = trans('ticket::view.The work you requested changed the deadline.');
                self::pushEmailTicketToQueue($data, $template);
            }
        }

        $relatedPersons = TicketRelater::getRelatedPersons($ticketNew->id);
        if(count($relatedPersons) > 0)
        {
            foreach ($relatedPersons as $item)
            {
                $data['mail_to'] = $item->email;
                $data['ticket_related_person_name'] = $item->name;
                $template = 'ticket::template.change_deadline.mail_change_deadline_for_related_person';
                self::pushEmailTicketToQueue($data, $template, false);
            }
            //set notify
            \RkNotify::put(
                $relatedPersons->lists('id')->toArray(),
                trans('ticket::view.The work related to you changed the deadline.'),
                $data['ticket_link'], ['category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }

        $messages = [
            'success'=> [
                Lang::get('ticket::view.Change deadline successful')
            ]
        ];

        return redirect()->route('ticket::it.request.check', ['id' => $ticket->id])->with('messages', $messages);
    }

    /**
     * [changeRelatedPerson: change related person of ticket]
     * @param  Request $request 
     * @return [view ticket detail]           
     */
    public function changeRelatedPerson(Request $request)
    {
        $auth = Permission::getInstance()->getEmployee();

        $relatedPersons = $request->related_persons_list;
        $ticketId = $request->ticket_id;

        $ticket = Ticket::getTicketById($ticketId);

        $checkShowAssignAndChangeStatus = TicketPermission::checkShowAssignAndChangeStatus($ticket, $auth->id);
        $isAllowEditRequest = TicketPermission::isAllowEditRequest();

        if($checkShowAssignAndChangeStatus && ($isAllowEditRequest || $auth->id == $ticket->created_by))
        {
            TicketRelater::deleteTicketRelatedPerson($ticketId);
        } else {
            View::viewErrorPermission();
        }
        
        if(!empty($relatedPersons))
        {
            $ticketRelaters = [];
            foreach ($relatedPersons as $key => $value) 
            {
                $ticketRelaters [] = array('ticket_id' => $ticketId, 'employee_id'=> $value);
            }
            TicketRelater::insert($ticketRelaters);
        }

        $data = array();
        $data = self::setDataPushEmail($ticket, $auth);

        $createdBy = Employee::find($ticket->created_by);
        if($auth->id != $ticket->assigned_to && $auth->id != $ticket->created_by)
        {
            if(!is_null($ticket->assigned_to))
            {
                $assignedTo = Employee::find($ticket->assigned_to);
                if ($assignedTo) {
                    $data['mail_to'] = $assignedTo->email;
                    $template = 'ticket::template.change_related_person.mail_change_related_person_for_assigned';
                    //set notify
                    $data['to_id'] = $assignedTo->id;
                    $data['noti_content'] = trans('ticket::view.The work you are doing has changed the related person.');
                    self::pushEmailTicketToQueue($data, $template);
                }
            } 
            if(!is_null($ticket->created_by) && $createdBy) 
            {
                $data['mail_to'] = $createdBy->email;
                $template = 'ticket::template.change_related_person.mail_change_related_person_for_creator';
                //set notify
                $data['to_id'] = $createdBy->id;
                $data['noti_content'] = trans('ticket::view.The work you requested changed the related person.');
                self::pushEmailTicketToQueue($data, $template);
            }
        } elseif($auth->id == $ticket->assigned_to && $createdBy) {   
            $data['mail_to'] = $createdBy->email;
            $template = 'ticket::template.change_related_person.mail_change_related_person_for_creator';
            //set notify
            $data['to_id'] = $createdBy->id;
            $data['noti_content'] = trans('ticket::view.The work you requested changed the related person.');
            self::pushEmailTicketToQueue($data, $template);
        } elseif($auth->id == $ticket->created_by) {   
            $assignedTo = Employee::find($ticket->assigned_to);
            if ($assignedTo) {
                $data['mail_to'] = $assignedTo->email;
                $template = 'ticket::template.change_related_person.mail_change_related_person_for_assigned';
                //set notify
                $data['to_id'] = $assignedTo->id;
                $data['noti_content'] = trans('ticket::view.The work you are doing has changed the related person.');
                self::pushEmailTicketToQueue($data, $template);
            }
        }

        $relatedPersons = TicketRelater::getRelatedPersons($ticket->id);
        if(count($relatedPersons) > 0)
        {
            foreach ($relatedPersons as $item)
            {
                if($item->id != $auth->id)
                {
                    $data['mail_to'] = $item->email;
                    $data['ticket_related_person_name'] = $item->name;
                    $template = 'ticket::template.create.mail_create_for_related_person';
                    self::pushEmailTicketToQueue($data, $template, false);
                }
            }
            //set notify
            \RkNotify::put(
                $relatedPersons->lists('id')->toArray(),
                trans(
                    'ticket::view.There is a request to resolve work from :ticket_created_by regarding you.',
                    ['ticket_created_by' => $data['ticket_created_by']]
                ),
                $data['ticket_link'],
                ['excerpt_current' => true, 'category_id' => RkNotify::CATEGORY_PROJECT]
            );
        }

        TicketRead::updateTicketRead($ticket->id, TicketRead::IS_NOT_READ, null);

        $messages = [
            'success'=> [
                Lang::get('ticket::view.Change related person successful')
            ]
        ];

        return redirect()->route('ticket::it.request.check', ['id' => $ticketId])->with('messages', $messages);
    }

    public function markRead(Request $request)
    {
        $ticketId = $request->id;
        $status = $request->status;
        $auth = Permission::getInstance()->getEmployee();

        $hasEmployeeInTicketRead = TicketRead::hasEmployeeInTicketRead($ticketId, $auth->id);

        if($hasEmployeeInTicketRead)
        {
            TicketRead::updateTicketRead($ticketId, $status, $auth->id);
        } else {
            TicketRead::insert(['ticket_id' => $ticketId, 'reader_id' => $auth->id, 'status' => $status]);
        }
    }

    /**
     * push email to queue
     * 
     * @return boolean
     */
    public function pushEmailTicketToQueue($data, $template, $notify = true)
    {
        $subject = $data['mail_title'];
        $emailQueue = new EmailQueue();

        $emailQueue->setTo($data['mail_to'])
                   ->setSubject($subject)
                   ->setTemplate($template, $data);
        //check set notify
        if ($notify && isset($data['to_id'])) {
            $emailQueue->setNotify($data['to_id'], $data['noti_content'], $data['ticket_link'], ['category_id' => RkNotify::CATEGORY_ADMIN]);
        }
        try
        {
            $emailQueue->save();
            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * [setDataPushEmail set data to push email]
     * @param [ticket] $ticket   
     * @param [employee] $employee 
     */
    public function setDataPushEmail($ticket, $employee)
    {
        $data = array();

        $data['user_mail']           = $employee->email;
        $data['mail_title']          = "[Request-IT][".$ticket->id."]".' '.ucfirst($ticket->subject);
        $data['ticket_id']           = $ticket->id;
        $data['ticket_subject']      = ucfirst($ticket->subject);
        $data['ticket_deadline']     = $ticket->deadline;
        $data['ticket_priority']     = ucfirst($ticket->attribute_priority);
        $data['ticket_status']       = ucfirst($ticket->attribute_status);
        $data['ticket_created_at']   = $ticket->created_at;
        $data['ticket_created_by']   = $ticket->created_name;
        $data['ticket_assigned_to']  = $ticket->assigned_name;
        $data['ticket_team_name']    = $ticket->team_name;
        $data['ticket_updated_by']   = $employee->name;
        $data['ticket_link']         = route( 'ticket::it.request.check', [ 'id' => $ticket->id ] );

        $dt                          = Carbon::parse($ticket->created_at);
        $data['ticket_date']         = $dt->format('d/m/Y');
        $data['ticket_time']         = $dt->format('H:i:s');

        return $data;
    }
}