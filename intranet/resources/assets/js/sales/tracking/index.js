var pageLength = 20;

/**
 * Init table customer feedbacks
 */
 
var tblFeedbacks = $('#tbl-feedback').DataTable({
    processing: true,
    lengthChange: false,
    bFilter: false,
    serverSide: true,
    ajax: urlFeedbacks ,
    pageLength: pageLength,
    order: [[ 3, "asc" ]],
    columnDefs: [ {
        sortable: false,
        "class": "index",
        targets: "no-sort",
    } ],
    columns: [
        {data: '', name: ''},
        {data: 'project_name', name: 'project_name'},
        {data: 'title', name: 'title'},
        {data: 'status', name: 'status'},
        {data: 'priority', name: 'priority'},
        {data: 'type', name: 'type'},
        {data: 'email', name: 'email'},
        {data: 'created_at', name: 'created_at'},
        {data: 'duedate', name: 'duedate'},
        {data: 'count_issues', name: 'count_issues'},
        {data: '', name: ''},
    ],
    fnDrawCallback: function() {
        var info = tblFeedbacks.page.info();
        $('#tbl-feedback tbody tr').each(function (index) {
            $(this).find('td:nth-child(1)').not('.dataTables_empty').html((info.page) * pageLength + index + 1);
        });
    },
});

$('#tbl-feedback thead').append($('#tbl-feedback2 thead').html());

/**
 * Init table my tasks
 */
 
var tblMyTasks = $('#tbl-my-task').DataTable({
    processing: true,
    lengthChange: false,
    bFilter: false,
    serverSide: true,
    ajax: urlMyTasks ,
    pageLength: pageLength,
    order: [[ 5, "asc" ]],
    columnDefs: [ {
        sortable: false,
        targets: "no-sort",
    } ],
    columns: [
        {data: '', name: ''},
        {data: 'title', name: 'title'},
        {data: 'assignee', name: 'assignee'},
        {data: 'project_name', name: 'project_name'},
        {data: 'email', name: 'email'},
        {data: 'status', name: 'status'},
        {data: 'priority', name: 'priority'},
        {data: 'created_at', name: 'created_at'},
        {data: 'duedate', name: 'duedate'},
    ],
    fnDrawCallback: function() {
        var info = tblMyTasks.page.info();
        $('#tbl-my-task tbody tr').each(function (index) {
            $(this).find('td:nth-child(1)').not('.dataTables_empty').html((info.page) * pageLength + index + 1);
        });
    },
});

$('#tbl-my-task thead').append($('#tbl-my-task2 thead').html());

$(function() {
    $('.dateMyTasks').datepicker({
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        dateFormat: 'yy-mm-dd',
        onSelect: function() {
            tblMyTasks.ajax.url( getUrl() ).load();
        }
    });

    $('.dateFeedbacks').datepicker({
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        dateFormat: 'yy-mm-dd',
        onSelect: function() {
            tblFeedbacks.ajax.url( getUrlFeedback() ).load();
        }
    });

    $('.dateRisks').datepicker({
        todayBtn: "linked",
        autoclose: true,
        todayHighlight: true,
        dateFormat: 'yy-mm-dd',
        onSelect: function() {
            tblRisks.ajax.url( getUrlRisk() ).load();
        }
    });
   
    $('#tbl-my-task thead tr.row-filter select').change(function() {
        tblMyTasks.ajax.url( getUrl() ).load();
    });
    
    $('#tbl-my-task thead tr.row-filter input[type=text]').keyup(function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            tblMyTasks.ajax.url( getUrl() ).load();
        }
    });

    function getUrl() {
        var url = urlMyTasks;
        url += '?title=' + $('#tbl-my-task .filter-title').val();
        url += '&assignee=' + $('#tbl-my-task .filter-assignee').val();
        url += '&project_name=' + $('#tbl-my-task .filter-project_name').val();
        url += '&pm=' + $('#tbl-my-task .filter-pm').val();
        url += '&status=' + $('#tbl-my-task .filter-status').val();
        url += '&priority=' + $('#tbl-my-task .filter-priority').val();
        url += '&created_at=' + $('#tbl-my-task .filter-created_at').val();
        url += '&duedate=' + $('#tbl-my-task .filter-duedate').val();
        return url;
    }

    $('#tbl-feedback thead tr.row-filter select').change(function() {
        tblFeedbacks.ajax.url( getUrlFeedback() ).load();
    });
    
    $('#tbl-feedback thead tr.row-filter input[type=text]').keyup(function(e) {
        var code = e.keyCode || e.which;
        if(code === 13) {
            tblFeedbacks.ajax.url( getUrlFeedback() ).load();
        }
    });

    function getUrlFeedback() {
        var url = urlFeedbacks;
        url += '?title=' + $('#tbl-feedback .filter-title').val();
        url += '&assignee=' + $('#tbl-feedback .filter-assignee').val();
        url += '&status=' + $('#tbl-feedback .filter-status').val();
        url += '&priority=' + $('#tbl-feedback .filter-priority').val();
        url += '&created_at=' + $('#tbl-feedback .filter-created_at').val();
        url += '&duedate=' + $('#tbl-feedback .filter-duedate').val();
        url += '&type=' + $('#tbl-feedback .filter-type').val();
        url += '&project_name=' + $('#tbl-feedback .filter-project_name').val();
        return url;
    }
});
function displayIssue(taskId, self) {
    self = $(self);
    if (self.data('direction') === 'open') {
        $.ajax({
            url: urlTaskChild,
            type: 'post',
            dataType: 'html',
            data: {
                _token: token,
                taskId: taskId,
                hasColumnProject: 1,
                index: self.closest('tr').find('td.index').text(),
            },
            success: function (data) {
                self.closest('tr').after(data);
                self.data('direction', 'close');
                self.find('span.glyphicon').removeClass('glyphicon-menu-down').addClass('glyphicon-menu-up');
            },
            error: function() {

            },
            complete: function () {

            }
        });
    } else {
        $('tr[data-parent-id='+self.data('id')+']').remove();
        self.data('direction', 'open');
        self.find('span.glyphicon').removeClass('glyphicon-menu-up').addClass('glyphicon-menu-down');
    }
    
}

