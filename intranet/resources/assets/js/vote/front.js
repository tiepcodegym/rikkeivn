(function ($) {
    
    $('.multi-select').each(function (){
        var url = $(this).data('url');
        $(this).select2({
            ajax: {
                url: url,
                dataType: 'json',
                delay: 500,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page,
                        typeExclude: 'current'
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 20) < data.total_count
                        }
                    };
                },
                cache: true,
                minimumInputLength: 1
            }
        });
    });
    
    $('#nominee_form').validate({
        ignore: [],
        rules: {
            nominee_id: {
                required: function () {
                    return ($('[name="nominee_id"]').length > 0);
                }
            },
            reason: {
                required: true
            }
        },
        messages: {
            nominee_id: {
                required: textValidRequired
            },
            reason: {
                required: textValidRequired
            }
        }
    });
    
    $('.vote-box a').click(function (e) {
        e.preventDefault();
        if ($(this).hasClass('processing')) {
            return;
        }
        var parent = $(this).parent();
        var url = $(this).attr('href');
        $(this).addClass('processing');
        var elThis = $(this);
        var nominee = elThis.closest('.nominee');
        var loading = parent.find('.v_loading');
        
        parent.find('a').addClass('hidden');
        loading.removeClass('hidden');
        $.ajax({
            type: 'POST',
            url: url,
            data: {
                _token: siteConfigGlobal.token,
                type: $(this).data('type')
            },
            success: function (data) {
                var numLeftOver = $('.num_left_over');
                var numLeft = parseInt(numLeftOver.text());
                if (data.add_vote) {
                    parent.find('.cancel').removeClass('hidden');
                    nominee.addClass('had_voted');
                    if (numLeft > 0) {
                        numLeftOver.text(numLeft - 1);
                    }
                } else {
                    parent.find('.vote').removeClass('hidden');
                    nominee.removeClass('had_voted');
                    numLeftOver.text(numLeft + 1);
                }
            },
            error: function (error) {
                elThis.removeClass('hidden');
                showModalError(error.responseJSON);
            },
            complete: function () {
                elThis.removeClass('processing');
                loading.addClass('hidden');
            }
        });
    });
    
    var showWord = 15;
    var showChar = 90;
    var showLine = 3;
    $('.content-more').each(function() {
        var contentShow = $(this).prev('.content-show');
        var content = $(this).html();
        var arrLine = content.split("\n");
        var arrContent = content.split(' ');
        var c = '', h = '';
        var hasMore = false;
        
        if (arrLine.length > showLine) {
            hasMore = true;
            content = arrLine.splice(0, showLine).join("\n");
            h = arrLine.join("\n");
            arrContent = content.split("\n");
        }
        
        if (content.length > showChar || arrContent.length > showWord) {
            hasMore = true;
            if (content.length > showChar) {
                c = content.substr(0, showChar);
                h = content.substr(showChar, content.length - showChar);
            } else {
                c = arrContent.splice(0, showWord).join(' ');
                h = arrContent.join(' ');
            }
        } else {
            c = content;
        }
        if (hasMore) {
            var html = c + '<span class="more-box">' +
                        '<span class="more-char"> ... </span>' +
                        '<span class="more-text"> ' + h + '</span>' +
                        '<a href="#vote_desc_more" data-toggle="modal" class="more-link">'+ textViewMore +'</a>' +
                    '</span>';

            contentShow.html(html);
        } else {
            contentShow.html(content);
        }
    });
    
    $('#vote_desc_more').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var nominee = button.closest('.nominee');
        var nomineeName = nominee.find('.nominee-name span').text();
        var modal = $(this);
        modal.find('.nominee-name').text(nomineeName);
        modal.find('.modal-body').html(nominee.find('.nominee-desc .content-more').html());
    });
    
    var maxHeight = 100;
    $('.vote-content').each(function () {
        var moreLink = $(this).parent().find('.more-link');
        if ($(this).height() >= maxHeight) {
            moreLink.removeClass('hidden');
        }
    });
    $('.vote-desc .more-link a').click(function (e) {
       e.preventDefault();
       var textMore = $(this).data('textMore');
       var textLess = $(this).data('textLess');
       var voteDesc = $(this).closest('.vote-desc');
       if (voteDesc.hasClass('show-full')) {
           voteDesc.removeClass('show-full');
           $(this).text(textMore);
       } else {
           voteDesc.addClass('show-full');
           $(this).text(textLess);
       }
    });
    
})(jQuery);

function showModalError(message) {
    var modal_warning = $('#modal-warning-notification');
    if (typeof message == "undefined") {
        message = 'Error!';
    }
    modal_warning.find('.text-default').html(message);
    modal_warning.modal('show');
}


