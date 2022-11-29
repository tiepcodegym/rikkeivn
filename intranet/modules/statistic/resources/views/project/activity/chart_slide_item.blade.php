<!-- block chart bug defect fixed -->
<div d-dom-tab="{!!$typeChart!!}" class="swiper-slide">
    <h3 data-no-result="{!!$typeChart!!}" class="hidden">{!!trans('statistic::view.no result')!!}</h3>
    <div class="pos-rel hidden" data-chart-type="com-{!!$typeChart!!}">
        <div d-dom-chart="{!!$typeChart!!}-company-team">
            <canvas id="project-{!!$typeChart!!}"></canvas>
        </div>
    </div>
</div>
<div  d-dom-chart="{!!$typeChart!!}-team">
    <div class="pos-rel swiper-slide" data-chart-type="{!!$typeChart!!}-{teamId}" d-dom-tab="{!!$typeChart!!}">
        <canvas id="project-{!!$typeChart!!}-{teamId}"></canvas>
    </div>
</div>
<!--end block chart bug defect fixed-->
