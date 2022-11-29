<?php
use Rikkei\Core\View\Form as FormView;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

$roles = getOptions::getInstance()->getRoles();
$listResults = getOptions::getInstance()->listArrayResults();
$listResults[getOptions::RESULT_DEFAULT] = trans('resource::view.Offering');
?>

<div class="row tab-pane active" id="tab_employeein">
    <div class="nopadding-col">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover table-grid-data">
                <thead>
                    <tr class="bg-light-blue">
                        <th>{{ trans('core::view.NO.') }}</th>
                        <th>{{ trans('resource::view.Test.Fullname') }}</th>
                        <th>{{ trans('resource::view.Email') }}</th>
                        <th>{{ trans('resource::view.Team') }}</th>
                        <th>{{ trans('resource::view.Offer date') }}</th>
                        <th width="120">{{ trans('resource::view.Test.Result') }}</th>
                        <th class="nowwrap">{{ trans('resource::view.Request.Create.Programming languages') }} / {{ trans('resource::view.Position apply') }}</th>
                        <th>{{ trans('resource::view.Channel') }}</th>
                        <th>{{ trans('resource::view.Channel Details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td></td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[{{ $cddTbl }}.fullname]" value="{{ FormView::getFilterData($cddTbl . '.fullname') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[{{ $cddTbl }}.email]" value="{{ FormView::getFilterData($cddTbl . '.email') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td></td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[{{ $cddTbl }}.offer_date]" value="{{ FormView::getFilterData($cddTbl.'.offer_date') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td>
                            <?php $offerDateResult = FormView::getFilterData('excerpt', 'result'); ?>
                            <select style="min-width: 80px;" name="filter[excerpt][result]" class="form-control select-grid filter-grid select-search">
                                <option value="">&nbsp;</option>
                                @foreach ($listResults as $value => $label)
                                <option value="{{ $value }}" {{ is_numeric($offerDateResult) && $offerDateResult == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td></td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[channel.name]" value="{{ FormView::getFilterData('channel.name') }}" placeholder="{{ trans('team::view.Search') }}..." />
                        </td>
                        <td>
                            <input type="text" class='form-control filter-grid' name="filter[{{ $cddTbl }}.presenter]" value="{{ FormView::getFilterData($cddTbl . '.presenter') }}" placeholder="{{ trans('team::view.Search') }}..." />
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
                            <a target="_blank" href="{{ route('resource::candidate.detail', ['id' => $item->id]) }}">{{ $item->fullname }}</a>
                        </td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->team_names ? $item->team_names : 'Others' }}</td>
                        <td>{{ $item->plan_date }}</td>
                        <td>
                            @if (isset($listResults[$item->result]))
                            {{ $listResults[$item->result] }}
                            @endif
                        </td>
                        <td>{!! getOptions::getInstance()->getProgOrPosName($item->prog_id, $programs, $roles) !!}</td>
                        <td>{{ $item->cnname }}</td>
                        <td>{{ $item->emname }}</td>
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
