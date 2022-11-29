function addTime(){var e='<div class="col-sm-4 col-md-3 col-lg-4 mini-time" style="margin-bottom: 8px"><div class="input-group"><input class="form-control time" type="text" name="time[]" readonly/><span class="input-group-addon"><span><i class="fa fa-clock-o" aria-hidden="true"></i></span></span></div><span class="delTime"><i class="fa fa-times-circle time-remove hidden" aria-hidden="true"></i></span></div>';$("#time").append(e),$(".input-group").each(function(){$(this).datetimepicker({ignoreReadonly:!0,format:"HH:mm",disabledTimeIntervals:[[moment().hour(0),moment().hour(7).minutes(59)],[moment().hour(17).minutes(30),moment().hour(24)]]})})}function uniqueName(){var e=$("#name").attr("checkName"),i=!1;return $.ajax({type:"GET",url:e,async:!1,data:{name:$("#name").val(),edit:$("#name").attr("edit")},success:function(e){i=1==e}}),i}function uniqueTime(e){var i,t={},n=[];for($("input[name='time[]']").each(function(){""!=$(this).val()&&n.push($(this).val())}),i=0;i<n.length;i++){if(t[n[i]])return!1;t[n[i]]=!0}return!0}function timeFormat(){var e,i=[];for($("input[name='time[]']").each(function(){""!=$(this).val()&&i.push($(this).val())}),e=0;e<i.length;e++){if(!/^\d{2}:\d{2}$/.test(i[e]))return!1;var t=i[e].split(":");if(t[0]>23||t[1]>59)return!1}return!0}function timeLimit(){var e,i=[];for($("input[name='time[]']").each(function(){""!=$(this).val()&&i.push($(this).val())}),e=0;e<i.length;e++){var t=i[e].split(":");if(t[0]<8||t[0]>17||17==t[0]&&t[1]>30)return!1}return!0}function formatRepo(e){if(e.loading)return e.text;var i='<div class="clearfix"><div clas="col-sm-10"><div class="clearfix"><div class="col-sm-6">'+e.text+"</div></div>";return i+="</div></div>"}function formatRepoSelection(e){return e.text||e.text}$("body").on("click",".delTime",function(){$(this).closest(".mini-time").remove()}),$("body").on("mouseenter",".mini-time",function(){$(this).find(".delTime i").removeClass("hidden")}),$("body").on("mouseleave",".mini-time",function(){$(this).find(".delTime i").addClass("hidden")}),$(".mini-time").css("margin-bottom","8px"),$(".input-group").each(function(){$(this).datetimepicker({ignoreReadonly:!0,format:"HH:mm",disabledTimeIntervals:[[moment().hour(0),moment().hour(7).minutes(59)],[moment().hour(17).minutes(30),moment().hour(24)]]})}),$.validator.addMethod("uniqueName",function(e,i,t){if(uniqueName())return!0},"Tên này đã được sử dụng, hãy nhập vào tên khác"),$.validator.addMethod("timeLimit",function(e,i,t){if(timeLimit())return!0},"Thời gian nhập vào phải nằm trong khoảng từ 08 giờ đến 17 giờ 30"),$.validator.addMethod("uniqueTime",function(e,i,t){if(uniqueTime(i))return!0},"Các khung thời gian phải khác nhau"),$.validator.addMethod("time24",function(e,i){return timeFormat()},"Thời gian nhập vào không đúng định dạng"),$("#form-edit-office").validate({onkeyup:!1,rules:{"music_offices[name]":{required:!0,maxlength:50,uniqueName:!0},"time[]":{uniqueTime:!0,timeLimit:!0,time24:!0},"music_offices[sort_order]":{number:!0,min:1}},messages:{"music_offices[name]":{required:"Bắt buộc phải điền tên",maxlength:"Tên văn phòng không dài quá 50 ký tự"},"music_offices[sort_order]":{number:"Sắp xếp phải là số và lớn hơn 0",min:"Sắp xếp phải là số và lớn hơn 0"}}});var timeOutKey;$("body").on("keypress","input.time",function(){clearTimeout(timeOutKey),timeOutKey=setTimeout(function(){$("#form-edit-office").valid()},500)}),$(".select2").select2({placeholder:"Điền mã nhân viên",allowClear:!0,ajax:{url:$("#search-member").val(),dataType:"json",delay:250,data:function(e){return{q:e.term,page:e.page}},processResults:function(e,i){return i.page=i.page||1,{results:e.items,pagination:{more:30*i.page<e.total_count}}},cache:!0},escapeMarkup:function(e){return e},minimumInputLength:1,templateResult:formatRepo,templateSelection:formatRepoSelection});