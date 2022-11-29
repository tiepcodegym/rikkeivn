var globalCurrentPoint = 0;
var globalCurrentEditEl = null;
var globalCurrentSort = {
    'name' : false,
    'current_dir' : false,
    'desc' : {
        'class' : 'sorting_desc',
        'dir' : 'asc',
    },
    'asc' : {
        'class' : 'sorting_asc',
        'dir' : 'desc',
    },
};
var globalDefaultPrice = '30000000';

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
    },
});

$('#activity_month_from').datepicker({
    format: 'yyyy-mm',
    viewMode: "months",
    minViewMode: "months",
    // endDate: '0y',
    autoclose: true,
});

$('#activity_month_to').datepicker({
    format: 'yyyy-mm',
    viewMode: "months",
    minViewMode: "months",
    // endDate: '0y',
    autoclose: true,
});

$("#activity_month_from").datepicker("setDate", globalMonthFrom);

$("#activity_month_to").datepicker("setDate", globalMonthTo);

$(".input-select-team-member").val(gloabalTeamId);

$(document).ready(function () {
    // load data
    loadingIcon(true);
    loadData();
});

$('#name').keyup(function () {
    $('.name-error').text('');
});

$(document).on('blur', '.tblDetailInput input, .tblDetailInput select', function () {
    if ($(this).val()) {
        $(this).removeClass('error-input');
    }

    if (!$('#tblOperationBody').find('.error-input').length) {
        $('.error-input-mess').hide();
    } else {
        $('.error-input-mess').show();
    }
});

// Even change input
$('#activity_month_from, #activity_month_to, #project_state').change(function () {
    globalPage = 1;
    if (globalMonthFrom !== $('#activity_month_from').val() || globalMonthTo !== $('#activity_month_to').val() || globalProjectState !== $('#project_state').val()) {
        globalMonthFrom = $('#activity_month_from').val();
        globalMonthTo = $('#activity_month_to').val();
        globalProjectType = $('#project_type').val();
        globalProjectState = $('#project_state').val();
        if (globalMonthFrom > globalMonthTo) {
            $('.alert-message-error').removeClass('hidden');
            $('.str-error-message').text(globalMessage['Montherror']);
        } else {
            $('.alert-message-error').addClass('hidden');
            $('.str-error-message').text('');
            loadData();
        }
    }
});

$('#project_type').change(function () {
    globalPage = 1;
    globalMonthFrom = $('#activity_month_from').val();
    globalMonthTo = $('#activity_month_to').val();
    globalProjectType = $('#project_type').val();
    globalProjectState = $('#project_state').val();
    loadData();
});
// Submit form
$('.btn-submit').click(function (e) {
    e.preventDefault();
    var currentModal = $(e.target).closest('.modal-body');

    var _token = currentModal.find("input[name='_token']").val();
    var name = currentModal.find("input[name='name']").val();
    var nameOld = currentModal.find("input[name='name']").data('name-old');
    var type = currentModal.find('.type').val();
    var typeOld = currentModal.find('.type').data('type-old');
    var datadetai = setDataJson(currentModal);
    var kind = currentModal.find('.kind_id').val();

    $.ajax({
        url: globalPostOperationUrl,
        type: 'post',
        dataType: 'json',
        data: {
            _token: _token,
            name: name,
            type: type,
            kind_id: kind,
            name_old: nameOld,
            type_old: typeOld,
            datadetai: datadetai,
        },
        // timeout: requestTimeOut,
        success: function (data) {
            if (data.status) {
                currentModal.find("#close-modal").click();
                $('#close-modal1').click();
                loadData();
                $('.alert-message').removeClass('hidden');
                setTimeout(function () {
                    $('.alert-message').addClass('hidden');
                }, 3000);
            }
        },
        error: function (reject) {
            if (reject.status === 422) {
                setErrorInput();
                var errors = $.parseJSON(reject.responseText);
                $.each(errors, function (key, value) {
                    $('.' + key + '-error').html(value);
                });
            }
        },
    });
});

$('.input-select-team-member').change(function () {
    loadData();
});

//update input point
$(document).on("click", ".col-point", function () {
    var pointRoot = $(this).text();
    globalCurrentEditEl = this;
    if (pointRoot) {
        globalCurrentPoint = pointRoot;
        $(this).html('<input class="input-point" type="text" value="' + pointRoot + '"/>');
    }
    $('.input-point').focus();
    $('.input-point').select();
});

$(document).on('keypress', '.input-point', function (e) {
    if (e.which === 13) {
        $(this).blur();
    }
});

// focus out point input event
$(document).on("blur", ".input-point", function (e) {
    var $parent = $(e.target).closest('.col-point');
    var oldValue = $($parent).data('old-value');
    var value = $(e.target).val();
    if (+oldValue !== +value) {
        mainUpdatePoint(e.target);
        $($parent).data('old-value', +value);
    } else {
        $($parent).html(parseFloat(oldValue).toFixed(2));
    }
});

