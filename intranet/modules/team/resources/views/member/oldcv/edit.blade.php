<div class="box box-info my-cv-box">
    <div class="cvbuilder-container">
        <div class="container">
            <div id="cv-layout-container">
                <div id="cv-layout" style="">
                    <div id="cvo-document-root">
                        <div id="cvo-document" class="cvo-document">
                            <div class="cvo-page">
                                <div class="cvo-subpage">
                                    <div id="cvo-body">
                                        @include('team::member.cv.general')
                                        <!-- #group-content -->
                                        <div id="group-content">
                                            @include('team::member.cv.objective')
                                            @include('team::member.cv.education')
                                            @include('team::member.cv.experience')
                                            @include('team::member.cv.certificate')
                                            @include('team::member.cv.skills')
                                            @include('team::member.cv.interest')
                                            @include('team::member.cv.project')
                                        </div>
                                    </div>
                                    <!-- END #group-content -->
                                    <div id="cv-watermark">
                                        © rikkei.vn
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>