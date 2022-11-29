<?php
$userCurrent = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
?>
<div class="home-feedback home-body-right-section col-sm-12">
    <div class="category-title">
        <span class="category-title-content">{{trans('news::view.Feedback')}}</span>
    </div>
    <div class="home-feedback-content">
        <div class="home-feedback-form">
            <form action="" class="wrapper">
                      <textarea name="feedback" id="js-feedback" class="txt-area" rows="1"
                                placeholder="{{trans('news::view.Send feedback placeholder')}}"></textarea>
                <button type="button" class="feedback-btn-submit" data-toggle="modal" data-target="#feedbackModal" id="js-feedback-btn-submit" disabled><span>{{trans('news::view.Send feedback')}} <i class="fa fa-location-arrow" aria-hidden="true"></i></span></button>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="feedbackModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{trans('news::message.thank_you')}}</h4>
            </div>
            <div class="modal-body">
                <p>{{trans('news::message.thank_you_for_your_feedback')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('core::view.Close')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    var globalEmployeeId = '{{$userCurrent->id}}';
    var globalToken = '{{csrf_token()}}';
</script>