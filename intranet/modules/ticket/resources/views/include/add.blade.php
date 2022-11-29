<?php
    use Rikkei\Ticket\Model\Ticket;
    use Rikkei\Team\View\TeamConst;

    $teamsIT = Ticket::getTeamsOfDeparmentIT();
?>

<div class="box-header with-border">
    <h3 class="box-title">{{ trans('ticket::view.Add ticket') }} </h3>
</div>
<!-- /.box-header -->

<!-- form start -->
<form role="form" method="post" action="{{ route('ticket::it.request.save') }}" class="" autocomplete="off" id="form-post-edit" enctype="multipart/form-data" onsubmit="return check();">
    {!! csrf_field() !!}
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="box-body">
                <div class="form-group">
                    <label class="control-label required">{{ trans('ticket::view.Subject') }}<em style="color: red">&nbsp;*</em></label>
                    <div class="input-box">
                        <input type="text" name="subject" class="form-control" placeholder="{{ trans('ticket::view.Subject') }}" value="" />
                    </div>
                </div>

                <div class="form-group row">
                    <div class="form-group-select2 col-md-6">
                        <label class="control-label">{{ trans('ticket::view.Priority') }}</label>
                        <div class="input-box">
                            <select name="priority" class="form-control select-search">
                                <option value="{{Ticket::PRIORITY_LOW}}">{{ trans('ticket::view.Low') }}</option>
                                <option value="{{Ticket::PRIORITY_NORMAl}}" selected>{{ trans('ticket::view.Normal') }}</option>
                                <option value="{{Ticket::PRIORITY_HIGH}}">{{ trans('ticket::view.Hight') }}</option>
                                <option value="{{Ticket::PRIORITY_EMERGENCY}}">{{ trans('ticket::view.Emergency') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6" >
                        <label class="control-label">{{ trans('ticket::view.Deadline') }}<em style="color: red">&nbsp;*</em></label>
                        <div class='input-group date' id='datetimepicker1'>
                            <input type='text' class="form-control" name="deadline" data-date-format="DD-MM-YYYY HH:mm" id="deadline"  title="{{ trans('ticket::view.Deadline must be at least 2 hours after the creation time of a task.') }}"/>
                            <span class="input-group-addon comelate-calendar">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                        <span style="color: red;font-size: 14px;" hidden="" class="checkTime">{{ trans('ticket::view.Deadline must be at least 2 hours after the creation time of a task.') }}</span>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-6">
                        <label class="control-label">{{ trans('ticket::view.IT department') }}<em style="color: red">&nbsp;*</em></label>
                        <div class="input-box form-group-select2">
                            <select id="team_id" name="team_id" class="form-control select-search" data-leader="">
                                @if(isset($teamsIT) && count($teamsIT))
                                    @foreach($teamsIT as $item)
                                        <option value="{{ $item->id }}" data-leader="{{ $item->leader_id }}" <?php if($item->code == TeamConst::CODE_HN_IT) { ?>selected<?php } ?>>{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <p style="color: red; font-size: 14px;" hidden="" id="leader-error">{{ trans('ticket::view.The department has not team leader') }}</p>
                        </div>
                        <input type="hidden" name="leader_id" id="leader_id" value="">
                    </div>
                    <div class="col-md-6">
                        <label class="control-label">{{ trans('ticket::view.Related persons') }}</label>
                        <div class="input-box form-group-select2">
                            <select id="related_persons" name="related_persons_list[]" class="form-control select-search" multiple>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label required">{{ trans('ticket::view.Content') }}<em>*</em></label>
                    <div class="input-box">
                        <textarea id="content" name="content" class="form-control required" style="height: 300px"></textarea>
                    </div>
                </div>

                <div class="form-group" hidden="">
                    <p class="help-block">{{ trans('ticket::view.Max. 1MB') }}</p>
                    <div class="btn btn-default btn-file">
                        <i class="fa fa-paperclip"></i> {{ trans('ticket::view.Attachment') }}
                        <input type="file" id="field" name="field[]" multiple>
                        <input type="hidden" id="remove" name="remove[]">
                    </div>
                    <span id="nameImage">
                      
                    </span>
                    <span id="errorSize" style="color: red;"></span>
                </div>
                <div class="uploadFile">
                     <input type="file" name="files">
                </div>
            </div>
            <!-- /.box-body -->

            <div class="box-footer">
                <button type="submit" class="btn btn-primary" id="submit"><i class="fa fa-paper-plane-o"></i> {{ trans('ticket::view.Send ticket') }}</button>
                <button type="button" class="btn btn-default" id="close"><i class="fa fa-ban"></i> {{ trans('ticket::view.Cancel send') }}</button>
            </div>
        </div>
    </div>
</form>