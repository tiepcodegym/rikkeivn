//action filter button
$().filterAjaxActionButton();

/* list project action */
function sortProject(elem,token) {
    renderProjectAnalyzeAction(elem);
}
/**
 * Sort data by comlumns in Project list table
 * @param html element elem
 * @param string token
 */
function renderProjectAnalyzeAction(elem, option) {
    if (option == undefined) {
        option = {};
    }
    $("#danhsachduan tbody").html(strLoading);
    if (option.curpage) {
        curpage = option.curpage;
    } else {
        curpage = 1;
    }
    filter = false;
    if (! elem) {
        elem = $('#danhsachduan > thead tr').find('th.sorting_desc:first, th.sorting_asc:first');
        if (elem.length) {
            elem = elem[0];
        } else {
            elem = $('#duoi3sao > thead tr th')[1];
        }
        filter = true;
    }
    var startDate = $("#startDate_val").val();
    var endDate = $("#endDate_val").val();
    var criteriaIds = $("#criteriaIds_val").val();
    var teamIds = $("#teamIds_val").val();
    var projectTypeIds = $("#projectTypeIds_val").val();
    var criteriaType = getCriteriaType($("#criteriaType_val").val());
    var sortType = getSortProjectType($(elem).attr('data-sort-type'));
    var ariaType = $(elem).attr('aria-type');
    dataSubmit = $().getFormDataSerializeFilter({
        dom: $('#danhsachduan .filter-input-grid:first input.filter-grid-ajax')
    });
    $.ajax({
        url: baseUrl + 'css/show_analyze_list_project/'+criteriaIds+'/'+teamIds+'/'+ projectTypeIds+'/'+startDate+'/'+endDate+'/'+criteriaType+'/'+curpage+'/'+sortType+'/'+ariaType,
        type: 'post',
        data: {
            _token: siteConfigGlobal.token,
            'filter': dataSubmit
        }
    })
    .done(function (data) { 
        if (! filter) {
            $(elem).parent().find('th:not(:first)').removeClass('sorting_asc').removeClass('sorting_desc').addClass('sorting');
            $(elem).parent().find('th:not(:first)').attr('aria-type','asc');
            if(ariaType == 'asc'){
                $(elem).attr('aria-type','desc');
                $(elem).removeClass('sorting').removeClass('sorting_desc').addClass('sorting_asc');
            }else if(ariaType == 'desc'){
                $(elem).attr('aria-type','asc');
                $(elem).removeClass('sorting').removeClass('sorting_asc').addClass('sorting_desc');
            }
        }
        var countResult = data["cssResultdata"]["data"].length; 
        var html = "";
        if (countResult > 0) {
            for(var i=0; i<countResult; i++){
                html += "<tr>";
                html += "<td class='text-align-center'>"+data["cssResultdata"]["data"][i]["id"]+"</td>";
                html += "<td>"+data["cssResultdata"]["data"][i]["project_name"]+"</td>";
                html += "<td>"+data["cssResultdata"]["data"][i]["teamName"]+"</td>";
                html += "<td>"+data["cssResultdata"]["data"][i]["pmName"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"]["data"][i]["css_end_date"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"]["data"][i]["css_result_created_at"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"]["data"][i]["point"]+"</td>";
                html += "</tr>";   
            }
            $("#danhsachduan tbody").html(html);
            $("#danhsachduan").parent().find(".pagination").html(data["paginationRender"]);
        } else{
            $("#danhsachduan tbody").html(noResult);
            $("#danhsachduan").parent().find(".pagination").html('');
        }
    });
}
/**
 * Show analyze project list paginate
 * @param int curpage
 * @param string token
 */
function showAnalyzeListProject(curpage, token, orderBy, ariaType) {
    renderProjectAnalyzeAction(null, {curpage: curpage});
}
jQuery(document).ready(function($) {
    $(document).on('keyup', '#danhsachduan .filter-input-grid input.filter-grid-ajax', function(event) {
        event.preventDefault();
        if (event.which == 13) { //enter press
            renderProjectAnalyzeAction(null);
        }
    });
    $(document).on('click', '.filter-action[data-table=table-filter-list-project] button', function(event) {
        renderProjectAnalyzeAction(null);
    });
});
/*--------------------- end list project action*/

/* action lt 3 star */
/**
 * Sort data by comlumns in less 3 star list table
 * @param html element elem
 * @param string token
 */
function sortLess3Star(elem){
    renderLessThreeStartAction(elem);
}

function renderLessThreeStartAction(elem, option) {
    if (option == undefined) {
        option = {};
    }
    $("#duoi3sao tbody").html(strLoading);
    cssresultids = $('#cssResultIds').val();
    if (option.curpage) {
        curpage = option.curpage;
    } else {
        curpage = 1;
    }
    filter = false;
    if (! elem) {
        elem = $('#duoi3sao > thead tr').find('th.sorting_desc:first, th.sorting_asc:first');
        if (elem.length) {
            elem = elem[0];
        } else {
            elem = $('#duoi3sao > thead tr th')[1];
        }
        filter = true;
    }
    sortType = getSortProjectType($(elem).attr('data-sort-type'));
    ariaType = $(elem).attr('aria-type');
    dataType = $(elem).attr('data-type'); 
    if(dataType === "question"){
        questionId = $(".box-select-question #question-choose").val();
        url = baseUrl + 'css/get_list_less_three_star_question/'+questionId+'/'+cssresultids+'/'+curpage+'/'+sortType+'/'+ariaType;
    } else if(dataType === 'all'){
        url = baseUrl + 'css/get_list_less_three_star/'+cssresultids+'/'+curpage+'/'+sortType+'/'+ariaType;
    }
    //curpage,token,cssresultids,orderby,ariatype
    dataSubmit = $().getFormDataSerializeFilter({
        dom: $('#duoi3sao .filter-input-grid:first input.filter-grid-ajax')
    });
    
    $.ajax({
        url: url,
        type: 'post',
        data: {
            _token: siteConfigGlobal.token,
            'filter': dataSubmit
        }
    })
    .done(function (data) {
        if (! filter) {
            $(elem).parent().find('th:not(:first)').removeClass('sorting_asc').removeClass('sorting_desc').addClass('sorting');
            $(elem).parent().find('th:not(:first)').attr('aria-type','asc');
            if(ariaType == 'asc'){
                $(elem).attr('aria-type','desc');
                $(elem).removeClass('sorting').removeClass('sorting_desc').addClass('sorting_asc');
            }else if(ariaType == 'desc'){
                $(elem).attr('aria-type','asc');
                $(elem).removeClass('sorting').removeClass('sorting_asc').addClass('sorting_desc');
            }
        }
        var countResult = data["cssResultdata"].length; 
        html = "";
        if(countResult > 0){
            for(var i=0; i<countResult; i++){
                html += "<tr>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["no"]+"</td>";
                html += "<td>"+data["cssResultdata"][i]["projectName"]+"</td>";
                html += "<td>"+data["cssResultdata"][i]["questionName"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["stars"]+"</td>";
                html += "<td>"+data["cssResultdata"][i]["comment"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["makeDateCss"]+"</td>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["cssPoint"]+"</td>";
                html += "</tr>";   
            }
            $("#duoi3sao tbody").html(html);
            $("#duoi3sao").parent().find(".pagination").html(data["paginationRender"]);
        } else{
            $("#duoi3sao tbody").html(noResult);
            $("#duoi3sao").parent().find(".pagination").html('');
        }
    });
}

/**
 * Show less 3* list pager action
 * @param int curpage
 * @param string token
 */
function getListLessThreeStar(curpage,token,cssresultids,orderby,ariatype){
    renderLessThreeStartAction(null, {curpage: curpage});
}

jQuery(document).ready(function($) {
    $(document).on('keyup', '#duoi3sao .filter-input-grid input.filter-grid-ajax', function(event) {
        event.preventDefault();
        if (event.which == 13) { //enter press
            renderLessThreeStartAction(null);
        }
    });
    $(document).on('click', '.filter-action[data-table=table-filter-lt-3-star] button', function(event) {
        renderLessThreeStartAction(null);
    });
});
/* ------------- end action lt 3 star */

/* action proposed */
/**
 * Sort data by comlumns in Proposed list table
 * @param html element elem
 * @param string token
 */
function sortProposed(elem,token){
    renderProsedListAnalyzeAction(elem);
}

function renderProsedListAnalyzeAction(elem, option) {
    if (option == undefined) {
        option = {};
    }
    $("#danhsachdexuat tbody").html(strLoading);
    var cssresultids = $('#cssResultIds').val();
    if (option.curpage) {
        curpage = option.curpage;
    } else {
        curpage = 1;
    }
    filter = false;
    if (! elem) {
        elem = $('#danhsachdexuat > thead tr').find('th.sorting_desc:first, th.sorting_asc:first');
        if (elem.length) {
            elem = elem[0];
        } else {
            elem = $('#danhsachdexuat > thead tr th')[1];
        }
        filter = true;
    }
    var ariaType = $(elem).attr('aria-type');
    var sortType = getSortProjectType($(elem).attr('data-sort-type'));
    var dataType = $(elem).attr('data-type'); 
    if(dataType === "question"){
        questionId = $(".box-select-question #question-choose").val();
        url = baseUrl + 'css/get_proposes_question/'+questionId+'/'+cssresultids+'/'+curpage+'/'+sortType+'/'+ariaType;
    } else if(dataType === 'all'){
        url = baseUrl + 'css/get_proposes/'+cssresultids+'/'+curpage+'/'+sortType+'/'+ariaType;
    }
    dataSubmit = $().getFormDataSerializeFilter({
        dom: $('#danhsachdexuat .filter-input-grid:first input.filter-grid-ajax')
    });
    $.ajax({
        url: url,
        type: 'post',
        data: {
            _token: siteConfigGlobal.token,
            'filter': dataSubmit
        }
    })
    .done(function (data) { 
        if (! filter) {
            $(elem).parent().find('th:not(:first)').removeClass('sorting_asc').removeClass('sorting_desc').addClass('sorting');
            $(elem).parent().find('th:not(:first)').attr('aria-type','asc');
            if(ariaType == 'asc'){
                $(elem).attr('aria-type','desc');
                $(elem).removeClass('sorting').removeClass('sorting_desc').addClass('sorting_asc');
            }else if(ariaType == 'desc'){
                $(elem).attr('aria-type','asc');
                $(elem).removeClass('sorting').removeClass('sorting_asc').addClass('sorting_desc');
            }
        }
        var countResult = data["cssResultdata"].length; 
        html = "";
        if(countResult > 0){
            for(var i=0; i<countResult; i++){
                html += "<tr>";
                html += "<td class='text-align-center'>"+data["cssResultdata"][i]["no"]+"</td>";
                html += "<td>"+data["cssResultdata"][i]["projectName"]+"</td>";
                html += "<td>"+data["cssResultdata"][i]["customerComment"]+"</td>";
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
    });
}
jQuery(document).ready(function($) {
    $(document).on('keyup', '#danhsachdexuat .filter-input-grid input.filter-grid-ajax', function(event) {
        event.preventDefault();
        if (event.which == 13) { //enter press
            renderProsedListAnalyzeAction(null);
        }
    });
    $(document).on('click', '.filter-action[data-table=table-filter-customer-suggest] button', function(event) {
        renderProsedListAnalyzeAction(null);
    });
});
/**
 * Show proposes list pager
 * @param int curpage
 * @param string token
 */
function getProposes(curpage,token,cssresultids,orderby,ariatype){
    renderProsedListAnalyzeAction(null, {curpage: curpage});
}
/* ------------- end action proposed */

function getSortProjectType(sortType){
    switch(sortType){
        case 'projectName':
            return 'css.project_name';
            break;
        case 'team':
            return 'teams.name';
            break;
        case 'pm':
            return 'css.pm_name';
            break;
        case 'projectDate':
            return 'css.end_date';
            break;    
        case 'makeDate':
            return 'result_make';
            break;
        case 'projectPoint':
            return 'result_point';
            break;
        case 'questionName':
            return 'question_name';
            break;
        case 'questionPoint':
            return 'point';
            break;
        case 'customerComment':
            return 'comment';
            break;
        case 'proposed':
            return 'proposed';
            break;
        case 'resultId':
            return 'id';
            break;
    }
}
jQuery(document).ready(function($) {
    /**
     * filter table criteria
     */
    $(document).on('keyup', '.table-filter-data .filter-input-grid input.filter-grid-disable', function(event) {
        inputChangeThis = $(this);
        inputValueFilter = {};
        inputFilterLength = 0;
        filterNullValue = true;
        inputChangeThis.parents('.filter-input-grid:first').children('.td-filter').find('input').each(function() {
            inputChangeSiblings = $(this);
            indexInput = inputChangeSiblings.parents('.td-filter:first').index();
            value = inputChangeSiblings.val();
            value = value.toLowerCase();
            inputValueFilter[indexInput] = {
                'reg': new RegExp(value,"g"),
                'value': value
            };
            inputFilterLength++;
            if (value) {
                filterNullValue = false;
            }
        });
        inputChangeThis.parents('.table-filter-data:first').children('tbody').children('tr').each(function() {
            trDataThis = $(this);
            if (! filterNullValue && trDataThis.children('td').length < inputFilterLength) {
                trDataThis.hide();
                return true;
            }
            trDataThis.children('td').each(function() {
                tdDataThis = $(this);
                text = tdDataThis.text();
                text = text.toLowerCase();
                indexTdData = $(this).index();
                if (inputValueFilter[indexTdData] !== undefined) {
                    if (! text && inputValueFilter[indexInput].value) {
                        trDataThis.hide();
                        return false;
                    }
                    if (! text.match(inputValueFilter[indexTdData].reg)) {
                        trDataThis.hide();
                        return false;
                    } else {
                        trDataThis.show();
                    }
                }
            });
        });
    });
    
    $(document).on('click', '.apply-analyze-b1', function(event) {
        $('#danhsachduan .filter-input-grid input').val('');
        $('#duoi3sao .filter-input-grid input').val('');
        $('#danhsachdexuat .filter-input-grid input').val('');
    });
});