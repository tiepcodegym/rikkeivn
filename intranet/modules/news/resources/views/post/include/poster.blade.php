@if($posters && count($posters))
<div class="home-news-body-right col-sm-12 home-news-posters">
    <div class="category-title">
        <span class="category-title-content">{{ trans('news::view.Poster') }}</span>
    </div>
    <div class="home-top-articles-content">
        <div class="row">
                @foreach($posters as $poster)
                        <div class="col-sm-12 poster-item">
                            <a href="{{$poster->link}}" target="_blank"><img src="{{ $poster->getThumbnail(true) }}" /></a>
                        </div>
                @endforeach
        </div>
    </div>
</div>
@endif