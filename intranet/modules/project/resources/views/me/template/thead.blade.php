<?php
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
$evalTable = MeEvaluation::getTableName();
?>
<thead>
    <tr>
        @if (isset($checkbox) && $checkbox)
        <th class="fixed-col"><input type="checkbox" class="_check_all"></th>
        @endif
        @if (!isset($staff_view))
        <th width="25" class="fixed-col">ID</th>
        @endif
        @if (isset($has_month) && $has_month)
        <th width="65" @if(!isset($staff_view)) class="fixed-col" @endif >{{trans('project::me.Month')}}</th>
        @endif
        @if (!isset($staff_view))
        <th class="fixed-col">{{trans('project::me.Account')}}</th>
        @endif
        @if (!isset($create_team))
        <th>{{trans('project::me.Project name')}}</th>
        @endif
        @if (isset($is_leader_view) || isset($is_review_team) || isset($staff_view))
        <th>{{ trans('project::me.Project type') }}</th>
        @endif
        <?php 
            $normal_weight = 0;
        ?>
        @if (!$normalAttrs->isEmpty())
            @foreach($normalAttrs as $attr)
                <?php $normal_weight += $attr->weight; ?>
                <th width="70" class="tooltip_group attr_normal" data-related-col="summary">
                    <span>{{$attr->label}}</span>
                    <i class="fa fa-question-circle"></i>
                    <div class="me_tooltip minw-335">
                        <p>{{ $attr->name }}</p>
                        <div>{!! $attr->description !!}</div>
                    </div>
                </th>
            @endforeach
        @endif
        
        <th data-name="avg-rules" width="70" class="tooltip_group cal-value _none" data-related-col="summary">
            <i class="fa fa-question-circle"></i>
            <p class="th_avg_rule_val" data-weight="{{ $normal_weight }}">({{ $normal_weight }}%)</p>
        </th>
        
        <?php 
            $perform_weight = 0;
        ?>
        @if (!$performAttrs->isEmpty())
            @foreach($performAttrs as $attr)
                <?php $perform_weight += $attr->weight; ?>
                <th width="70" class="tooltip_group attr_perform" data-related-col="summary">
                    <span>{{$attr->label}}</span>
                    <i class="fa fa-question-circle"></i>
                    <div class="me_tooltip minw-335">
                        <p>{{ $attr->name }}</p>
                        <div>{!! $attr->description !!}</div>
                    </div>
                </th>
            @endforeach
        @endif
        
        <th data-name="individual-index" class="tooltip_group attr_perform cal-value _none" data-related-col="summary" width="50">
            <span>{!! trans('project::me.Individual index') !!}</span>
            <i class="fa fa-question-circle"></i>
            <p class="th_individual_val" data-weight="{{ $perform_weight }}">({{ $perform_weight }}%)</p>
            <span class="me_tooltip">
                {!! trans('project::me.Individual index') !!}
            </span>
        </th>
        <th width="50" class="attr_normal tooltip_group" data-related-col="summary">
            <span>{!! trans('project::me.Project point') !!}</span>
            <i class="fa fa-question-circle"></i>
            <span class="me_tooltip text-center">
                {!! trans('project::me.Project point detail') !!}
                @if (isset($create_team))
                <br />
                {{ trans('project::me.Avg project joined') }}
                @endif
            </span>
        </th>
        <th class="tooltip_group attr_normal proj_index_col" width="50" data-related-col="summary">
            <span>{!! trans('project::me.Project Type Factor') !!}</span>
            <i class="fa fa-question-circle"></i>
            <span class="me_tooltip text-center">
                {!! trans('project::me.Project index detail') !!}
                @if (isset($create_team))
                <br />
                {{ trans('project::me.Avg project joined') }}
                @endif
            </span>
        </th>
        <th data-name="performance" class="tooltip_group cal-value _none" data-related-col="summary" width="50">
            <i class="fa fa-question-circle"></i>
            <p class="th_perform_val" data-weight="{{ 100 - $normal_weight - $perform_weight }}">({{100 - $normal_weight - $perform_weight}}%)</p>
        </th>
        <th data-name="summary" width="50" class="tooltip_group cal-value attr_perform">
            <span>{{trans('project::me.Summary')}}</span>
            <i class="fa fa-question-circle"></i>
            <span class="me_tooltip text-center">{{trans('project::me.Summary')}}</span>
        </th>
        <th width="50" class="tooltip_group attr_perform" style="min-width: 55px;">
            <span>{{trans('project::me.Effort in Project')}}</span>
            <i class="fa fa-question-circle"></i>
            <p>{{trans('project::me.days')}}</p>
            <span class="me_tooltip">
                {!! trans('project::me.Work day in this project') !!}
            </span>
        </th>
        <th @if(!isset($sort_contri) || !$sort_contri)  @else style="min-width: 110px;" @endif width="65" class="tooltip_group _word_break attr_perform">
            <span>{{trans('project::me.Contribution level')}}</span>
            <i class="fa fa-question-circle"></i>
            <span class="me_tooltip text-center">{{trans('project::me.Contribution level')}}</span>
            <div class="sorting sort_static {{ Config::getDirClass('avg_point') }} col-name" data-order="avg_point" data-dir="{{ Config::getDirOrder('avg_point') }}"></div>
        </th>
        <th width="65" class="tooltip_group _bd_right attr_normal">
            <span>{{trans('project::me.Note')}}</span>
            <i class="fa fa-question-circle"></i>
            <span class="me_tooltip text-center">{{trans('project::me.Note')}}</span>
        </th>
        <th @if(!isset($sort_contri) || !$sort_contri)  @else style="min-width: 90px;" @endif class="attr_normal tooltip_group">
             {{ trans('project::me.Status') }}
             <div class="sorting sort_static {{ Config::getDirClass('status') }} col-name" data-order="status" data-dir="{{ Config::getDirOrder('status') }}"></div>
        </th>

        @if (isset($action_col) && $action_col)
        <th style="{{ !isset($staff_view) ? 'min-width: 130px;' : '' }}"></th>
        @endif
    </tr>
    <tr>
        @if (isset($checkbox))
        <th class="text-center fixed-col"></th>
        @endif
        <?php
            $length = $normalAttrs->count() + $performAttrs->count() + 12; //21
            $config = 2;
            if(isset($is_leader_view)) {
                $config = 3;
                $length = $length + 2;//23
            }
            if (isset($is_review_team)) {
                $config = 3;
                $length = $length + 1; //22
            }
            if (isset($create_team)) {
                $length = $length - 2; //19
                $config = 2;
            }
            if (isset($staff_view)) {
                $length = $length - 1; // 20
                $config = 0;
            }
        ?>
        @for($i = 1; $i <= $length; $i++)
            @if($i <= $config)
            <th class="text-center fixed-col">({{$i}})</th>
            <?php
                if($config == 2 && $i == 2) {
                    $i += 2;
                }
                if ($config == 3 && $i == 3) {
                    $i += 1;
                }
            ?>
            @else
            <th class="text-center">({{$i}})</th>
            @endif
        @endfor
    </tr>
</thead>

