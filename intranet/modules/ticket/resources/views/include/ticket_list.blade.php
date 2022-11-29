<?php
    use Carbon\Carbon;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Team\View\Config;
    use Rikkei\Team\View\Permission;
    use Rikkei\Team\Model\TeamMember;
    use Rikkei\Ticket\Model\Ticket;
    use Rikkei\Ticket\Model\TicketAttribute;
    use Rikkei\Ticket\Model\TicketRead;
    use Rikkei\Ticket\View\TicketPermission;
    use Illuminate\Support\Facades\Auth;

    $auth = Permission::getInstance()->getEmployee();

    $ticketAttribute = TicketAttribute::getListAttributes();
    $ticketItemsTable = Ticket::getTableName();
    $teamsIT = Ticket::getTeamsOfDeparmentIT();
    $membersOfTeam = TeamMember::getAllMemberOfTeamByCode(Ticket::TEAM_CODE);

    $isAllowManageMyRequest = TicketPermission::isAllowManageMyRequest();
    $isAllowManageRequestOfTeam = TicketPermission::isAllowManageRequestOfTeam();
    $isAllowManageRequestOfDepartmentIT = TicketPermission::isAllowManageRequestOfDepartmentIT();
    $isAllowViewRequestOfTeam = TicketPermission::isAllowViewRequestOfTeam();
    $isAllowViewRequestOfDepartmentIT = TicketPermission::isAllowViewRequestOfDepartmentIT();
?>
<div class="box-header">
    <h3 class="box-title" style="font-size: 21px;">{{ $boxTitle }} </h3>
    <div class="pull-right">   
        @include('team::include.filter', ['domainTrans' => 'ticket'])
    </div>
