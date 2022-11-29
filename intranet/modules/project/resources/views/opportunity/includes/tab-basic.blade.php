<?php
use Rikkei\Project\Model\Opportunity as Project;

$arrayTypeMM = Project::arrayTypeMM();
?>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{ trans('project::view.Opportunity Name') }} <em>*</em></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" id="name" name="proj[name]" placeholder="{{ trans('project::view.Opportunity Name') }}"
                           value="{{ old('proj.name') ? old('proj.name') : ($project ? $project->name : null) }}">
                </div>
            </div>

            <div class="form-group col-sm-6" id="select-sales">
                <label class="col-sm-4 control-label required">{{ trans('project::view.Salesperson') }} <em>*</em></label>
                <div class="col-sm-8">
                    <select id="sale_id" class="form-control select-search-employee"
                            name="sale_id[]" multiple data-remote-url="{{ URL::route('team::employee.list.search.ajax') }}">
                        @if (!$projectSales->isEmpty())
                            @foreach ($projectSales as $sale)
                            <option value="{{ $sale->id }}" selected>{{ ucfirst(preg_replace('/\@.*/', '', $sale->email)) }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="form-group col-sm-6" id="input-production-cost">
                <label class="col-sm-4 pd-md-0 control-label">{{ trans('project::view.Approved production cost') }}</label>
                <div class="col-sm-8">
                    <?php
                    $projTypeMM = old('proj.type_mm') ? old('proj.type_mm') : ($project ? $project->type_mm : null);
                    ?>
                    <div class="input-group input-group-select">
                        <input type="text" class="form-control" id="cost_approved_production" name="quality[cost_approved_production]" 
                               placeholder="{{ trans('project::view.Approved production cost') }}"
                               value="{{ old('quality.cost_approved_production') ? old('quality.cost_approved_production') : ($quality ? $quality->cost_approved_production : null) }}">
                        <span class="input-group-addon">
                            <select class="select-addon select-same select-search" data-same="type_mm" name="proj[type_mm]">
                                @foreach ($arrayTypeMM as $value => $label)
                                <option value="{{ $value }}" {{ $value == $projTypeMM ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group col-sm-6" id="input-billable-effort">
                <label class="col-sm-4 control-label">{{ trans('project::view.Billable Effort') }}</label>
                <div class="col-sm-8">
                    <div class="input-group input-group-select">
                        <input type="text" class="form-control" id="billable_effort" name="quality[billable_effort]" 
                               placeholder="{{ trans('project::view.Billable Effort') }}"
                               value="{{ old('quality.billable_effort') ? old('quality.billable_effort') : ($quality ? $quality->billable_effort : null) }}">
                        <span class="input-group-addon">
                            <select class="select-addon select-same select-search" data-same="type_mm" name="proj[type_mm]">
                                @foreach ($arrayTypeMM as $value => $label)
                                <option value="{{ $value }}" {{ $value == $projTypeMM ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="form-group col-sm-6" id="input-approved-cost">
                <label class="col-sm-4 control-label">{{ trans('project::view.Approved cost') }}</label>
                <div class="col-sm-8">
                    <div class="input-group input-group-select">
                        <input type="text" class="form-control" id="approved_cost" name="quality[approved_cost]" 
                               placeholder="{{ trans('project::view.Approved cost') }}"
                               value="{{ old('quality.approved_cost') ? old('quality.approved_cost') : ($quality ? $quality->approved_cost : null) }}">
                        <span class="input-group-addon">
                            <select class="select-addon select-same select-search" data-same="type_mm" name="proj[type_mm]">
                                @foreach ($arrayTypeMM as $value => $label)
                                <option value="{{ $value }}" {{ $value == $projTypeMM ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="form-group col-sm-6" id="select-team">
                <label for="team_id" class="col-sm-4 control-label required">{{ trans('project::view.Group') }} <em>*</em></label>
                <div class="col-sm-8">
                    <div class="dropdown team-dropdown">
                        <select id="team_id" class="form-control team-dev-tree multiselect2" name="team_id[]" multiple></select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{ trans('project::view.Start Date') }} <em>*</em></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control date-picker" id="start_at" name="proj[start_at]" placeholder="YY-MM-DD"
                           value="{{ old('proj.start_at') ? old('proj.start_at') : ($project ? $project->start_at : null) }}">
                </div>
            </div>

            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label required">{{ trans('project::view.End Date') }} <em>*</em></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control date-picker" id="end_at" name="proj[end_at]" placeholder="YY-MM-DD"
                           value="{{ old('proj.end_at') ? old('proj.end_at') : ($project ? $project->end_at : null) }}">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 form-group" id="select-prog_langs">
                <label for="prog_langs" class="col-sm-4 control-label margin-top--10">{{ trans('project::view.Programming language') }}</label>
                <div class="col-sm-8">
                    <div class="dropdown team-dropdown">
                        <select id="prog_langs" class="form-control multiselect2" name="prog_langs[]" multiple>
                            @if ($programsOption)
                                @foreach ($programsOption as $value => $label)
                                <option value="{{ $value }}" {{ in_array($value, $projectPrograms) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group col-sm-6">
                <label class="col-sm-4 control-label">{{ trans('project::view.Customer') }}</label>
                <div class="col-sm-8">
                    <select name="proj[cust_contact_id]" class="select-search" id="cust_contact_id"
                        data-remote-url="{{ URL::route('sales::search.ajax.customer') }}">
                        @if ($customer)
                        <option value="{{ $customer->id }}" selected>{{ $customer->name }}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6 form-group" id="input-project-note">
                <label class="col-sm-4 control-label">{{ trans('project::view.Note') }}</label>
                <div class="col-sm-8">
                    <textarea class="form-control white-space-preline note-desc" id="description" name="proj[description]"
                              placeholder="{{ trans('project::view.Note') }}" rows="4"
                              >{{ old('proj.description') ? old('proj.description') : ($project ? $project->description : null) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

