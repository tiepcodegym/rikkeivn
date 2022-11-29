<div class="hashtag-wrapper">
    <ul class="hashtag-list">
        @foreach($tags as $tag)
            @php
                $tag = str_replace('#', '', $tag);
                if (!$tag) continue;
            @endphp
            <li class="hashtag-item">
                <a href="{{route('news::post.index.cat', ['search' => $tag, 'slug' => 'tags'])}}">
                    <svg style="padding-right: 5px" width="25" height="25" viewBox="0 0 30 30" fill="none"><circle cx="15" cy="15" r="15" fill="#1E1B1D"></circle><path d="M10.78 21h1.73l.73-3.2h2.24l-.74 3.2h1.76l.72-3.2h3.3v-1.6H17.6l.54-2.4H21v-1.6h-2.5l.72-3.2h-1.73l-.73 3.2h-2.24l.74-3.2H13.5l-.73 3.2H9.5v1.6h2.93l-.56 2.4H9v1.6h2.52l-.74 3.2zm2.83-4.8l.54-2.4h2.24l-.54 2.4H13.6z" fill="#fff"></path></svg>
                    {{$tag}}
                </a>
            </li>
        @endforeach
    </ul>
</div>