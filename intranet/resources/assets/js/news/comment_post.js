/**
 * Config emoticon
 */
var converter = new showdown.Converter({
    omitExtraWLInCodeBlocks: true,
    noHeaderId: true,
    simplifiedAutoLink: true,
    strikethrough: true,
    tables: true,
    takslists: true,
    ghMentions: true,
    ghMentionsLink: siteConfigGlobal.base_url + 'contact?s={u}@rikkeisoft.com',
}); // markdown js
RKExternal.moreHeight.init();

$.emojiarea.path = emoticonPath;
$.emojiarea.icons = icons;

initVirtualEditor();

setAllEmoticonContent(true);

/**
 * init virtual editor emoticons
 *
 * @returns {undefined}
 */
function initVirtualEditor(domTextarea) {
    if (domTextarea === undefined || !domTextarea.length) {
        domTextarea = $('.emojis-wysiwyg');
    }
    domTextarea.each(function () {
        var val = $(this).val();
        if (!val) {
            return true;
        }
        val = val.trim();
    });
    domTextarea.emojiarea({
        wysiwyg: true,
        buttonLabel: '<img src="' + emoticonPath + '/icon.jpg" alt="smiley" width="20" />',
    });
    $('.emoji-button').attr('title', 'smiley');
}

/**
 * Set emoticon for content comment
 *
 * @returns {void}
 */
function setEmoticonContent(self) {console.log(replaceTextToIcon(getValueCmt(self.data('id'))));
    self.html(nl2br(replaceTextToIcon(getValueCmt(self.data('id')))));
    /*if (isTrim) {

    } else {
        var virtualEditor = self.closest('.content-block').find('.emoji-wysiwyg-editor');
        self.html(virtualEditor.html());
    }*/
    //If content contains only image
    if (self.text().replace(/\s/g, '').length === 0 && self.find('img').length < 20) {
        self.find('img').addClass('img-big');
    }
    RKExternal.moreHeight.viewMore(self);
    self.find('img').load(function () {
        RKExternal.moreHeight.viewMore(self);
    });
}