function getCurrentSortName() {
    if (globalCurrentSort['name']) return globalCurrentSort['name'];

    return '';
}

function getCurrentDir() {
    if (globalCurrentSort['current_dir']) return globalCurrentSort['current_dir'];

    return '';
}

function getSortingClass(sortName) {
    var currentDir = getCurrentDir();
    var currentSortName = getCurrentSortName();
    if (currentDir && sortName === currentSortName) return globalCurrentSort[currentDir]['class'];

    return '';
}

function getSortingDir(sortName) {
    var currentDir = getCurrentDir();
    var currentSortName = getCurrentSortName();
    
    if (currentDir && sortName === currentSortName) return globalCurrentSort[currentDir]['dir'];

    return 'desc';
}

function mainUpdatePoint(e) {
    var productionCostIdCtr = $(e).parents('td').find('.production_cost_id');
    var yearMonth = $(e).parents('td').data('year-month');
    var projectId = $(e).parents('tr').data('project-id');
    var cost = $(e).parents('tr').data('cost');
    var teamId = $(e).parents('tr').data('team-id');
    var inputUpdatedPoint = parseFloat($(e).val());
    var storePoint = 0;
    if (!Number.isNaN(inputUpdatedPoint)) {
        switch (true) {
            case inputUpdatedPoint <= 0:
                break;
            default:
                storePoint = inputUpdatedPoint;
        }
    }
    var data = {
        id: productionCostIdCtr.val(),
        approved_production_cost: parseFloat(storePoint).toFixed(2),
        team_id: teamId,
        project_id: projectId,
        cost: cost,
        year: yearMonth.split('-')[0],
        month: yearMonth.split('-')[1],
        is_future: 0
    };
    var dataProjsAdditional = {
        id: $(e).parents('tr').data('additional-id'),
        approved_production_cost: parseFloat(storePoint).toFixed(2),
        is_future: 1
    };
    var $elColPoint = $(e).parent();
    if ($(e).parents('tr').data('additional-id') === 0) {
        updatePoint(data, productionCostIdCtr, $elColPoint.data('old-value'));
    } else {
        updatePoint(dataProjsAdditional, productionCostIdCtr, $elColPoint.data('old-value'));
    }
    $($elColPoint).html(parseFloat(storePoint).toFixed(2));
    $($elColPoint).data('old-value', +storePoint);
}

function updatePoint(data, productionCostIdCtr, oldValue) {
    $.ajax({
        url: globalGetPointUpdateUrl,
        type: 'post',
        dataType: 'json',
        data: data,
        success: function (data) {
            if (data.data) {
                productionCostIdCtr.val(data.data.id);
                var indexToDefineSelectorTotalMonth = data.data.year + '-' + (+data.data.month < 10 ? ('0' + (+data.data.month)) : data.data.month);
                var selectorTotalMonth = $('.head-index-total[data-index="' + indexToDefineSelectorTotalMonth + '"]');
                var currentTotalPointOfSelectedMonth = +selectorTotalMonth.html();
                var newTotalPointOfSelectedMonth = currentTotalPointOfSelectedMonth - (+oldValue) + (+data.data.approved_production_cost);
                $(selectorTotalMonth).html(newTotalPointOfSelectedMonth.toFixed(2));
            }
        },
    });
}

// Create array, get from month to month in year
function getMonthInYear(from_month, to_month) {
    from_month = moment(from_month);
    to_month = moment(to_month);
    var arr_month = [];
    while (from_month <= to_month) {
        arr_month.push(from_month.format('YYYY-MM'));
        from_month.add(1, 'months');
    }

    return arr_month;
}

