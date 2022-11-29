(function ( $ ) {
    if ($('input, select').attr('readonly')) {
        $(this).css('pointer-events', 'none');
    }
    // function common Select2 Ajax
    $.fn.selectSearchAjax = function(options) {
        var defaults = {
            url: "",
            pages: 1,
            delay: 200,
            multiple: false,
            allowClear: false,
            allowHtml: false,
            closeOnSelect: true,
            tags: false,
            minimumInputLength: 2,
            initSelection : function (element, callback) {
                var id = '';
                var text = '';
                var data = [];
                data.push({id: id, text: text});
                callback(data);
            }
        };
        var settings = $.extend( {}, defaults, options );
        var object = this;

        object.init = function(selector) {
            $(selector).select2({
                multiple: settings.multiple,
                closeOnSelect : settings.closeOnSelect,
                allowClear: settings.allowClear,
                allowHtml: settings.allowHtml,
                tags: settings.tags,
                minimumInputLength: settings.minimumInputLength,
                ajax: {
                    url: settings.url,
                    dataType: 'json',
                    delay: settings.delay,
                    data: function (params) {
                        return {
                            q: params.term,
                            page: params.page,
                            employeeBranch: "{{ isset($employeeBranch['branch']) && !empty($employeeBranch['branch']) ? $employeeBranch['branch'] : '' }}"
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 10) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                placeholder: settings.placeholder,
                templateResult: object.formatRepo,
                templateSelection: object.formatRepoSelection,
                initSelection : settings.initSelection
            });
        }

        // temple
        object.formatRepo = function(repo) {
            if (repo.loading) {
                return repo.text;
            }
            return markup = repo.text;
        }

        // temple
        object.formatRepoSelection = function(repo) {
            return repo.text;
        }

        // Event select
        object.on("select2:select", function (e) {
            // remove all sesssion storage
            sessionStorage.clear();

            // assign session storage
            var id = $("#assign_id").val();
            var text = $("#assign_id option:selected").text();
            if (text != null) {
                sessionStorage.setItem('assign_id_' + id, text);
            }
        })

        // init
        var selectors = $(this);
        return $.each(selectors, function(index, selector) {
            object.init(selector);
        });
    };

    // select2 course
    $('.select-search-course').selectSearchAjax({
        url: $('.select-search-course').data('remote-url')
    });

    // select2 person assigned
    $('.select-search-person').selectSearchAjax({
        url: $('.select-search-person').data('remote-url'),
        initSelection: function (element, callback) {
            // get session storage
            var id = assignId;
            var text = '';
            if ( typeof(Storage) !== 'undefined') {
                if (sessionStorage.getItem('assign_id_' + id) !== null) {
                    text = sessionStorage.getItem('assign_id_' + id);
                    $('select[name="assign_id"]').append("<option value='" + id + "' selected>" + text + "</option>");
                }
            } else {
                console.log('Trình duyệt của bạn không hỗ trợ!');
            }
            var data = [];
            data.push({id: id, text: text});
            callback(data);
        }
    });

    var removeSpace = function(selectChange, length) {
        var input = '';
        if (selectChange.length <= length) {
            selectChange.each(function (index) {
                if (index == 0) {
                    input += $(this).text().trim();
                } else {
                    input += ', ' + $(this).text().trim();
                }
            });
            setTimeout(function () {
                if (input.length) {
                    $('#select-team .btn-group .multiselect').attr('title', input);
                    $('#select-team .btn-group .multiselect-selected-text').html(input);
                }
            }, 10);
        }
    }
    var selectChange = $('#select-team select option:selected');
    removeSpace(selectChange, 2);
    $('#select-team select').multiselect({
        nonSelectedText: "--------------",
        numberDisplayed: 2,
        // onChange: function () {
        //     var selectChange = $('#select-team select option:selected');
        //     removeSpace(selectChange, 2);
        // }
    });

    // Mutilselect
    $('#education_object .education_object').multiselect();

    // Select2
    $(function() {
        $('.select-search-employee').selectSearchEmployee();
        $('.select-search-employee-no-pagination').selectSearchEmployeeNoPagination();
    });
    onload=function() {
        var e=document.getElementById("refreshed");
        if (e.value=="no") e.value="yes";
        else {
            e.value="no";
            $('.btn-create').prop('disabled',true);
            location.reload();}
    }

    // Form Validate
    function validateHtml(elem) {
        var val = elem.val();
        var id = elem.attr('id');
        if (parseInt(val) == 0 || val == '' || val == null) {
            $('#' + id + '-error').show().html(messageError);
            elem.parent().find('label.error').show();//.html("{{ trans('core::message.This field is required') }}");
        } else {
            $('#' + id + '-error').hide().html('');
        }
    }
    $("#team_id, #tag_id, #type_id, #object_id, #description, #title, #status").on('change', function() {
        validateHtml($(this));
    });

    // Trim validate
    $('input[type="text"]').on('change', function(){
        this.value = $.trim(this.value);
    });

    // Reason jquery
    var reason = function() {
        var id = $("#status").val();
        if (id == statusPending || id == statusReject) {
            $("#reason-form").show();
        } else {
            $("#reason-form").hide();
        }
    }
    reason();
    $("#status").on('change', function() {
        reason();
    });

    // Select2 tags
    $(".education-tag").select2({
        tags: true,
        tokenSeparators: ['/',',',';']
    });
    // Datepicker
    $('.start-date').datetimepicker({
        minDate: new Date().setHours(0,0,0,0),
        useCurrent: true,
        format: 'DD-MM-YYYY'
    });
}( jQuery ));