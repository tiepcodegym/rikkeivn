<?php

use Rikkei\Core\View\CoreUrl;
use Rikkei\Project\Model\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Rikkei\Project\Model\Risk;

$checkEdit = (isset($riskInfo) && $riskInfo);
?>
@extends('layouts.default')

@section('title')
 {{ trans('sales::view.Sale tracking') }}
@endsection

@section('content')
<div class="nav-tabs-custom tab-keep-status tracking-page" >
    <ul class="nav nav-tabs">
        <li class="active"><a href="#my_task" data-toggle="tab" aria-expanded="true">{{ trans('project::view.Relate task') }}</a></li>
        <li ><a href="#customer_feedback" data-toggle="tab" aria-expanded="false">{{ trans('project::view.Customer feedback') }}</a></li>
        <li ><a href="#risk" data-toggle="tab" aria-expanded="false">{{ trans('project::view.Risk') }}</a></li>
    </ul>
    <div class="tab-content min-height-150">
        <div class="tab-pane active" id="my_task">
            @include('sales::tracking.include.my_task')
        </div>
        <div class="tab-pane " id="customer_feedback">
            @include('sales::tracking.include.customer_feedback')
        </div>
        <div class="tab-pane" id="risk">
            @include('sales::tracking.include.risk')
        </div>
    </div>
    <!-- /.tab-content -->
</div>

<!-- modal ncm editor-->
<div class="modal fade" id="modal-task_detail">
	<div class="modal-dialog modal-full-width">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">×</span></button>
				<h4 class="modal-title">{{ trans('project::view.None Compliance') }}</h4>
			</div>
			<div class="modal-body">
				<div class="modal-ncm-editor-main"></div>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div><!-- end modal ncm editor -->
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('sales/css/tracking.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('script')
<script>
var urlMyTasks = '{{ route("sales::tracking.myTasks") }}';
var urlFeedbacks = '{{ route("sales::tracking.feedbacks") }}';
var urlRisks = '{{ route("sales::tracking.risks") }}';
var urlTaskChild = '{{ route("project::task.task_child.ajax", ["redirect" => true]) }}';
var token = '{{ csrf_token() }}';
var typeIssueCSS = {{ Task::TYPE_ISSUE_CSS }};
var urlTaskRisk = '{{ route("project::task.task_risk.ajax") }}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<script src="{{ URL::asset('lib/js/moment.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.43/js/bootstrap-datetimepicker.min.js"></script>
<script src="{{ CoreUrl::asset('sales/js/tracking/index.js') }}"></script>
<script type="text/javascript" src="{{ CoreUrl::asset('project/js/script.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script> 
    $(document).ready(function() {
        $('input.date-picker').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('input#finish_date').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        $('input#test_date').datetimepicker({
            format: 'YYYY-MM-DD'
        });
        RKfuncion.select2.init({enforceFocus:1});
         RKfuncion.formValidateTask();
    });
</script>
<script>
    $(document).ready(function() {
        jQuery.extend(jQuery.validator.messages, {
            required: requiredText,
        });
        $('#form-task-edit-customer').validate({
            rules: {
                'task[type]': "required",
                'task_assign[]': "required",
                'task[title]': "required",
                'task[content]': "required",
                'task[project_id]': "required"
            }
        });

        $('#form-task-edit-customer_child').validate({
            rules: {
                'task[title]': "required",
                'task[type]': "required",
                'task_assign[]': "required",
                'task[duedate]': "required",
                'task[create_at]': "required",
                'task[content]': "required",
            }
        })

        $('#form-task-edit-child-risk').validate({
            rules: {
                'task[title]': "required",
                'task_assign[]': "required",
                'task[duedate]': "required",
                'task[content]': "required",
            }
        })
    }); 
</script>
<script>
    $(document).ready(function() {  
        $(".modal-body .form-riks-detail").validate({
            errorPlacement: function(error, element) {
                if (element.attr("name") == "team_owner" )
                    $("#error-team-owner").html( error );
                else
                    error.insertAfter(element);
            },
            rules: {
                'content': "required",
                'project_id': "required",
                'weakness': "required",
                'level_important': "required",
                'team_owner':{required: function(){
                    if($('#owner').val() === "")
                        return true;
                    else
                        return false;
                   }
                } 
            }
        });
    });
</script>
<script language="javascript">
    // Hàm xử lý khi thẻ select thay đổi giá trị project được chọn
    // project là tham số truyền vào và cũng chính là thẻ select
    function projectChanged(project)
    {
        var value = project.value;
        if (value != ''){
            $('.relate-task-type').removeClass('hidden');
        } else {
            $('.relate-task-type').addClass('hidden');
        }
    }
</script>
@endsection
