<?php
use Rikkei\SlideShow\View\ImageHelper;
use Rikkei\Core\Model\CoreConfigData;

$imageHelper = new ImageHelper();
$sizeImageShow = CoreConfigData::getSizeImageShow();
?>
@foreach($fileOfSlide as $key => $file)
<?php
    $url = $imageHelper->setImage($urlImage. $file->file_name)
            ->resizeWatermark($sizeImageShow['width'], $sizeImageShow['height']);
?>
    <div class="swiper-slide" style="background-image:url({{ $url }});">
        @if ($file->description)
            <div class="slide-desc">
                <p class="sd-inner">
                {{ $file->description }}
                </p>
            </div>
        @endif
    </div>
@endforeach