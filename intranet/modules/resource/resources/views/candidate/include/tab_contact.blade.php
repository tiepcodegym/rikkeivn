<div class="tab-pane <?php if($tabActive == 'tab_contact'): ?> active <?php endif; ?>" id="tab_contact">
    <form id="form-contact-candidate" class="form-horizontal form-candidate-detail form-contact" method="post" action="{{$urlSubmit}}" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <input type="hidden" name="candidate_id" value="{{$candidate->id}}">
        @if ($candidate->request_id)
        <input type="hidden" name="request_id" value="{{$candidate->request_id}}">
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="form-group position-relative">
                    <label for="contact_result" class="col-lg-4 control-label">{{trans('resource::view.Candidate.Detail.Contact result')}} <em class="required" aria-required="true">*</em></label>
                    <div class="col-lg-8">
                        <span>  
                            <select id="contact_result" name="contact_result" class="form-control">
                                <option value="0">{{ trans('resource::view.Contacting') }}</option>
                                @foreach ($resultOptions as $option)
                                <option value="{{ $option['id'] }}" @if($checkEdit && $option['id'] == $candidate->contact_result) selected @endif>{{ $option['name'] }}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group position-relative ">
                    <label for="contact_note" class="col-lg-4 control-label">{{trans('resource::view.Candidate.Detail.Note')}}</label>
                    <div class="col-lg-8">
                        <span>  
                            <textarea rows="5" name="contact_note" class="form-control">{{$candidate->contact_note ? $candidate->contact_note: ''}}</textarea>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-12 <?php if((int)$candidate->contact_result !== \Rikkei\Resource\View\getOptions::RESULT_FAIL): ?>hidden<?php endif; ?> interested-input-container">
                <div class="form-group position-relative">
                    <label class="col-lg-4 control-label">{{trans('resource::view.Candidate.Create.Interested')}}</label>
                    <div class="col-lg-8">
                        <span>
                            <select name="interested" class="form-control">
                                @foreach ($interestedOptions as $key => $interested)
                                    <option value="{!! $key !!}"
                                            class="{!! $interested['class'] !!} font-15"
                                            @if ((int)$candidate->interested === $key) selected @endif>{!! $interested['label'] !!}</option>
                                @endforeach
                            </select>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 align-center margin-top-40">
                <button type="submit" class="btn btn-primary">{{trans('resource::view.Candidate.Detail.Submit Contact')}}</button>
            </div>
        </div>
        <input type="hidden" name="detail" value="detail" />
    </form>
</div>
