@extends('manage_time::layout.common_layout')

@section('title-common')
    {{ trans('manage_time::view.Supplement register') }} 
@endsection

<?php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Auth;
    use Rikkei\Core\View\CoreUrl;
    use Rikkei\Core\View\View;
    use Rikkei\Core\View\Form;
    use Rikkei\Team\View\Config;
    use Rikkei\ManageTime\Model\SupplementRegister;
    use Rikkei\ManageTime\Model\SupplementEmployee;
    use Rikkei\ManageTime\View\View as ManageView;

    $registerTable = SupplementRegister::getTableName();
    $registerEmployeeTbl = SupplementEmployee::getTableName();
    $employeeCreateTable = 'employee_table_for_creator';
    $startDateFilter = Form::getFilterData('except', "$registerEmployeeTbl.start_at");
    $endDateFilter = Form::getFilterData('except', "$registerEmployeeTbl.end_at");
    $approvedDateFilter = Form::getFilterData('except', "$registerTable.approved_at");

    $statusUnapprove = SupplementRegister::STATUS_UNAPPROVE;
    $statusApproved = SupplementRegister::STATUS_APPROVED;
    $statusDisapprove = SupplementRegister::STATUS_DISAPPROVE;
    $statusCancel = SupplementRegister::STATUS_CANCEL;
    $urlApprove = route('manage_time::profile.supplement.approve');
    $urlDisapprove = route('manage_time::profile.supplement.disapprove');
    $contentModalApprove = trans('manage_time::view.Do you want to approve the register of supplement?');
    $showButton = isset($isRelates) && $isRelates == true ? false : true;
    $header = isset($isRelates) ? trans('manage_time::view.Supplement relates to me') : (isset($isApprove) ? trans('manage_time::view.I approves supplement register') : trans('manage_time::view.Supplement register list'))
?>

@section('css-common')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" />
@endsection

@section('sidebar-common')
    @include('manage_time::include.sidebar_supplement')
@endsection

