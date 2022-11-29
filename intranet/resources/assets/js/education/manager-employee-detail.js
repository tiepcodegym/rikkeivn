$(document).ready(function () {
    var formatDate = 'YYYY-MM-DD';

    init();

    // Init load page
    function init() {
        $('#from_date').datetimepicker({
            format: formatDate,
            showClear: true
        });

        $('#to_date').datetimepicker({
            format: formatDate,
            showClear: true
        });

        $(document).on('dp.change', '#from_date, #to_date', function () {
            $('#table_list_study').DataTable().ajax.reload();
            $('#table_list_teaching').DataTable().ajax.reload()
        });
    }

    // Get data study
    var datatableStudy = $('#table_list_study').DataTable({
        ordering: false,
        searching: false,
        oLanguage: globalDataLang,
        processing: true,
        bLengthChange: false,
        serverSide: true,
        pageLength: 50,
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'class_name', name: 'class_name'},
            {data: 'courses_name', name: 'courses_name'},
            {data: 'start_date_study', name: 'start_date_study'},
            {data: 'count_class_study', name: 'count_class_study'},
            {data: 'start_date', name: 'start_date'},
            {data: 'end_date', name: 'end_date'},
            {data: 'employee_name', name: 'employee_name'},
        ],
        ajax: {
            url: globalAjaxUrl,
            dataType: 'json',
            data: function (d) {
                d.class_name = $('#student_class_name').val();
                d.courses_name = $('#student_courses_name').val();
                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
                d.employee_id = globalEmployeeId;
                d.isStudy = true;
            }
        },
    });

    // Get data teaching
    var datatableTeaching = $('#table_list_teaching').DataTable({
        ordering: false,
        searching: false,
        oLanguage: globalDataLang,
        processing: true,
        bLengthChange: false,
        serverSide: true,
        pageLength: 50,
        columns: [
            {data: 'id', name: 'id'},
            {data: 'name', name: 'name'},
            {data: 'class_name', name: 'class_name'},
            {data: 'courses_name', name: 'courses_name'},
            {data: 'start_date_teaching', name: 'start_date_teaching'},
            {data: 'number_teaching', name: 'number_teaching'},
            {data: 'number_student', name: 'number_student'},
            {data: 'point_average', name: 'point_average'},
            {data: 'employee_name', name: 'employee_name'},
        ],
        ajax: {
            url: globalAjaxUrl,
            dataType: 'json',
            data: function (d) {
                d.class_name = $('#teaching_class_name').val();
                d.courses_name = $('#teaching_courses_name').val();
                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
                d.employee_id = globalEmployeeId;
                d.isStudy = false;
            }
        },
    });

    // Set index number for table
    datatableStudy.on('draw.dt', function () {
        var PageInfo = $('#table_list_study').DataTable().page.info();
        datatableStudy.column(0, {page: 'current'}).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + PageInfo.start;
        });
    });

    // Set index number for table
    datatableTeaching.on('draw.dt', function () {
        var PageInfo = $('#table_list_teaching').DataTable().page.info();
        datatableTeaching.column(0, {page: 'current'}).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + PageInfo.start;
        });
    });

    // Handle change field search of list study
    $(document).on("change", "#student_class_name, #student_courses_name", function () {
        $('#table_list_study').DataTable().ajax.reload()
    });

    // Handle change field search of list teaching
    $(document).on("change", "#teaching_class_name, #teaching_courses_name", function () {
        $('#table_list_teaching').DataTable().ajax.reload()
    });
});