(function ($, RKExternal, RKfuncion) {
    RKExternal.moreHeight.init();
    // notes manage edit
    RKExternal.select2.init();
    if (typeof CKEDITOR === 'object') {
        RKfuncion.CKEditor.init(['content'], true);
    }
    /*var desEditor = CKEDITOR.replace('content', {
     extraPlugins: 'autogrow,image2,fixed',
     removePlugins: 'justify,colorbutton,indentblock,resize',
     removeButtons: 'About',
     startupFocus: true
     });
     CKFinder.setupCKEditor(desEditor, '/lib/ckfinder');*/

    /*$('.btn-submit-ckeditor').click(function () {
     //desEditor.updateElement();
     var aux = document.createElement("input");
     aux.setAttribute("value", $('#render-link').val());
     document.body.appendChild(aux);
     aux.select();
     document.execCommand("copy");
     document.body.removeChild(aux);
     });*/
    $('.input-group.date').datepicker({format: "yyyy-mm-dd"});

    if ($('#form-notes-edit').length) {
        var messageValidate = {
            required: message_required,
            date: message_date
        };
        $('#form-notes-edit').validate({
            rules: {
                'notes[version]': {
                    required: true
                },
                'notes[status]': {
                    required: true
                },
                'notes[release_at]': {
                    date: true
                }
            },
            messages: {
                'notes[version]': {
                    required: messageValidate.required
                },
                'notes[status]': {
                    required: messageValidate.required
                },
            },
        });
    }
// end notes manage edit

// note manage index
    var callDatatable = function () {
        if (!$('#table_id').length || !$('#table_id').DataTable) {
            return true;
        }
        var table = $('#table_id').DataTable({
            processing: true,
            serverSide: true,
            ajax: manage_notes_anyData,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Vietnamese.json"
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'version', name: 'version'},
                {data: 'created_by', name: 'created_by'},
                {data: 'release_at', name: 'release_at',
                    render: function (data, type, row, meta) {
                        return data.substr(0, 10);
                    }
                },
                {data: 'status', name: 'status',
                    render: function (data, type, row, meta) {
                        return data == 0 ? 'Disable' : 'Enable';
                    }
                },
                {data: 'id', name: 'id',
                    render: function (data, type, row, meta) {
                        return '<a href="' + manage_notes_edit + '/' + data + '" class="btn-edit" title="' + notes_view_Edit + '"><i class="fa fa-edit"></i></a>';

                    }

                }
            ],
            "columnDefs": [{
                    "bSort": false,
                    "searchable": false,
                    "orderable": false,
                    "targets": 5
                }],
            "order": [[ 0, "desc" ]]
        });

        $('#table_id thead tr').clone(true).appendTo('#table_id thead');
        var total = $('#table_id thead tr:eq(1) th').length;
        $('#table_id thead tr:eq(1) th').each(function (i) {
            if (i > 0 && i < total - 1) {
                $(this).html('<input type="text" placeholder="Tìm kiếm..." class="filter-grid form-control" id/>');
                $(this).css({"padding": "10px 4px", "border-bottom": "1px solid #d2d6de"});

                //delay keyup
                function delay(callback, ms) {
                    var timer = 0;
                    return function () {
                        var context = this, args = arguments;
                        clearTimeout(timer);
                        timer = setTimeout(function () {
                            callback.apply(context, args);
                        }, ms || 0);
                    };
                }

                $('input', this).keyup(delay(function (e) {
                    if (table.column(i).search() !== this.value) {
                        table
                                .column(i)
                                .search(this.value)
                                .draw();
                    }
                }, 800));

            } else {
                $(this).css({"width": "80px", "border-bottom": "1px solid #d2d6de"});
                $('.no-sort').attr("class", "");
                $('#table_id thead tr:eq(1) th:eq(0)').html("");
            }
        });
    };
    callDatatable();
//end note manage index

// notes user
    $('.show-more').click(function (e) {
        var toggle = this;
        e.preventDefault();
        if ($(toggle).find('i').attr('class') == 'fa fa-arrow-down') {
            setTimeout(function () {
                $(toggle).html(view_view + '<i class="fa fa-arrow-up"></i>');
            }, 200)
        } else {
            setTimeout(function () {
                $(toggle).html(view_view_more + '<i class="fa fa-arrow-down"></i>');
            }, 200)
        }

        var idContent = $(toggle).attr('href');
        if ($(idContent).hasClass('open')) {
            $(idContent).removeClass('open');
        } else {
            $(idContent).addClass('open');
        }
    });
// end notes user

// show checkbox send notification
$('select[name="notes[status]"]').change(function () {
    if ($(this).val() === enableStatus) {
        $('input[name="has_notify"]').closest('.form-group').removeClass('hidden');
    } else {
        $('input[name="has_notify"]').closest('.form-group').addClass('hidden');
    }
});
})(jQuery, RKExternal, RKfuncion);
