<div d-db-wrap="deli">
    <div class="swiper-slide" data-dom-center="1">
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