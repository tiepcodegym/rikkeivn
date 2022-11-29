$(function() {
    $(document).on('change', '.search-change', function(e) {
        e.stopPropagation();
        $('.btn-search-filter').trigger('click');
    })

    $('.pagination').on('click', '.page-link', function(event) {
        event.preventDefault();
        var pageThis = $(this).attr('href');
        dataSubmit = {};
        dataSubmit.page = pageThis;
        dataSubmit = { 'filter_pager': dataSubmit, 'current_url': currentUrl };
        console.log(dataSubmit);
        $.ajax({
            url: urlPage,
            type: 'GET',
            data: dataSubmit,
            dataType: 'json',
            success: function(data) {
                window.location.reload();
            },
        });
    });
})

$(function() {
    $("body").on('click', '#btn-showmodal', function() {
        $("#modalGrateful .error").html('');
        $("#modalGrateful .modal-date").removeClass('hidden');
        $("#modalGrateful .modal-title").html('Tri ân những nhân viên đã chọn');
        $('#modalGrateful .submit-grateful-remove').addClass('submit-grateful');
        $('#modalGrateful .submit-grateful').removeClass('submit-grateful-remove');
        $('#show-modalGrateful').click();
    })

    $("body").on('click', '#btn-showmodal-remove', function() {
        $("#modalGrateful .error").html('');
        $("#modalGrateful .modal-date").addClass('hidden');
        $("#modalGrateful .modal-title").html('Bỏ tri ân những nhân viên đã chọn');
        $('#modalGrateful .submit-grateful').addClass('submit-grateful-remove');
        $('#modalGrateful .submit-grateful-remove').removeClass('submit-grateful');
        $('#show-modalGrateful').click();
    })

    $('#modalGrateful').on('click', '.submit-grateful', function(e) {
        var _that = this;
        $(this).addClass('disabled');
        $(this).find('.fa-refresh').removeClass('hidden');

        var date = $('input[name="grateful_date"]').val();
        var note = $('textarea[name="grateful_note"]').val();
        var arrCheckItem = getCheckItem(sessionKeys);
        var check = true;

        $("#modalGrateful .error").html('');
        if (!arrCheckItem.length) {
            check = false;
            $("#modalGrateful .error").html('Bạn chưa chọn nhân viên');
        }
        if (check) {
            $.ajax({
                url: gratefulStore,
                type: 'POST',
                dataType: 'json',
                data: {
                    '_token': _token,
                    'date': date,
                    'note': note,
                    'arrItem': arrCheckItem
                },
                success: function(data) {
                    if (!data.status) {
                        $("#modalGrateful .error").html(data.message);
                        return;
                    }
                    arrCheckItem.forEach(function(item, index) {
                        $('tr[data-tr-row="' + item + '"]').addClass('bg_gratefuled');
                    })
                    $(_that).find('.fa-refresh').addClass('hidden');
                    $(_that).removeClass('disabled');

                    $('#show-modalGrateful').click();
                    $('#show-modalMessage').click();
                    $('textarea[name="grateful_note"]').val('')
                    removeCheckItem(sessionKeys);
                },
                error: function(e) {
                    $("#modalGrateful .error").html('Hệ thống có lỗi. Xin vui lòng thử lại sau!');
                    $(_that).find('.fa-refresh').addClass('hidden');
                    $(_that).removeClass('disabled');
                }
            });
        } else {
            $(_that).find('.fa-refresh').addClass('hidden');
            $(_that).removeClass('disabled');
        }
    })

    $('#modalGrateful').on('click', '.submit-grateful-remove', function(e) {
        var _that = this;
        $(this).addClass('disabled');
        $(this).find('.fa-refresh').removeClass('hidden');

        var note = $('textarea[name="grateful_note"]').val();
        var arrCheckItem = getCheckItem(sessionKeys);
        var check = true;

        $("#modalGrateful .error").html('');
        if (!arrCheckItem.length) {
            check = false;
            $("#modalGrateful .error").html('Bạn chưa chọn nhân viên');
        }
        if (note == '') {
            check = false;
            $("#modalGrateful .error").html('Nêu lý do bỏ nhân viên đã tri ân');
        }
        if (check) {
            $.ajax({
                url: gratefulRemove,
                type: 'POST',
                dataType: 'json',
                data: {
                    '_token': _token,
                    'note': note,
                    'arrItem': arrCheckItem
                },
                success: function(data) {
                    if (!data.status) {
                        $("#modalGrateful .error").html(data.message);
                        return;
                    }
                    arrCheckItem.forEach(function(item, index) {
                        $('tr[data-tr-row="' + item + '"]').removeClass('bg_gratefuled');
                    })
                    $(_that).find('.fa-refresh').addClass('hidden');
                    $(_that).removeClass('disabled');

                    $('#show-modalGrateful').click();
                    $('#show-modalMessage').click();
                    $('textarea[name="grateful_note"]').val('')
                    removeCheckItem(sessionKeys);
                },
                error: function(e) {
                    $("#modalGrateful .error").html('Hệ thống có lỗi. Xin vui lòng thử lại sau!');
                    $(_that).find('.fa-refresh').addClass('hidden');
                    $(_that).removeClass('disabled');
                }
            });
        } else {
            $(_that).find('.fa-refresh').addClass('hidden');
            $(_that).removeClass('disabled');
        }
    })
})