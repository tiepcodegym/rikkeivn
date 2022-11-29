@extends('layouts.ticket_layout')

@section('title-ticket')
    {{ trans('ticket::view.Ticket detail') }} 
@endsection

@section('css-ticket')
    <?php 
      use Rikkei\Core\View\CoreUrl;
      use Rikkei\Core\View\View;
      use Rikkei\Team\Model\Employee;
      use Rikkei\Team\Model\TeamMember;
    ?>
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/bootstrap3-wysihtml5.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('team/css/style.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_ticket/css/ticket.css') }}" />
    <link rel="stylesheet" href="{{ CoreUrl::asset('asset_ticket/css/jquery.fileuploader.css') }}" />
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />

    <style type="text/css">
        .dropdown-menu a {
            cursor: pointer;
        }
    </style>
@endsection

@section('content-ticket')
    <?php
        use Rikkei\Ticket\Model\Ticket;
        use Rikkei\Ticket\Model\TicketThread;
        use Rikkei\Ticket\View\DateTime;
        use Rikkei\Ticket\View\TicketPermission;
        use Rikkei\Team\View\Permission;
        use Carbon\Carbon;
        use Illuminate\Support\Facades\Auth;

        $auth = Permission::getInstance()->getEmployee();

        $isAllowManageMyRequest = TicketPermission::isAllowManageMyRequest();
        $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
        $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();

        $isAllowEditRequest = TicketPermission::isAllowEditRequest();
        $isAllowComment = TicketPermission::isAllowComment($ticket, $auth->id);

        $checkShowAssignAndChangeStatus = TicketPermission::checkShowAssignAndChangeStatus($ticket, $auth->id);

        $isAllowCreatedChangeStatus = false;
        $isAllowAssignedChangeStatus = false;
        if($auth->id == $ticket->created_by) {
            $isAllowCreatedChangeStatus = true;
        } elseif($auth->id == $ticket->assigned_to && $isAllowManageMyRequest) {
            $isAllowAssignedChangeStatus = true;
        }
    ?>
    <!-- Ticket detail -->
    <div class="content-area">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="box-title" style="font-size: 21px;max-width: 100%;word-wrap: break-word;"><i class="fa fa-globe"> </i> {{ $ticket->subject }}</h2>
                    </div> 

                    @if($checkShowAssignAndChangeStatus)
                        <div class="col-md-6">
                            <div class="pull-right">
                                @if((empty($ticket->closed_at) && ($ticket->ticket_status != Ticket::STATUS_CLOSED)) && (empty($ticket->resolved_at) && ($ticket->ticket_status != Ticket::STATUS_RESOLVED)))
                                    @if($isAllowEditRequest)
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_change_team"><i class="fa fa-users" style="color:orange;"> </i> {{ trans('ticket::view.Change department IT') }}</button>

                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_change_priority"><i class="fa fa-pencil-square-o" style="color:orange;"> </i> {{ trans('ticket::view.Change priority') }}</button>

                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_change_deadline"><i class="fa fa-calendar" style="color:orange;"> </i> {{ trans('ticket::view.Change deadline') }}</button>
                                    @endif
                                    
                                    @if($isAllowEditRequest || $auth->id == $ticket->created_by)
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_change_related_person"><i class="fa fa-user" style="color:orange;"> </i> {{ trans('ticket::view.Change relater person') }}</button>
                                    @endif
                                    
                                     @if($isAllowEditRequest)
                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_assign"><i class="fa fa-hand-o-right" style="color:orange;"> </i> {{ trans('ticket::view.Assign') }}</button>
                                    @endif
                                @endif
                                 
                                @if($isAllowEditRequest)
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" id="d1">
                                            <i class="fa fa-exchange" style="color:teal;" id="hidespin"> </i>
                                            <i class="fa fa-spinner fa-spin" style="color:teal; display:none;" id="spin"></i>
                                            {{ trans('ticket::view.Change status') }}  <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if($ticket->ticket_status == Ticket::STATUS_OPENED || $ticket->ticket_status == Ticket::STATUS_FEEDBACK)
                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_INPROGRESS }})"> <i class="fa fa-folder-open-o" style="color:#ff0000;"> </i>{{ trans('ticket::view.Inprogress') }}</a>
                                                </li>
                                            @endif

                                            @if($ticket->ticket_status == Ticket::STATUS_INPROGRESS)
                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_RESOLVED }})"> <i class="fa fa-check-circle-o" style="color:#008000;"> </i>{{ trans('ticket::view.Resolved') }}</a>
                                                </li>
                                            @endif

                                            @if($ticket->ticket_status == Ticket::STATUS_RESOLVED)
                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_FEEDBACK }})"> <i class="fa fa-arrow-circle-o-left" style="color:#008000;"> </i>{{ trans('ticket::view.FeedBack') }}</a>
                                                </li>

                                                 @if($isAllowManageRequestOfDepartmentIT) 
                                                    <li>
                                                        <a id="ticket_close" data-toggle="modal" data-target="#modal_close_request"> <i class="fa fa-check" style="color:#008000;"> </i>{{ trans('ticket::view.Closed') }}</a>
                                                    </li>
                                                @endif
                                            @endif

                                            @if($ticket->ticket_status == Ticket::STATUS_FEEDBACK && $isAllowManageRequestOfDepartmentIT) 
                                                <li>
                                                    <a id="ticket_close" data-toggle="modal" data-target="#modal_close_request"> <i class="fa fa-check" style="color:#008000;"> </i>{{ trans('ticket::view.Closed') }}</a>
                                                </li>
                                            @endif
                                            
                                            @if($isAllowManageRequestOfDepartmentIT || $isAllowCreatedChangeStatus)
                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_CANCELLED }})"> <i class="fa fa-trash" style="color:#ff0000;"> </i>{{ trans('ticket::view.Cancel') }}</a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                @elseif($isAllowCreatedChangeStatus)
                                    @if($ticket->ticket_status == Ticket::STATUS_RESOLVED)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" id="d1">
                                                <i class="fa fa-exchange" style="color:teal;" id="hidespin"> </i>
                                                <i class="fa fa-spinner fa-spin" style="color:teal; display:none;" id="spin"></i>
                                                {{ trans('ticket::view.Change status') }}  <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_FEEDBACK }})"> <i class="fa fa-arrow-circle-o-left" style="color:#008000;"> </i>{{ trans('ticket::view.FeedBack') }}</a>
                                                </li>
                                                
                                                <li>
                                                    <a id="ticket_close" data-toggle="modal" data-target="#modal_close_request"> <i class="fa fa-check" style="color:#008000;"> </i>{{ trans('ticket::view.Closed') }}</a>
                                                </li>

                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_CANCELLED }})"> <i class="fa fa-trash" style="color:#ff0000;"> </i>{{ trans('ticket::view.Cancel') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @elseif($ticket->ticket_status == Ticket::STATUS_FEEDBACK)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" id="d1">
                                                <i class="fa fa-exchange" style="color:teal;" id="hidespin"> </i>
                                                <i class="fa fa-spinner fa-spin" style="color:teal; display:none;" id="spin"></i>
                                                {{ trans('ticket::view.Change status') }}  <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">                                                  
                                                <li>
                                                    <a id="ticket_close" data-toggle="modal" data-target="#modal_close_request"> <i class="fa fa-check" style="color:#008000;"> </i>{{ trans('ticket::view.Closed') }}</a>
                                                </li>

                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_CANCELLED }})"> <i class="fa fa-trash" style="color:#ff0000;"> </i>{{ trans('ticket::view.Cancel') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @elseif($ticket->ticket_status == Ticket::STATUS_OPENED || $ticket->ticket_status == Ticket::STATUS_INPROGRESS)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" id="d1">
                                                <i class="fa fa-exchange" style="color:teal;" id="hidespin"> </i>
                                                <i class="fa fa-spinner fa-spin" style="color:teal; display:none;" id="spin"></i>
                                                {{ trans('ticket::view.Change status') }}  <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_CANCELLED }})"> <i class="fa fa-trash" style="color:#ff0000;"> </i>{{ trans('ticket::view.Cancel') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                @elseif($isAllowAssignedChangeStatus)
                                    @if($ticket->ticket_status == Ticket::STATUS_OPENED || $ticket->ticket_status == Ticket::STATUS_FEEDBACK)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" id="d1">
                                                <i class="fa fa-exchange" style="color:teal;" id="hidespin"> </i>
                                                <i class="fa fa-spinner fa-spin" style="color:teal; display:none;" id="spin"></i>
                                                {{ trans('ticket::view.Change status') }}  <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_INPROGRESS }})"> <i class="fa fa-folder-open-o" style="color:#ff0000;"> </i>{{ trans('ticket::view.Inprogress') }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif

                                    @if($ticket->ticket_status == Ticket::STATUS_INPROGRESS)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" id="d1">
                                                <i class="fa fa-exchange" style="color:teal;" id="hidespin"> </i>
                                                <i class="fa fa-spinner fa-spin" style="color:teal; display:none;" id="spin"></i>
                                                {{ trans('ticket::view.Change status') }}  <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($ticket->ticket_status == Ticket::STATUS_INPROGRESS)
                                                    <li>
                                                        <a data-toggle="modal" data-target="#modal_change_status" onclick="getValueStatus({{ Ticket::STATUS_RESOLVED }})"> <i class="fa fa-check-circle-o" style="color:#008000;"> </i>{{ trans('ticket::view.Resolved') }}</a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <!-- /.box-header -->

            <!-- Ticket assign modal -->
            @include('ticket::modal.assign_modal')
            <!-- /.Ticket assign modal -->

            <!-- Ticket change status modal -->
            @include('ticket::modal.change_status_modal')
            <!-- /.Ticket change status modal -->

            <!-- Ticket change team modal -->
            @include('ticket::modal.change_team_modal')
            <!-- /.Ticket change team modal -->

            <!-- Ticket change deadline modal -->
            @include('ticket::modal.change_deadline_modal')
            <!-- /.Ticket change deadline modal -->
            <!-- /.Ticket change team modal -->

            <!-- Ticket change priority modal -->
            @include('ticket::modal.change_priority_modal')
            <!-- /.Ticket change priority modal -->

            <!-- Ticket change related person modal -->
            @include('ticket::modal.change_related_person_modal')
            <!-- /.Ticket change related person modal -->

            <div class="box-body form-horizontal">
                <div class="row">
                    <section class="">
                        <div class="col-md-12"> 
                            <div class="">
                                <div class="">
                                    <div class="col-md-4"> 
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Created at') }}: </b></label>
                                            <div class="col-md-8">
                                                <label class="control-label ticket-label" style="text-transform: capitalize;">{{ Carbon::parse($ticket->created_at)->format('d/m/Y H:i:s') }}</label>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4"> 
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Deadline') }}: </b></label>
                                            <div class="col-md-8">
                                                <label class="control-label ticket-label" style="text-transform: capitalize;">{{ Carbon::parse($ticket->deadline)->format('d/m/Y H:i:s') }}</label>
                                                </select>
                                            </div>
                                        </div>                
                                    </div>
                                    @if(!empty($ticket->resolved_at))
                                        <div class="col-md-4"> 
                                            <div class="form-group form-label-left form-group-select2">
                                                <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Completed at') }}: </b></label>
                                                <div class="col-md-8">
                                                    <label class="control-label ticket-label" style="text-transform: capitalize;">{{ Carbon::parse($ticket->resolved_at)->format('d/m/Y H:i:s') }}</label>
                                                    </select>
                                                </div>
                                            </div> 
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12"> 
                            <div class="">
                                <div class="">
                                    <div class="col-md-4">
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Created by') }}: </b></label>
                                            <div class="col-md-8">
                                                <label class="control-label ticket-label">{{ $ticket->created_name . ' (' . preg_replace('/@.*/', '',$ticket->created_email) . ')' }}</label>
                                                </select>
                                            </div>
                                        </div>    
                                    </div>
                                    <div class="col-md-4"> 
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Assigned to') }}: </b></label>
                                            <div class="col-md-8">
                                                <label class="control-label ticket-label">{{ $ticket->assigned_name . ' (' . preg_replace('/@.*/', '',$ticket->assigned_email) . ')' }}   </label>
                                                </select>
                                            </div>
                                        </div>                      
                                    </div>
                                    <div class="col-md-4"> 
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.IT department') }}: </b></label>
                                            <div class="col-md-8">
                                                <label class="control-label ticket-label">{{ $ticket->team_name }}   </label>
                                                </select>
                                            </div>
                                        </div>                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="">
                                <div class="">
                                    <div class="col-md-4"> 
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Priority') }}: </b></label>
                                            <div class="col-md-8">
                                                <label class="control-label ticket-label" style="text-transform: capitalize;">{{ $ticket->attribute_priority }} </label>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4"> 
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Status') }}: </b></label>
                                            <div class="col-md-8">
                                                <label class="control-label ticket-label" style="text-transform: capitalize;">{{ $ticket->attribute_status }} </label>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group form-label-left form-group-select2">
                                            <label class="col-md-4 control-label"><b>{{ trans('ticket::view.Related persons') }}: </b></label>
                                            <div class="col-md-8">
                                                <?php
                                                    $totalRelatedPersons = count($relatedPersons);
                                                    $countRelatedPersons = 0;
                                                ?>

                                                @if(isset($relatedPersons) && count($relatedPersons))
                                                    @foreach($relatedPersons as $item)
                                                        <?php 
                                                            $countRelatedPersons++; 
                                                        ?>
                                                         @if($countRelatedPersons == $totalRelatedPersons)
                                                            <label class="control-label ticket-label">{{ $item->name . ' (' . preg_replace('/@.*/', '',$item->email) . ') ' }} </label>
                                                        @else

                                                            <label class="control-label ticket-label">{{ $item->name . ' (' . preg_replace('/@.*/', '',$item->email) . '), ' }} </label>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

        <!-- Box comment -->
        <div class="nav-tabs-custom">
            <div class="col-xs-12">
                <h3 class="page-header"><i class="fa fa-user"></i> {{ trans('ticket::view.Content') }}</h3>
            </div>
            <div class="tab-content">
                <div class="active tab-pane" id="activity">
                    <!-- Post -->
                    <div class="post">
                        <div class="user-block">
                            <img class="img-circle img-bordered-sm" src="{{ $ticket->avatar_url }}" alt="user image">
                                <span class="ticket-user">
                                    <a>{{ $ticket->created_name }}</a>
                                </span>
                            <span class="ticket-time"><i class="fa fa-clock-o"></i> {{ DateTime::convertDateTime($ticket->created_at) }}</span>
                        </div>
                        <!-- /.user-block -->
                        <p>
                            {!! $ticket->content !!}
                        </p>
                        @if(isset($ticketImages) && count($ticketImages))
                            @foreach($ticketImages as $img)
                                <a class="fancybox" href="{{ URL::asset($img->url_image) }}" rel="group" style="cursor:zoom-in"><img src="{{ URL::asset($img->url_image) }}" width="161" height="123" border="0" alt=""></a>
                            @endforeach
                        @endif
                    </div>
                    <!-- /.post -->

                    @if(isset($ticketComment) && count($ticketComment))
                        @foreach($ticketComment as $item)
                            <?php
                                $content = View::nl2br($item->content);
                            ?>

                            <!-- Post -->
                            <div class="post clearfix ticket-word-wrap">
                                <div class="user-block">
                                    <img class="img-circle img-bordered-sm" src="{{ $item->avatar_url }}" alt="User Image">
                                    <span class="ticket-user">
                                      <a>{{ $item->created_by}}</a>
                                    </span>
                                    <span class="ticket-time"><i class="fa fa-clock-o"></i> {{ DateTime::convertDateTime($item->created_at) }}</span>
                                </div>
                                <!-- /.user-block -->
                                @if($item->type == TicketThread::COMMENT_PRIORITY)
                                    <p>
                                        <span>{{ trans('ticket::view.Change priority') }}:&nbsp;</span>{!! $item->note !!}
                                    </p>
                                    <p>
                                        <span>{{ trans('ticket::view.Reason') }}:&nbsp;</span>{!! $content !!}
                                    </p>
                                @elseif($item->type == TicketThread::COMMENT_RATING)
                                    @if($ticket->rating == Ticket::RATING_UNSATISFIED)
                                        <p>
                                            <span>{{ trans('ticket::view.Rating') }}:&nbsp;</span>{{ trans('ticket::view.Unsatisfied') }}
                                        </p>
                                        <p>
                                            <span>{{ trans('ticket::view.Reason') }}:&nbsp;</span>{!! $content !!}
                                        </p>
                                    @else
                                        <p>
                                            <span>{{ trans('ticket::view.Rating') }}:&nbsp;</span>{{ trans('ticket::view.Satisfied') }}
                                        </p>
                                    @endif
                                @elseif($item->type == TicketThread::COMMENT_DEADLINE)
                                    <p>
                                        <span>{{ trans('ticket::view.Change deadline') }}:&nbsp;</span>{!! $item->note !!}
                                    </p>
                                    <p>
                                        <span>{{ trans('ticket::view.Reason') }}:&nbsp;</span>{!! $content !!}
                                    </p>
                                @else
                                    <p>
                                        {!! $item->content !!}
                                    </p>
                                @endif
                            </div>
                            <!-- /.post -->
                        @endforeach
                    @endif

                    @if(($ticket->ticket_status != Ticket::STATUS_CLOSED) && ($ticket->ticket_status != Ticket::STATUS_CANCELLED) && $isAllowComment)
                        <!-- Post comment -->
                        <div class="post clearfix ticket-word-wrap">
                            <div class="user-block">
                                <h4>{{ trans('ticket::view.Comment') }}</h4>
                            </div>
                            <!-- /.user-block -->

                            <form id="form-comment" action="{{ route('ticket::it.request.comment') }}" method="post">
                                {!! csrf_field() !!}
                                <div class="form-group">
                                    <textarea id="comment_textarea" name="comment_content" class="form-control" style="height: 200px"></textarea>
                                    <label id="title-error" class="error" for="title" style="display: none;">{{ trans('ticket::view.You have not comment') }}</label>
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                </div>
                                <div class="form-group">
                                    <div class="pull-left">
                                        <button type="submit" onClick="return checkComment();" class="btn btn-primary"><i class="fa fa-comments-o"></i> {{ trans('ticket::view.Send comment') }}</button>
                                        <input type="hidden" name="check_comment" value="false" id="check_comment">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.post -->
                    @endif
                </div>
                <!-- /.tab-pane -->
            </div>
            <!-- /.tab-content -->
        </div>
    </div>

    <!-- box add -->
    <div class="box box-primary" id="box_add" hidden="">
        @include('ticket::include.add')
    </div>

    <!-- Ticket close modal -->
    @include('ticket::modal.close_modal')
    <!-- /.Ticket close modal -->
