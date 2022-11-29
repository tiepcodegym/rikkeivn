<div class="row">
    <div id="comments" class="col-md-12">
        <div class="box box-primary box-solid">
            <div class="box-header with-border" style="margin-left: -5px; margin-right: -5px;">
                <h3 class="box-title">{{ trans('project::view.Comments') }}</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <form id="form-candidate-comment" method="post" action="{{ route('resource::candidate.comment') }}" 
                          class="form-submit-ajax has-valid" autocomplete="off" data-callback-success="commentSuccess">
                        {!! csrf_field() !!}
                        <input type="hidden" name="id" value="{{ $candidate->id }}" />
                        <input type="hidden" name="comment_id" />
                        <div class="col-md-10 form-group">
                            <textarea name="candidate_comment[content]" class="form-control text-resize-y key_enter_submit_candidate" rows="3" id="comment"></textarea>
                            <span class="info-comment hidden" style="font-size: 11px; margin-left: 5px;">{{ trans('resource::view.Candidate.Detail.Press ESC') }}</span>
                            <label id="comment-error" class="text-red hidden" for="comment">{{ trans('resource::message.Kindly add comments') }}</label>
                        </div>
                        <div class="col-md-2 form-group">
                            <button class="btn btn-primary" id="candidate_comment_submit" type="submit">{{ trans('project::view.Add') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                            <button class="btn btn-primary" id="candidate_comment_save" type="submit" style="display: none;">{{ trans('project::view.Save') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-xs-12 comment-list" style="margin-top: 20px;">
                        <div class="grid-data-query task-list-ajax" data-url="{{ URL::route('resource::candidate.comment.list.ajax', ['id' => $candidate->id]) }}">
                            <span><i class="fa fa-spin fa-refresh hidden"></i></span>
                            <div class="grid-data-query-table">
                                @include ('resource::candidate.include.comment.list_comment')
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
