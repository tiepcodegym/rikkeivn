<div class="col-md-5 col-sm-6">
    <h3 class="_me_title">Monthly Evaluation (ME) Process</h3>
    <ol>
        <li>PM evaluates project members</li>
        <li>Group Leader review ME</li>
    </ol>
    <div class="me_chart text-center">
        <img class="img-responsive" alt="char" src="{{URL::asset('asset_help/images/ME.png')}}">
    </div>
    <h3 class="_me_title">Contribution level</h3>
    <ul class="_table_list">
        <li><span>Excellent:</span> <span>4 &lt;= Summary</span></li>
        <li><span>Good:</span> <span>3 &lt;= Summary &lt; 4</span></li>
        <li><span>Fair:</span> <span>2 &lt;= Summary &lt; 3</span></li>
        <li><span>Satisfactory:</span> <span>1 &lt;= Summary &lt; 2</span></li>
        <li><span>Unsatisfactory:</span> <span>Summary &lt; 1</span></li>
    </ul>
</div>
<div class="col-md-7 col-sm-6">
    <h3 class="_me_title">{!! trans('help::seed-view.ME guide') !!}</h3>
    <div>
        {!! trans('help::seed-view.ME score') !!}

        {!! trans('help::seed-view.ME discipline') !!}
        
        {!! trans('help::seed-view.ME professional activities') !!}
        
        {!! trans('help::seed-view.ME social activities') !!}
        
        {!! trans('help::seed-view.ME criteria') !!}
        
        <br>
        {!! trans('help::seed-view.ME note') !!}              
    </div>
</div>
