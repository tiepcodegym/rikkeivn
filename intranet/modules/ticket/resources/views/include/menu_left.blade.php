<?php
    use Rikkei\Ticket\Model\Ticket;
    use Rikkei\Ticket\Model\TicketRead;
    use Rikkei\Ticket\View\TicketPermission;
    use Rikkei\Team\View\Permission;
    use Illuminate\Support\Facades\Auth;

    $auth = Permission::getInstance()->getEmployee();

    $isAllowManageMyRequest = TicketPermission::isAllowManageMyRequest();
    $isAllowViewRequestOfTeam = TicketPermission::isAllowViewRequestOfTeam();
    $isAllowViewRequestOfDepartmentIT = TicketPermission::isAllowViewRequestOfDepartmentIT();
    $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
    $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();

    if($isAllowManageRequestOfTeam || $isAllowViewRequestOfTeam)
    {
        $idTeamIT = Ticket::getTeamIdOfDepartmentIT($auth->id);
    }
?>

<!-- MENU_CREATED -->

<div>
    <button type="button" class="btn btn-danger" id="btn_add"><span style="" class="glyphicon glyphicon-plus"></span> <b>&nbsp;{{ trans('ticket::view.Add a request') }}</b> </button>
</div>
<br>

<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ticket-menu">
            <h3 class="box-title">{{ trans('ticket::view.Tickets created') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ticket">
            <li>
                <a href="{{ route('ticket::it.request.status') }}" id="menu_all">
                    <i class="fa fa-inbox"></i> {{ trans('ticket::view.All') }}
                    @if(Ticket::countTicketsCreatedBy($auth->id) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsCreatedBy($auth->id) > TicketRead::countTicketsCreatedByIsRead($auth->id))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label bg-green pull-right">
                                {{ Ticket::countTicketsCreatedBy($auth->id) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.status', ['id' => Ticket::STATUS_OPENED ]) }}" style="text-transform: capitalize;">
                    <i class="fa fa-envelope-o"></i> {{ trans('ticket::view.New') }}
                    @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_OPENED) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_OPENED) > TicketRead::countTicketsCreatedByIsRead($auth->id, Ticket::STATUS_OPENED))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label label-primary pull-right">
                                {{ Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_OPENED) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.status', ['id' => Ticket::STATUS_INPROGRESS ]) }}" style="text-transform: capitalize;"> 
                    <i class="glyphicon glyphicon-import"></i> {{ trans('ticket::view.Inprogress') }}
                    @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_INPROGRESS) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_INPROGRESS) > TicketRead::countTicketsCreatedByIsRead($auth->id, Ticket::STATUS_INPROGRESS))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label label-primary pull-right">
                                {{ Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_INPROGRESS) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.status', ['id' => Ticket::STATUS_RESOLVED ]) }}" style="text-transform: capitalize;"> 
                    <i class="fa fa-registered"></i> {{ trans('ticket::view.Resolved') }}
                    @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_RESOLVED) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_RESOLVED) > TicketRead::countTicketsCreatedByIsRead($auth->id, Ticket::STATUS_RESOLVED))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label label-primary pull-right">
                                {{ Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_RESOLVED) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.status', ['id' => Ticket::STATUS_OVERDUE ]) }}" style="text-transform: capitalize;"> 
                    <i class="fa fa-calendar-times-o"></i> {{ trans('ticket::view.Overdue') }}
                    @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_OVERDUE) > 0)
                        <span class="pull-right">
                            
                            @if(Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_OVERDUE) > TicketRead::countTicketsCreatedByIsRead($auth->id, Ticket::STATUS_OVERDUE))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label bg-red pull-right">
                                {{ Ticket::countTicketsCreatedBy($auth->id, Ticket::STATUS_OVERDUE) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->

<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ticket-menu">
            <h3 class="box-title">{{ trans('ticket::view.Tickets related') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ticket">
            <li>
                <a href="{{ route('ticket::it.request.related.status') }}" id="menu_all">
                    <i class="fa fa-inbox"></i> {{ trans('ticket::view.All') }}
                    @if(Ticket::countTicketsOfRelatedPerson($auth->id) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsOfRelatedPerson($auth->id) > TicketRead::countTicketsRelatedPersonIsRead($auth->id))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label bg-green pull-right">
                                {{ Ticket::countTicketsOfRelatedPerson($auth->id) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.related.status', ['id' => Ticket::STATUS_OPENED ]) }}" style="text-transform: capitalize;">
                    <i class="fa fa-envelope-o"></i> {{ trans('ticket::view.New') }}
                    @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_OPENED) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_OPENED) > TicketRead::countTicketsRelatedPersonIsRead($auth->id, Ticket::STATUS_OPENED))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label label-primary pull-right">
                                {{ Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_OPENED) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.related.status', ['id' => Ticket::STATUS_INPROGRESS ]) }}" style="text-transform: capitalize;"> 
                    <i class="glyphicon glyphicon-import"></i> {{ trans('ticket::view.Inprogress') }}
                    @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_INPROGRESS) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_INPROGRESS) > TicketRead::countTicketsRelatedPersonIsRead($auth->id, Ticket::STATUS_INPROGRESS))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label label-primary pull-right">
                                {{ Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_INPROGRESS) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.related.status', ['id' => Ticket::STATUS_RESOLVED ]) }}" style="text-transform: capitalize;"> 
                    <i class="fa fa-registered"></i> {{ trans('ticket::view.Resolved') }}
                    @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_RESOLVED) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_RESOLVED) > TicketRead::countTicketsRelatedPersonIsRead($auth->id, Ticket::STATUS_RESOLVED))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label label-primary pull-right">
                                {{ Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_RESOLVED) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('ticket::it.request.related.status', ['id' => Ticket::STATUS_OVERDUE ]) }}" style="text-transform: capitalize;"> 
                    <i class="fa fa-calendar-times-o"></i> {{ trans('ticket::view.Overdue') }}
                    @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_OVERDUE) > 0)
                        <span class="pull-right">
                            @if(Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_OVERDUE) > TicketRead::countTicketsRelatedPersonIsRead($auth->id, Ticket::STATUS_OVERDUE))
                                <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                            @endif
                            <span class="label bg-red pull-right">
                                {{ Ticket::countTicketsOfRelatedPerson($auth->id, Ticket::STATUS_OVERDUE) }}
                            </span>
                        </span>
                    @endif
                </a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->

