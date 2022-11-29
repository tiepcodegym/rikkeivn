(function ($) {

    $(document).ready(function () {
        $('.note-item .note-show').each(function () {
            $(this).shortContent();
            $(this).removeClass('hidden');
        });
    });

    $('body').on('click', '.note-edit-btn', function (e) {
        e.preventDefault();
        var noteItem = $(this).closest('.note-item');
        var noteShow = noteItem.find('.note-show');
        var noteEdit = noteItem.find('.note-edit');
        if (noteShow.hasClass('hidden')) {
            noteShow.removeClass('hidden');
            noteEdit.addClass('hidden');
        } else {
            noteShow.addClass('hidden');
            noteEdit.removeClass('hidden');
        }
    });

    $('body').on('change', '.note-item .note-edit', function () {
        var noteEdit = $(this);
        var noteItem = $(this).closest('.note-item');
        var noteError = noteItem.find('.note-error');
        noteError.text('').addClass('hidden');
        var loading = noteItem.find('.loading');
        if (!loading.hasClass('hidden')) {
            return;
        }
        var noteContent = noteEdit.val();
        if (noteContent.trim().length > 500) {
            noteError.text(textErrorMaxLength).removeClass('hidden');
            return;
        }
        var noteShow = noteItem.find('.note-show');
        var week = $(this).closest('tr').data('week');
        loading.removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: saveNoteUrl,
            data: {
                week: week,
                note: noteContent,
                email: noteItem.data('email'),
                _token: siteConfigGlobal.token,
            },
            success: function (result) {
                if (result.delete) {
                    noteItem.find('.note-name').text('');
                    noteItem.addClass('note-current');
                } else {
                    noteItem.find('.note-name').text(result.name + ': ');
                    noteItem.removeClass('note-current');
                }
                noteShow.text(result.note).removeClass('hidden shortened').shortContent();
                noteEdit.addClass('hidden');
            },
            error: function (error) {
                noteError.text(error.responseJSON).removeClass('hidden');
            },
            complete: function () {
                loading.addClass('hidden');
            },
        });
    });

    // add jquery funtion short content
    $.fn.shortContent = function (settings) {
	
        var config = {
                showChars: 60,
                showLines: 3,
                ellipsesText: "...",
                moreText: textShowMore,
                lessText: textShowLess,
        };

        if (settings) {
                $.extend(config, settings);
        }

        $(document).off("click", '.morelink');

        $(document).on(
            {
                click: function () {
                    var $this = $(this);
                    if ($this.hasClass('less')) {
                        $this.removeClass('less');
                        $this.html(config.moreText);
                    } else {
                        $this.addClass('less');
                        $this.html(config.lessText);
                    }
                    $this.parent().prev().toggle();
                    $this.prev().toggle();
                    return false;
                },
            },
            '.morelink'
        );

        return this.each(function () {
                var $this = $(this);
                if ($this.hasClass("shortened")) {
                    return;
                }

                $this.addClass("shortened");
                var content = $this.html();
                var moreContent = '';
                var arrLine = content.split("\n");
                var c = content, h = '';
                var hasMore = false;
                
                if (arrLine.length > config.showLines) {
                    hasMore = true;
                    content = arrLine.splice(0, config.showLines).join("\n");
                    moreContent = arrLine.join("\n");
                }
                
                if (content.length > config.showChars) {
                    hasMore = true;
                    c = content.substr(0, config.showChars);
                    h = content.substr(config.showChars, content.length - config.showChars) + moreContent;
                } else {
                    c = content;
                    h = moreContent;
                }
                
                if (hasMore) {
                    var html = c + '<span class="moreellipses">' + config.ellipsesText + ' </span><span class="morecontent"><span>' + h + '</span> <a href="#" class="morelink">' + config.moreText + '</a></span>';
                    $this.html(html);
                    $(".morecontent span").hide();
                }
        });

    };

    listPrograms = listPrograms ? JSON.parse(listPrograms) : [];
    listPositions = listPositions ? JSON.parse(listPositions) : [];

    var theadTbl = $('#modal_report_detail table thead tr:first').clone();
    $('body').on('click', 'td.col-data', function (e) {
        e.preventDefault();
        var items = $(this).data('items');
        var checkWorking = false;
        if ($(this).data('working') !== 'undefined' && $(this).data('working')) {
            checkWorking = true;
        }
        if (typeof items == 'undefined') {
            return;
        }
        var week = $(this).closest('tr').data('week');
        var colName = $(this).closest('table').find('thead tr:first th:eq('+ $(this).index() +')').text();
        $('#modal_report_detail .modal-title').html(textWeek + ': ' + week + ': ' + colName);
        $('#modal_report_detail').modal('show');
        if (items.length < 0) {
            return;
        }

        var dataTable = $('#modal_report_detail table');
        if (dataTable.closest('.dataTables_wrapper').length > 0) {
            dataTable.DataTable().destroy();
            dataTable.empty();
        }

        var html = '';
        var hasDate = false;
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            //render name
            var progPosName = renderProgNames(item);
            html += '<tr>' + '<td>'+ (i + 1) +'</td>' +
                    '<td><a target="_blank" href="'+ routeDetail + '/' + item.id +'">' + htmlEntities(item.fullname) + '</a></td>' +
                    '<td>' + item.email + '</td>' +
                    '<td>' + (progPosName ? progPosName : '') + '</td>' +
                    '<td>' + item.recruiter +'</td>';
            if (typeof item.start_working_date != 'undefined') {
                html += '<td>'+ item.start_working_date +'</td>';
                hasDate = true;
            }
            if (typeof item.name !== 'undefined') {
                html += '<td>'+ item.name +'</td>';
            }
            html += '</tr>';
        }
        if (checkWorking) {
            if (theadTbl.find('.th-add').length === 0) {
                theadTbl.append('<th class="th-add">'+ textDate + '</th><th class="th-add">' + textTeam + '</th>');
            }
        } else {
            theadTbl.find('.th-add').remove();
        }

        if (!html) {
            html = '<tr><td colspan="' + checkWorking ? 7 : 5 + '">' + textNotItem + '</td></tr>';
        }
        html = '<thead>' + theadTbl[0].outerHTML + '</thead><tbody>' + html + '</tbody>';

        dataTable.html(html);
        dataTable.DataTable({
            pageLength: 20,
        });
    });

    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    /*
     * render program or position name
     */
    function renderProgNames(item) {
        var result = [];
        var strProgIds = item.prog_id;
        var arrProgIds = strProgIds.split(',');
        if (arrProgIds.length < 1) {
            return '';
        }
        for (var i = 0; i < arrProgIds.length; i++) {
            var progPosName = '';
            if ($.isNumeric(arrProgIds[i])) {
                progPosName = typeof listPrograms[arrProgIds[i]] != 'undefined' ? listPrograms[arrProgIds[i]] : '';
            } else {
                var posId = arrProgIds[i].split('_')[1];
                progPosName = typeof listPositions[posId] != 'undefined' ? listPositions[posId] : '';
            }
            if (progPosName) {
                result.push(progPosName);
            }
        }
        return result.join(', ');
    }

})(jQuery);
