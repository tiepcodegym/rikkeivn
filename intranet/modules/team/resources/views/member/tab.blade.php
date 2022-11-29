<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Core\View\CoreUrl;
use Rikkei\Test\View\ViewTest;

$isTabWork = ($statusWork == 'work');
$currentDay = date("Y-m-d");
$total = 0; //count item
?>
<div id="tab-content" class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data" style="min-height: 200px">
        <thead>
            <tr>
                <th><input type="checkbox" class="check-all" id="tbl_check_all" data-list=".table-check-list"></th>
                <th style="width: 20px" class="col-id">{{ trans('core::view.NO.') }}</th>
                <th>{{ trans('team::view.Avatar_en') }}</th>
                <th style="width: 50px" class="sorting {{ Config::getDirClass('employee_code', $urlFilter) }} col-id" data-order="employee_code" data-dir="{{ Config::getDirOrder('employee_code', $urlFilter) }}">{{ trans('team::view.Code') }}</th>
                <th class="sorting {{ Config::getDirClass('name', $urlFilter) }} col-name" data-order="name" data-dir="{{ Config::getDirOrder('name', $urlFilter) }}">{{ trans('team::view.Name') }}</th>
                <th class="sorting {{ Config::getDirClass('email', $urlFilter) }} col-name" data-order="email" data-dir="{{ Config::getDirOrder('email', $urlFilter) }}">{{ trans('team::view.Email') }}</th>
                <th class="sorting {{ Config::getDirClass('role_name', $urlFilter) }} col-name" data-order="role_name" data-dir="{{ Config::getDirOrder('role_name', $urlFilter) }}" style="width: 135px;">{{ trans('team::view.Position') }}</th>
                <th class="sorting {{ Config::getDirClass('join_date', $urlFilter) }} col-name" data-order="join_date" data-dir="{{ Config::getDirOrder('join_date', $urlFilter) }}">{{ trans('team::view.Join date') }}</th>
                <th class="sorting {{ Config::getDirClass('offcial_date', $urlFilter) }} col-name" data-order="offcial_date" data-dir="{{ Config::getDirOrder('offcial_date', $urlFilter) }}">{{ trans('team::view.Offcial date') }}</th>
                <th width="60" class="sorting {{ Config::getDirClass('leave_date', $urlFilter) }} col-name" data-order="leave_date" data-dir="{{ Config::getDirOrder('leave_date', $urlFilter) }}">{{ trans('team::view.Leave date') }}</th>
                <th>{{ trans('team::profile.Contract type') }}</th>
                <th style="width: 90px;">{{ trans('team::view.Roles') }}</th>
                <th style="width: 110px;">{{ trans('team::view.Skillsheet') }}</th>
                <th style="width: 82px;">&nbsp;</th>
            </tr>
        </thead>
        <tbody class="checkbox-list table-check-list" data-all="#tbl_check_all" data-export="">
            <tr class="filter-input-grid">
                <td></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[employee_code]" value="{{ Form::getFilterData('employee_code', null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[{{ $employeeTableAs }}.name]" value="{{ Form::getFilterData("{$employeeTableAs}.name", null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[{{ $employeeTableAs }}.email]" value="{{ Form::getFilterData("{$employeeTableAs}.email", null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>&nbsp;</td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[{{ $employeeTableAs }}.join_date]" value="{{ Form::getFilterData("{$employeeTableAs}.join_date", null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[{{ $employeeTableAs }}.offcial_date]" value="{{ Form::getFilterData("{$employeeTableAs}.offcial_date", null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="text" name="filter[{{ $employeeTableAs }}.leave_date]" value="{{ Form::getFilterData("{$employeeTableAs}.leave_date", null, $urlFilter) }}" placeholder="{{ trans('team::view.Search') }}..." class="filter-grid form-control" autocomplete="off" />
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12 filter-multi-select multi-select-style">
                            <?php
                            $filterContractType = (array) Form::getFilterData('in', "{$employeeWorkTbl}.contract_type", $urlFilter);
                            ?>
                            <select name="filter[in][{{ $employeeWorkTbl }}.contract_type][]" class="form-control multi-select-bst filter-grid hidden select-multi" multiple="multiple">
                                @foreach($optionsWorkContract as $key => $value)
                                    <option value="{{ $key }}" {{ in_array($key, $filterContractType) ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12 filter-multi-select multi-select-style">
                            <?php
                            $filterRoles = (array) Form::getFilterData('in', "{$roleSpecialTabelAs}.id", $urlFilter);
                            ?>
                            <select name="filter[in][{{ $roleSpecialTabelAs }}.id][]" class="form-control multi-select-bst filter-grid hidden js-select-multi-role" multiple="multiple">
                                @foreach($optionRoles as $key => $value)
                                    <option value="{{ $key }}" {{ in_array($key, $filterRoles) ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            $filterStatusSkillSheet = Form::getFilterData('except', "status", $urlFilter);
                            ?>
                            <select name="filter[except][status]" class="form-control select-grid filter-grid select-search" autocomplete="off">
                                <option>&nbsp;</option>
                                @foreach($optionStatusSkillSheet as $key => $value)
                                <option value="{{ $key }}"<?php if ($key == $filterStatusSkillSheet): ?> selected<?php endif;
                            ?>>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </td>
                <td>&nbsp;</td>
            </tr>
            @if($collectionModel)
            <?php $i = View::getNoStartGrid($collectionModel); ?>
            @foreach($collectionModel as $item)
                <?php
                $getShortContent = View::getShortContent($item->role_special, 5);
                ?>
                <tr>
                <td><input type="checkbox" class="check-item" value="{{ $item->id }}"></td>
                <td>{{ $i }}</td>
                <td><img width="75" class="img-responsive img-circle" src="{{ $item->getAvatarUrl() }}"></td>
                <td>{{ $item->employee_code }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->role_name }}</td>
                <td class="white-space-nowrap">{{ $item->join_date }}</td>
                <td class="white-space-nowrap">{{ empty($item->offcial_date) || $item->offcial_date == '0000-00-00' ? '' : $item->offcial_date }}</td>
                <td>{{ $item->leave_date }}</td>
                <td>{{ empty($item->contract_type) ? '' : $optionsWorkContract[$item->contract_type] }}</td>
                <td class="q_content_toggle">
                    @if (!$getShortContent['has_more'])
                        <div class="q_content">{!! $getShortContent['short_content'] !!}</div>
                    @else
                        <div class="content_short">
                            <div class="q_content">{!! $getShortContent['short_content'] !!}...</div>
                        </div>
                        <div class="content_full">
                            <div class="q_content">{!! $item->role_special !!}</div>
                        </div>
                        <a href="#" class="link q_view_more"
                           data-short-text="{{ trans('test::test.view_short') }}"
                           data-full-text="{{ trans('test::test.view_more') }}">[{{ trans('test::test.view_more') }}
                            ]</a>
                    @endif
                </td>
                <td>
                    <span class="label {{ EmplCvAttrValue::getLabelByStatus($item->valSkillSheet) }}" style="display: block; width: 70px; margin: auto;">{{ EmplCvAttrValue::getValueOfEmp($item) }}</span>
                </td>
                <td class="white-space-nowrap">
                    <a href="{{ route('team::member.profile.index', ['id' => $item->id, 'type' => 'cv']) }}" title="View Skillsheet" target="_blank" class="btn-edit {!! $displayButtonViewSkill !!}" style="background-color:#305E8E; border-color: #305E8E;">
                        <i class="fa fa-file"></i>
                    </a>
                    <a href="{{ route('team::member.profile.index', ['id' => $item->id ]) }}" class="btn-edit {!! $displayButtonViewProfile !!}">
                        <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php $i++; ?>
            @endforeach
            @else
            <tr>
                <td colspan="12" class="text-center">
                    <h2 class="no-result-grid">{{trans('core::view.No results found')}}</h2>
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
<div class="box-body">
    @include('team::include.pager', ['urlSubmitFilter' => $urlFilter])
</div>
