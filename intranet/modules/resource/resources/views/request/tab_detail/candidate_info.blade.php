<?php
use Rikkei\Resource\View\View;
?>
<h3>
    {{trans('resource::view.Request.Detail.Total candidate')}} &nbsp;
    <span class="label bg-blue">{{ number_format($totalCan, 0, ',', '.') }}</span>
</h3>
<div class="table-responsive table-hover table-content-1" >
    <div class="pull-right">
        <div><i>{{trans('resource::view.Click passed candidates to view details')}}</i></div>
        <div><span class="bg-blue note"></span> <i class="note-text">{{trans('resource::view.Not enough requested number')}}</i></div>
        <div><span class="bg-navy note"></span> <i class="note-text">{{trans('resource::view.Not enough requested number and there is 1 or more positions exceeding requested number')}} </i></div>
        <div><span class="bg-green note"></span> <i class="note-text">{{trans('resource::view.Enough requested number')}}</i></div>
        <div><span class="bg-purple note"></span> <i class="note-text">{{trans('resource::view.Enough requested number and there is 1 or more positions exceeding requested number')}} </i></div>
    </div>
    <table class="edit-table table table-striped table-bordered table-condensed dataTable">
        <thead>
            <tr class="text-align-center">
                <th class="col-md-1">{{ trans('resource::view.Request.Detail.No') }}</th>
                <th class="col-md-5">{{ trans('resource::view.Request.Detail.Channel name') }}</th>
                <th class="col-md-1">{{ trans('resource::view.Draft') }}</th>
                <th class="col-md-1">{{ trans('resource::view.Request.Create.Contacting') }}</th>
                <th class="col-md-1">{{ trans('resource::view.Request.Create.Entry test') }}</th>
                <th class="col-md-1">{{ trans('resource::view.Request.Create.Interviewing') }}</th>
                <th class="col-md-1">{{ trans('resource::view.Request.Create.Offering') }}</th>
                <th class="col-md-1">{{ trans('resource::view.Request.Create.End') }}</th>
                <th class="col-md-1">{{ trans('resource::view.Request.Create.Fail') }}</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $totalDraft = 0;
                $totalContact = 0;
                $totalEntryTest = 0;
                $totalInterview = 0;
                $totalOffer = 0;
                $totalPass = 0;
                $totalFail = 0;
                $total = 0;
            ?>
            @foreach ($channelsCan as $key => $item)
            <?php
                $totalDraft += (int)$item->countDraft;
                $totalContact += (int)$item->countContact;
                $totalEntryTest += (int)$item->countEntryTest;
                $totalInterview += (int)$item->countInterview;
                $totalOffer += (int)$item->countOffer;
                $totalPass += (int)$item->countPass;
                $totalFail += (int)$item->countFail;
            ?>
            <tr @if ($key % 2 == 0) class="even" @endif>
                <td>{{$key + 1}}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->countDraft }}</td>
                <td>{{ $item->countContact }}</td>
                <td>{{ $item->countEntryTest }}</td>
                <td>{{ $item->countInterview }}</td>
                <td>{{ $item->countOffer }}</td>
                <td>{{ $item->countPass }}</td>
                <td>{{ $item->countFail }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2" class="text-align-right"></td>
                <td ><span class="label bg-gray">{{ number_format($totalDraft, 0, ',', '.') }}</span></td>
                <td ><span class="label bg-yellow">{{ number_format($totalContact, 0, ',', '.') }}</span></td>
                <td ><span class="label bg-yellow">{{ number_format($totalEntryTest, 0, ',', '.') }}</span></td>
                <td ><span class="label bg-yellow">{{ number_format($totalInterview, 0, ',', '.') }}</span></td>
                <td ><span class="label bg-yellow">{{ number_format($totalOffer, 0, ',', '.') }}</span></td>
                <?php
                    $bgPass = View::getBgColor($checkOverload, $checkFull);
                ?>
                <td ><span class="label cursor-pointer {{$bgPass}}" onclick="showNumberResourceInfo();">{{ number_format($totalPass, 0, ',', '.') }}</span></td>
                <td ><span class="label bg-red">{{ number_format($totalFail, 0, ',', '.') }}</span></td>
            </tr>
            
        </tbody>
    </table>
</div>
<div class="panel-group margin-top-50" id="candidate-table-list">
    <div class="panel panel-primary">
        <div class="panel-heading" role="tab">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#candidate-table-list" href="#change1" aria-expanded="true">{{ trans('resource::view.Candidate list') }}</a>
            </h4>
        </div>
        <div id="change1" class="panel-collapse collapse in" role="tabpanel" aria-expanded="true">
            <div class="panel-body">
                @include ('resource::request.include.datatable')
            </div>

        </div>
    </div>
</div>
@include ('resource::request.include.number_resource_info')