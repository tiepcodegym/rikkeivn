/** 
 * 
 * iCheck load
 */
$('input').iCheck({
    checkboxClass: 'icheckbox_minimal-blue',
    radioClass: 'iradio_minimal-blue'
});   

//Init datepicker for elements contains class `date`
$('.date').datepicker({
    todayBtn: "linked",
    language: "it",
    autoclose: true,
    todayHighlight: true,
    dateFormat: 'yy-mm-dd'
});

//Init range date
var currentDate = new Date();
var currentYear = new Date().getFullYear();

/**
* click filter
*/
function filterAnalyze(token){
    //hidden and clear empty apply old result
    $(".ketquaapply").hide();
    $("#danhsachduan tbody").html('');
    $("#duoi3sao tbody").html('');
    
    //get criteria type
    var criteriaType = $("input[name=tieuchi]:checked").attr("id");
    
    //get project type checked
    var projectTypeIds = "";
    $('input[type=checkbox][name=project_type]:checked').each(function(){
        if(projectTypeIds == ""){
            projectTypeIds = $(this).val();
        } else{
            projectTypeIds += "," + $(this).val();
        }
    });
    
    //get team checked
    var teamIds = "";
    $('input[class=team-tree-checkbox]:checked').each(function(){
        if(teamIds == ""){
            teamIds = $(this).attr("data-id");
        } else{
            teamIds += "," + $(this).attr("data-id");
        }
    });
    
    //check if don't any check project type or team
    if(projectTypeIds == ""){
        if(teamIds == ""){
            $('#modal-warning').modal('show');
            $('#modal-warning .modal-body').html(reProjectAndTeam);
        }else{
            $('#modal-warning').modal('show');
            $('#modal-warning .modal-body').html(reTypeProject);
        }
        return false;
    }else{
        if(teamIds == ""){
            $('#modal-warning').modal('show');
            $('#modal-warning .modal-body').html(reTeam);
            return false;
        }
    }

    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
    
    $(".apply-click-modal").show();
    $.ajax({
        url: baseUrl + '/css/filter_analyze',
        type: 'post',
        data: {
            _token: token, 
            startDate: startDate,
            endDate: endDate,
            projectTypeIds: projectTypeIds,
            teamIds: teamIds,
            criteriaType: criteriaType,
        },
    })
    .done(function (data) { 
        $('.btn-apply').show();
        $(".apply-click-modal").hide();
        $("div.theotieuchi").html(data);
        $(".tbl-criteria").hide();
        $(".no-result").hide();
        $(".no-result-"+criteriaType).show();
        $(document).trigger('icheck');
        $("#startDate_val").val(startDate);
        $("#endDate_val").val(endDate);
        $("#teamIds_val").val(teamIds);
        $("#projectTypeIds_val").val(projectTypeIds);
        
        var elem = $("table[data-id="+criteriaType+"] tbody");
        elem.parent().show(); //Show filter table checked
        fixScroll(elem);
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
}

/**
 * Fix if filter table has scroll
 */
function fixScroll(elem){  
    if (typeof elem.get(0) !== 'undefined') {
        var height = elem.height(); 
        var scrollHeight = elem.get(0).scrollHeight;
        
        //if IE browser
        if(msieversion()) {
            scrollHeight = scrollHeight -1;
        }
        
        if(scrollHeight > height){
            elem.parent().find('thead tr th:nth-child(3)').css('margin-left','-10px');
            if(isMobile()) {
                elem.parent().find('thead tr th:last-child').css('margin-left','10px');
            } else {
                // If IE browser
                if(msieversion()) { 
                    //IF question table
                    if(elem.parent().data('id') == 'tcQuestion') { 
                        elem.parent().find('thead tr th:last-child').css('margin-left','-7px');
                    } 
                    //Other table
                    else { 
                        elem.parent().find('thead tr th:last-child').css('margin-left','-4px');
                    }
                } 
                // Other browser
                else { 
                    elem.parent().find('thead tr th:last-child').css('margin-left','-4px');
                }
            }
        }else{
            elem.parent().find('thead tr th:nth-child(3)').css('margin-left','0');
            elem.parent().find('thead tr th:last-child').css('margin-left','0');
        }
    }
}

function msieversion() {

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))  // If Internet Explorer, return version number
    {
        return true;
    }
    
    return false;
}

