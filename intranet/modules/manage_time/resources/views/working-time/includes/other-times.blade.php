<?php
$listOt = $hasOtherTime['list']['ot'];
$listLeave = $hasOtherTime['list']['leave_day'];
$listSupplement = $hasOtherTime['list']['supplement'];
$listBusiness = $hasOtherTime['list']['business'];
?>

@if (!isset($noTitle))
<h3 class="box-title margin-bottom-10">{{ trans('manage_time::view.Register related') }}</h3>
@endif

@if (!$listOt->isEmpty())
<p><strong>{{ trans('manage_time::view.OT register') }}</strong></p>
<ul>
    @foreach ($listOt as $item)
    <?php $itemLink = route('ot::ot.editot', ['id' => $item->id]) ?>
    <li><a target="_blank" href="{{ $itemLink }}">{{ $itemLink }}</a></li>
    @endforeach
</ul>
@endif

@if (!$listLeave->isEmpty())
<p><strong>{{ trans('manage_time::view.Leave day register') }}</strong></p>
<ul>
    @foreach ($listLeave as $item)
    <?php $itemLink = route('manage_time::profile.leave.edit', ['id' => $item->id]) ?>
    <li><a target="_blank" href="{{ $itemLink }}">{{ $itemLink }}</a></li>
    @endforeach
</ul>
@endif

@if (!$listSupplement->isEmpty())
<p><strong>{{ trans('manage_time::view.Supplement register') }}</strong></p>
<ul>
    @foreach ($listSupplement as $item)
    <?php $itemLink = route('manage_time::profile.supplement.edit', ['id' => $item->id]) ?>
    <li><a target="_blank" href="{{ $itemLink }}">{{ $itemLink }}</a></li>
    @endforeach
</ul>
@endif

@if (!$listBusiness->isEmpty())
<p><strong>{{ trans('manage_time::view.Business trip register') }}</strong></p>
<ul>
    @foreach ($listBusiness as $item)
    <?php $itemLink = route('manage_time::profile.mission.edit', ['id' => $item->id]) ?>
    <li><a target="_blank" href="{{ $itemLink }}">{{ $itemLink }}</a></li>
    @endforeach
</ul>
@endif