function checkValidContractDateEmployee(e,t){for(var a=Object.keys(t),o=!1,r=0;r<a.length;r++)e>=a[r]&&(o=!0);return o}function getDataMappingMonth(e,t,a){if(!globalEmployeePoints[e][t][a]&&a>"2012-01"){var o=Object.keys(globalEmployeePoints[e][t]);o=o.sort(),o=o.reverse();for(var r in o)if(a>o[r]){var n=globalEmployeePoints[e][t][o[r]];return n&&n.leave_date&&a>n.leave_date?null:n}}return globalEmployeePoints[e][t][a]}function getStringCalculatedMonth(e,t){var a=moment(e);return moment(a).add(t,"months").format("Y-MM")}function getPointForMaternity(e,t){if(!globalEmployeeMaternity.hasOwnProperty(t))return 0;for(var a in globalEmployeeMaternity[t]){var o=globalEmployeeMaternity[t][a];if(e===o.leave_start)return o.percent_not_working_from_leave_start;if(e===o.leave_end)return o.percent_not_working_until_leave_end;if(e>o.leave_start&&e<o.leave_end)return 1}return 0}function getTotalMemberPointEachMonth(e){globalEmployeePoints=e.member_points,globalEmployeeMaternity=e.maternity_data;for(var t=globalMonthFrom,a={};t<=$("#activity_month_to_overview").val();){var o=0;for(var r in globalEmployeePoints)if(globalEmployeePoints.hasOwnProperty(r))for(var n in globalEmployeePoints[r]){var l=globalEmployeePoints[r][n];if(checkValidContractDateEmployee(t,l)){var i=getDataMappingMonth(r,n,t);if(i){var s=i.point;if(i.join_date>t)continue;i.join_date===t&&(s=i.actual_point_first_month),i.leave_date===t&&(s=i.actual_point_last_month);var v=r.split("-")[1],d=getPointForMaternity(t,v);s-=d*s,o+=parseFloat(s>=0?s:0)}}}a[t]=o,t=getStringCalculatedMonth(t,1)}return a}function getDetailTotalPointEachMonth(e){var t,a,o,r=getTotalMemberPointEachMonth(e),n=e.project_points,l=[];for(var i in r)t=n.hasOwnProperty(i)&&n[i].hasOwnProperty(TYPE_OSDC)?n[i][TYPE_OSDC].cost:0,a=n.hasOwnProperty(i)&&n[i].hasOwnProperty(TYPE_BASE)?n[i][TYPE_BASE].cost:0,o=n.hasOwnProperty(i)&&n[i].hasOwnProperty(TYPE_ONSITE)?n[i][TYPE_ONSITE].cost:0,l.push({month:i,members:r[i],osdc:t,base:a,onsite:o,project:parseFloat(t)+parseFloat(a)+parseFloat(o)});return l}function getDataOverview(e){$("#messageBoxOverview").empty(),$(".message-overview").html(""),$(".table-overview-responsive").empty(),e=getDetailTotalPointEachMonth(e);var t="",a="";if(a+='<table class="dataTable table-bordered table-hover table-grid-data not-padding-th dataTable-project table-overview-first">',a+="<thead>",a+="<tr>",a+='<th class="head-month-first scrips-overview" colspan="3">'+globalHeader.Month+"</th>",a+="</tr>",a+="<tr>",a+='<th class="head-month-first" colspan="3">'+globalHeader["Number of human actual"]+"</th>",a+="</tr>",a+="<tr>",a+='<th class="head-month-first" colspan="3">'+globalHeader["Work effort"]+"</th>",a+="</tr>",a+="<tr>",a+='<th class="head-month-first" colspan="3">'+globalHeader.OSDC+"</th>",a+="</tr>",a+="<tr>",a+='<th class="head-month-first" colspan="3">'+globalHeader["Project Base"]+"</th>",a+="</tr>",a+="<tr>",a+='<th class="head-month-first" colspan="3">'+globalHeader.Onsite+"</th>",a+="</tr>",a+="<tr>",a+='<th class="head-month-first" colspan="3">'+globalHeader["Busy rate"]+"</th>",a+="</tr>",a+="</thead>",a+="</table>",$("#dataOverview").append(a),e.length>0){for(var o=[],r=[],n=[],l=[],i=0;i<e.length;i++){var s="",v="";if(e[i].month===globalCurrentMonth&&(s='class="month-now"',v="table-now"),o.push(e[i].month),r.push(e[i].members),n.push(e[i].project),e[i].members)var d=Math.round(e[i].project/e[i].members*100);else var d=0;l.push(d),d+="%",t='<table class="dataTable table-bordered table-hover table-grid-data not-padding-th dataTable-project table-overview '+v+'">',t+="<thead>",t+="<tr "+s+">",t+='<th class="head-month scrips-overview" colspan="3">'+e[i].month+"</th>",t+="</tr>",t+="<tr>",t+='<th class="head-month number-format" colspan="3">'+e[i].members.toFixed(2)+"</th>",t+="</tr>",t+="<tr>",t+='<th class="head-month number-format" colspan="3">'+(+e[i].project).toFixed(2)+"</th>",t+="</tr>",t+="<tr>",t+='<th class="head-month number-format" colspan="3">'+(+e[i].osdc).toFixed(2)+"</th>",t+="</tr>",t+="<tr>",t+='<th class="head-month number-format" colspan="3">'+(+e[i].base).toFixed(2)+"</th>",t+="</tr>",t+="<tr>",t+='<th class="head-month number-format" colspan="3">'+(+e[i].onsite).toFixed(2)+"</th>",t+="</tr>",t+="<tr>",t+='<th class="head-month number-format" colspan="3">'+d+"</th>",t+="</tr>",t+="</thead>",t+="</table>",$("#dataOverview").append(t)}var h=$(".tab-content").width(),c=parseInt($(".table-overview").css("width").replace("px",""))+2,m=c*$(".table-overview").length+parseInt($(".table-overview-first").css("width").replace("px",""))+2;m>h?$(".total-responsive-overview").css({width:h,"overflow-x":"scroll"}):$(".total-responsive-overview").css({width:m,"overflow-x":"hidden"}),$("#dataOverview").css("width",m),resetCanvas(),accessCanvas(r,n,l,o),formatNumber()}loadingIcon(!1),loadingIconStart(!1)}function resetCanvas(){$("#graph-container").empty(),$("#graph-container").append('<hr><canvas id="results-graph"><canvas>');var e=document.querySelector("#results-graph");e.getContext("2d")}function accessCanvas(e,t,a,o){var r=document.getElementById("results-graph").getContext("2d");return new Chart(r,{type:"bar",data:{datasets:[{label:"Members",data:e,order:1,backgroundColor:"rgba(255, 159, 64, 0.2)",borderColor:"rgba(255, 159, 64, 1)",borderWidth:1,yAxisID:"y-axis-1",pointStyle:"rect"},{label:"Projects",data:t,order:1,backgroundColor:"rgba(75, 192, 192, 0.2)",borderColor:"rgba(75, 192, 192, 1)",borderWidth:1,yAxisID:"y-axis-1",pointStyle:"rect"},{label:"Busy Rate",data:a,type:"line",order:2,backgroundColor:"rgba(255, 99, 132, 0.2)",borderColor:"rgba(255, 99, 132, 1)",borderWidth:1,yAxisID:"y-axis-2",pointStyle:"line"}],labels:o},options:{legend:{labels:{usePointStyle:!0}},showAllTooltips:!0,responsive:!0,maintainAspectRatio:!1,tooltips:{mode:"label"},elements:{line:{fill:!1}},scales:{yAxes:[{type:"linear",display:!0,position:"left",id:"y-axis-1",gridLines:{display:!0},labels:{show:!0},ticks:{beginAtZero:!0,callback:function(e,t,a){return e+" MM"}},scaleLabel:{display:!0,labelString:"Total MM (MM)",padding:15,fontFamily:"Arial"}},{type:"linear",display:!0,position:"right",id:"y-axis-2",gridLines:{display:!0},labels:{show:!0},ticks:{beginAtZero:!0,callback:function(e,t,a){return e+" %"},suggestedMax:100},scaleLabel:{display:!0,padding:15,labelString:"Busy Rate (%)",fontFamily:"Arial"}}]}}})}function loadDataOverview(){loadingIcon(!0);var e,t=$("#activity_month_from_overview").val(),a=$("#activity_month_to_overview").val();e=$(".input-select-team-member").length?$(".input-select-team-member").val():$("#selected-team").data("id"),$.ajax({url:globalGetOperationUrl,type:"post",dataType:"json",data:{monthFrom:t,monthTo:a,teamId:e,typeViewMain:globalTypeViewMain},success:function(e){e.data?getDataOverview(e.data):errorDataOverview(globalMessage["No results found"])}})}function errorDataOverview(e){$("#messageBoxOverview").empty();var t="";t+='<div class="alert alert-error alert-dismissible">',t+='<button type="button" class="close" data-dismiss="alert">×</button>',t+='<strong class="message-overview"></strong>',t+="</div>",$("#messageBoxOverview").append(t),$(".message-overview").html(e),$(".total-responsive-overview").css({width:"0px","overflow-x":"hidden"}),$(".table-overview-responsive").empty(),resetCanvas()}function loadingIcon(e){e?($(".loading-icon-overview").removeClass("hidden"),$(".table-overview-responsive, .graph-container").addClass("hidden"),$(".total-responsive-overview").css({width:"0px",height:"683px","overflow-x":"hidden"})):($(".loading-icon-overview").addClass("hidden"),$(".table-overview-responsive, .graph-container").removeClass("hidden"),$(".total-responsive-overview").css({height:"auto"}))}function loadingIconStart(e){e?$(".se-pre-con").show():$(".se-pre-con").hide()}function formatNumber(){$("body .number-format").each(function(e){var t=$(this).text().trim();t=t.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g,"$1,"),$(this).text(t)})}$(document).ready(function(){loadingIcon(!0),loadingIconStart(!0),$(".input-select-team-member").val(gloabalTeamId),$("#activity_month_to_overview").trigger("change")});var globalMonthFrom,globalMonthto,globalEmployeePoints=null,globalEmployeeMaternity={};const TYPE_OSDC=1,TYPE_BASE=2,TYPE_ONSITE=5;$.ajaxSetup({headers:{"X-CSRF-TOKEN":$('meta[name="_token"]').attr("content")}}),$("#activity_month_from_overview").datepicker({format:"yyyy-mm",viewMode:"months",minViewMode:"months",autoclose:!0}),$("#activity_month_to_overview").datepicker({format:"yyyy-mm",viewMode:"months",minViewMode:"months",autoclose:!0}),$("#activity_month_from_overview").datepicker("setDate",gloabalStartDateFilter),$("#activity_month_to_overview").datepicker("setDate",gloabalEndDateFilter),$("#activity_month_from_overview, #activity_month_to_overview").change(function(){globalMonthFrom===$("#activity_month_from_overview").val()&&globalMonthto===$("#activity_month_to_overview").val()||(globalMonthFrom=$("#activity_month_from_overview").val(),globalMonthto=$("#activity_month_to_overview").val(),globalMonthFrom>globalMonthto?errorDataOverview(globalMessage.Montherror):loadDataOverview())}),$(".input-select-team-member").change(function(){loadDataOverview()});