/**
 * apply filter
 * @param string token
 */
function apply(token){
    //get criteria type and id
    var criteriaType = $("input[name=tieuchi]:checked").attr("id");
    var criteriaIds = "";
    
    var classCriteriaCheck = getCriteriaChecked();
    $('input[class='+classCriteriaCheck+']:checked').each(function(){
        if(criteriaIds == ""){
            if(classCriteriaCheck == "checkQuestionItem"){
                if($(this).attr("data-questionid")){
                    criteriaIds = $(this).attr("data-questionid");
                }
            }else{
                criteriaIds = $(this).attr("data-id");
            }
        } else{
            if(classCriteriaCheck == "checkQuestionItem"){
                if($(this).attr("data-questionid")){
                    criteriaIds += "," + $(this).attr("data-questionid");
                }
            }else{
                criteriaIds += "," + $(this).attr("data-id");
            }
            
        }
    });
    
    if(criteriaIds == ""){
        $('#modal-warning').modal('show');
        $('#modal-warning .modal-body').html(reCriteria);
        return false;
    }
    
    var teamIds = $("#teamIds_val").val();
    var projectTypeIds = $("#projectTypeIds_val").val();
    var startDate = $("#startDate_val").val();
    var endDate = $("#endDate_val").val();
    $(".apply-click-modal").show();
    $.ajax({
        url: baseUrl + '/css/apply_analyze',
        type: 'post',
        data: {
            _token: token, 
            startDate: startDate,
            endDate: endDate,
            criteriaIds: criteriaIds,
            teamIds: teamIds,
            projectTypeIds: projectTypeIds,
            criteriaType: criteriaType,
        },
    })
    .done(function (data) { 
        $("#criteriaIds_val").val(criteriaIds);
        $("#criteriaType_val").val(criteriaType);
        $(".apply-click-modal").hide();
        $(".ketquaapply").show();
        if(criteriaType == "tcQuestion"){
            $(".box-select-question").show();
            $(".box-select-question #question-choose").html(data["htmlQuestionList"]);
            removeEmptyCate();
        }else{
            if(criteriaType == "tcTeam") {
                $('#danhsachduan .filter-input-grid input[name=name]').hide();
            } else {
                $('#danhsachduan .filter-input-grid input[name=name]').show();
            }
            $(".box-select-question").hide();
            $(".box-select-question #question-choose").html('');
        }
        $('html, body').animate({
            scrollTop: $(".ketquaapply").offset().top
        }, 100);
        
        //Fill data result list table
        var countResult = data["cssResultPaginate"]["cssResultdata"]["data"].length; 
        var html = "";
        for(var i=0; i<countResult; i++){
            html += "<tr>";
            html += "<td class='text-align-center'>"+data["cssResultPaginate"]["cssResultdata"]["data"][i]["id"]+"</td>";
            html += "<td class=\"font-japan\">"+data["cssResultPaginate"]["cssResultdata"]["data"][i]["project_name"]+"</td>";
            html += "<td>"+data["cssResultPaginate"]["cssResultdata"]["data"][i]["teamName"]+"</td>";
            html += "<td>"+data["cssResultPaginate"]["cssResultdata"]["data"][i]["pmName"]+"</td>";
            html += "<td class='text-align-center'>"+data["cssResultPaginate"]["cssResultdata"]["data"][i]["css_end_date"]+"</td>";
            html += "<td class='text-align-center'>"+data["cssResultPaginate"]["cssResultdata"]["data"][i]["css_result_created_at"]+"</td>";
            html += "<td class='text-align-center'>"+data["cssResultPaginate"]["cssResultdata"]["data"][i]["point"]+"</td>";
            html += "</tr>";   
        } 
        $("#danhsachduan tbody").html(html);
        $("#danhsachduan").parent().find(".pagination").html(data["cssResultPaginate"]["paginationRender"]);
        //End fill data result list table
        
        //Get data to all result chart
        var dataResult = [],points = [];
        $.each(data['allResultChart'], function(key, value){ 
            points.push([new Date(value.date),value.point]);
            
        });
        
        dataResult.push({
            label: 'Điểm CSS',
            lines:{show:true},
            points:{show:true},
            data: points
        }); 
        
        //Set data to all result chart
        var options,chart;
        
        options = {
            legend:{container: $('#legend-container-all')},
            grid: { hoverable: true,borderColor: "#ccc" } ,
            xaxis: {mode: "time",timeformat: "%d/%m/%y"}
        };
        chart = $.plot($("#chartAll"),dataResult,options);
        
        $("#chartAll").bind("plothover", function (event, pos, item) {
            $("#tooltip").remove();
            if (item) {    
              var x = item.datapoint[0],y = item.datapoint[1].toFixed(2);
              var dateFormat = $.datepicker.formatDate('dd/mm/yy', new Date(x));
              showTooltip(item.pageX, item.pageY, dateFormat + " : " + y + " điểm");
            }
        }); 
        //End all result chart
        
        //Get data compare chart
        var dataCompare = [];
        $.each(data["compareChart"], function(key, value) {
            var points = [];
            $.each(value.data,function(k, v){
                points.push([(new Date(v.date)).getTime(),v.point]);
            });
            dataCompare.push({
                label: value.name,
                lines:{show:true},
                points:{show:true},
                data: points
            }); 
        });
        
        //Set data to compare chart
        var noColumn = 5;
        if($(window).width() < 500) {
            noColumn = 3;
        }
        options = {
            legend:{
                position:"ne",
                container: $('#legend-container'), 
                noColumns: noColumn
            },
            grid: { hoverable: true,borderColor: "#ccc" } ,
            xaxis: {mode: "time",timeformat: "%d/%m/%y"}
        };
        chart = $.plot("#chartFilter", dataCompare,options);
        
        $("#chartFilter").bind("plothover", function (event, pos, item) {
            $("#tooltip").remove();
            if (item) {    
              var x = item.datapoint[0],y = item.datapoint[1].toFixed(2);
              var dateFormat = $.datepicker.formatDate('dd/mm/yy', new Date(x));
              showTooltip(item.pageX, item.pageY, dateFormat + " : " + y + " điểm");
            }
        }); 

        //end compare chart

        // pie chart Point
        $('#chartPoint').remove();
        $('.chartPoint').append('<canvas id="chartPoint"></canvas>');
        newChart($('#chartPoint'), data['piechartPoint'], data['piechartLabel'], 'doughnut');
        //Fill data to Less 3* table and Proposed table
        if(criteriaType != "tcQuestion"){
            //Less 3* table
            var countResult = data["lessThreeStar"]["cssResultdata"].length; 
            html = "";
            if(countResult > 0){
                for(var i=0; i<countResult; i++){
                    html += "<tr>";
                    html += "<td class='text-align-center'>"+data["lessThreeStar"]["cssResultdata"][i]["no"]+"</td>";
                    html += "<td class=\"font-japan\">"+data["lessThreeStar"]["cssResultdata"][i]["projectName"]+"</td>";
                    html += "<td class=\"font-japan\">"+data["lessThreeStar"]["cssResultdata"][i]["questionName"]+"</td>";
                    html += "<td class='text-align-center'>"+data["lessThreeStar"]["cssResultdata"][i]["stars"]+"</td>";
                    html += "<td>"+data["lessThreeStar"]["cssResultdata"][i]["comment"]+"</td>";
                    html += "<td class='text-align-center'>"+data["lessThreeStar"]["cssResultdata"][i]["makeDateCss"]+"</td>";
                    html += "<td class='text-align-center'>"+data["lessThreeStar"]["cssResultdata"][i]["cssPoint"]+"</td>";
                    html += "</tr>";   
                }
                $("#duoi3sao tbody").html(html);
                $("#duoi3sao").parent().find(".pagination").html(data["lessThreeStar"]["paginationRender"]);
            }else{
                $("#duoi3sao tbody").html(noResult);
                $("#duoi3sao").parent().find(".pagination").html('');
            }
            
            //Proposed table
            countResult = data["proposes"]["cssResultdata"].length; 
            html = "";
            if(countResult > 0){
                for(var i=0; i<countResult; i++){
                    html += "<tr>";
                    html += "<td class='text-align-center'>"+data["proposes"]["cssResultdata"][i]["no"]+"</td>";
                    html += "<td class=\"font-japan\">"+data["proposes"]["cssResultdata"][i]["projectName"]+"</td>";
                    html += "<td class=\"font-japan\">"+data["proposes"]["cssResultdata"][i]["customerComment"]+"</td>";
                    html += "<td class='text-align-center'>"+data["proposes"]["cssResultdata"][i]["makeDateCss"]+"</td>";
                    html += "<td class='text-align-center'>"+data["proposes"]["cssResultdata"][i]["cssPoint"]+"</td>";
                    html += "</tr>";   
                }
                $("#danhsachdexuat tbody").html(html);
                $("#danhsachdexuat").parent().find(".pagination").html(data["proposes"]["paginationRender"]);
            }else{
                $("#danhsachdexuat").parent().find(".pagination").html('');
                $("#danhsachdexuat tbody").html(noResult);
            }
            
            //sort column by all result
            $("#duoi3sao thead th").attr('data-type','all');
            $("#danhsachdexuat thead th").attr('data-type','all');
        }else{
            $("#duoi3sao tbody").html('');
            $("#duoi3sao").parent().find(".pagination").html('');
            $("#danhsachdexuat tbody").html('');
            $("#danhsachdexuat").parent().find(".pagination").html('');
            
            //sort column by all question
            $("#duoi3sao thead th").attr('data-type','question');
            $("#danhsachdexuat thead th").attr('data-type','question');
        }
        //Set css resultids 
        $('#cssResultIds').val(data["strResultIds"]);
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
}

function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css( {
        position: 'absolute', display: 'none', top: y - 25, left: x - 60,
        border: '1px solid #fdd', padding: '2px', 'background-color': '#fee', opacity: 0.80
    }).appendTo("body").fadeIn(200);
}

function isMobile () {
    var isMobile = false; //initiate as false
    // device detection
    if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
        || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) isMobile = true;
        
    return isMobile;
}

