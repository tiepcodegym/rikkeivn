$(document).ready(function(){const e=1,i=2,a=4;$(".icon-old ul li").click(function(){$("input#icon_url").val("");var e=$(this).find("img").attr("src");$(".box-upload-file #icon_url_old").val(e),$(".box-upload-file img").attr("src",e),$(".icon-old ul li").removeClass("active"),$(this).addClass("active")}),$("#icon_url").change(function(){$("#icon_url_old").val(""),$(".box-upload-file img").attr("src",""),$(".icon-old ul li").removeClass("active"),readURL(this)}),window.onWeekDayAllHandleChange=function(e){var i=$(e).closest(".day-box"),a=i.find(".day-list input");if(e.checked)for(var d=0;d<a.length;d++)a[d].checked=!0;else for(var d=0;d<a.length;d++)a[d].checked=!1},window.onWeekDayHandleChange=function(e){var i=$(e).closest(".day-box"),a=i.find(".day-list input"),d=i.find("#week_days_all"),t=i.find(".day-list input:checked");return t.length===a.length?void(d[0].checked=!0):void(d[0].checked=!1)},window.onGroupHandleChange=function(d){var t=d.value,o=$("#daysOfWeek"),l=$("#pickOneDay"),n=$("#txt_date_apply"),r=$("#txt_date_apply_required");return t==a?(o.show(),l.hide(),r.hide(),void n.removeAttr("required")):t==e?(r.show(),n.attr("required","required"),o.hide(),void l.show()):t==i?(l.hide(),void n.val("")):(o.hide(),l.show(),r.hide(),void n.removeAttr("required"))},$("#datepicker_start_at,#datepicker_end_at").datetimepicker({allowInputToggle:!0,format:"LT",sideBySide:!0}),window.readURL=function(e){if(e.files&&e.files[0]){var i=new FileReader;i.onload=function(e){$(".box-upload-file img").attr("src",e.target.result)},i.readAsDataURL(e.files[0])}else $(".box-upload-file img").attr("src","")}});