function nl2br (str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

/**
 * Set emoji for all comment
 *
 * @param {type} isTrim
 * @returns {undefined}
 */
function setAllEmoticonContent(isTrim, domCmt) {
    if (typeof domCmt === 'undefined' || !domCmt || !domCmt.length) {
        domCmt = $('.content-block .span-comment');
    }
    domCmt.each(function () {
        setEmoticonContent($(this), isTrim);
    });
}

/**
 * Add new line for text
 * @param {string} str
 * @param {boolean} is_xhtml
 * @returns {string}
 */
function nl2br(str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

/**
 * Escape javascript's script
 * @param {string} value
 * @returns {string}
 */
function escapeHtml(value) {
    return value;
    //return $('<div/>').text(value).html();
}

/**
 * Refresh count comment after add or delete commentedit
 * @param {int} amount
 * @returns {void}
 */
function refreshCountComment(amount) {
    var commentDiv = $('.count-comment:last');
    var countCommnet = commentDiv.data('count-org');
    if (!countCommnet) {
        countCommnet = 0;
    } else {
        countCommnet = parseInt(countCommnet);
    }
    countCommnet = countCommnet + parseInt(amount);
    commentDiv.data('count-org', countCommnet);
    $('.count-comment').text(countCommnet);
}

function refreshCountCommentUp(amount) {
    var commentDiv = $('.count-comment-up');
    var countCommnet = commentDiv.text().trim();
    if (!countCommnet) {
        countCommnet = 0;
    } else {
        countCommnet = parseInt(countCommnet);
    }
    commentDiv.text(countCommnet + parseInt(amount));
}

/**
 * Show required text if comment content empty
 *
 * @param {dom} element
 * @returns {void}
 */
function warning(element) {
    element.parent().find("#comment-error").html(required_comment);
    element.css('border', '1px solid red');
    setTimeout(function () {
        element.parent().find("#comment-error").html('');
        element.css('border', '1px solid #ccc');
    }, 2000);
}

/**
 * reset editor
 */
function resetEditorMain() {
    $('#create_comment .emoji-wysiwyg-editor').html('').blur().trigger('blur');
    $('.emoji-wysiwyg-editor[contenteditable="false"]').attr({contenteditable: 'true'});
}

/**
 * Remove all emoji after add new
 */
function refreshEditor() {
    //Refresh emoji-wysiwyg-editor
    $('.emoji-wysiwyg-editor').remove();
    $('.emoji-button').remove();
    initVirtualEditor();
}

/**
 * Focus cursor into virtual editor (div element)
 *
 * @param {dom} editor
 * @returns {void}
 */
function focusEditor(editor) {
    editor = editor.get(0);
    editor.focus();
    if (typeof window.getSelection != "undefined"
        && typeof document.createRange != "undefined") {
        var range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(editor);
        textRange.collapse(false);
        textRange.select();
    }
}

/**
 * Virtual editor keydown event
 */
$(document).on('keydown', '.emoji-wysiwyg-editor', function (event) {
    var self = $(this);
    var textarea = self.prev("textarea");
    var comment = escapeHtml(textarea.val().trim());
    //alt enter or shift enter then break line
    if (event.altKey && event.which === 13 || event.which === 13 && event.shiftKey) {
        if (!comment) {
            return false;
        }
    } else if (event.keyCode === 13) {
        if (!comment) {
            textarea.next('.emoji-wysiwyg-editor').attr('tabindex', -1).focus();
            warning(self);
            return false;
        }
        var e = jQuery.Event("keydown");
        e.which = 13;
        e.keyCode = 13;
        textarea.trigger(e);
    } else if (event.keyCode === 27) {
        var e = jQuery.Event("keydown");
        e.which = 27;
        e.keyCode = 27;
        textarea.trigger(e);
    }
});

/*
 * trigger change when paste content
 */
$(document).on('paste', '.emoji-wysiwyg-editor', function () {
    var self = $(this);
    var textarea = self.prev("textarea");
    setTimeout(function () {
        var content = self.text();
        textarea.val(content);
    }, 100);
});

/**
 * Editor keyup event
 * Set emoji open button from editor height
 * If height > 150px (has scroll bar) then set right 20px.
 * Else set right 5px
 */
$(document).on('keyup', '.emoji-wysiwyg-editor', function () {
    var self = $(this);
    var editorHeight = self[0].scrollHeight;
    var emojiButton = self.next('.emoji-button');
    var posRight = editorHeight > 150 ? '20px' : '5px';
    emojiButton.css('right', posRight);
});

/**
 * Insert, Update root comment event
 */
$(document).on('keydown', '.textarea-parent', function (event) {
    var _this = $(this);
    var id = _this.parent('.parent-comment').find('input[name="id"]').val();
    var postId = _this.parent('.parent-comment').find('input[name="post_id"]').val();
    var span = _this.closest('div.content-comment-' + id).find('span.comment-' + id);
    var pFullComment = _this.closest('div.content-comment-' + id).find('p.not-trim-comment.comment-' + id);
    var comment = escapeHtml(_this.val().trim());
    var virtualEdit = _this.next('.emoji-wysiwyg-editor');
    if (event.altKey && event.which === 13 || event.which === 13 && event.shiftKey) {
        _this.textareaAutoSize();
        if (!comment) {
            //enter line
            return false;
        }
    } else if (event.keyCode === 13) {
        if (!comment) {
            warning(_this);
            return false;
        } else {
            if ($.trim(pFullComment.text()) === comment) {
                _this.parent().addClass('hidden');
                span.removeClass('hidden').html(virtualEdit.html());
                removeLoadMoreLess(id);
                setEmoticonContent(span);
                return false;
            }
            virtualEdit.attr('contenteditable', 'false');
            $.ajax({
                url: comment_url,
                method: 'POST',
                timeout: 10000,
                data: {
                    comment: comment,
                    post_id: postId,
                    _token: _token,
                    id: id
                },
                success: function (data) {
                    if (data == -1) {
                        cancelEdit(_this, span, p);
                        $('#comment-error-modal').modal('show');
                        return;
                    }
                    if (!id || id === '') { // add new cmt
                        _this.val('');
                        $("#comment-content-pending").prepend(data);
                        _this.css('height', '46px');
                        refreshCountComment(1);
                        refreshCountCommentUp(1);
                        var child = $("#comment-content-pending").children(':first-child');
                        initVirtualEditor(child.find('.emojis-wysiwyg'));
                        setAllEmoticonContent(true, child.find('.span-comment'));
                        resetEditorMain();
                    } else { // edit cmt
                        span.removeClass('hidden');
                        cmtAll[id] = comment;
                        _this.textareaAutoSize();
                        _this.parent('.parent-comment').addClass('hidden');
                        var child = _this.closest('.area-comment');
                        setAllEmoticonContent(true, child.find('.span-comment'));
                        removeLoadMoreLess(id);
                        resetEditorMain();
                        if (!checkPermission) {
                            if (!(sttAutoSettingApproveComment == sttAutoApproveComment)) {
                                if ($('#approve-message-' + id).length > 0) {
                                    $('#approve-message-' + id).remove();
                                }
                                var errorDiv = $("<span class='error' id='approve-message-" + id + "'> (Bình luận này chưa được duyệt) </span>");
                                //add div error
                                $('.content-comment-' + id).before(errorDiv);
                                if ($('.status_value_' + id).val() != stt) {
                                    //add bg comment
                                    $('.content-comment-' + id).css("background", "#f7f0cb");

                                    //add tooltip
                                    var oldComment = $('.store_value_' + id).val();
                                    $('.content-comment-' + id).attr({
                                        'data-toggle': 'tooltip',
                                        'title': 'Approved Value:' + oldComment,
                                    });
                                }
                            }
                        }
                    }
                },
                error: function () {
                    refreshEditor();
                    $('#comment-error-modal').modal('show');
                },
            });
        }
    } else if (event.keyCode === 27) {
        if (_this.hasClass('root-comment')) {
            _this.val('');
            refreshEditor();
            focusEditor(virtualEdit);
        } else {
            cancelEdit(_this, span, pFullComment);
        }
    }
});

function removeLoadMoreLess(commentId) {
    $('a.load-more.comment-' + commentId).remove();
    $('a.load-less.comment-' + commentId).remove();
}

/**
 * Show editor to insert reply comment
 */
$(document).on("click", ".reply-comment", function () {
    var parent_id = $(this).attr("data-id");
    var parentContainer = $("#add_new_reply_" + parent_id + ' #reply-' + parent_id);
    parentContainer.removeClass('hidden');
    refreshEditor();
    var virtualEditor = parentContainer.find('.emoji-wysiwyg-editor');
    focusEditor(virtualEditor);
});

/**
 * Insert, Update reply comment event
 */
$(document).on('keydown', '.reply-text', function (event) {
    var _this = $(this);
    var id = _this.parent('.parent-comment').find('input[name="id"]').val();
    var postId = $('#create_comment').find('input[name="post_id"]').val();
    var parent_id = _this.attr('data-parent-id');
    var span = _this.closest('div.content-reply-comment-' + id).find('span.comment-' + id);
    var p = _this.closest('div.content-reply-comment-' + id).find('p.not-trim-comment.comment-' + id);
    var comment = $.trim($(this).val());
    var virtualEditor = _this.next('.emoji-wysiwyg-editor');

    if (event.altKey && event.which === 13 || event.which === 13 && event.shiftKey) {
        _this.textareaAutoSize();
        if (!comment) {
            //enter line
            return false;
        }
    } else if (event.keyCode === 13) {
        if (!comment) {
            _this.parent().find("#comment-error").html(required_comment);
            _this.css('border', '1px solid red');
            setTimeout(function () {
                _this.parent().find("#comment-error").html('');
                _this.css('border', '1px solid #ccc');
            }, 2000);
            return false;
        } else {
            if ($.trim(p.text()) === comment) {
                _this.parent().addClass('hidden');
                span.removeClass('hidden').html(virtualEditor.html());
                removeLoadMoreLess(id);
                setEmoticonContent(span);
                return false;
            }
            virtualEditor.attr('contenteditable', 'false');
            $.ajax({
                url: comment_url,
                method: 'POST',
                timeout: 10000,
                data: {
                    comment: comment,
                    _token: _token,
                    parent_id: parent_id,
                    id: id,
                    post_id: postId
                },
                success: function (data) {
                    if (parseInt(data) === parseInt(-1)) {
                        cancelEdit(_this, span, p);
                        $('#comment-error-modal').modal('show');
                        return false;
                    }
                    if (!id) {
                        _this.val('');
                        $("#content-reply-" + parent_id).prepend(data);
                        _this.closest('#reply-' + parent_id).addClass('hidden');
                        refreshCountComment(1);
                        refreshCountCommentUp(1);
                        var child = $("#content-reply-" + parent_id).children(':first-child');
                        initVirtualEditor(child.find('.emojis-wysiwyg'));
                        setAllEmoticonContent(true, child.find('.span-comment'));
                        resetEditorMain();
                    } else {
                        cmtAll[id] = comment;
                        _this.textareaAutoSize();
                        _this.parent().addClass('hidden');
                        var child = _this.closest('.area-comment');
                        setAllEmoticonContent(true, child.find('.span-comment'));
                        span.removeClass('hidden');
                        p.html(nl2br(comment, false));
                        removeLoadMoreLess(id);
                        resetEditorMain();
                        if (!checkPermission) {
                            if (!(sttAutoSettingApproveComment == sttAutoApproveComment)) {
                                if ($('#approve-message-' + id).length > 0) {
                                    $('#approve-message-' + id).remove();
                                }
                                var errorDiv = $("<span class='error' id='approve-message-" + id + "'> (Bình luận này chưa được duyệt) </span>");
                                $('.content-reply-comment-' + id).before(errorDiv);
                                if ($('.value_status_reply_' + id).val() != stt) {
                                    //add bg comment
                                    $('.content-reply-comment-' + id).css("background", "#f7f0cb");

                                    //add tooltip
                                    var oldComment = $('.value_reply_' + id).val();
                                    $('.content-reply-comment-' + id).attr({
                                        'data-toggle': 'tooltip',
                                        'title': 'Approved Value:' + oldComment,
                                    });
                                }
                            }
                        }
                    }
                },
                error: function () {
                    refreshEditor();
                    $('#comment-error-modal').modal('show');
                },
            });
        }
    } else if (event.keyCode === 27) {
        cancelEdit(_this, span, p);
    }
});

/**
 * Cancel edit comment, restore old value
 *
 * @param {element} element
 * @param {element} span
 */
function cancelEdit(element, span, p) {
    if (element.hasClass('reply-new')) {
        element.val('').trigger('input');
        element.closest('div[id^=reply-]').addClass('hidden');
        element.closest('.content-block').find('.load-more').removeClass('hidden');
    } else {
        element.val(p.text());
        element.parent('.parent-comment').addClass('hidden');
        span.removeClass('hidden');
        element.closest('.content-block').find('.load-more').removeClass('hidden');
        refreshEditor();
    }
}

/**
 * Load more comment
 */
$("#load_more_comment").click(function () {
    page = parseInt(page) + 1;
    $.ajax({
        url: url_load_more,
        data: {
            page: page,
            post_id: post_id,
            _token: siteConfigGlobal.token
        },
        type: "post",
        success: function (t) {
            $("#get-more-comment").append(t.data);
            if (!t.comments.next_page_url) {
                $("#load_more_comment").remove();
            }
            setAllEmoticonContent();
            resetEditorMain();
        }
    });
});

/**
 * Edit comment
 */
$(document).on('click', '.edit-comment', function (event) {
    event.preventDefault();
    var id = $(this).attr('data-id');
    showEditForm($('.content-comment-' + id), id, '.textarea-parent');
});

/**
 * Edit reply comment
 */
$('body').on('click', '.edit-reply-comment', function (event) {
    event.preventDefault();
    var id = $(this).attr("data-id");

    showEditForm($('.content-reply-comment-' + id), id, '.reply-text');
});

/**
 * Show virtual editor and hide span text
 *
 * @param {dom} classParent div container
 * @param {int} commentId
 * @param {string} textareaClass
 */
function showEditForm(classParent, commentId, textareaClass) {
    classParent.find('.parent-comment').removeClass('hidden');
    $('span.comment-' + commentId).addClass('hidden');
    $('a.comment-' + commentId + '.load-more').addClass('hidden');
    $('a.comment-' + commentId + '.load-less').addClass('hidden');
    var textarea = classParent.find(textareaClass);
    var editor = textarea.next('.emoji-wysiwyg-editor');
    editor.html(getValueCmt(commentId)).blur().trigger('blur');
    focusEditor(editor);
}

/**
 * Delete comment
 */
$('body').on('click', '.delete-comment', function (event) {
    event.preventDefault();
    var id = $(this).attr("data-id");
    bootbox.confirm({
        message: 'Are you sure delete comment?',
        className: 'modal-default',
        buttons: {
            cancel: {
                label: 'Cancel', className: 'pull-left',
            }
        },
        callback: function (result) {
            if (result) {
                $.ajax({
                    method: 'POST',
                    url: delete_comment,
                    data: {id: id, _token: _token},
                    success: function (response) {
                        $('.comment-post-' + id).remove();
                        if (response && response.count) {
                            refreshCountComment(-response.count);
                            refreshCountCommentUp(-response.count);
                        }
                    }
                });
            }
        }
    });
});

/**
 * Delete reply comment
 */
$('body').on('click', '.delete-reply-comment', function (event) {
    event.preventDefault();
    var _this = $(this);
    var id = _this.attr("data-id");
    bootbox.confirm({
        message: 'Are you sure delete comment?',
        className: 'modal-default',
        buttons: {
            cancel: {
                label: 'Cancel', className: 'pull-left',
            }
        },
        callback: function (result) {
            if (result) {
                $.ajax({
                    method: 'POST',
                    url: delete_comment,
                    data: {id: id, _token: _token},
                    success: function (response) {
                        $('.content-reply-' + id).remove();
                        if (response && response.count) {
                            refreshCountComment(-response.count);
                            refreshCountCommentUp(-response.count);
                        }
                    }
                });
            }
        }
    });
});

/**
 * Approve comment
 */
$('body').on("click", ".btn-approve", function () {
    var _this = $(this);
    _this.prop("disabled", "disabled");
    var t = _this.attr("data-id"),
        e = $("#approve-message-" + t);
    $.ajax({
        type: "post",
        data: {
            id: t,
            _token: siteConfigGlobal.token
        },
        url: approve_url,
        success: function (o) {
            $("#approve-" + t).fadeOut("fast"), e.html(o).hide().fadeIn("slow"), e.removeClass("error"), e.css("color", "red");
            _this.parents('.comment-parent').find(".reply-comment").remove();
            var dateCreateElement = _this.closest('.area-comment').find(".format");

            //if comment root add reply button
            if (_this.data('root')) {
                dateCreateElement.prepend('<a class="reply-comment" data-id="' + t + '">Trả lời</a>');
            }

            //add html like event
            var htmlLike = '<div class="like-div">';
            htmlLike += '<a class="like-button" onclick="like(this, ' + t + ', ' + typeComment + ')" link="' + like_url + '">';
            htmlLike += '<i class="font-style-normal thumb-dislike">' + likeText + '</i>';
            htmlLike += '</a>';
            htmlLike += '&nbsp';
            htmlLike += '<div class="like-container hidden" onclick="showLike(' + t + ', ' + typeComment + ')">';
            htmlLike += '<i class="fa fa-thumbs-up thumb-like size-detail" aria-hidden="true"></i>';
            htmlLike += '<span class="count-like">0</span>';
            htmlLike += '</div>';
            htmlLike += '</div>';
            dateCreateElement.prepend(htmlLike);

            var nextClass = $("#approve-message-" + t).next().prop('className');
            //parent comment
            if (nextClass.indexOf("content-comment") > -1) {
                $('.content-comment-' + t).css('background', '');
                $('.content-comment-' + t).tooltip('disable');
                if (_this.parents('.comment-parent').find('.edit-comment').length < 1) {
                    _this.parents('.comment-parent').find('.btn-group').remove();
                }
            } else {
                $('.content-reply-comment-' + t).css('background', '');
                $('.content-reply-comment-' + t).tooltip('disable');
                if (_this.parents('.comment-parent').find('.edit-comment').length < 1) {
                    _this.parents('.area-comment').find('.dropdown.reply-comment-menu').remove();
                }
            }
        }
    })
});

/**
 * load more reply comment
 * @type {number}
 */
var reply_page = 1;
$(document).on("click", ".loadmore-comment-reply", function () {
    parent_id = $(this).attr("data-id");
    reply_page += 1;
    total = reply_page * commentPerPage;
    $.ajax({
        url: url_load_more,
        data: {
            page: reply_page,
            parent_id: parent_id,
            _token: siteConfigGlobal.token
        },
        type: "post",
        success: function (result) {
            $("#get-more-reply-comment-" + parent_id).append(result.view);
            if (total >= result.data) {
                $("#loadmore-reply-" + parent_id).remove();
            }
            refreshEditor();
            setAllEmoticonContent(true);
        }
    })
});


$(document).ready(function () {
    var _token = $('input[name="_token"]').val();

    $('#checkedAll').change(function () {
        $('input:checkbox').not(this).prop('checked', this.checked);
    });

    $(".checkSingle").click(function () {
        if ($(this).is(":checked")) {
            var isAllChecked = 0;
            $(".checkSingle").each(function () {
                if (!this.checked) {
                    isAllChecked = 1;
                }
            });
            if (isAllChecked == 0) {
                $("#checkedAll").prop("checked", true);
            }
        } else {
            $("#checkedAll").prop("checked", false);
        }
    });

    /**
     * Delete comment
     */
    $('.deleteAll').click(function () {
        bootbox.confirm({
            message: 'Are you sure?',
            className: 'modal-default',
            buttons: {
                cancel: {
                    label: 'Cancel', className: 'pull-left',
                }
            },
            callback: function (result) {
                if (result) {
                    listValue = [];
                    $('.checkSingle:checked').each(function () {
                        if ($(this).val()) {
                            listValue.push(parseInt($(this).val()));
                        }
                    });
                    if (listValue.length <= 0) {
                        bootbox.alert({message: "Please select row delete", className: 'modal-default'});
                    } else {
                        $.ajax({
                            url: url_delete,
                            type: 'POST',
                            data: {_token: _token, data: listValue},
                            dataType: 'JSON',
                            success: function (data) {
                                if (data['success']) {
                                    location.reload();
                                } else if (data['error']) {
                                    bootbox.alert({message: data['error'], className: 'modal-default'});
                                }
                            },
                            error: function (data) {
                                bootbox.alert({message: data.responseText, className: 'modal-default'});
                            }
                        });
                    }
                }
            }
        });
    });

    /**
     * Approve all comment
     */
    $('#approveAll').click(function () {
        bootbox.confirm({
            message: 'Are you sure approve all comment?',
            className: 'modal-default',
            buttons: {
                cancel: {
                    label: 'Cancel', className: 'pull-left',
                }
            },
            callback: function (result) {
                if (result) {
                    listDataId = [];
                    $('.checkSingle:checked').each(function () {
                        if ($(this).val()) {
                            listDataId.push(parseInt($(this).val()));
                        }
                    });
                    if (listDataId.length <= 0) {
                        bootbox.alert({message: "Select comment need approve", className: 'modal-default'});
                    } else {
                        $.ajax({
                            url: url_approve_all,
                            method: 'POST',
                            data: {data: listDataId, _token: _token},
                            success: function (dt) {
                                for (var i = 0; i < listDataId.length; i++) {
                                    var id = listDataId[i];
                                    $('.changeStatus-' + id).html('Approve');
                                }
                                $('input:checkbox').removeAttr('checked');
                                bootbox.alert({message: "Comment approve success", className: 'modal-default'});
                            }
                        })
                    }
                }
            }
        });
    });

    /**
     * UnApprove all comment
     */
    $('#un_approve_all').click(function () {
        bootbox.confirm({
            message: 'Are you sure unapprove all comment?',
            className: 'modal-default',
            buttons: {
                cancel: {
                    label: 'Cancel', className: 'pull-left',
                }
            },
            callback: function (result) {
                if (result) {
                    listDataId = [];
                    $('.checkSingle:checked').each(function () {
                        if ($(this).val()) {
                            listDataId.push(parseInt($(this).val()));
                        }
                    });
                    if (listDataId.length <= 0) {
                        bootbox.alert({message: "Select comment need unapprove", className: 'modal-default'});
                    } else {
                        $.ajax({
                            url: url_unapprove_all,
                            method: 'POST',
                            data: {data: listDataId, _token: _token},
                            success: function () {
                                for (var i = 0; i < listDataId.length; i++) {
                                    var id = listDataId[i];
                                    $('.changeStatus-' + id).html('Unapprove');
                                }
                                $('input:checkbox').removeAttr('checked');
                                bootbox.alert({message: "Unapprove all comment success", className: 'modal-default'});
                            }
                        })
                    }
                }
            }
        });
    });
});

$(document).ready(function () {
    $('.dropdown-menu.action-reply-comment').attr('style', 'max-height: none !important');
    $('[data-toggle="tooltip"]').tooltip();
});

$('#icon-comment').click(function() {
    $('html, body').animate({
        scrollTop: $("#my-comment").offset().top
    }, 1500);
});

$("#tagcloud a").tagcloud({
    size: {start: 14, end: 36, unit: "px"},
    color: {start: '#6f6f6f', end: '#6f6f6f'}
});

$('.view-more').click(function () {
    var comment = $(this).parent().find('.full-comment').html();
    $('#modal-comment .modal-body p').html(comment);
    $('#modal-comment').modal('show');
});

/**
 * Load more comment content
 * @param {int} commentId
 * @returns void
 */
function viewMore(commentId) {
    var textMore = $('p.content-comment.not-trim-comment.comment-' + commentId).html();
    $('span.comment-' + commentId).html(replaceTextToIcon(textMore));
    $('a.load-more.comment-' + commentId).addClass('hidden');
    $('a.load-less.comment-' + commentId).removeClass('hidden');
}

/**
 * Load less comment content
 * @param {int} commentId
 * @returns {void}
 */
function viewLess(commentId) {
    var textLess = $('p.content-comment.trim-comment.comment-' + commentId).html();
    $('span.comment-' + commentId).html(replaceTextToIcon(textLess));
    $('a.load-more.comment-' + commentId).removeClass('hidden');
    $('a.load-less.comment-' + commentId).addClass('hidden');
}

/**
 * strip tags html
 *
 * @param {type} str
 * @returns {unresolved}
 */
function stripTags(str) {
    return str.replace(/<[^>]+>/gm, '');
}

/**
 * Replace emoji key to icon
 *
 * @param {string} str
 * @returns {unresolved}
 */
function replaceTextToIcon(str) {
    //str = RKfuncion.general.stripTagsAndEncode(str);
    str = converter.makeHtml(str);
    for (var key in icons) {
        str = str.replace(new RegExp(escapeRegex(key), 'g'), getImage(key));
    }
    return str;
}

/**
 * Get img emoji from key
 *
 * @param {string} key
 * @returns {String}
 */
function getImage(key) {
    return '<img src="' + emoticonPath + '/' + icons[key] + '" alt="' + key + '" class="emoji" />'
}

/**
 * Escape special character
 *
 * @param {string} str
 * @returns {String}
 */
function escapeRegex(str) {
    return (str + '').replace(/([.?*+^$[\]\\(){}|-])/g, '\\$1');
}

/**
 * get value of cmt
 *
 * @param {int} id
 * @returns {String}
 */
function getValueCmt(id) {
    return typeof cmtAll[id] !== 'undefined' ? stripTags(cmtAll[id]) : '';
}
