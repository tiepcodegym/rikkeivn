<div class="row">
    <div class="col-md-6">
        <select id="select-year" class="form-control select-search">
            @foreach($listYears as $year)
                <option value="{{ $year }}" @if ($year == $yearSelected) selected @endif >
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <select id="select-team" class="form-control select-search">
            <option value="0">{{trans('resource::view.Dashboard.Choose group')}}</option>
                @foreach($teamsOptionAll as $option)
                    @if ($option['is_soft_dev'])
                        <option value="{{ $option['value'] }}" @if ($option['value'] == $teamId) selected @endif >
                            {{ $option['label'] }}
                        </option>
                    @endif
                @endforeach
        </select>
    </div>
</div><br>
<div class="row">
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">{{trans("resource::view.Total man month")}}</h3>
            </div>
            <div class="box-body ">
                <canvas id="totalEffort" ></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">{{ trans('resource::view.Count employee by effort and count plan') }}</h3>
            </div>
            <div class="box-body">
                <canvas id="bar-chart" ></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-body">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('resource::view.Man month by role in last 6 months') }}</h3>
                </div>
                <canvas id="role"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-body">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('resource::view.Man month and count project by project type') }}</h3>
                </div>
                <canvas id="project"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-body">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('resource::view.Count programming language in last 6 months') }}</h3>
                </div>
                <canvas id="program"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">{{ trans('resource::view.Count programming language') }}</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="box-body">
              <canvas id="doughnut-chart"></canvas>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>