// Setdata
function setData(from_month, to_month) {
    var strHtml = '';
    var strHtmlTotal = '';
    var companyElement, typeElement;
    var arr_moth;

    $('#tblBatchBody thead tr').find('.type').nextAll().remove();
    $('#tblBatchBody thead tr').find('.total').nextAll().remove();

    arr_moth = getMonthInYear(from_month, to_month);

    for (var i = 0, l = arr_moth.length; i < l; i++) {
        var monthToday = '';
        if (arr_moth[i] === globalCurrentMonth) {
            monthToday = 'month-today';
        }
        strHtml += '<th data-value="' + arr_moth[i] + '" id="head-month' + i + '" class="cell-month item-month ' + monthToday + '">' + arr_moth[i];
        strHtml += '</th>';
        strHtmlTotal += '<th data-index="' + arr_moth[i] + '" class="head-index-total" ></th>';
    }

    strHtml += '<th class="text-center head-action cell-status sorting ' + getSortingClass('status') + '" data-dir="' + getSortingDir('status') + '" data-order="status">Status</th>';
    strHtml += '<th class="head-action cell-start sorting ' + getSortingClass('start_date') + '" data-dir="' + getSortingDir('start_date') + '" data-order="start_date">Start Date</th>';
    strHtml += '<th class="head-action cell-end sorting ' + getSortingClass('end_date') + '" data-dir="' + getSortingDir('end_date') + '" data-order="end_date">End Date</th>';
    strHtml += '<th class="head-action text-center cell-action" >Action</th>';

    strHtmlTotal += '<th class="text-center cell-status" >  </th>';
    strHtmlTotal += '<th class="head-action cell-start" ></th>';
    strHtmlTotal += '<th class="head-action cell-end" ></th>';
    strHtmlTotal += '<th class="head-action text-center cell-action" ></th>';

    //Triggert Sort company
    companyElement = $('#jsSortCompanyName');
    $(companyElement).removeClass();
    $(companyElement).addClass(getSortingClass('company_name'));
    $(companyElement).addClass('sorting cell-name head-action');
    $(companyElement).data('dir', getSortingDir('company_name'));
    $(companyElement).data('order', 'company_name');

    //Triggert Sort company
    typeElement = $('#jsSortType');
    $(typeElement).removeClass();
    $(typeElement).addClass(getSortingClass('type'));
    $(typeElement).addClass('sorting cell-type head-action');
    $(typeElement).data('dir', getSortingDir('type'));
    $(typeElement).data('order', 'type');

    $('#tblBatchBody thead tr').find('.type').after(strHtml);
    $('#tblBatchBody thead tr').find('.total').after(strHtmlTotal);
}

function loadData() {
    // load
    showLoading();
    $('.table-responsive ').addClass('hidden');
    setData(globalMonthFrom, globalMonthTo);
    var currentPage = globalPage ? globalPage : 1;
    var currentUrl = [location.protocol, '//', location.host, location.pathname].join('');
    var selectValue;
    var currentSortName = getCurrentSortName();
    var currentDir = getCurrentDir();

    if ($('.input-select-team-member').length) {
        selectValue = $('.input-select-team-member').val();
    } else {
        selectValue = $('#selected-team').data('id');
    }
    var parameter = {
        monthFrom: globalMonthFrom,
        monthTo: globalMonthTo,
        page: currentPage,
        url: currentUrl,
        limit: globalPageLimit,
        typeViewMain: globalTypeViewMain,
        selectedType: JSON.stringify(globalProjectType),
        selectedState: JSON.stringify(globalProjectState)
    };
    if (selectValue) {
        parameter.teamId = selectValue;
    }
    if (currentSortName) {
        parameter.currentSortName = currentSortName;
    }
    if (currentDir) {
        parameter.currentDir = currentDir;
    }
    $.ajax({
        url: globalGetOperationUrl,
        type: 'post',
        dataType: 'json',
        data: parameter,
        success: function (data) {
            $('#example2_paginate').empty();
            if (data.status) {
                globalIndex = data.data.from;
                renderHtmlData(data.data.data);
                setTotalMMEachMonth(data.totalMMEachMonth);
                $('#example2_paginate').append(data.html);

                hideLoading();
                loadingIcon(false);
            }
        },
        error: function (x, t, m) {
            if (t === 'timeout') {
                $('#modal-warning-notification .modal-body p.text-default').text(globalErrorTimeoutText);
            } else {
                $('#modal-warning-notification .modal-body p.text-default').text(globalErrorText);
            }
        },
    });
}

// --------------------------- Event Click Pagination ------------------------ //

$('body').on('click', '.head-action.sorting', function (event) {
    event.preventDefault();
    globalCurrentSort.name = $(this).data('order');
    globalCurrentSort.current_dir = $(this).data('dir');
    loadData();
});

$('body').on('click', '.pagination a', function (event) {
    event.preventDefault();
    var page = $(this).attr('href').split('page=')[1];
    globalPage = page;
    var currentUrl = [location.protocol, '//', location.host, location.pathname].join('');
    globalCurrentUrl = currentUrl;
    var month_from = $('#activity_month_from').val();
    var month_to = $('#activity_month_to').val();
    var selectValue;
    if ($('.input-select-team-member').length) {
        selectValue = $('.input-select-team-member').val();
    } else {
        selectValue = $('#selected-team').data('id');
    }
    $('.project').first().click();
    var parameter = {
        monthFrom: month_from,
        monthTo: month_to,
        page: page,
        url: currentUrl,
        limit: $('.input-sm').val(),
        typeViewMain: globalTypeViewMain,
        selectedType: JSON.stringify(globalProjectType),
        selectedState: JSON.stringify(globalProjectState)
    };
    if (selectValue) {
        parameter.teamId = selectValue;
    }
    var currentSortName = getCurrentSortName();
    var currentDir = getCurrentDir();
    if (currentSortName) {
        parameter.currentSortName = currentSortName;
    }
    if (currentDir) {
        parameter.currentDir = currentDir;
    }
    callAjaxDelele(parameter);
});

