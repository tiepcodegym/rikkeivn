<div class="tab-pane{!!isset($chartTabActive)?' active':''!!}" id="tab-content-{!!$typeChart!!}">
    <div d-dom-tab="{!!$typeChart!!}">
        <div class="row">
            <div class="col-sm-12">
                <div class="box box-info chart-full-box">
                    <div class="box-body">
                        <h3 data-no-result="{!!$typeChart!!}" class="hidden">{!!trans('statistic::view.no result')!!}</h3>
                        <div class="pos-rel hidden" data-chart-type="com-{!!$typeChart!!}">
                            <div class="btn-group-chart">
                                <button class="btn btn-primary btn-sm" data-btn-chart="hide">{!!trans('statistic::view.Hide all')!!}</button>
                                <button class="btn btn-primary btn-sm" data-btn-chart="show">{!!trans('statistic::view.Show all')!!}</button>
                            </div>
                            <div d-dom-chart="{!!$typeChart!!}-company-team">
                                <canvas id="project-{!!$typeChart!!}"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row hidden" d-dom-chart="{!!$typeChart!!}-team">
            <div class="col-sm-6">
                <div class="box box-info chart-full-box">
                    <div class="box-body">
                        <div class="pos-rel" data-chart-type="{!!$typeChart!!}-{teamId}">
                            <div class="btn-group-chart">
                                <button class="btn btn-primary btn-sm" data-btn-chart="hide">{!!trans('statistic::view.Hide all')!!}</button>
                                <button class="btn btn-primary btn-sm" data-btn-chart="show">{!!trans('statistic::view.Show all')!!}</button>
                            </div>
                            <canvas id="project-{!!$typeChart!!}-{teamId}"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
