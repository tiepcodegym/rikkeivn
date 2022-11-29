@if(!$collectionMagazine->isEmpty())
    <div class="swiper-container swiper3">
        <div class="swiper-wrapper">
                @foreach($collectionMagazine as $post)
                    <?php
                    $image = $post->images->first();
                    $imageSrc = null;
                    if ($image) {
                        $imageSrc = $image->getSrc('slide');
                    }
                    if (!$imageSrc) {
                        $imageSrc = asset('common/images/noimage.png');
                    }
                    ?>
                    <div class="swiper-slide">
                        <div class="post-slide">
                            <a target="_blank" href="{{ route('magazine::read', ['id' => $post->id, 'slug' => $post->slug]) }}"><img src="{{ $imageSrc }}"></a>
                            <div class="post-desc">
                                <h3 class="post-title">
                                    <a target="_blank" href="{{ route('magazine::read', ['id' => $post->id, 'slug' => $post->slug]) }}">
                                        {{ $post->name }}
                                    </a>
                                </h3>
                                <div class="post-date">{{ $post->created_at->format('H:i d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
        </div>
    <!-- If we need navigation buttons -->
    <div class="swiper-button-prev swiper-button-prev3"></div>
    <div class="swiper-button-next swiper-button-next3"></div>
</div>
@else
    <div class="text-center">
        {{ trans('magazine::view.There are no Magazine.') }}
    </div>
@endif
