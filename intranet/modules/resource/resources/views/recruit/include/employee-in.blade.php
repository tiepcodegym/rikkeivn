<?php
use Rikkei\Core\View\Form as FormView;
use Carbon\Carbon;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;

$roles = getOptions::getInstance()->getRoles();
$workingtypeOptions = getOptions::listWorkingTypeInternal();
$types = Candidate::listTypes();
?>
<div class="row tab-pane active" id="tab_employeein">
    <div class="nopadding-col">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover table-grid-data">
                <thead>
                    <tr class="bg-light-blue">
                        <th style="width: 3%">{{ trans('core::view.NO.') }}</th>
                        <th style="width: 16%">{{ trans('resource::view.Name join') }}</th>
                        <th style="width: 12%">{{ trans('resource::view.Team') }}</th>
                        <th style="width: 6%">{{ trans('resource::view.Date join') }}</th>
                        <th class="nowwrap">{{ trans('resource::view.Request.Create.Programming languages') }}</th>
                        <th style="width: 15%">{{ trans('resource::view.Email') }}</th>
                        <th style="width: 11%">{{ trans('resource::view.Channel') }}</th>
                        <th style="width: 11%">{{ trans('resource::view.Channel Details') }}</th>
                        <th style="width: 11%">{{ trans('resource::view.Contract Type') }}</th>
                        <th style="width: 15%">{{ trans('resource::view.Contract Length') }}</th>
                        <th style="width: 15%">{{ trans('resource::view.Type') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[candidate][name]" value="{{ FormView::getFilterData('candidate', 'name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[candidate][email]" value="{{ FormView::getFilterData('candidate', 'email') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[candidate][channel]" value="{{ FormView::getFilterData('candidate', 'channel') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[candidate][presenter]" value="{{ FormView::getFilterData('candidate', 'presenter') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td>
                            <select style="width: 100%" name="filter[candidate][working_type]" class="form-control select-grid filter-grid select-search">
                                <option value="">&nbsp;</option>
                                @foreach ($workingtypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ ($value == $cddWorkingTypeFilter) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>                            
                        </td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[candidate][contract_length]" value="{{ FormView::getFilterData('candidate', 'contract_length') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td>
                            <select style="width: 100%" name="filter[candidate][type]" class="form-control select-grid filter-grid select-search">
                                <option value="">&nbsp;</option>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}"{!! ($value == $cddTypeFilter) ? ' selected' : '' !!}>{{ $label }}</option>
                                @endforeach
                            </select>                            
                        </td>
                    </tr>
                    @if(!$collectionModel->isEmpty())
                    <?php
                    $joinPerPage = $collectionModel->perPage();
                    $joinCurrentPage = $collectionModel->currentPage();
                    ?>
                    @foreach($collectionModel as $stt => $item)
                    <tr>
                        <td>{{ ($stt + 1 + ($joinCurrentPage - 1) * $joinPerPage) }}</td>
                        <td class="nowwrap">
                            @if ($item['type'] == 1)
                            <a target="_blank" href="{{ route('team::member.profile.index', $item['id']) }}">{{ $item['name'] }}</a>
                            @else
                            <a target="_blank" href="{{ route('resource::candidate.detail', ['id' => $item['id']]) }}">{{ $item['name'] }}</a>
                                @if (!isset($isTrainee))
                                <br /> <small>(<i>{{ trans('resource::view.Candidate.Detail.Candidate') }}</i>)</small>
                                @endif
                            @endif
                        </td>
                        <td>{{ $item['team_names'] ? $item['team_names'] : 'Others' }}</td>
                        <td>{{ Carbon::parse($item['join_date'])->toDateString() }}</td>
                        <td>{!! getOptions::getInstance()->getProgOrPosName($item['prog_id'], $programs, $roles) !!}</td>
                        <td>{{ $item['email'] }}</td>
                        <td>{{ $item['cnname'] }}</td>
                        <td>{{ $item['empname'] }}</td>
                        <td>{{ getOptions::getWorkingTypeLabel($item['working_type']) }}</td>
                        <td>{{ $item['contract_length'] }}</td>
                        <td>{{ Candidate::getType($item['level_type']) }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="12" class="text-center"><h3>{{ trans('resource::message.No data') }}</h3></td>
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
