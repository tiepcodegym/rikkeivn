<?php
use Rikkei\Core\View\Form as FormView;
use Carbon\Carbon;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;

$workingtypeOptions = getOptions::listWorkingTypeInternal() + getOptions::listWorkingTypeExternal();
$routeMember = 'team::member.profile.index';
$types = Candidate::listTypes();
?>
<div class="row tab-pane active"  id="tab_employeeout">
    <div class="nopadding-col">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover table-grid-data">
                <thead>
                    <tr class="bg-light-blue">
                        <th style="width: 3%">{{ trans('core::view.NO.') }}</th>
                        <th style="width: 15%">{{ trans('resource::view.Name leave') }}</th>
                        <th style="width: 13%">{{ trans('resource::view.Team') }}</th>
                        <th style="width: 10%">{{ trans('resource::view.Date leave') }}</th>
                        <th style="width: 24%">{{ trans('resource::view.Reason') }}</th>
                        <th style="width: 11%">{{ trans('resource::view.Contract Type') }}</th>
                        <th style="width: 11%">{{ trans('resource::view.Contract Length') }}</th>
                        <th style="width: 8%">{{ trans('resource::view.Statistics.Status') }}</th>
                        <th style="width: 5%" class="nowwrap">{{ trans('resource::view.Statistics.Leader Approve') }}</th>
                        <th style="width: 15%">{{ trans('resource::view.Type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[employee][name]" value="{{ FormView::getFilterData('employee', 'name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <select style="width: 100%" name="filter[employee][working_type]" class="form-control select-grid filter-grid select-search">
                                <option value="">&nbsp;</option>
                                @foreach ($workingtypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($value == $empWorkingTypeFilter) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>                            
                        </td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[employee][contract_length]" value="{{ FormView::getFilterData('candidate', 'contract_length') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td></td>
                        <td></td>
                        <td>
                            <select style="width: 100%" name="filter[candidate][type]" class="form-control select-grid filter-grid select-search">
                                <option value="">&nbsp;</option>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}"{!! ($value == $cddTypeFilter) ? ' selected' : '' !!}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @if (!$collectionModel->isEmpty())
                    <?php
                    $leavePerPage = $collectionModel->perPage();
                    $leaveCurrentPage = $collectionModel->currentPage();
                    ?>
                    @foreach($collectionModel as $stt => $item)
                    <tr>
                        <td>{{ ($stt + 1 + ($leaveCurrentPage - 1) * $leavePerPage) }}</td>
                        <td class="nowwrap">
                            @if ($item->type == 1)
                            <a target="_blank" href="{{ route($routeMember, $item->id) }}">{{ $item->name }}</a>
                            @else
                            <a target="_blank" href="{{ route('resource::candidate.detail', ['id' => $item->id]) }}">{{ $item->name }}</a>
                            <br /> <small>(<i>{{ trans('resource::view.Candidate.Detail.Candidate') }}</i>)</small>
                            @endif
                        </td>
                        <td>{{ $item->team_names ? $item->team_names : 'Others' }}</td>
                        <td>{{ Carbon::parse($item->leave_date)->toDateString() }}</td>
                        <td class="prewrap">{{ $item->leave_reason }}</td>
                        <td>{{ getOptions::getWorkingTypeLabel($item['working_type']) }}</td>
                        <td>{{ $item['contract_length'] }}</td>
                        <td class="account-status" data-account-status="{{$item->account_status}}" data-select="0" data-id="{{$item->id}}">
                            {{$item->getAccountStatus()}}
                        </td>
                        <td class="text-align-center">
                            <input type="checkbox" class="leader-check" data-id="{{$item->id}}" 
                                @if($item->leader_approved) 
                                    checked
                                @endif
                            >
                        </td>
                        <td>{{ Candidate::getType($item['level_type']) }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="9" class="text-center"><h3>{{ trans('resource::message.No data') }}</h3></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="box-body">
            @include('resource::recruit.paginate')
        </div>
    </div>
</div>