@endsection

@section('script-ticket')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>

    <script src="{{ URL::asset('lib/ckeditor/ckeditor.js') }}"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/amd/bootstrap3-wysihtml5.all.min.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/bootstrap3-wysihtml5.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.19.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_ticket/js/ticket_check.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_ticket/js/jquery.fileuploader.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_ticket/js/ticket.js') }}"></script>
    <script type="text/javascript">
        const messageValidate = "Trường bắt buộc";
        const MESSAGE_REQUIRE = '{{ trans('core::message.This field is required') }}';
        const MESSAGE_RANGE_LENGTH = '{{ trans('core::view.This field not be greater than :number characters', ['number' => 255]) }}';
    </script>
    
    <script type="text/javascript">
        $(document).ready(function() 
        {
            $(".select-search").select2();

            $('.uploadFile input:file').fileuploader({
                addMore: true,
                fileMaxSize : 1,
                extensions : ['jpg', 'jpeg', 'png', 'bmp'],
                captions: {
                    button: function(options) { return 'Choose ' + (options.limit == 1 ? 'File' : 'Files'); },
                    feedback: function(options) { return 'Choose ' + (options.limit == 1 ? 'file' : 'files') + ' to upload'; },
                    feedback2: function(options) { return options.length + ' ' + (options.length > 1 ? ' files were' : ' file was') + ' chosen'; },
                    drop: 'Drop the files here to Upload',
                    paste: '<div class="fileuploader-pending-loader"><div class="left-half" style="animation-duration: ${ms}s"></div><div class="spinner" style="animation-duration: ${ms}s"></div><div class="right-half" style="animation-duration: ${ms}s"></div></div> Pasting a file, click here to cancel.',
                    removeConfirmation: 'Are you sure you want to remove this file?',
                    errors: {
                        filesLimit: 'Only ${limit} files are allowed to be uploaded.',
                        filesType: 'Only ${extensions} files are allowed to be uploaded.',
                        fileSize: '${name} is too large! Please choose a file up to ${fileMaxSize}MB.',
                        filesSizeAll: 'Files that you choosed are too large! Please upload files up to ${maxSize} MB.',
                        fileName: 'File with the name ${name} is already selected.',
                        folderUpload: 'You are not allowed to upload folders.'
                    }
                }
            });

            $('#leader_id_change').val($('#change_team').select2().find(":selected").attr("data-leader"));

            $("#btn_add").click(function(){
                $("#box_add").show(800);
                $('.content-area').hide(800);
            });

            $("#close").click(function(){
                $("#box_add").hide(800);
                $('.content-area').show(800);
            });

            show_editor_comment = "{{ $checkShowAssignAndChangeStatus }}";

            if(show_editor_comment)
            {
                editorComment = CKEDITOR.replace('comment_content', {
                        toolbar: [
                            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                            { name: 'clipboard', groups: [ 'clipboard', 'undo' ], items: [ 'Cut', 'Copy', '-', 'Undo', 'Redo' ] },
                            { name: 'others', items: [ '-' ] },
                            { name: 'styles', items: [ 'Format', 'Styles', 'FontSize' ] },
                            { name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ] }
                        ]
                    }
                );
                CKEDITOR.config.title = false;

                editorComment.on('change',function()
                {
                    check_comment = $('#check_comment').val();

                    if(check_comment == 'true')
                    {
                        contentComment = CKEDITOR.instances.comment_textarea.getData().replace(/<[^>]*>/gi, '');
                        if(contentComment.trim().length != 0)
                        {
                            $("#title-error").hide();
                        }
                    }
                });
            }

            $('#form-change-priority').validate({
                rules: {
                    'reason_change_priority': {
                        required: true,
                    }
                },
                messages: {
                    'reason_change_priority': {
                        required: '<?php echo trans('core::view.This field is required'); ?>'
                    }
                }
            });

            $('#form-change-deadline').validate({
                rules: {
                    'reason_change_deadline': {
                        required: true,
                    }
                },
                messages: {
                    'reason_change_deadline': {
                        required: '<?php echo trans('core::view.This field is required'); ?>'
                    }
                }
            });

            $('#form-close-request').validate({
                rules: {
                    'reason_unsatisfied': {
                        required: true,
                    }
                },
                messages: {
                    'reason_unsatisfied': {
                        required: '<?php echo trans('core::view.This field is required'); ?>'
                    }
                }
            });

            var date = new Date();
            var dateAdd = date.setMinutes(date.getMinutes() + 121);
            $('#datetimepicker-change-deadline').datetimepicker({
                allowInputToggle: true,
                defaultDate: dateAdd,
                sideBySide: true,
            });

            $('input[type="radio"].minimal').iCheck({
                radioClass: 'iradio_minimal-blue'
            });

            $('#satisfied input').on('ifChecked', function(event) {
                checked = event.target.checked;

                if(checked)
                {
                    $('#box_reason_unsatisfied').hide();
                    $('#reason_unsatisfied-error').hide();
                    $('#reason_unsatisfied').val('');
                }
            });

            $('#unsatisfied input').on('ifChecked', function(event) {
                checked = event.target.checked;

                if(checked)
                {
                    $('#box_reason_unsatisfied').show();
                }
            });

            $('#change_team').on('select2:select', function (evt) {
                leader_id = $('#change_team').select2().find(":selected").attr("data-leader");
                $('#leader_id_change').val(leader_id);
                $('#leader-change-error').hide();
            });

            $('#team_id').on('select2:select', function (evt) {
                leader_id = $('#team_id').select2().find(":selected").attr("data-leader");
                $('#leader_id').val(leader_id);
                $('#leader-error').hide();
                $('#submit').prop("disabled", false);
            });

            $("#related_persons").select2({
                ajax: {
                    url: "{{ route('ticket::it.request.find-employee') }}",
                    dataType: "JSON",
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            $("#change_related_person").select2({
                ajax: {
                    url: "{{ route('ticket::it.request.find-employee') }}",
                    dataType: "JSON",
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            $('.comelate-calendar').on('click', function() {
                $('.select-search').select2('close'); 
                $('#datetimepicker1').data("DateTimePicker").show();
            });
        });
    </script>
@endsection