$(document).on('click', 'a.post-ajax', function() {
    $.ajax({
        url: $(this).data('url-ajax'),
        type: 'post',
        dataType: 'json',
        data: {
            _token: token,
        },
        success: function (data) {
            $('#modal-task_detail .modal-body').html(data.htmlModal);
            $('#modal-task_detail').modal('show');
            console.log(data.htmlModal);
        },
        error: function() {

        },
        complete: function () {

        }
    });
});

/**
 * Init table customer feedbacks
 */
var tblRisks = $('#tbl-risk').DataTable({
    processing: true,
    lengthChange: false,
    bFilter: false,
    serverSide: true,
    ajax: urlRisks ,
    pageLength: pageLength,
    order: [[ 4, "desc" ]],
    columnDefs: [ {
        sortable: false,
        targets: "no-sort",
    } ],
    columns: [
        {data: '', name: ''},
        {data: 'project_name', name: 'project_name'},
        {data: 'content', name: 'content'},
        {data: 'weakness', name: 'weakness'},
        {data: 'level_important', name: 'level_important'},
        {data: 'owner', name: 'owner'},
        {data: 'status', name: 'status'},
        {data: 'count_task', name: 'count_task'},
        {data: '', name: ''},
    ],
    fnDrawCallback: function() {
        var info = tblRisks.page.info();
        $('#tbl-risk tbody tr').each(function (index) {
            var numRow = info.page * pageLength + index + 1;
            $(this).find('td:nth-child(1)').not('.dataTables_empty').html(numRow);
            var td = $(this).find('.count-task');
            td.attr('onclick', 'displayTaskRisk(' + td.data('id') + ', this, ' + (numRow - 1) + ')'); 
        });
    },
});

$('#tbl-risk thead').append($('#tbl-risk2 thead').html());

$('#tbl-risk thead tr.row-filter select').change(function() {
    tblRisks.ajax.url( getUrlRisk() ).load();
});

$('#tbl-risk thead tr.row-filter input[type=text]').keyup(function(e) {
    var code = e.keyCode || e.which;
    if(code === 13) {
        tblRisks.ajax.url( getUrlRisk() ).load();
    }
});

function getUrlRisk() {
    var url = urlRisks;
    url += '?content=' + $('#tbl-risk .filter-content').val();
    url += '&weakness=' + $('#tbl-risk .filter-weakness').val();
    url += '&status=' + $('#tbl-risk .filter-status').val();
    url += '&level_important=' + $('#tbl-risk .filter-level_important').val();
    url += '&owner=' + $('#tbl-risk .filter-owner').val();
    url += '&project=' + $('#tbl-risk .filter-project').val();
    return url;
}

function displayTaskRisk(riskId, self, index) {
    self = $(self);
    if (self.data('direction') === 'open') {
        $.ajax({
            url: urlTaskRisk,
            type: 'post',
            dataType: 'html',
            data: {
                _token: token,
                riskId: riskId,
                index: index,
            },
            success: function (data) {
                self.closest('tr').after(data);
                self.data('direction', 'close');
                self.find('span.glyphicon').removeClass('glyphicon-menu-down').addClass('glyphicon-menu-up');
            },
            error: function() {

            },
            complete: function () {

            }
        });
    } else {
        $('tr[data-risk-id='+self.data('id')+']').remove();
        self.data('direction', 'open');
        self.find('span.glyphicon').removeClass('glyphicon-menu-up').addClass('glyphicon-menu-down');
    }
}

