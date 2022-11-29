<?php 
use Rikkei\Resource\View\View;
?>
<!--/. Start experience -->
<div class="cvo-block" id="cvo-experience">
    <h3 class="cvo-block-title">
        <span id="cvo-experience-blocktitle">{{ trans('team::view.Work experience') }}</span>
    </h3>
    <div id="experience-table">
        @if(isset($workExperiences))
        @foreach($workExperiences as $experience)
        <div class="row">
            <div class="time">
                <span class="cvo-experience-start start default_min_width">{{ View::getDate($experience->start_at, 'm/Y') }}</span>
                -
                <span class="cvo-experience-end end default_min_width">{{ View::getDate($experience->end_at, 'm/Y') }}</span>
            </div>

            <div class="company">
                <span class="cvo-experience-company default_min_width">{{ $experience->company }}</span>
                <span class="cvo-experience-position default_min_width">{{ $experience->position }}</span>
                @if($experience->address)
                <span class="cvo-experience-position default_min_width">{{ $experience->address }}</span>
                @endif
                @if(isset($experience->description) && $experience->description)
                <div class="cvo-experience-details default_min_width">{{ $experience->description }}</div>
                @endif

            </div>
            <div style="clear:both;"></div>
        </div>
        <div style="clear:both;"></div>
        @endforeach
        @endif
    </div>
</div>
<!--/. End experience -->