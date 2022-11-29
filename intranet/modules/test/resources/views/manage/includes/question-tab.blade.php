<div class="nav-tabs-custom nav-tabs-rikkei test-tabs">
    <div class="right-barbox">
    </div>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#question_multiple_choice" data-toggle="tab"
                        aria-expanded="true">{{ trans('test::test.question multiple choice') }}</a></li>
        <li class=""><a href="#question_written" data-toggle="tab"
                        aria-expanded="false">{{ trans('test::test.question written') }}</a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="question_multiple_choice">
            @include('test::manage.includes.question-multiple-choice')
        </div>
        <div class="tab-pane" id="question_written">
            @include('test::manage.includes.question-written')
        </div>
        <br/>
    </div>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>