@if($isAllowManageMyRequest)
    <!-- MENU_ASSIGNED -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <div class="pull-left ticket-menu">
                <h3 class="box-title">{{ trans('ticket::view.My tickets') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked menu-ticket">
                <li>
                    <a href="{{ route('ticket::it.request.assigned.status') }}">
                        <i class="fa fa-inbox"></i> {{ trans('ticket::view.All') }}
                        @if(Ticket::countTicketsAssignedTo($auth->id) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsAssignedTo($auth->id) > TicketRead::countTicketsAssignedToIsRead($auth->id))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label bg-green pull-right">
                                    {{ Ticket::countTicketsAssignedTo($auth->id) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.assigned.status', ['id' => Ticket::STATUS_OPENED ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-envelope-o"></i> {{ trans('ticket::view.New') }}
                        @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_OPENED) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_OPENED) > TicketRead::countTicketsAssignedToIsRead($auth->id, Ticket::STATUS_OPENED))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_OPENED) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.assigned.status', ['id' => Ticket::STATUS_INPROGRESS ]) }}" style="text-transform: capitalize;">
                        <i class="glyphicon glyphicon-import"></i> {{ trans('ticket::view.Inprogress') }}
                        @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_INPROGRESS) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_INPROGRESS) > TicketRead::countTicketsAssignedToIsRead($auth->id, Ticket::STATUS_INPROGRESS))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_INPROGRESS) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.assigned.status', ['id' => Ticket::STATUS_FEEDBACK ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-reply-all"></i> {{ trans('ticket::view.FeedBack') }}
                        @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_FEEDBACK) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_FEEDBACK) > TicketRead::countTicketsAssignedToIsRead($auth->id, Ticket::STATUS_FEEDBACK))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_FEEDBACK) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.assigned.status', ['id' => Ticket::STATUS_OVERDUE ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-calendar-times-o"></i> {{ trans('ticket::view.Overdue') }}
                        @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_OVERDUE) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_OVERDUE) > TicketRead::countTicketsAssignedToIsRead($auth->id, Ticket::STATUS_OVERDUE))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label bg-red pull-right">
                                    {{ Ticket::countTicketsAssignedTo($auth->id, Ticket::STATUS_OVERDUE) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <!-- /.box-body -->
    </div>
<!-- /. box -->
@endif

@if($isAllowManageRequestOfTeam || $isAllowViewRequestOfTeam)
    <!-- MENU_TEAM -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <div class="pull-left ticket-menu">
                <h3 class="box-title">{{ trans('ticket::view.Tickets of team') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked menu-ticket">
                <li>
                    <a href="{{ route('ticket::it.request.team.status') }}">
                        <i class="fa fa-inbox"></i> {{ trans('ticket::view.All') }}
                        @if(Ticket::countTicketsOfTeam($idTeamIT) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfTeam($idTeamIT) > TicketRead::countTicketsOfTeamIsRead($idTeamIT, $auth->id))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label bg-green pull-right">
                                    {{ Ticket::countTicketsOfTeam($idTeamIT) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.team.status', ['id' => Ticket::STATUS_OPENED ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa fa-envelope-o"></i> {{ trans('ticket::view.New') }}
                        @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_OPENED) > 0)
                            <span class="pull-right">
                               @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_OPENED) > TicketRead::countTicketsOfTeamIsRead($idTeamIT, $auth->id, Ticket::STATUS_OPENED))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_OPENED) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.team.status', ['id' => Ticket::STATUS_INPROGRESS ]) }}" style="text-transform: capitalize;"> 
                        <i class="glyphicon glyphicon-import"></i> {{ trans('ticket::view.Inprogress') }}
                        @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_INPROGRESS) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_OPENED) > TicketRead::countTicketsOfTeamIsRead($idTeamIT, $auth->id, Ticket::STATUS_OPENED))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_INPROGRESS) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.team.status', ['id' => Ticket::STATUS_FEEDBACK ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-reply-all"></i> {{ trans('ticket::view.FeedBack') }}
                        @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_FEEDBACK) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_FEEDBACK) > TicketRead::countTicketsOfTeamIsRead($idTeamIT, $auth->id, Ticket::STATUS_FEEDBACK))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_FEEDBACK) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.team.status', ['id' => Ticket::STATUS_OVERDUE ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-calendar-times-o"></i> {{ trans('ticket::view.Overdue') }}
                        @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_OVERDUE) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_OVERDUE) > TicketRead::countTicketsOfTeamIsRead($idTeamIT, $auth->id, Ticket::STATUS_OVERDUE))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>

                                @endif
                                <span class="label bg-red pull-right">
                                    {{ Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_OVERDUE) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.team.status', ['id' => Ticket::STATUS_CLOSED ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-minus-circle"></i> {{ trans('ticket::view.Closed') }}
                        @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_CLOSED) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_CLOSED) > TicketRead::countTicketsOfTeamIsRead($idTeamIT, $auth->id, Ticket::STATUS_CLOSED))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-success pull-right">
                                    {{ Ticket::countTicketsOfTeam($idTeamIT, Ticket::STATUS_CLOSED) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <!-- /.box-body -->
    </div>
@endif

@if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
    <!-- MENU_TEAM -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <div class="pull-left ticket-menu">
                <h3 class="box-title">{{ trans('ticket::view.Tickets of department IT') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked menu-ticket">
                <li>
                    <a href="{{ route('ticket::it.request.dashboard.status') }}">
                        <i class="fa fa-inbox"></i> {{ trans('ticket::view.All') }}
                        @if(Ticket::countTicketsOfDepartmentIT() > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfDepartmentIT() > TicketRead::countTicketsOfDepartmentITIsRead($auth->id))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label bg-green pull-right">
                                    {{ Ticket::countTicketsOfDepartmentIT() }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.dashboard.status', ['id' => Ticket::STATUS_OPENED ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa fa-envelope-o"></i> {{ trans('ticket::view.New') }}
                        @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_OPENED) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_OPENED) > TicketRead::countTicketsOfDepartmentITIsRead($auth->id, Ticket::STATUS_OPENED))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_OPENED) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.dashboard.status', ['id' => Ticket::STATUS_INPROGRESS ]) }}" style="text-transform: capitalize;"> 
                        <i class="glyphicon glyphicon-import"></i> {{ trans('ticket::view.Inprogress') }}
                        @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_INPROGRESS) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_INPROGRESS) > TicketRead::countTicketsOfDepartmentITIsRead($auth->id, Ticket::STATUS_INPROGRESS))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_INPROGRESS) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.dashboard.status', ['id' => Ticket::STATUS_FEEDBACK ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-reply-all"></i> {{ trans('ticket::view.FeedBack') }}
                        @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_FEEDBACK) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_FEEDBACK) > TicketRead::countTicketsOfDepartmentITIsRead($auth->id, Ticket::STATUS_FEEDBACK))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-primary pull-right">
                                    {{ Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_FEEDBACK) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.dashboard.status', ['id' => Ticket::STATUS_OVERDUE ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-calendar-times-o"></i> {{ trans('ticket::view.Overdue') }}
                        @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_OVERDUE) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_OVERDUE) > TicketRead::countTicketsOfDepartmentITIsRead($auth->id, Ticket::STATUS_OVERDUE))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>

                                @endif
                                <span class="label bg-red pull-right">
                                    {{ Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_OVERDUE) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
                <li>
                    <a href="{{ route('ticket::it.request.dashboard.status', ['id' => Ticket::STATUS_CLOSED ]) }}" style="text-transform: capitalize;"> 
                        <i class="fa fa-minus-circle"></i> {{ trans('ticket::view.Closed') }}
                        @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_CLOSED) > 0)
                            <span class="pull-right">
                                @if(Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_CLOSED) > TicketRead::countTicketsOfDepartmentITIsRead($auth->id, Ticket::STATUS_CLOSED))
                                    <i class="fa fa-circle pull-left" style="font-size: 8px"></i>
                                @endif
                                <span class="label label-success pull-right">
                                    {{ Ticket::countTicketsOfDepartmentIT(Ticket::STATUS_CLOSED) }}
                                </span>
                            </span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <!-- /.box-body -->
    </div>
@endif





