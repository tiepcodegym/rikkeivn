<?php
use Rikkei\Team\Model\Checkpoint;
use Rikkei\Team\View\CheckpointPermission;
use Carbon\Carbon;


$isDetailPage = isset($isDetail) && $isDetail;
$isPreviewPage = isset($isPreview) && $isPreview;
?>

<section id="make-header">
    <input type="hidden" name="checkpoint_id" id="checkpoint_id" value="{{$checkpoint->id}}">
    <input type="hidden" name="emp_id" id="emp_id" value="{{$emp->id}}">
    @if ($isDetailPage)
    <input type="hidden" name="result_id" id="result_id" value="{{$result->id}}">
    @endif

    <div class="logo-rikkei"><img src="{{ URL::asset('common/images/logo-rikkei.png') }}"></div>
    <h2 class="title <?php if($checkpoint->checkpoint_type_id == 2){ echo 'title-base'; }?>">{{trans('team::view.Checkpoint.Make.Head title')}}</h2>
    <h2 class="title mobile-title <?php if($checkpoint->checkpoint_type_id == 2){ echo 'title-base'; }?>">{{trans('team::view.Checkpoint.Make.Mobile head title')}}</h2>
    <span class="visible-check"></span>
    <div  class="point-container">
    @if ($isDetailPage)
    <div class="total-point-container total-point-container-leader <?php if($checkpoint->checkpoint_type_id == 2){ echo 'total-point-container-base'; }?>">
        <div class="total-point-text <?php if($checkpoint->checkpoint_type_id == 2){ echo 'color-green'; } ?>">{{ trans('team::view.Checkpoint.Detail.Point title text') }}</div>
        <div class="total-point <?php if($checkpoint->checkpoint_type_id == 2){ echo 'total-point-base'; }?>" >
            @if ($result->leader_total_point == 0)
            {{ trans('team::view.Checkpoint.Detail.Not yet review') }}
            @else
            {{ number_format($result->leader_total_point,2) }}
            @endif
        </div>
    </div>
    @endif
    <div class="total-point-container <?php if($checkpoint->checkpoint_type_id == 2){ echo 'total-point-container-base'; }?>">
        <div class="total-point-text <?php if($checkpoint->checkpoint_type_id == 2){ echo 'color-green'; } ?>">{{ trans('team::view.Checkpoint.Make.Point title text') }}</div>
        <div class="total-point <?php if($checkpoint->checkpoint_type_id == 2){ echo 'total-point-base'; }?>" >{{ isset($result) && $result  ? number_format($result->total_point, 2) : '0.00' }}</div>
    </div>
        </div>
</section>
<section>

<!-- CHECKPOINT INFORMATION -->
    <div class="row project-info">
        <div class="col-xs-12 header <?php if($checkpoint->checkpoint_type_id == 2){ echo 'header-base'; }?>">{{ trans('team::view.Checkpoint.Make.Info title') }}</div>
        <div class="col-xs-12 col-sm-6 padding-left-right-5px">
            <div class="row-info">{{ trans('team::view.Checkpoint.Make.Leader name') }} {{ isset($evaluator) ? $evaluator->name :  trans('team::view.Checkpoint.Preview.Evaluator name') }}</div>
            <div class="row-info">{{ trans('team::view.Checkpoint.Make.Checkpoint time') }}{{ $checktime->check_time }}</div>
            <div class="row-info">{{ trans('team::view.Checkpoint.Make.Checkpoint date') }}{{ date('d/m/Y',strtotime($checkpoint->start_date)) . ' - ' . date('d/m/Y',strtotime($checkpoint->end_date)) }}</div>
        </div>
        <div class="col-xs-12 col-sm-6 padding-left-right-5px">
            <div class="row-info">{{ trans('team::view.Checkpoint.Make.Make name')}} {{ $isDetailPage ? $empMake->name : $emp->name }}</div>
            <div class="row-info">{{ trans('team::view.Checkpoint.Make.Make email')}} {{ $isDetailPage ? $empMake->email : $emp->email }}</div>
            @include('team::checkpoint.include.me-avg-point')
        </div>
    </div>
<!-- END CHECKPOINT INFORMATION -->

