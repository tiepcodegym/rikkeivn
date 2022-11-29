(function ( $ ) {
    // Select2 tag ajax
    $.fn.select2tagAjax = function(options) {
        var defaults = {
            url: "",
            pages: 1,
            delay: 300,
            placeholder: '',
            multiple: true,
            closeOnSelect : false,
            tags: false,
        };
        var settings = $.extend( {}, defaults, options );
        var tag = this;

        tag.init = function(selector) {
            var selector = selector;
            $(selector).select2({
                multiple: settings.multiple,
                closeOnSelect : settings.closeOnSelect,
                allowClear: settings.allowClear,
                allowHtml: settings.allowHtml,
                tags: settings.tags,
                theme : settings.theme,
                ajax: {
                    url: settings.url,
                    dataType: 'json',
                    delay: settings.delay,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 5) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) {
                    return markup;
                },
                placeholder: settings.placeholder,
                templateResult: tag.formatRepo,
                templateSelection: tag.formatRepoSelection
            });
            tag.countItemTag(selector);
        }

        // formatRepo in select2
        tag.formatRepo = function(repo) {
            if (repo.loading) {
                return repo.text;
            }
            return markup  = repo.text;
        }

        // formatRepoSelection in select2
        tag.formatRepoSelection = function(repo) {
            return repo.text;
        }

        tag.countItemTag = function(selector) {
            var counter = $(selector).val() ? $(selector).val().length : 0;
            var target = $(selector).parent()
            var rendered = target.find('.select2-selection__rendered');
            target.find('.select2-selection__counter').remove();
            if (counter > 2) {
                rendered.hide();
                rendered.find('li:not(.select2-search--inline)').hide();
                rendered.after('<div class="select2-selection__counter">' + counter + ' selected</div>');
                target.find('.select2-selection__counter').show();
            } else {
                rendered.show();
                target.find('.select2-selection__counter').hide()
            }

        }

        // unselecting select2
        tag.on("select2:unselecting", function (e) {
            var idSelected = e.params.args.data.id;
            if ($(e.params.args.originalEvent.currentTarget).hasClass("select2-results__option")) {
                $(e.params.args.originalEvent.currentTarget).attr('aria-selected', 'false');
                $(".select2-tag option[value='" + idSelected + "']").remove();
            }
            tag.countItemTag($(this));
        })

        tag.on("select2:close", function (e) {
            $('.btn-search-filter').trigger('click');
        })

        // change select2
        tag.on("change", function(e) {
            if (tag.val().length > 0) {
                tag.countItemTag($(this));
            }
        })

        var selectors = $(this);
        return $.each(selectors, function(index, selector){
            tag.init(selector);
        });
    };

    // Select2 search ajax
    $.fn.selectSearchAjax = function(options) {
        var defaults = {
            url: "",
            pages: 1,
            delay: 300,
            multiple: false,
            allowClear: true,
            allowHtml: true,
            tags: false,
            minimumInputLength: 2,
            maximumSelectionLength: 1,
            initSelection : function (element, callback) {
                var id = '';
                var text = '';
                var data = [];
                data.push({id: id, text: text});
                callback(data);
            }
        };
        var settings = $.extend( {}, defaults, options );
        var search = this;

        search.init = function(selector) {
            $(selector).select2({
                multiple: settings.multiple,
                closeOnSelect : settings.closeOnSelect,
                allowClear: settings.allowClear,
                allowHtml: settings.allowHtml,
                tags: settings.tags,
                minimumInputLength: settings.minimumInputLength,
                maximumSelectionLength: settings.minimumInputLength,
                ajax: {
                    url: settings.url,
                    dataType: 'json',
                    delay: settings.delay,
                    data: function (params) {
                        return {
                            q: params.term,
                            employee_branch: "{{ $employee_branch['branch'] }}",
                            page: params.page
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
                templateResult: search.formatRepo,
                templateSelection: search.formatRepoSelection,
                initSelection : settings.initSelection
            });
        }

        // temple
        search.formatRepo = function(repo) {
            if (repo.loading) {
                return repo.text;
            }

            return markup  = repo.text;
        }

        // temple
        search.formatRepoSelection = function(repo) {
            return repo.text;
        }

        // Event select
        search.on("select2:select", function (e) {
            $('.btn-search-filter').trigger('click');
        })

        // init
        var selectors = $(this);
        return $.each(selectors, function(index, selector){
            search.init(selector);
        });
    };

    // Call select2 Ajax
    $('.select2-tag').select2tagAjax({
        url: $('.select2-tag').data('remote-url'),
        theme: "select2-tag-custom"
    })

    $('.select-search-person').selectSearchAjax({
        url: $('.select-search-person').data('remote-url'),
        minimumInputLength: 1,
        initSelection: function (element, callback) {
            var id = filterAssigned;
            var target = $('.employee-assigned-' + id);
            var text = target.data('assigned-name');
            var data = [];
            if(id.length > 0) {
                $('select[name="filter[search][assign_id]"]').append("<option value='" + id + "' selected>" + text + "</option>");
            }
            data.push({id: id, text: text});
            callback(data);
        }
    });

    $('.select-search-title').selectSearchAjax({
        url: $('.select-search-title').data('remote-url'),
        initSelection: function (element, callback) {
            var id = filtertitle;
            var text = filtertitle;
            var data = [];
            data.push({id: id, text: text});
            callback(data);
        }
    });

    // datetimepicker
    $('.from_date, .to_date').datetimepicker({
        useCurrent: true,
        format: 'DD-MM-YYYY'
        // minDate: moment()
    });
    $('.from_date').on('dp.change', function (e) {
        var incrementDay = moment(new Date(e.date));
        incrementDay.add(1, 'days');
        $('.to_date').data('DateTimePicker').minDate(incrementDay);
        $(this).data("DateTimePicker").hide();
    });

    $('.to_date').datetimepicker().on('dp.change', function (e) {
        var decrementDay = moment(new Date(e.date));
        decrementDay.subtract(1, 'days');
        $('.from_date').data('DateTimePicker').maxDate(decrementDay);
        // $(this).data("DateTimePicker").hide();
    });
    $('.select-multi').multiselect({
        nonSelectedText: "--------------",
        numberDisplayed: 2,
        onDropdownHide: function(event) {
            RKfuncion.filterGrid.filterRequest(this.$select);
        }
    });
    $('#select-team select').multiselect({
        nonSelectedText: "--------------",
        numberDisplayed: 1

    });
    $('.scope .multiselect-selected-text').html(function (i, html) {
        $('.scope .multiselect-selected-text').html(html.replace(/&nbsp;/g, ''))
        $('.scope .multiselect dropdown-toggle').attr('title', html.replace(/&nbsp;/g, ''))
    });
}( jQuery ));
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
// Export Excel
$('#export_list').click(function (e) {
    e.preventDefault();
    var form = document.createElement('form');
    form.setAttribute('method', 'post');
    form.setAttribute('action', $(this).data('url'));
    var params = {
        _token: siteConfigGlobalToken
    };

    for (var key in params) {
        var hiddenField = document.createElement('input');
        hiddenField.setAttribute('type', 'hidden');
        hiddenField.setAttribute('name', key);
        hiddenField.setAttribute('value', params[key]);
        form.appendChild(hiddenField);
    }

    document.body.appendChild(form);
    form.submit();
    form.remove();
});

// trigger event enter
$('.filter-multi-select').on('keypress', function(e) {
    if (e.keycode === 13) {
        $('.btn-search-filter').trigger('click');
    }
})