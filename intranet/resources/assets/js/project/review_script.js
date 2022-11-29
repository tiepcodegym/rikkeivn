(function ($) {

    function initFilterSelect2(element) {
        var select2Option = {};
        if (typeof element.attr('data-placeholder') != 'undefined') {
            select2Option = {
                allowClear: true,
                placeholder: element.attr('data-placeholder'),
            };
        }
        if (typeof element.attr('data-url') != 'undefined' && element.attr('data-url')) {
            select2Option.minimumInputLength = 2;
            select2Option.ajax = {
                url: element.attr('data-url'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 20) < data.total_count,
                        },
                    };
                },
                cache: true,
            };
        }
        element.select2(select2Option);
    }

    //set fillter to left table
    setTimeout(function () {
        //date picker filter month
        $('.month-picker').datepicker({
            format: 'yyyy-mm',
            viewMode: 'months',
            minViewMode: 'months',
            autoclose: true,
            clearBtn: true,
        }).on('changeDate', function (e) {
            //check new version
            var dMonth = e.date.getMonth() + 1;
            var month = e.date.getFullYear() + '-' + (dMonth < 10 ? '0' + dMonth : dMonth);
            var currProj = $('#filter_projects').val();
            if (checkNewVersion(month, currProj)) {
                return false;
            }

            $('.btn-search-filter').click();
        });

        var filterMonth = $('.month-picker').val();
        if (filterMonth) {
            checkNewVersion(filterMonth, $('#filter_projects').val())
        }

        var meTable = $('#_me_table');
        if (meTable.length > 0) {
            meTable.find('tbody tr:first').appendTo(meTable.find('thead'));
        }
        //init select 2
        $('.fixed-table-container table td select').each(function () {
            initFilterSelect2($(this));
        });
        
    }, 500);
    
    $('#notEvaluate').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('.modal-title').text(button.text());
    });

    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    
    $(document).ready(function () {

        $.ajax({
            type: 'GET',
            url: urlStatistic,
            success: function (data) {
                //review items
                $('#_me_table tbody tr:not(.tr-filter)').remove();
                $('#_me_table tbody').append(data.review_items.collection_html);
                $('#collection_pager').html(data.review_items.collection_pager);
                var fixedCols = $('.fixed-table thead tr:first .fixed-col').length;
                $(".fixed-table").tableHeadFixer({"left" : fixedCols});

                //statistic
                var footerStatis = $('#footer_statistic');
                $.each(data.statistic, function (index, dataItem) {
                    var textItem = parseInt(dataItem.num);
                    if (typeof dataItem.percent != 'undefined') {
                        textItem += ' ('+ dataItem.percent +'%)';
                    }
                    footerStatis.find('.val-' + index).text(textItem);
                });
                //append me statistic
                $('#head_statistic').html($('#footer_statistic').html()).removeClass('hidden');
                //append project not eval
                var notProjHtml = '';
                var projsNotEval = data.proj_not_eval;
                if (projsNotEval.length > 0) {
                    $('#proj_not_eval_box').removeClass('hidden');
                    for (var i = 0; i < projsNotEval.length; i++) {
                        var item = projsNotEval[i];
                        var pmName = '';
                        if (item.email) {
                            pmName = item.email.split("@")[0];
                            pmName = '<span class="text-uppercase">' + htmlEntities(pmName) + '</span>';
                        }
                        var memberOfProject = item.employees ? item.employees.split(',') : [];

                        var memberCollapse = $('#proj_member_collapse').clone();
                        memberCollapse.removeClass('hidden').removeAttr('id');
                        memberCollapse.find('.proj-name').html(htmlEntities(item.name) + ' - ' + pmName);

                        var memberListHtml = '';
                        if (memberOfProject.length > 0) {
                            memberCollapse.find('.box-tools').removeClass('hidden');
                            memberListHtml = '<ul>';
                            for (var j = 0; j < memberOfProject.length; j++) {
                                var member = memberOfProject[j];
                                memberListHtml += '<li class="member-not-eval">' + htmlEntities(member) + '</li>';
                            }
                            memberListHtml += '</ul>';
                        }
                        memberCollapse.find('.members-list').append(memberListHtml);

                        notProjHtml += '<tr>';
                        notProjHtml += '<td><a href="' + projPoinEditUrl + '/' + item.id + '" target="_blank">' + htmlEntities(item.project_code_auto) + '</a></td>' +
                                '<td>' +
                                '<p>' + textProjName + ' : ' + htmlEntities(item.name) + '</p>' +
                                '<p>' + textPmName + ': ' + pmName + '</span></p>' +
                                '<p>' + textViewGroup + ': ' + htmlEntities(item.team_names) + '</p>' +
                                '</td>' +
                                '<td>' + memberCollapse[0].outerHTML + '</td>';
                        notProjHtml += '</tr>';
                    }
                    $('#table-not-eval tbody').html(notProjHtml);
                    $('#table-not-eval').DataTable();
                }
                //append total member
                $('#total_member').text(data.total_member);
            },
            error: function () {
                $('#footer_statistic').html('Something error!')
            },
        });
        
    });
    
    //ajax action item
    $('body').on('submit', '._action_btns form', function (e) {
        e.preventDefault();
        
        var button = $(this).find('button[type="submit"]');
        var url = $(this).attr('action');
        var method = $(this).find('input[name="_method"]').val();
        if (!method) {
            $(this).attr('method');
        }
        var tr = $(this).closest('tr');
        var trId = tr.attr('data-eval');
        var allTr = $('tr[data-eval="'+ trId +'"]');
        allTr.addClass('processing');
        var elAction = $(this).parent();
        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function (data) {
                if (data.success) {
                    if (method == 'DELETE') {
                        allTr.remove();
                    } else {
                        elAction.find('.form_after_submit').remove();
                        tr.find('.status_label').text(data.status_label);
                        allTr.find('input[type="checkbox"]').remove();
                    }
                    _showStatus(data.message);
                } else {
                    showModalError(data.message);
                } 
            },
            complete: function () {
                button.prop('disabled', false);
                allTr.removeClass('processing');
            }
        });
        return false;
    });

})(jQuery);