// -----------------------Even click button create project---------------------------//

$('body').on('click', '.button_tracking', function (event) {
    $('#tblOperationBody tbody tr').remove();
    $('#name').val('');
    $('.labl-error').text('');
    $('#taskModal .btn-operation-project').click();
});

$('body').on('click', '.btn-edit', function (event) {
    $('#name').val('');
    $('.labl-error').text('');
    var projectName = $(event.target).closest('.cell-action').find('.btn-edit').data('project-name');
    var typeProject = $(event.target).closest('.cell-action').find('.btn-edit').data('project-type');
    var kindProject = $(event.target).closest('.cell-action').find('.btn-edit').data('project-kind');
    initDataDetailOfProjectFuture(projectName, typeProject, kindProject);
});

function initDataDetailOfProjectFuture(projectName, typeProject, kindProject) {
    var data = {
        name: projectName,
        type: typeProject,
        kind_id : kindProject
    };

    $.ajax({
        url: globalGetDataDetailProjectFutureUrl,
        type: 'get',
        data: data,
        success: function (data) {
            if (data.status) {
                if (data.data) {
                    renderProjectDataDetailIntoView(data.data);
                }
            }
        },
    });
}

function renderProjectDataDetailIntoView(datas) {
    $('#projectFuture .box.box-info').html(datas);
    removeUselessTeam('.project-future-team-member');
}

/* -----------------------------button add row project detail------------------------*/
$('body').on('click', '.btn-operation-project', function (event) {
    var index = $('#tblOperationBody tbody').find('.tblDetailInput').length;
    var parent = $('#tblOperationBody tbody').find('tr:last');
    var currentTarget = $(event.target).closest('.modal-body');

    if (index > 0) {
        var lastTabindex = parent.attr('tabindex');
        var nextMonth = $('#activity_month_from' + lastTabindex).val();
        addRowDetail(null, index, moment(nextMonth).add('month', 1).format("YYYY-MM"), currentTarget);
    } else {
        addRowDetail(null, 0, moment().format("YYYY-MM"), currentTarget);
        $('#tblOperationBody tbody').find('tr[tabindex=1]').addClass('table-css-no-active');
    }
    $('#cost_approved_production' + (index + 1)).focus();
    if ((index + 1) % 2 == 0) {
        $('#tblOperationBody tbody').find('tr[tabindex=' + (index + 1) +']').addClass('table-css-active');
    } else {
        $('#tblOperationBody tbody').find('tr[tabindex=' + (index + 1) +']').addClass('table-css-no-active');
    }
});

// ------------------------------add row detail-------------------------------------//
$('body').on('click', '.btn-add-row', function (event) {
    var $this = $(this);
    var strHtml = '';
    var parent = $this.closest('tr');
    var rowDetail = $this.closest('tr');
    var index = Number(parent.attr('data-row'));
    var rowspan = parent.find('td:first').attr('rowspan');
    var tabindex = parent.attr('tabindex');
    var lengthTr = Number($('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').length) + 1;

    if (!rowspan) {
        strHtml = renderRowSpan(tabindex, (index + 1));
        var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
        trFirst.find('td:first').attr('rowspan', (index + 1));
    } else {
        strHtml = renderRowSpan(tabindex, Number(rowspan) + 1);
        var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
        trFirst.find('td:first').attr('rowspan', (Number(rowspan) + 1));
    }
    rowDetail.after(strHtml);
    if (tabindex % 2 == 0) {
        $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').addClass('table-css-active');
    } else {
        $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').addClass('table-css-no-active');
    }
    if (lengthTr > 1) {
        var fristItem = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
        var itemNotLast = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']:not(:last)');
        itemNotLast.find('.btn-add-row').addClass('hidden');
        fristItem.find('.btn-add-row, .btn-remove-row').addClass('hidden');
    }
    if (!rowspan) {
        renderDataChildSelectTeam(tabindex, (index + 1));
    } else {
        renderDataChildSelectTeam(tabindex, Number(rowspan) + 1);
    }
});

//------------------------------------ Remove row detail ------------------------------//
$('body').on('click', '.btn-remove-row ', function (event) {
    var $this = $(this);
    var rowDetail = $this.closest('tr');
    var parent = $this.closest("tr");

    if (parent.find('tr').length == 1) {
        parent.remove();
    } else {
        rowDetail.remove();
        var tabindex = parent.attr('tabindex');
        var trFirst = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').first();
        var Rowspan = trFirst.find('td:first').attr('rowspan');
        trFirst.find('td:first').attr('rowspan', (Rowspan - 1));
        var itemLast = $('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').last();
        itemLast.find('.btn-add-row').removeClass('hidden');
        if ($('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').length) {
            itemLast.find('.btn-remove-row ').removeClass('hidden');
        }
        itemLast.find('.btn-add-row').removeClass('hidden');
        if ($('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']').length) {
            itemLast.find('.btn-remove-row ').removeClass('hidden');
        }
    }
});