@section('content-common')
    <!-- Box approve list -->
    <div class="box box-primary">
        <div class="box-header filter-mobile-left">
            <div class="filter-panel-left pager-filter">
                <h3 class="box-title managetime-box-title">{{ $header }}</h3>
            </div>
            @include('manage_time::include.filter_approve')
            @if(Route::is('manage_time::profile.supplement.approve-list'))
                <div class="note">
                    <br>
                    {!! trans('manage_time::view.Late applications') !!}
                </div>
            @endif
        </div>
        <!-- /.box-header -->

        <div class="box-body no-padding">
            <div class="table-responsive">
                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table " id="managetime_table_fixed">
                    <thead class="managetime-thead">
                        <tr>
                            <th class="col-checkbox checkbox-all">
                                @if(isset($collectionModel) && count($collectionModel))
                                    <input type="checkbox" class="minimal" name="" value="">
                                @endif
                            </th>

                            <th class="col-no">{{ trans('manage_time::view.No.') }}</th>

                            <th class="sorting {{ Config::getDirClass('creator_name') }} col-title col-creator-name" data-order="creator_name" data-dir="{{ Config::getDirOrder('creator_name') }}">{{ trans('manage_time::view.Registrant') }}</th>

                            <th class="sorting {{ Config::getDirClass('created_at') }} col-title col-created-at" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('manage_time::view.Created at') }}</th>

                            <th class="sorting {{ Config::getDirClass('start_at') }} col-title col-date-start" data-order="start_at" data-dir="{{ Config::getDirOrder('start_at') }}">{{ trans('manage_time::view.From date') }}</th>

                            <th class="sorting {{ Config::getDirClass('end_at') }} col-title col-date-end" data-order="end_at" data-dir="{{ Config::getDirOrder('end_at') }}">{{ trans('manage_time::view.End date') }}</th>

                            <th class="sorting {{ Config::getDirClass('approved_at') }} col-title col-approved-at" data-order="approved_at" data-dir="{{ Config::getDirOrder('approved_at') }}">{{ trans('manage_time::view.Approval time') }}</th>

                            <th class="sorting {{ Config::getDirClass('number_days_supplement') }} col-title col-number-days-supplement" data-order="number_days_supplement" data-dir="{{ Config::getDirOrder('number_days_supplement') }}">{{ trans('manage_time::view.Number of days supplement') }}</th>

                            <th class="col-title col-reason">{{ trans('manage_time::view.Supplement reason') }}</th>

                            @if(isset($status))
                                <th class="col-title col-status">{{ trans('manage_time::view.Status') }}</th>
                            @else
                                <th class="sorting {{ Config::getDirClass('status') }} col-title col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}">{{ trans('manage_time::view.Status') }}</th>
                            @endif
                            
                            <th class="col-action-user"></th>
                        </tr>
                    </thead>
                </table>

                <table class="table table-striped dataTable table-bordered table-hover table-grid-data managetime-table-control supplement-list-tbl" id="managetime_table_primary">
                    <thead class="managetime-thead">
                        <tr>
                            <th class="managetime-col-20 checkbox-all col-checkbox" style="min-width: 20px;">
                                @if(isset($collectionModel) && count($collectionModel))
                                    <input type="checkbox" class="minimal" name="" value="">
                                @endif
                            </th>

                            <th class="managetime-col-25 col-no" style="min-width: 25px;">{{ trans('manage_time::view.No.') }}</th>

                            <th class="sorting {{ Config::getDirClass('creator_name') }} col-title managetime-col-75 col-creator-name" data-order="creator_name" data-dir="{{ Config::getDirOrder('creator_name') }}" style="min-width: 75px; max-width: 75px;">{{ trans('manage_time::view.Registrant') }}</th>

                            <th class="sorting {{ Config::getDirClass('created_at') }} col-title managetime-col-60 col-created-at" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Created at') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_start') }} col-title managetime-col-60 col-date-start" data-order="date_start" data-dir="{{ Config::getDirOrder('date_start') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.From date') }}</th>

                            <th class="sorting {{ Config::getDirClass('date_end') }} col-title managetime-col-60 col-date-end" data-order="date_end" data-dir="{{ Config::getDirOrder('date_end') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.End date') }}</th>

                            <th class="sorting {{ Config::getDirClass('approved_at') }} col-title managetime-col-60 col-approved-at" data-order="approved_at" data-dir="{{ Config::getDirOrder('approved_at') }}" style="min-width: 60px; max-width: 60px;">{{ trans('manage_time::view.Approval time') }}</th>

                            <th class="sorting {{ Config::getDirClass('number_days_supplement') }} col-title managetime-col-35 col-number-days-supplement" data-order="number_days_supplement" data-dir="{{ Config::getDirOrder('number_days_supplement') }}" style="min-width: 35px; max-width: 35px;">{{ trans('manage_time::view.Number of days supplement') }}</th>

                            <th class="col-title managetime-col-80 col-reason" style="min-width: 80px; max-width: 120px;">{{ trans('manage_time::view.Supplement reason') }}</th>

                            @if(isset($status))
                                <th class="col-title managetime-col-100 col-status" style="min-width: 100px; max-width: 100px;">{{ trans('manage_time::view.Status') }}</th>
                            @else
                                <th class="sorting {{ Config::getDirClass('status') }} col-title managetime-col-100 col-status" data-order="status" data-dir="{{ Config::getDirOrder('status') }}" style="min-width: 100px; max-width: 100px;">{{ trans('manage_time::view.Status') }}</th>
                            @endif
                            
                            <th class="managetime-col-40 col-action-user" style="min-width: 40px; max-width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="position_start_header_fixed">
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[{{ $employeeCreateTable }}.name]" value='{{ Form::getFilterData("{$employeeCreateTable}.name") }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerEmployeeTbl }}.start_at]" value='{{ $startDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerEmployeeTbl }}.end_at]" value='{{ $endDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="filter[except][{{ $registerTable }}.approved_at]" value='{{ $approvedDateFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control filter-date" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?php
                                            $numberDaysSupplementFilter = Form::getFilterData('except', "$registerTable.number_days_supplement");
                                        ?>
                                        <input type="text" name="filter[except][{{ $registerTable }}.number_days_supplement]" value='{{ $numberDaysSupplementFilter }}' placeholder="{{ trans('manage_time::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                                    </div>
                                </div>
                            </td>
                            <td>
                            </td>
                            @if(empty($status))
                                <td style="min-width: 100px; max-width: 100px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?php
                                                $filterStatus = Form::getFilterData('number', "supplement_registers.status");
                                            ?>
                                            <select name="filter[number][supplement_registers.status]" class="form-control select-grid filter-grid select-search" autocomplete="off" style="width: 100%;">
                                                <option value="">&nbsp;</option>
                                                <option value="{{ $statusUnapprove }}" <?php
                                                    if ($filterStatus == $statusUnapprove): ?> selected<?php endif; 
                                                        ?>>{{ trans('manage_time::view.Unapprove') }}
                                                </option>
                                                <option value="{{ $statusApproved }}" <?php
                                                    if ($filterStatus == $statusApproved): ?> selected<?php endif; 
                                                        ?>>{{ trans('manage_time::view.Approved') }}
                                                </option>
                                                <option value="{{ $statusDisapprove }}" <?php
                                                    if ($filterStatus == $statusDisapprove): ?> selected<?php endif; 
                                                        ?>>{{ trans('manage_time::view.Disapprove') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            @else
                                <td>&nbsp;</td>
                            @endif
                            <td>&nbsp;</td>
                        </tr>

                        <?php $currentPath = Route::currentRouteAction();?>
                        
                        @if(isset($collectionModel) && count($collectionModel))
                            <?php $i = View::getNoStartGrid($collectionModel); ?>
                            @foreach($collectionModel as $item)
                                <?php
                                    $tdClass = '';
                                    $status = '';
                                    if ($item->status == $statusApproved) {
                                        $tdClass = 'managetime-approved';
                                        $status = trans('manage_time::view.Approved');
                                    } elseif ($item->status == $statusDisapprove) {
                                        $tdClass = 'managetime-disapprove';
                                        $status = trans('manage_time::view.Disapprove');
                                    } else {
                                        $tdClass = 'managetime-unapprove';
                                        $status = trans('manage_time::view.Unapprove');
                                    }
                                ?>
                                <tr>
                                    <td class="{{ $tdClass }} text-center"><input type="checkbox" class="minimal" name="" value="{{ $item->register_id }}"></td>
                                    <td class="{{ $tdClass }}">{{ $i }}</td>
                                    <td class="{{ $tdClass }}">{{ $item->creator_name }}</td>
                                    <td class="{{ $tdClass }} created_at">{{ Carbon::parse($item->created_at)->format('d-m-Y H:i') }}</td>
                                    <td class="{{ $tdClass }} start_at">{{ Carbon::parse($item->start_at)->format('d-m-Y H:i') }}</td>
                                    <td class="{{ $tdClass }} end_at">{{ Carbon::parse($item->end_at)->format('d-m-Y H:i') }}</td>
                                    @if (isset($item->approved_at))
                                        <td class="{{ $tdClass }}">{{ Carbon::parse($item->approved_at)->format('d-m-Y H:i') }}</td>
                                    @else
                                        <td class="{{ $tdClass }}"></td>
                                    @endif
                                    <td class="{{ $tdClass }} number_days">{{ $item->number_days }}</td>
                                    <td class="{{ $tdClass }} managetime-read-more">{!! View::nl2br(ManageView::getSupplementReason($item)) !!}</td>
                                    <td class="{{ $tdClass }}">{{ $status }}</td>
                                    <td>
                                        <a class="btn btn-success" title="View detail" href="{{ route('manage_time::profile.supplement.detail', ['id' => $item->register_id]) }}" >
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php $i++; ?>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="9" class="text-center">
                                    <h2 class="no-result-grid">{{ trans('manage_time::view.No results found') }}</h2>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <!-- /.table -->

                <!-- Approve modal -->
                @include('manage_time::include.modal.modal_approve')

                <!-- Disapprove modal -->
                @include('manage_time::include.modal.modal_disapprove')

                <!-- No select modal -->
                @include('manage_time::include.modal.modal_noselect')
            </div>
            <!-- /.table-responsive -->
        </div>
        <!-- /.box-body -->

        <div class="box-footer no-padding">
            <div class="mailbox-controls">   
                @include('team::include.pager')
            </div>
        </div>
    </div>
    <!-- /. box -->
@endsection

@section('script-common')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/icheck.min.js"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/register.list.js') }}"></script>
    <script src="{{ CoreUrl::asset('asset_managetime/js/approve.list.js') }}"></script>
    
    <script type="text/javascript">
        var urlApprove = '{{ $urlApprove }}';
        var urlDisapprove = '{{ $urlDisapprove }}';

        var fixTop = $('#position_start_header_fixed').offset().top;
        $(window).scroll(function() {
            var scrollTop = $(window).scrollTop();
            if (scrollTop > fixTop) {
                var widthColCheckbox = $('#managetime_table_primary .col-checkbox').width();
                $('#managetime_table_fixed .col-checkbox').width(widthColCheckbox);

                var widthColNo = $('#managetime_table_primary .col-no').width();
                $('#managetime_table_fixed .col-no').width(widthColNo);

                var widthColCreatorName = $('#managetime_table_primary .col-creator-name').width();
                $('#managetime_table_fixed .col-creator-name').width(widthColCreatorName);

                var widthColDateStart = $('#managetime_table_primary .col-date-start').width();
                $('#managetime_table_fixed .col-date-start').width(widthColDateStart);

                var widthColEndStart = $('#managetime_table_primary .col-date-end').width();
                $('#managetime_table_fixed .col-date-end').width(widthColEndStart);

                var widthColApprovedAt = $('#managetime_table_primary .col-approved-at').width();
                $('#managetime_table_fixed .col-approved-at').width(widthColApprovedAt);

                var widthColNumberDaysSupplement = $('#managetime_table_primary .col-number-days-supplement').width();
                $('#managetime_table_fixed .col-number-days-supplement').width(widthColNumberDaysSupplement);

                var widthColReason = $('#managetime_table_primary .col-reason').width();
                $('#managetime_table_fixed .col-reason').width(widthColReason);

                var widthColStatus = $('#managetime_table_primary .col-status').width();
                $('#managetime_table_fixed .col-status').width(widthColStatus);

                var widthColAction = $('#managetime_table_primary .col-action-user').width();
                $('#managetime_table_fixed .col-action-user').width(widthColAction);
            }
        });
    </script>
@endsection
