<?php 
    use Rikkei\Resource\View\View;
?>
<!--/. Start certification -->
<div class="cvo-block" id="cvo-certification">
    <h3 class="cvo-block-title">
        <span id="cvo-certification-blocktitle" class="default_min_width">{{ trans('team::view.Cetificate') }}</span></h3>
    <div id="certification-table">
        @if(isset($certificates))
        @foreach($certificates as $certificate)
        <div class="row ">
            <div class="time">
                <span class="cvo-certification-time default_min_width">{{ View::getDate($certificate->start_at, 'm/Y') }}</span>
                @if($certificate->end_date)
                -
                <span class="cvo-education-end end default_min_width">{{ View::getDate($certificate->end_date, 'm/Y') }}</span>
                @endif
            </div>
            <div class="details">
                <span class="cvo-certification-title default_min_width">{{ $certificate->name }} ( {{ Rikkei\Core\View\View::getLabelLanguageLevel($certificate->level) }} )</span>
            </div>
            <div style="clear: both"></div>
        </div>
        @endforeach
        @endif
    </div>
</div>
<!--/. End certification -->

<!--/. Start award -->
<div class="cvo-block" id="cvo-award">
    <h3 class="cvo-block-title">
        <span id="cvo-award-blocktitle"  class="default_min_width">{{ trans('team::profile.Prize') }}</span>
    </h3>
    <div id="award-table">
        @if(isset($prizes))
        @foreach($prizes as $prize)
        <div class="row ">
            <div class="time">
                <span class="cvo-education-start start default_min_width">{{ View::getDate($prize->issue_date, 'm/Y') }}</span>
                @if($prize->expired_date)
                -
                <span class="cvo-education-end end default_min_width">{{ View::getDate($prize->expired_date, 'm/Y') }}</span>
                @endif
            </div>
            <div class="details">
                <span class="cvo-award-title default_min_width">{{ $prize->name }} ( {{ $prize->level }} )</span>
            </div>
            <div style="clear: both"></div>
        </div>
        @endforeach
        @endif
    </div>
</div>
<!--/. End award -->