// ------------------------------------ Ajax Delete Operation ------------------------//
$('body').on('click', '.btn-ok', function (event) {
    var selectValue;
    if ($('.input-select-team-member').length) {
        selectValue = $('.input-select-team-member').val();
    } else {
        selectValue = $('#selected-team').data('id');
    }
    $.ajax({
        url: globalDeleteOperationUrl,
        type: 'post',
        dataType: 'json',
        data: {
            id: globalId,
        },
        success: function (data) {
            if (data.status) {
                var parameter = {
                    monthFrom: globalMonthFrom,
                    monthTo: globalMonthTo,
                    page: globalPage,
                    url: globalCurrentUrl,
                    limit: $('.input-sm').val(),
                    typeViewMain: globalTypeViewMain,
                    selectedType: JSON.stringify(globalProjectType),
                    selectedState: JSON.stringify(globalProjectState)
                };
                if (selectValue) {
                    parameter.teamId = selectValue;
                }
                var currentSortName = getCurrentSortName();
                var currentDir = getCurrentDir();
                if (currentSortName) {
                    parameter.currentSortName = currentSortName;
                }
                if (currentDir) {
                    parameter.currentDir = currentDir;
                }
                callAjaxDelele(parameter);

                $('.alert-message-delete').removeClass('hidden');
                setTimeout(function () {
                    $('.alert-message-delete').addClass('hidden');
                }, 3000);
            }
        },
        error: function (x, t, m) {
            if (t === 'timeout') {
                $('#modal-warning-notification .modal-body p.text-default').text(globalErrorTimeoutText);
            } else {
                $('#modal-warning-notification .modal-body p.text-default').text(globalErrorText);
            }
        },
    });
});

$('body').on('click', '#dele-button', function (event) {
    globalId = '';
    globalId = $(this).attr('data-id');
});

$('body').on('change', '.input-sm', function (event) {
    globalPageLimit = $('.input-sm').val();
    globalPage = 1;
    loadData();
    globalIndex = 1;
});

// ------------------------------------ Add row table --------------------------------//
function addRowDetail(p_objData, numRowNo, data, currentTarget) {
    var initIndex = numRowNo + 1;
    var strApprovedCost = '';
    var strApprovedPrice  = globalDefaultPrice;

    var strHtml = '';
    // strHtml += '<tbody tabindex="' + initIndex + '" class="tblDetailInput">';
    strHtml += '    <tr data-row="' + 1 + '" tabindex="' + initIndex + '" class="tblDetailInput">';
    strHtml += '       <td>';
    strHtml += '          <input type="text" id="activity_month_from' + initIndex + '" name="month" class="form-control form-inline month-picker maxw-100" value="' + data + '" autocomplete="off">';
    strHtml += '       </td>';
    strHtml += '       <td class="approved-cost-wrapper">';
    strHtml += '          <input  type="number" step="any" min="0" class="form-control " id="cost_approved_production' + initIndex + '" name="cost_approved_production' + initIndex + '" placeholder="' + globalHeader['Approved production cost'] + '" value="' + strApprovedCost + '" />';
    strHtml += '       </td>';
    strHtml += '       <td>';
    strHtml += '         <div class="dropdown team-dropdown">';
    strHtml += '           <span>';
    strHtml += '               <select id="team-group-' + initIndex + '" name="filter[exception][teamId]" class="form-control filter-grid select-search has-search team-dev-tree' + initIndex + '">';
    strHtml += '               </select>';
    strHtml += '           </span>';
    strHtml += '         </div>';
    strHtml += '       </td>';
    strHtml += '       <td>';
    strHtml += '        <div class="operation-note-input">';
    strHtml += '<textarea rows="1" class="non-required note_item form-control"  id="approve_cost_note' + initIndex + '" name="approve_cost_note' + initIndex + '" placeholder="' + globalHeader['Note'] + '"></textarea>';
    strHtml += '        </div>';
    strHtml += '       </td>';
    strHtml += '       <td>';
    strHtml += '        <div class="operation-note-input">';
    strHtml += '<input type="number" step="any" min="0" class="non-required approve_cost_item form-control" id="price' + initIndex + '" name="price" value="' + strApprovedPrice + '" />';
    strHtml += '        </div>';
    strHtml += '       </td>';

    strHtml += '<td>';
    strHtml += '<select class="form-control" id="unit_price' + initIndex + '" name="unit_price">';
    for (var key in globalUnitPrices) {
        // check if the property/key is defined in the object itself, not in parent
        if (globalUnitPrices.hasOwnProperty(key)) {
            strHtml += '<option value="' + key + '" ';
            strHtml += '>';
            strHtml += globalUnitPrices[key];
            strHtml += '</option>';
        }
    }
    strHtml += '</select></td>';

    strHtml += '       <td> <span href="#" style="color: seagreen" class="btn-add-row"><i class="fa fa-plus"></i></span> </td>';
    strHtml += '       <td> <span href="#" style="color: #d33724" class="btn-remove-row "><i class="fa fa-minus"></i></span>  </td>';
    strHtml += '    </tr>';
    // strHtml += '</tbody>';
    currentTarget.find('#tblOperationBody tbody').append(strHtml);

    $('.month-picker').datepicker({
        format: 'yyyy-mm',
        viewMode: "months",
        minViewMode: "months",
        startDate: "0m",
        autoclose: true,
    }).on('changeDate', function (e) {
        //    nothing
    });
    renderDataSelectTeam(initIndex);
}

