<div class="tab-pane" id="tab-content-deli">
    <h3 data-no-result="deli" class="hidden">{!!trans('statistic::view.no result')!!}</h3>
    <div class="pos-rel" data-chart-type="com-deli">
        <div class="row hidden" d-db-wrap="deli">
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-body">
                        <h4 class="text-center"><strong>{team}</strong></h4>
                        <div class="row">
                            <ul class="col-md-6 dt-left">
                                <h5><strong>{!!trans('statistic::view.Project deliver')!!}</strong></h5>
                                <p>{number_deli} {!!trans('statistic::view.deliver')!!} / {number_deli_proj} {!!trans('statistic::view.project')!!}</p>
                                <div d-db-list-1="deli" data-list-type="deli" data-team-id="{team_id}">
                                    <li d-db-list-item="deli">
                                        <a target="_blank" href="{!!route('project::point.edit', ['id' => 'xxx000'])!!}">{proj_name}</a>
                                    </li>
                                    <li d-db-list-item-more="deli">
                                        <a href="javascript:void(0);" d-list-detail="deli">.....</a>
                                    </li>
                                </div>
                            </ul>
                            <ul class="col-md-6 dt-right">
                                <h5 class="red"><strong>{!!trans('statistic::view.Deliver out of date')!!}</strong></h5>
                                <p>{number_out} {!!trans('statistic::view.deliver')!!} / {number_out_proj} {!!trans('statistic::view.project')!!}</p>
                                <ul d-db-list-2="deli" data-list-type="out" data-team-id="{team_id}"></ul>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
