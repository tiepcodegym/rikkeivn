<div class="box-body scrolls">
    <div class="row" style="margin: 0px !important;">
        <div class="form-group col-sm-12 col-md-4">
            <label class="col-sm-4 control-label required">{{trans('project::view.Project Name')}} <em>*</em></label>
            <div class="col-sm-8">
                <input type="text" class="form-control" id="name" name="name" data-name-old="{{$projectName}}"
                       placeholder="{{trans('project::view.Project name')}}" value="{{$projectName}}">
                <label class="name-error labl-error error" for="name"></label>
            </div>
        </div>
        <div class="form-group col-sm-12 col-md-4">
            <label class="col-sm-4 control-label required">{{trans('project::view.Project kind')}} <em>*</em></label>
            <div class="col-sm-8">
                <select name="type" class="form-control kind_id" data-type-old="{{$kindProject}}">
                    @foreach($labelKindProject as $key => $value)
                        <option value="{{$key}}" {{$kindProject == $key ? 'selected' : '' }}>{{$value}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group col-sm-12 col-md-4">
            <label class="col-sm-4 control-label required">{{trans('project::view.Project Type')}} <em>*</em></label>
            <div class="col-sm-8">
                <select name="type" class="form-control type" data-type-old="{{$typeProject}}">
                    @foreach($labelTypeProject as $key => $value)
                        <option value="{{$key}}" {{$typeProject == $key ? 'selected' : '' }}>{{$value}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row" style="margin: 0px !important;">
        <div class="col-sm-2">&ensp;</div>
        <div class="col-sm-8 group-error">
            <label class="error-input-mess labl-error error"></label>
        </div>
        <div class="col-md-2">&ensp;</div>
    </div>
    <div class="row" style="margin: 0px !important;">
        <div class="col-sm-12">
            <table id="tblOperationBody" class="table  dataTable table-bordered  table-grid-data">
                <thead>
                <tr>
                    <th style="min-width: 100px;" class="col-month required">{{ trans('project::me.Month') }}<em>*</em></th>
                    <th style="min-width: 50px"
                        class="col-cost required">{{ trans('project::view.Approved production cost') }}<em>*</em></th>
                    <th style="min-width: 150px;" class="col-team required">{{trans('project::view.Group')}}<em>*</em></th>
                    <th style="min-width: 150px;" class="col-team ">{{trans('project::view.Note')}}</th>
                    <th style="min-width: 150px;" class="col-price required">{{trans('project::view.Approved production price')}}<em>*</em></th>
                    <th style="min-width: 100px;" class="col-price required">{{trans('project::view.Approved production unit price')}}<em>*</em></th>
                    <th style="min-width: 10px;">&ensp;</th>
                    <th style="min-width: 10px;">&ensp;</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $tabIndex = 1;
                @endphp
                @foreach($dataUsingForView as $month => $itemsEachMonth)
                    @php
                        $rowSpan = count($itemsEachMonth);
                        $isFirstRow = false;
                        $dataRowIndex = 1;
                        $isLastRow = false;
                    @endphp
                    @foreach ($itemsEachMonth as $item)
                        @php
                            $isLastRow = $dataRowIndex == $rowSpan;
                            $id = $tabIndex . ($dataRowIndex > 1 ? '_' . $dataRowIndex : '');
                        @endphp
                        <tr data-row="{{$dataRowIndex}}" tabindex="{{$tabIndex}}"
                            class="{{!$isFirstRow ? 'tblDetailInput' : ''}} {{$tabIndex % 2 ? 'table-css-no-active' : 'table-css-active'}}">
                            @if(!$isFirstRow)
                                <td rowspan="{{$rowSpan}}"><input type="text" id="activity_month_from{{$id}}"
                                                                  name="month"
                                                                  class="form-control form-inline month-picker maxw-100"
                                                                  value="{{$month}}" autocomplete="off"></td>
                            @endif
                            <td class="approved-cost-wrapper"><input type="number" step="any" min="0"
                                                                     class="form-control "
                                                                     id="cost_approved_production{{$id}}"
                                                                     name="cost_approved_production{{$id}}"
                                                                     value="{{$item->approved_production_cost}}">
                            </td>

                                <td>
                                <div class="dropdown team-dropdown">
                                    <select id="team-group-{{$id}}"  class="project-future-team-member form-control select-search has-search" data-team="dev">
                                        <option value="0">{{trans('resource::view.Dashboard.Choose group')}}</option>
                                            @foreach($teamsOptionAll as $option)
                                                @if ($teamIdsAvailable === true || in_array($option['value'], $teamTreeAvailable))
                                                    <option value="{{ $option['value'] }}"
                                                                <?php if ($option['is_soft_dev'] != \Rikkei\Team\Model\Team::IS_SOFT_DEVELOPMENT): ?> disabled <?php endif; ?>
                                                                @if ($option['value'] == $item->team_id) selected @endif
                                                        >
                                                        {{ $option['label'] }}
                                                    </option>
                                                @endif
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="operation-note-input"><textarea rows="1"
                                                                            class="non-required note_item form-control"
                                                                            id="approve_cost_note{{$id}}"
                                                                            name="approve_cost_note{{$id}}">{{$item->note}}</textarea>
                                </div>
                            </td>
                            <td>
                                @php
                                    $conditionDisplayDefaultPrice = ($item->price == 0 || $item->price == '' || is_null($item->price))
                                @endphp
                                <div class="operation-price-input"><input type="number" step="any" min="0"
                                                                          class="form-control "
                                                                          id="price{{$id}}"
                                                                          name="price{{$id}}"
                                                                          value="{{$conditionDisplayDefaultPrice ? 30000000 : $item->price}}">
                                </div>
                            </td>
                            <td>
                                <div class="dropdown team-dropdown">
                                    <select id="unit_price{{$id}}" >
                                        @foreach($unitPrices as $key => $value)
                                            <option value="{{ $key }}" @if ($key == $item->unit_price) selected @endif
                                            >
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td><span href="#" style="color: seagreen" class="btn-add-row {{$isLastRow ? '' : 'hidden'}}"><i class="fa fa-plus"></i></span></td>
                            <td><span href="#" style="color: #d33724" class="btn-remove-row"><i class="fa fa-minus"></i></span></td>
                        </tr>
                        @php
                            $dataRowIndex++;
                            $isFirstRow = true;
                        @endphp
                    @endforeach
                    @php
                        $tabIndex++;
                    @endphp
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row" style="position: relative; margin: 0px">
        <div class="col-sm-8" style="height: 50px;">
            <div class="button-add">
                <span href="#" class="btn-add btn-operation-project add-operation-project"><i
                            class="fa fa-plus"></i></span>
            </div>
        </div>
    </div>
</div>