//---------------------------- Function render rowspan when click button (add or remove) ----//
function renderRowSpan(tabindex, index) {

    var strApprovedCost = '';
    var strApprovedPrice = globalDefaultPrice;
    var strHtml = '';
    strHtml += '    <tr data-row="' + index + '" tabindex="' + tabindex + '">';
    strHtml += '       <td class="approved-cost-wrapper">';
    strHtml += '          <input type="number" step="any" min="0" class="form-control" id="cost_approved_production' + tabindex + '_' + index + '" name="cost_approved_production" placeholder="' + globalHeader['Approved production cost'] + '" value="' + strApprovedCost + '" />';
    strHtml += '       </td>';
    strHtml += '       <td>';
    strHtml += '         <div class="dropdown team-dropdown">';
    strHtml += '           <span>';
    strHtml += '               <select id="team-group-' + tabindex + '_' + index + '" name="filter[' + tabindex + '][' + index + '][exception][teamId]" class="form-control filter-grid select-search has-search team-dev-tree' + tabindex + '_' + index + '">';
    strHtml += '               </select>';
    strHtml += '           </span>';
    strHtml += '         </div>';
    strHtml += '       </td>';
    strHtml += '       <td>';
    strHtml += '        <div class="operation-note-input">';
    strHtml += '<textarea rows="1" class="non-required note_item form-control"  id="approve_cost_note' + tabindex + '_' + index + '" name="approve_cost_note"  placeholder="' + globalHeader['Note'] + '" ></textarea>';
    strHtml += '        </div>';
    strHtml += '       </td>';
    strHtml += '       <td>';
    strHtml += '        <div class="operation-note-input">';
    strHtml += '<input type="number" step="any" min="0" class="non-required approve_cost_item form-control" id="price' + tabindex + '_' + index + '" name="price" value="' + strApprovedPrice + '" />';
    strHtml += '        </div>';
    strHtml += '       </td>';

    strHtml += '<td>';
    strHtml += '<select class="form-control" id="unit_price' + tabindex + '_' + index + '" name="unit_price">';
    for (var key in globalUnitPrices) {
        // check if the property/key is defined in the object itself, not in parent
        if (globalUnitPrices.hasOwnProperty(key)) {
            strHtml += '<option value="' + key + '" ';
            strHtml += '>';
            strHtml += globalUnitPrices[key];
            strHtml += '</option>';
        }
    }
    strHtml += '</select></td>';

    strHtml += '       <td> <span href="#" style="color: seagreen" class="btn-add-row"><i class="fa fa-plus"></i></span> </td>';
    strHtml += '       <td> <span href="#" style="color: #d33724" class="btn-remove-row "><i class="fa fa-minus"></i></span>  </td>';
    strHtml += '    </tr>';

    return strHtml;
}

// Show loading
function showLoading() {
    $('.loading-icon').removeClass('hidden');
}

// hide loading
function hideLoading() {
    $('.loading-icon').addClass('hidden');
}

// function render select
function renderDataSelectTeam(initIndex) {
    if (typeof globalTeamModule !== 'undefined' && $('select.team-dev-tree' + initIndex).length) {
        var teamDevOption = RKfuncion.teamTree.init(globalTeamModule.teamPath, globalTeamModule.teamSelected);
        var htmlTeamDevOption, disabledTeamDevOption, selectedTeamDevOption, cssdisabled;
        $.each(teamDevOption, function (i, v) {
            disabledTeamDevOption = v.disabled ? ' disabled' : '';
            cssdisabled = v.disabled ? ' style-disabled' : '';
            selectedTeamDevOption = v.selected ? ' selected' : '';
            htmlTeamDevOption += '<option class="item-team ' + cssdisabled + '" data-old="' + v.label + '" value="' + v.id + '"' + disabledTeamDevOption + ''
                + selectedTeamDevOption + '>' + v.label + '</option>';
        });

        $('select.team-dev-tree' + initIndex).append('<option value="" class="item-team action-item" data-old="">&nbsp;</option>');
        $('select.team-dev-tree' + initIndex).append(htmlTeamDevOption);
    }
}