/**
 * get checkbox in tables filter
 */
function getCriteriaChecked(){
    var criteriaType = $("input[name=tieuchi]:checked").attr("id");
    if(criteriaType == "tcProjectType"){
        return "checkProjectTypeItem";
    }else if(criteriaType == "tcProjectName"){
        return "checkProjectNameItem";
    }else if(criteriaType == "tcTeam"){
        return "checkTeamItem";
    }else if(criteriaType == "tcPm"){
        return "checkPmItem";
    }else if(criteriaType == "tcBrse"){
        return "checkBrseItem";
    }else if(criteriaType == "tcCustomer"){
        return "checkCustomerItem";
    }else if(criteriaType == "tcSale"){
        return "checkSaleItem";
    }else if(criteriaType == "tcQuestion"){
        return "checkQuestionItem";
    }
}

strLoading = '<tr class="loading"><td colspan="7"><img class="loading-img" src="'+baseUrl+'sales/images/loading.gif" /></td></tr>';
noResult = '<tr><td colspan="7" style="text-align:center;">Không có kết quả nào được tìm thấy</td></tr>';

$(document).on('icheck', function(){
    $('input').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass: 'iradio_minimal-blue'
    }); 

    var arrCetirea = ['ProjectType', 'ProjectName','Team','Pm','Brse','Customer','Sale'];
    for(var i=0; i<arrCetirea.length; i++){ 
        // Make "Item" checked if checkAll are checked
        $('#check'+arrCetirea[i]).on('ifChecked', function (event) {
            var id = '.' + $(this).attr("id") + "Item:visible"; 
            $(id).iCheck('check');
            triggeredByChild = false;
        });

        // Make "Item" unchecked if checkAll are unchecked
        $('#check'+arrCetirea[i]).on('ifUnchecked', function (event) {
            var id = '.' + $(this).attr("id") + "Item:visible"; 
            if (!triggeredByChild) {
                $(id).iCheck('uncheck');
            }
            triggeredByChild = false;
        });

        // Remove the checked state from "All" if any checkbox is unchecked
        $('.check'+arrCetirea[i]+'Item').on('ifUnchecked', function (event) {
            triggeredByChild = true;
            var id = '#' + $(this).attr("class"); 
            id = id.replace("Item",""); 
            $(id).iCheck('uncheck');
        });

        // Make "All" checked if all checkboxes are checked
        $('.check'+arrCetirea[i]+'Item').on('ifChecked', function (event) {
            var id = '#' + $(this).attr("class"); 
            id = id.replace("Item","");
            if ($('.' + $(this).attr("class")).filter(':checked').length == $('.' + $(this).attr("class")).length) {
                $(id).iCheck('check');
            }
        });
    }
    
    /** iCheck event theotieuchi la cau hoi */  
    // Make "Item" checked if checkAll are checked
    $('.checkQuestionItem').on('ifChecked', function (event) {
        var parent_id = $(this).attr('data-id');
        $('.checkQuestionItem[parent-id='+parent_id+']').iCheck('check');
        triggeredByChild = false;
    });
    
    // Make "Item" unchecked if checkAll are unchecked
    $('.checkQuestionItem').on('ifUnchecked', function (event) {
        //if (!triggeredByChild) {
            var parent_id = $(this).attr('data-id');
            $('.checkQuestionItem[parent-id='+parent_id+']').iCheck('uncheck');
        //}
        triggeredByChild = false;
    });

    $('.team-tree-checkbox').on('ifChecked', function (event) {
        var parent_id = $(this).attr('data-id');
        $('.team-tree-checkbox[parent-id='+parent_id+']').iCheck('check');
        triggeredByChild = false;
    });

    // Make "Item" unchecked if checkAll are unchecked
    $('.team-tree-checkbox').on('ifUnchecked', function (event) {
        //if (!triggeredByChild) {
        var parent_id = $(this).attr('data-id');
        $('.team-tree-checkbox[parent-id='+parent_id+']').iCheck('uncheck');
        //}
        triggeredByChild = false;
    });

    //show table project type
    $('#tcProjectType').on('ifChecked', function (event) {
        $('.tbl-criteria').hide(); 
        $('table[data-id=tcProjectType]').show();
        $('.no-result').hide();
        $('.no-result-tcProjectType').show();
        
        //Fix with table if has scroll
        fixScroll($('table[data-id=tcProjectType] tbody'));
    });

    //show table project type
    $('#tcProjectName').on('ifChecked', function (event) {
        $('.tbl-criteria').hide();
        $('table[data-id=tcProjectName]').show();
        $('.no-result').hide();
        $('.no-result-tcProjectName').show();

        //Fix with table if has scroll
        fixScroll($('table[data-id=tcProjectName] tbody'));
    });

    //show table team
    $('#tcTeam').on('ifChecked', function (event) {
        $('.tbl-criteria').hide();
        $('table[data-id=tcTeam]').show();
        $('.no-result').hide();
        $('.no-result-tcTeam').show();
        
        //Fix with table if has scroll
        fixScroll($('table[data-id=tcTeam] tbody'));
    });

    //show table pm
    $('#tcPm').on('ifChecked', function (event) {
        $('.tbl-criteria').hide();
        $('table[data-id=tcPm]').show();
        $('.no-result').hide();
        $('.no-result-tcPm').show();
        
        //Fix with table if has scroll
        fixScroll($('table[data-id=tcPm] tbody'));
    });

    //show table brse
    $('#tcBrse').on('ifChecked', function (event) {
        $('.tbl-criteria').hide();
        $('table[data-id=tcBrse]').show();
        $('.no-result').hide();
        $('.no-result-tcBrse').show();
        
        //Fix with table if has scroll
        fixScroll($('table[data-id=tcBrse] tbody'));
    });

    //show table customer
    $('#tcCustomer').on('ifChecked', function (event) {
        $('.tbl-criteria').hide();
        $('table[data-id=tcCustomer]').show();
        $('.no-result').hide();
        $('.no-result-tcCustomer').show();
        
        //Fix with table if has scroll
        fixScroll($('table[data-id=tcCustomer] tbody'));
    });    

    //show table sale
    $('#tcSale').on('ifChecked', function (event) {
        $('.tbl-criteria').hide();
        $('table[data-id=tcSale]').show();
        $('.no-result').hide();
        $('.no-result-tcSale').show();
        
        //Fix with table if has scroll
        fixScroll($('table[data-id=tcSale] tbody'));
    }); 

    //show table question
    $('#tcQuestion').on('ifChecked', function (event) {
        $('.tbl-criteria').hide();
        $('table[data-id=tcQuestion]').show();
        $('.no-result').hide();
        $('.no-result-tcQuestion').show();
        fixScroll($('table[data-id=tcQuestion] tbody'));
    }); 
}).trigger('icheck'); // trigger it for page load