<!-- CHECKPOINT DETAIL -->
    <div class="row project-detail">
        <div class="col-xs-12 header <?php if ($checkpoint->checkpoint_type_id == 2) { echo 'header-base'; } ?>">
            <div class="col-xs-12 col-sm-4">{{ trans('team::view.Checkpoint.Make.Criteria title') }}</div>
            <div class="col-sm-1 rating-header">{{ trans('team::view.Checkpoint.Make.Rank title') }} 1 </div>
            <div class="col-sm-1 rating-header">{{ trans('team::view.Checkpoint.Make.Rank title') }} 2</div>
            <div class="col-sm-1 rating-header">{{ trans('team::view.Checkpoint.Make.Rank title') }} 3</div>
            <div class="col-sm-1 rating-header">{{ trans('team::view.Checkpoint.Make.Rank title') }} 4</div>
            <div class="col-sm-{{ $isDetailPage ? 2 : 4 }} comment-header tooltip-group">
                {{ trans('team::view.Checkpoint.Make.Comment title') }}
                <i class="fa fa-question-circle"></i>
                <span class="tooltip">{{ trans('team::view.Checkpoint.Create.Comment title tooltip') }}</span>
            </div>
            @if ($isDetailPage)
            <div class="col-sm-2 comment-header">{{ trans('team::view.Checkpoint.Detail.Leader comment title') }}</div>
            <div class="note-comment">{{ trans('team::view.Checkpoint.Make.Comment title')}}</div>
            @endif
        </div>
        @foreach ($cate as $item)

            <div class="col-xs-12 root-cate <?php if ($checkpoint->checkpoint_type_id == 2) { echo 'root-cate-base'; } ?>">
                {{$item["sort_order"] . ". " .$item['name']}}
            </div>    

            @if ($item['cateChild'])
                @foreach ($item['cateChild'] as $itemChild)
                    <div class="col-xs-12 child-cate">{{ $itemChild["sort_order"] . ". " .$itemChild['name'] }}</div>
                    @if ($itemChild['questionsChild'])
                        @foreach ($itemChild['questionsChild'] as $questionChild)
                        <div class="row container-question">
                            <div class="col-xs-12 col-sm-4 fix-height tooltip-group">
                                <div class="question ">
                                    <span class="num-border">{{ $questionChild->sort_order }}</span>
                                    {{ $questionChild->content }}
                                    <i class="fa fa-question-circle"></i>
                                </div>
                                <span class="tooltip">{!! $questionChild->tooltip !!}</span>
                            </div>
                            @for ($i = 1; $i <= Checkpoint::TOTAL_CHOICE; $i++)
                                <?php $rank_text = 'rank' . $i . '_text'; ?>
                                <div class="col-xs-12 col-sm-1 fix-height container-question-child btn 
                                     {{ CheckpointPermission::getClassButton($i, $questionChild->point, $questionChild->leader_point) }}"
                                     data-questionid="{{$questionChild->id}}" data-rank="{{$i}}" data-weight="{{$questionChild->weight}}"
                                     {{ ($isDetailPage && $canEdit) || !$isDetailPage ? 'onclick=selectRank(this);' : '' }} >
                                    <div class="rate">
                                        <div class="rateit" >{{ $questionChild->$rank_text}}</div>
                                    </div>
                                </div>
                            @endfor
                            <div class="col-xs-12 col-sm-{{ $isDetailPage ? 2 : 4 }} ">
                                <div class="dropdown comment tooltip-group fix-height">
                                    <textarea type="text" class="comment-question form-control" rows="5" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}" value="{{ $questionChild->comment}}" {{ $isDetailPage ? 'readonly' : '' }} >{{ $questionChild->comment}}</textarea>
                                        @if (!empty($questionChild->comment))
                                        <div class="dropdown-content detail-content-dropdown">
                                            
                                            <textarea type="text" rows="5" readonly="true" class="form-control" maxlength="1000">{{ $questionChild->comment}}</textarea>
                                        </div>

                                        @endif

                                </div>
                            </div>
                            
                            @if ($isDetailPage)
                            <div class="col-xs-12 col-sm-2 ">
                                <div class="comment tooltip-group fix-height">
                                    <input type="text"  class="comment-question comment-question-leader form-control" type="text" maxlength="1000" data-questionid="{{$questionChild->id}}"  value="{{ $questionChild->leader_comment}}" <?php if(!$canEdit && !$canCmt){ echo 'readonly="true"'; } ?> />
                                    @if (!empty($questionChild->leader_comment))
                                    <span class="tooltip">{{ $questionChild->leader_comment}}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @endif
                @endforeach
            @elseif ($item['questions'])
                @foreach ($item['questions'] as $question)
                <div class="row container-question overflow-visible">
                    <div class="col-xs-12 col-sm-4 fix-height tooltip-group">
                        <div class="question">
                            <span class="num-border">{{ $question->sort_order }}</span>
                            {{ $question->content}}
                            <i class="fa fa-question-circle"></i>
                        </div>
                        <span class="tooltip">{!! CheckpointPermission::getTooltipCheckpoint($question, $checktime) !!}</span>
                    </div>
                    @for ($i = 1; $i <= Checkpoint::TOTAL_CHOICE; $i++)
                        <?php $rank_text = 'rank' . $i . '_text'; ?>
                        <div class="col-xs-12 col-sm-1 fix-height container-question-child btn 
                            {{ CheckpointPermission::getClassButton($i, $question->point, $question->leader_point) }}"
                            data-questionid="{{$question->id}}" data-rank="{{$i}}" data-weight="{{$question->weight}}"
                            {{ ($isDetailPage && $canEdit) || !$isDetailPage ? 'onclick=selectRank(this);' : '' }} >
                            <div class="rate">
                               <div class="rateit" >{{ $question->$rank_text}}</div>
                            </div>
                        </div>
                    @endfor
                    <div class="col-xs-12 col-sm-{{ $isDetailPage ? 2 : 4 }} fix-height">
			<div class="dropdown comment tooltip-group">
                            <textarea type="text"  class="comment-question form-control" rows="5" maxlength="1000" data-questionid="{{$question->id}}" value="{{ $question->comment}}" {{ $isDetailPage ? 'readonly' : '' }} >{{ $question->comment}}</textarea>
                            @if (!empty($question->comment))
                            <div class="dropdown-content detail-content-dropdown">
                                <textarea type="text" rows="5" readonly="true" class="form-control" maxlength="1000">{{ $question->comment}}</textarea>
                            </div>
                            @endif				
			</div>
                    </div>
                    @if ($isDetailPage)
                    <div class="col-xs-12 col-sm-2 fix-height">
			<div class="dropdown comment tooltip-group">
                            <textarea type="text"  class="comment-question comment-question-leader form-control" type="text" maxlength="1000" data-questionid="{{$question->id}}" value="{{ $question->leader_comment}}" <?php if(!$canEdit && !$canCmt){ echo 'readonly="true"'; } ?>>{{ $question->leader_comment}}</textarea>
                            @if (!empty($question->leader_comment))
                            <div class="dropdown-content detail-content-dropdown">
				<textarea type="text" rows="5" readonly="true" class="form-control" maxlength="1000">{{ $question->leader_comment}}</textarea>
                            </div>
                            @endif
			</div>
                    </div>
                    @endif
                </div>
                @endforeach
            @endif
        @endforeach


        <!-- Proposed employee -->
        <div class="row proposed container-question">
            <div class="col-xs-12 col-sm-5 question tooltip-group">
                {{ trans('team::view.Checkpoint.Make.Plan text') }}
                <i class="fa fa-question-circle"></i>
                <span class="tooltip">
                    Tham khảo các mục dưới đây: 
                    <br>- Kế hoạch học lên cao/ học thêm chứng chỉ/ nâng cao tiếng Nhật (tiếng Anh)
                    <br>- Kế hoạch nâng cao kỹ năng chuyên môn/ năng lực quy trình dự án
                    <br>- Nâng cao kỹ năng mềm (quản lý, làm nhóm,...) & đề xuất giải pháp/sáng kiến để nhân viên/công ty phát triển, lớn mạnh
                    <br>- ....
                </span>
            </div>
            <div class="col-xs-12 col-sm-7 comment proposed-comment">
                <textarea  class="proposed form-control" id="proposed" maxlength="2000" {{ $isDetailPage ? 'readonly' : '' }}>{{ $isDetailPage ? $result->comment : '' }}</textarea>
            </div>
        </div>

        <!-- Final comment leader -->
        @if ($isDetailPage)
        <div class="row proposed proposed-leader container-question">
            <div class="col-xs-12 col-sm-5 question tooltip-group">
                {{ trans('team::view.Checkpoint.Detail.Leader final comment text') }}
                <i class="fa fa-question-circle"></i>
                <span class="tooltip">
                    Người đánh giá với tư cách là người đi trước, nhiều kinh nghiệm đưa ra những lời khuyên, nhắn nhủ để người được 
                       đánh giá có thể khắc phục nhược điểm & phát triển các thế mạnh của bản thân
                </span>
            </div>
            <div class="col-xs-12 col-sm-7 comment proposed-comment proposed-comment-leader">
                <textarea  class="proposed form-control" id="proposed-leader" maxlength="2000"
                <?php if (!$canEdit && !$canCmt) { echo 'readonly="true"'; } ?>>{{ $isDetailPage ? $result->leader_comment : '' }}</textarea>
            </div>
        </div>
        @endif
        @if ((($isDetailPage && ($canEdit || $isLeader || $canCmt)) || !$isDetailPage) && (!isset($isPreview) || !$isPreview))
            <!-- Button submit -->
            <div class="col-xs-12 container-submit">
                <button onclick="confirm();" type="button" class="btn btn-primary <?php if($checkpoint->checkpoint_type_id == 2){ echo 'button-base'; }?>">{{trans('team::view.Checkpoint.Make.Submit button text')}}</button>
            </div>
        @endif
    </div>
<!-- END CHECKPOINT DETAIL -->
</section>