// function render select
function renderDataChildSelectTeam(initIndex, indexRow) {
    if (typeof globalTeamModule !== 'undefined' && $('select.team-dev-tree' + initIndex + '_' + indexRow).length) {
        var teamDevOption = RKfuncion.teamTree.init(globalTeamModule.teamPath, globalTeamModule.teamSelected);
        var htmlTeamDevOption, disabledTeamDevOption, selectedTeamDevOption, cssdisabled;
        $.each(teamDevOption, function (i, v) {
            disabledTeamDevOption = v.disabled ? ' disabled' : '';
            cssdisabled = v.disabled ? ' style-disabled' : '';
            selectedTeamDevOption = v.selected ? ' selected' : '';
            htmlTeamDevOption += '<option class="item-team ' + cssdisabled + '" data-old="' + v.label + '" value="' + v.id + '"' + disabledTeamDevOption + ''
                + selectedTeamDevOption + '>' + v.label + '</option>';
        });

        $('select.team-dev-tree' + initIndex + '_' + indexRow).append('<option value="" class="item-team action-item" data-old="">&nbsp;</option>');
        $('select.team-dev-tree' + initIndex + '_' + indexRow).append(htmlTeamDevOption);
    }
}

// Set Data Json
function setDataJson(currentModal) {
    var $tbodyCtr = currentModal.find('#tblOperationBody tbody tr.tblDetailInput');
    var objectParent = {};
    var data = [];

    $tbodyCtr.each(function () {
        var tabindex = $(this).attr('tabindex');
        var dataMonth = currentModal.find('#activity_month_from' + tabindex).val().split('-');
        var g_arrDetailData = [];

        currentModal.find('#tblOperationBody tbody').find('tr[tabindex=' + tabindex +']:not(:first)').each(function (index) {
            var dataRow = $(this).attr('data-row');
            var teamId = currentModal.find('#team-group-' + tabindex + '_' + dataRow).val();
            var approved_production_cost = currentModal.find('#cost_approved_production' + tabindex + '_' + dataRow).val();
            var approve_cost_note = currentModal.find('#approve_cost_note' + tabindex + '_' + dataRow).val();
            var price = currentModal.find('#price' + tabindex + '_' + dataRow).val();
            var unit_price = currentModal.find('#unit_price' + tabindex + '_' + dataRow).val();
            var billable_effort_select = currentModal.find('#billable_effort_select' + tabindex + '_' + dataRow).val();

            g_arrDetailData.push({
                teamId: teamId,
                approved_production_cost: approved_production_cost,
                billable_effort_select: billable_effort_select,
                approve_cost_note: approve_cost_note,
                price : price,
                unit_price : unit_price,
            });
        });

        objectParent = {
            teamId: currentModal.find('#team-group-' + tabindex).val(),
            approved_production_cost: currentModal.find('#cost_approved_production' + tabindex).val(),
            approve_cost_note: currentModal.find('#approve_cost_note' + tabindex).val(),
            billable_effort_select: currentModal.find('#billable_effort_select' + tabindex).val(),
            month: dataMonth['1'],
            year: dataMonth['0'],
            price : currentModal.find('#price' + tabindex).val(),
            unit_price : currentModal.find('#unit_price' + tabindex).val(),
            detail: g_arrDetailData,
        };
        data.push(objectParent);
    });

    return data;
}

// Set Data Json
function setErrorInput() {
    var $tbodyCtr = $('#tblOperationBody tbody');

    $tbodyCtr.each(function () {
        $(this).find('input, select').each(function () {
            if (!$(this).val()) {
                $('.error-input-mess').text(globalMessage['Input all']);
                $(this).addClass('error-input');
            } else {
                $(this).removeClass('error-input');
            }
        });

        if (!$('#tblOperationBody').find('.error-input').length) {
            $('.error-input-mess').hide();
        } else {
            $('.error-input-mess').show();
        }
    });
}

function generateProjectUrl(projectId, name) {
    if (projectId > 0) {
        return '<a target="_blank" class="cell-team-href" href="' + globalProjectUrl.replace('id', projectId) + '">' + name + '</a>';
    }

    return name;
}

function generateProjectTooltip(cost, typeMM) {
    if (cost === '0') {
        return '';
    }

    return globalTooltipCost + ': ' + cost + ' ' + typeMM;
}