</div>
<!-- /.box-header -->
<div class="box-body no-padding">
    <div class="table-responsive">
        <table class="table table-striped dataTable table-hover table-grid-data ticket-table">
            <thead>
                <tr>
                    <th class="ticket-col-width-80">{{ trans('core::view.NO.') }}</th>

                    <th class="ticket-col-width-25">&nbsp;</th>
                    
                    @if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
                        <th class="sorting {{ Config::getDirClass('subject') }} col-title ticket-col-width-360" data-order="subject" data-dir="{{ Config::getDirOrder('subject') }}">{{ trans('ticket::view.Subject') }}</th>
                    @else
                        <th class="sorting {{ Config::getDirClass('subject') }} col-title ticket-col-width-400" data-order="subject" data-dir="{{ Config::getDirOrder('subject') }}">{{ trans('ticket::view.Subject') }}</th>
                    @endif

                    <th class="sorting {{ Config::getDirClass('priority_id') }} col-title ticket-col-width-100" data-order="priority_id" data-dir="{{ Config::getDirOrder('priority_id') }}">{{ trans('ticket::view.Priority') }}</th>

                    <th class="sorting {{ Config::getDirClass('created_by') }} col-title ticket-col-width-100" data-order="created_by" data-dir="{{ Config::getDirOrder('created_by') }}">{{ trans('ticket::view.Created by') }}</th>

                    <th class="sorting {{ Config::getDirClass('assigned_to') }} col-title ticket-col-width-100" data-order="assigned_to" data-dir="{{ Config::getDirOrder('assigned_to') }}">{{ trans('ticket::view.Assigned to') }}</th>

                    @if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
                        <th class="sorting {{ Config::getDirClass('team_name') }} col-title ticket-col-width-120" data-order="team_name" data-dir="{{ Config::getDirOrder('team_name') }}">{{ trans('ticket::view.IT department') }}</th>
                    @endif

                    <th class="sorting {{ Config::getDirClass('deadline') }} col-title ticket-col-width-100" data-order="deadline" data-dir="{{ Config::getDirOrder('deadline') }}">{{ trans('ticket::view.Deadline') }}</th>

                    <th class="sorting {{ Config::getDirClass('status_id') }} col-title ticket-col-width-100" data-order="status_id" data-dir="{{ Config::getDirOrder('status_id') }}">{{ trans('ticket::view.Status') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[{{ $ticketItemsTable }}.subject]" value="{{ Form::getFilterData("{$ticketItemsTable}.subject") }}" placeholder="{{ trans('ticket::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                    $filterPriority = Form::getFilterData('number', "tp.id");
                                ?>
                                <select name="filter[number][tp.id]" class="form-control select-grid filter-grid select-search ticket-select-search">
                                    <option>&nbsp;</option>
                                    @foreach($ticketAttribute as $key => $value)
                                        @if(!is_null($value->priority))
                                            <option value="{{ $value->id }}" <?php
                                                    if ($value->id == $filterPriority): ?> selected<?php endif; 
                                                        ?>>{{ $value->priority }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[cb.name]" value="{{ Form::getFilterData("cb.name") }}" placeholder="{{ trans('ticket::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[at.name]" value="{{ Form::getFilterData("at.name") }}" placeholder="{{ trans('ticket::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                    </td>

                    @if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $filterTeam = Form::getFilterData('number', "team_table.id");
                                    ?>
                                    <select name="filter[number][team_table.id]" class="form-control select-grid filter-grid select-searchticket-select-search ticket-select-search">
                                        <option>&nbsp;</option>
                                        @foreach($teamsIT as $item)
                                            @if(!is_null($item->id))
                                                <option class="ds" value="{{ $item->id }}" <?php
                                                        if ($item->id == $filterTeam): ?> selected<?php endif; 
                                                            ?>>{{ $item->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                    @endif

                    <td>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="text" name="filter[{{ $ticketItemsTable }}.deadline]" value="{{ Form::getFilterData("{$ticketItemsTable}.deadline") }}" placeholder="{{ trans('ticket::view.Search') }}..." class="filter-grid form-control" />
                            </div>
                        </div>
                    </td>
                    @if(empty($status))
                        <td>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                        $filterStatus = Form::getFilterData('number', "ts.id");
                                    ?>
                                    <select name="filter[number][ts.id]" class="form-control select-grid filter-grid select-search ticket-select-search">
                                        <option>&nbsp;</option>
                                        @foreach($ticketAttribute as $key => $value)
                                            @if(!is_null($value->status))
                                                <option class="ds" value="{{ $value->id }}" <?php
                                                        if ($value->id == $filterStatus): ?> selected<?php endif; 
                                                            ?>>{{ $value->status }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </td>
                    @else
                        <td>&nbsp;</td>
                    @endif
                </tr>
                @if(isset($collectionModel) && count($collectionModel))
                    <?php $i = View::getNoStartGrid($collectionModel); ?>
                    @foreach($collectionModel as $item)
                        <?php 
                            $isRead = TicketRead::isRead($item->id, $auth->id);
                            
                        ?>
                        @if(!$isRead)
                            <tr class="tr-mark-unread" data-val="{{ $item->id }}" data-status="{{ TicketRead::IS_READ }}">
                                <td>
                                    <span>{{ $i }}</span>
                                </td>

                                <td>
                                    <span class="ticket-mark pull-left">
                                        <a class="mark-read" data-toggle="tooltip" data-placement="top" title="{{ trans('ticket::view.Mark as read') }}" style="cursor: pointer;"><i class="fa fa-circle-o"></i></a>
                                    </span>
                                    <span class="pull-right"><i class="fa fa-circle" style="font-size: 8px"></i></span>
                                </td>
                                
                                <td class="ticket-subject">
                                    <a href="{{ route('ticket::it.request.check', ['id' => $item->id ]) }}">
                                        {{ $item->subject }}
                                    </a>
                                </td>
                                <td style="text-transform: capitalize;"><b>{{ $item->priority }}</b></td>
                                <td>{{ $item->created_name }}</td>
                                <td>{{ $item->assigned_name }}</td>

                                @if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
                                    <td>{{ $item->team_name }}</td>
                                @endif

                                <td>{{ $item->deadline }}</td>
                                <td style="text-transform: capitalize;">{{ $item->status }}</td>
                            </tr>
                        @else
                            <tr class="tr-mark-read" data-val="{{ $item->id }}" data-status="{{ TicketRead::IS_NOT_READ }}">
                                <td>
                                    <span>{{ $i }}</span>
                                </td>

                                <td>
                                    <span class="ticket-mark pull-left">
                                        <a class="mark-read" data-toggle="tooltip" data-placement="top" title="{{ trans('ticket::view.Mark as unread') }}" style="cursor: pointer;"><i class="fa fa-circle-o"></i></a>
                                    </span>
                                </td>
                                
                                <td class="ticket-subject">
                                    <a href="{{ route('ticket::it.request.check', ['id' => $item->id ]) }}">
                                        {{ $item->subject }}
                                    </a>
                                </td>
                                <td style="text-transform: capitalize;">{{ $item->priority }}</td>
                                <td>{{ $item->created_name }}</td>
                                <td>{{ $item->assigned_name }}</td>

                                @if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
                                    <td>{{ $item->team_name }}</td>
                                @endif

                                <td>{{ $item->deadline }}</td>
                                <td style="text-transform: capitalize;">{{ $item->status }}</td>
                            </tr>
                        @endif
                        <?php $i++; ?>
                    @endforeach
                @else
                    <tr>
                        @if($isAllowManageRequestOfDepartmentIT || $isAllowViewRequestOfDepartmentIT)
                            <td colspan="9" class="text-center">
                                <h2 class="no-result-grid">{{ trans('ticket::view.No results found') }}</h2>
                            </td>
                        @else
                            <td colspan="8" class="text-center">
                                <h2 class="no-result-grid">{{ trans('ticket::view.No results found') }}</h2>
                            </td>
                        @endif
                    </tr>
                @endif
            </tbody>
        </table>
        <!-- /.table -->
    </div>
    <!-- /.table-responsive -->
</div>
<!-- /.box-body -->

<div class="box-footer no-padding">
    <div class="mailbox-controls">   
        @include('team::include.pager')
    </div>
</div>