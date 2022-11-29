<?php 
use Rikkei\Resource\View\View;
$toOptionDegree = Rikkei\Team\Model\EmployeeSchool::listDegree();
$toOptionQuality = Rikkei\Team\Model\QualityEducation::getAll();
if (!function_exists('getHtmlEducaction')) 
{ 
    function getHtmlEducaction($education, $toOptionDegree , $toOptionQuality) {
        ?>
        <div class="row">
            <div class="time">
                <span class="cvo-education-start start default_min_width">{{ View::getDate($education->start_at, 'm/Y') }}</span>
                -
                <span class="cvo-education-end end default_min_width">{{ $education->end_at ? View::getDate($education->end_at, 'm/Y') : ''}}</span>
            </div>

            <div class="school">
                <span class="cvo-education-school default_min_width"> {{ $education->school }}</span>
                <span class="cvo-education-details default_min_width"> {{ trans('team::view.Majors')}} :  {{ $education->majors }}</span>
                <span class="cvo-education-details default_min_width"> {{ trans('team::profile.Faculty') }} : {{ $education->faculty }}</span>
                @if($education->degree)
                <span class="cvo-education-details default_min_width">{{ trans('team::profile.Graduated type') }} : {{ isset($toOptionDegree[$education->degree]) ? $toOptionDegree[$education->degree] : '' }}</span>
                @endif
                @if($education->quality)
                <span class="cvo-education-details default_min_width">{{ trans('team::profile.Quality') }} : {{ isset($toOptionQuality[$education->quality]) ?  $toOptionQuality[$education->quality] : '' }}</span>
                @endif
            </div>
        </div>
        <div style="clear:both;"></div>
    <?php    
    }
}
?>
<!--/. Start education -->
<div class="cvo-block" id="cvo-education">
    <h3 class="cvo-block-title">
        <span id="cvo-education-blocktitle" class="default_min_width">{{ trans('team::profile.Education')}}</span>
    </h3>
    <div id="education-table">
        @if(isset($educations) && count($educations))
            @foreach($educations as $education)
                <?php echo getHtmlEducaction($education, $toOptionDegree, $toOptionQuality)?> 
            @endforeach
        @endif
    </div>
</div>
<!--/. End education -->