// render html details
function renderHtmlData(p_data) {
    var strHtml;
    var lengtTh = $('#tblBatchBody thead tr th').length;

    $('#tblBatchBody tbody tr').remove();
    if (p_data.length && typeof p_data[0].id !== 'undefined') {
        for (var i = 0, l = p_data.length; i < l; i++) {
            var strStartDate = p_data[i].start_date.slice(0, 7);
            var strEndDate = p_data[i].end_date.slice(0, 7);
            var additionalId = p_data[i].status === 'Future' ? p_data[i].id : '0';
            strHtml = '';
            strHtml += '<tr data-cost="' +  p_data[i].cost_approved_production + '"  data-project-id="' + p_data[i].project_id + '" data-team-id="' + p_data[i].team_id + '" data-additional-id="' + additionalId + '">';
            strHtml += '    <td class="cell-no">' + globalIndex + '</td>';
            strHtml += '    <td class="cell-name">' + p_data[i].company_name + '</td>';
            strHtml += '    <td data-toggle="tooltip" data-container="body" title="' + generateProjectTooltip(p_data[i].cost_approved_production, p_data[i].type_mm ) + '" class="cell-name">' + generateProjectUrl(p_data[i].project_id, p_data[i].name) + '</td>';
            strHtml += '    <td class="cell-type">' + p_data[i].type + '</td>';
            strHtml += '    <td class="cell-team">' + p_data[i].team + '</td>';

            $('#tblBatchBody thead tr th.item-month').each(function () {
                var dataVal = $(this).attr('data-value');
                if (p_data[i][dataVal]) {
                    strHtml += '<td class="text-center cell-month" data-year-month="' + dataVal + '">' +
                        '<label class="col-point" data-old-value="' + p_data[i][dataVal].cost + '">' + parseFloat(p_data[i][dataVal].cost).toFixed(2) + '</label>' +
                        '<input type="hidden" class="production_cost_id" value="' + p_data[i][dataVal].id + '"/>' +
                        '<button type="submit" class="warning-action warning-notification _btn_feedback is-disabled warning-over-cost hidden" data-noti="' + generateProjectTooltip(p_data[i].cost_approved_production, p_data[i].type_mm) + '. <br> ' + globalErrorOverCost + '"/>' +
                        '</td>';
                } else if (p_data[i].status !== 'Future' && dataVal >= strStartDate && dataVal <= strEndDate) {
                    strHtml += '<td class="text-center cell-month" data-year-month="' + dataVal + '">' +
                        '<label class="col-point" data-old-value="0">0.00</label><input type="hidden" class="production_cost_id" value=""/>' +
                        '<button type="submit" class="warning-action warning-notification _btn_feedback is-disabled warning-over-cost hidden" data-noti="' + generateProjectTooltip(p_data[i].cost_approved_production, p_data[i].type_mm) + '. <br> ' + globalErrorOverCost + '"/>' +
                        '</td>';
                } else {
                    strHtml += '<td class="cell-month cell-month"></td>';
                }
            });
            strHtml += '<td class="text-center cell-status">' + p_data[i].status + '</td>';
            if (p_data[i].status === 'Future') {
                strHtml += '<td class="cell-start"></td>';
                strHtml += '<td class="cell-end"></td>';
                strHtml += '<td class="text-center cell-action"><button data-toggle="modal" data-target="#projectFuture" data-keyboard="false" data-backdrop="static" data-project-name="' +  p_data[i].name + '" data-project-type="' +  p_data[i].type_id + '" data-project-kind="' +  (p_data[i].kind_id ? p_data[i].kind_id : 6) + '" data-id="' + p_data[i].id + '" type="button" class="btn-edit" ><span><i class="fa fa-edit"></i></span></button><button data-id="' + p_data[i].id + '" type="button" id="dele-button" class="btn-delete delete-confirm" ><span><i class="fa fa-trash"></i></span></button></td>';
            } else {
                strHtml += '<td class="text-center cell-start">' + p_data[i].start_date + '</td>';
                strHtml += '<td class="text-center cell-end">' + p_data[i].end_date + '</td>';
                strHtml += '<td class="cell-action" ></td>';
            }
            strHtml += '</tr>';

            $('#tblBatchBody tbody').append(strHtml);
            globalIndex++;
            strHtml = '';
        }
    } else {
        strHtml = '<tr><td colspan="' + lengtTh + '" class="text-align-center"><h2>' + globalMessage['No results found'] + '</h2></td></tr>';
        $('#tblBatchBody tbody').append(strHtml);
        strHtml = '';
    }
    $('.table-responsive ').removeClass('hidden');
}

// --------------------- Call Ajax Delete ----------------//
function callAjaxDelele(parameter) {
    $.ajax({
        url: globalGetOperationUrl,
        type: 'post',
        dataType: 'json',
        data: parameter,
        success: function (data) {
            $('#example2_paginate').empty();
            if (data.status) {
                globalIndex = data.data.from;
                renderHtmlData(data.data.data);
                setTotalMMEachMonth(data.totalMMEachMonth);
                $('#example2_paginate').append(data.html);

                hideLoading();
            }
        },
    });
}

// process icon loading
function loadingIcon($check) {
    if ($check) {
        $('.se-pre-con').show();
    } else {
        $('.se-pre-con').hide();
    }
}

function setTotalMMEachMonth(data) {
    $('.head-index-total').each(function (index) {
        var currentSelectorMonth = $(this);
        var monthValue = $(currentSelectorMonth).data('index');
        $(currentSelectorMonth).html(data[monthValue] ? data[monthValue].toFixed(2) : 0);
    })
}