/**
 * Question less 3* change event
 */
$(document).ready(function(){
   $(".box-select-question #question-choose").change(function(){
       $(".box-select-question #question-choose option[value=0]").remove();
       var questionId = $(this).val(); 
       if(questionId == 0){
           $("#duoi3sao tbody").html('');
           $("#duoi3sao").parent().find(".pagination").html('');
           $("#danhsachdexuat tbody").html('');
           $("#danhsachdexuat").parent().find(".pagination").html('');
       }else{
           var curpage = 1;
           var cssresultids = $("#question-choose option:selected").data("cssresult");
           var token = $("#question-choose option:selected").data("token");
           
           getListLessThreeStarByQuestion(questionId,curpage,token,cssresultids,'result_make','desc');
           getProposesQuestion(questionId,curpage,token,cssresultids,'result_make','desc');
       }
   }); 
});

/**
 * Get less 3* list by question
 * @param int questionId
 * @param int curpage
 * @param string token
 * @param string cssresultids
 */
function getListLessThreeStarByQuestion(questionId,curpage,token,cssresultids,orderby,ariatype){
    $("#duoi3sao tbody").html(strLoading);
    $.ajax({
        url: baseUrl + 'css/get_list_less_three_star_question/'+questionId+'/'+cssresultids+'/'+curpage+'/'+orderby+'/'+ariatype,
        type: 'post',
        data: {
            _token: token, 
        },
    })
    .done(function (data) {  
        var countResult = data["cssResultdata"].length; 
        html = "";
        if(countResult > 0){
            for(var i=0; i<countResult; i++){
            html += "<tr>";
            html += "<td class='text-align-center'>"+data["cssResultdata"][i]["no"]+"</td>";
            html += "<td class=\"font-japan\">"+data["cssResultdata"][i]["projectName"]+"</td>";
            html += "<td class=\"font-japan\">"+data["cssResultdata"][i]["questionName"]+"</td>";
            html += "<td class='text-align-center'>"+data["cssResultdata"][i]["stars"]+"</td>";
            html += "<td>"+data["cssResultdata"][i]["comment"]+"</td>";
            html += "<td class='text-align-center'>"+data["cssResultdata"][i]["makeDateCss"]+"</td>";
            html += "<td class='text-align-center'>"+data["cssResultdata"][i]["cssPoint"]+"</td>";
            html += "</tr>";   
            }
            $("#duoi3sao tbody").html(html);
            $("#duoi3sao").parent().find(".pagination").html(data["paginationRender"]);
        }else {
            $("#duoi3sao tbody").html(noResult);
            $("#duoi3sao").parent().find(".pagination").html('');
        }
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
}

/**
 * Show proposes list by question
 * @param int questionId
 * @param int curpage
 * @param string token
 * @param string cssresultids
 */
function getProposesQuestion(questionId,curpage,token,cssresultids,orderby,ariatype){
    $("#danhsachdexuat tbody").html(strLoading);
    $.ajax({
        url: baseUrl + 'css/get_proposes_question/'+questionId+'/'+cssresultids+'/'+curpage+'/'+orderby+'/'+ariatype,
        type: 'post',
        data: {
            _token: token, 
        },
    })
    .done(function (data) { 
        //danh sach de xuat
        var countResult = data["cssResultdata"].length; 
        html = "";
        if(countResult > 0){
            for(var i=0; i<countResult; i++){
                html += "<tr>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["no"]+"</td>";
                html += "<td class=\"font-japan\">"+data["cssResultdata"][i]["projectName"]+"</td>";
                html += "<td class=\"font-japan\">"+data["cssResultdata"][i]["customerComment"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["makeDateCss"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["cssPoint"]+"</td>";
                html += "</tr>";   
            }
            $("#danhsachdexuat tbody").html(html);
            $("#danhsachdexuat").parent().find(".pagination").html(data["paginationRender"]);
        }else{
            $("#danhsachdexuat tbody").html(noResult);
            $("#danhsachdexuat").parent().find(".pagination").html('');
        }
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
}

/**
 * When apply is question type
 * In combobox question, remove empty cate (haven't any question)
 */
function removeEmptyCate(){
    var arr = []; // Storage questions id
    var arrCate = []; // Storage not empty categories id
    
    // Get questions id
    $("#question-choose option[data-type=question]").each(function(){
        arr.push($(this).attr('parent-id'));
    });
    
    // Get not empty categories id
    $("#question-choose option[class=parent]").each(function(){
        if(jQuery.inArray($(this).attr('data-id'), arr) !== -1){
            arrCate.push($(this).attr('data-id'));
            var parentId = $(this).attr('parent-id');
            // If have parent category
            if($("#question-choose option[class=parent][data-id="+parentId+"]").length > 0){
                arrCate.push($(this).attr('parent-id'));
                var elem = $("#question-choose option[class=parent][data-id="+parentId+"]");
                var grandId = elem.attr('parent-id');
                // If have grand parent category
                if($("#question-choose option[class=parent][data-id="+grandId+"]").length > 0){
                    arrCate.push(elem.attr('parent-id'));
                }
            }
        }
    });
    
    // Remove empty cate
    $("#question-choose option[class=parent]").each(function(){
        if(jQuery.inArray($(this).attr('data-id'), arrCate) == -1 && $(this).attr('data-type') !== 'overview'){
            $(this).remove();
        }
    });
}

function getCriteriaType(type){
    switch(type){
        case 'tcProjectType':
            criteriaType = "projectType";
            break;
        case 'tcTeam':
            criteriaType = "team";
            break;
        case 'tcPm':
            criteriaType = "pm";
            break;
        case 'tcBrse':
            criteriaType = "brse";
            break;
        case 'tcCustomer':
            criteriaType = "customer";
            break;
        case 'tcSale':
            criteriaType = "sale";
            break;
        case 'tcQuestion':
            criteriaType = "question";
            break;
    }
    return criteriaType;
}

function getDateDiff(date1,date2){
    var date1 = new Date(date1);
    var date2 = new Date(date2);
    var timeDiff = Math.abs(date2.getTime() - date1.getTime());
    var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
    return diffDays;
}

function newChart(idChart, data, label, type) {
    new Chart(idChart, {
        type: type,
        data: {
            labels: Object.values(label),
            datasets: [{
                data: Object.values(data),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            legend: {
                display: true,
                position: "top",
                align: "center",
                labels: {
                    fontSize: 18,
                    padding: 50,
                }
            },
        },
    });
}
