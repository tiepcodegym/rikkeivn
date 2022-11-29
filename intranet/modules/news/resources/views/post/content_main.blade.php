<div class="blog-main">
    <div class="row">
        @include('news::post.include.sidebar')
        <div class="col-md-9 blog-content">
            <div class="blog-list">
                <div class="bc-inner">
                    @include('news::post.include.post_list')
                </div>
            </div>
        </div>
        <input type="hidden" id="refresh" value="no">
    </div>
</div>
