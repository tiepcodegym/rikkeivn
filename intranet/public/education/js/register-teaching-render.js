function renderHtmlOption(a,e){var d="";return $.each(a,function(a,t){var n=e==a?"selected":"";d+='<option value="'+a+'"'+n+">"+t+"</option>"}),d}function renderHtmlDeitalShift(a,e){var d="",t="",n="";e&&(d=e.name,t=e.start_date,n=e.end_date);var i="";return i+='<tr tabindex="'+a+'">',i+="   <td>",i+='      <input type="number" min="1" id="name_'+a+'" placeholder="'+globalCa+'" name="detail_class_choose['+a+'][name]" class="form-control" value="'+d+'">',i+="   </td>",i+="   <td>",i+='      <input type="text" autocomplete="off" class="form-control date start_date" id="start_date_'+a+'"  value="'+t+'" name="detail_class_choose['+a+'][start_date]" data-provide="datepicker" placeholder="'+globalStartTime+'" />',i+="   </td>",i+="   <td>",i+='      <input type="text" autocomplete="off" class="form-control date end_date" id="end_date_'+a+'" value="'+n+'" name="detail_class_choose['+a+'][end_date]" data-provide="datepicker" placeholder="'+globalEndTime+'" />',i+="   </td>",globalIsShow?i+="   <td></td>":(i+="   <td>",i+='      <a class="btn btn-danger btn-remove" id="btn-remove_'+a+'">Remove <i class="glyphicon glyphicon-remove"></i></a>',i+="   </td>"),i+="</tr>"}function totalTime(){var a=0,e=$(".tblDetailInput");e.find("tr td input.end_date").each(function(){var e=""==$(this).parents("tr").find(".start_date").val()?0:moment($(this).parents("tr").find(".start_date").val(),"YYYY-MM-DD H:mm"),d=""==$(this).val()?0:moment($(this).val(),"YYYY-MM-DD H:mm");if(0!=d&&0!=e){var t=moment.duration(d.diff(e)),n=t.asHours();a+=n}}),a>0&&($(".tranning_hour").val(Math.ceil(a)),$("#tranning_hour-error").addClass("hidden"))}function loadding(){$("#course_id").addClass("hidden"),$("#update_cate_loading").removeClass("hidden"),$("#class_id").addClass("hidden"),$("#update_class_loading").removeClass("hidden"),$("#update_detail_loading").removeClass("hidden"),$(".detail-class").addClass("hidden")}function hiddenLoading(){$("#update_cate_loading").addClass("hidden"),$("#course_id").removeClass("hidden"),$("#update_class_loading").addClass("hidden"),$("#class_id").removeClass("hidden"),$("#update_detail_loading").addClass("hidden"),$(".detail-class").removeClass("hidden")}