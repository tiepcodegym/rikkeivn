$(function (){
    var pgurl = window.location.href.substr(window.location.href);

    $('.request-menu li a').each(function() 
    {
        if ($(this).attr('href') == pgurl) {
            $(this).addClass('active');
        }
    });

    $('.request-select-2').select2({
        minimumResultsForSearch: 5
    });

    //selectSearchReload();
    RKfuncion.select2.init();

    $('.date-picker').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true
    });

    $('.filter-date').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true,
    }).on('changeDate', function(e) {
        e.stopPropagation();
        $('.btn-search-filter').trigger('click');
    }).on('keyup', function(e) {
        e.stopPropagation();
        if (e.keyCode === 13) {
            $('.btn-search-filter').trigger('click');
        }
    }).on('clearDate', function(e) {
        e.stopPropagation();
        $('.btn-search-filter').trigger('click');
    });

    $('#inventory-submit-btn').click(function (e) {
        if ($('#check_mail_send').is(':checked')) {
            var form = $(this).closest('form');
            bootbox.confirm({
                className: 'modal-warning',
                message: $(this).data('noti'),
                callback: function (result) {
                    if (result) {
                        form[0].submit();
                    }
                }
            })
            return false;
        }
    });

    //select 2 search asset item
    function initSelectAsset() {
        $('.select-search-asset').each(function () {
            var dom = $(this);
            // var catId = dom.closest('tr').data('cat');
            var catId = dom.closest('tr').attr('data-cat');
            dom.select2({
                minimumInputLength: 2,
                ajax: {
                    url: dom.data('remote-url'),
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        var excludeIds = [];
                        $('[data-cat="'+ catId +'"] .select-search-asset').each(function () {
                            var value = $(this).val();
                            if (value) {
                                excludeIds.push(value);
                            }
                        });
                        return {
                            q: params.term, // search term
                            page: params.page,
                            cat_id: catId,
                            employee_id: $('input[name="item[employee_id]"]').val(),
                            'exclude_ids[]': excludeIds,
                        };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 20) < data.total_count
                            },
                        };
                    },
                    cache: true,
                },
            });
        });
    };
    //================
    $('body').on('change', '.category', function () {
        //=== check dupliace
        var catIdNew = $(this).val();
        catId = $(this).closest('tr').attr('data-cat');
        $('.request-category-duplicate-error').hide();
        if(catIdNew === catId) {
            return false;
        }
        $('#btn_req_warehouse').prop('disabled', false);
        $('#btn_req_allocation').prop('disabled', false);
        var categoryDuplicate = [];
        $('.request-item').each(function() {
            categoryDuplicate[$(this).attr('data-cat')] = $(this).attr('data-cat');
        });
        if (jQuery.inArray(catIdNew, categoryDuplicate) >= 0) {
            $('.request-category-duplicate-error').show();
            $('#btn_req_warehouse').prop('disabled', true);
            $('#btn_req_allocation').prop('disabled', true);
            document.getElementById("btn_req_allocation").disabled = true;
            return false;
        }
        categoryDuplicate = [];
        var url = $(this).data('remote-url');
        var iconLoading = $('#update_cate_loading');

        if (!catId || !catIdNew || !url || !iconLoading.hasClass('hidden')) {
            return;
        }

        var trItem = $(this).closest('tr');
        iconLoading.removeClass('hidden');
        $('#btn_add_item').prop('disabled', true);
        $('.btn-del-item').prop('disabled', true);
        $.ajax({
           type: 'POST',
           url: url,
           data: {
               _token: siteConfigGlobal.token,
               cat_id: catId,
               catIdNew: catIdNew,
           },
           success: function (data) {
                if (catIdNew == -1) {
                    trItem.closest('table').find('tr[data-cat="'+ catId +'"]').remove();
                } else {
                    var trCats = $('#list-asset-category tr[data-cat="'+ catId +'"]');
                    trCats.attr('data-cat', catIdNew);
                    trCats.find("[data-cat-id]").each(function () {
                        $(this).attr('data-cat-id', catIdNew);
                    });
                    trCats.find(".select-search-asset").each(function () {
                        $(this).html('');
                    });
                   initSelectAsset();
                }
                //history
                if (data.history) {
                    var historyItem = $('.history-list .history-item:first').clone();
                    historyItem.find('.note').remove();
                    historyItem.find('.author strong').text(currUserNameAcc);
                    historyItem.find('.author i').text('at ' + data.history.created_at);
                    historyItem.find('.comment').text(data.history.content);
                    historyItem.prependTo($('.history-list .box-body'));
                }
           },
           error: function (error) {
               bootbox.alert({
                   className: 'modal-danger',
                   message: error.responseJSON,
               });
           },
           complete: function () {
               iconLoading.addClass('hidden');
               $('#btn_add_item').prop('disabled', false);
               $('.btn-del-item').prop('disabled', false);
           },
        });
    });
    //===============
    initSelectAsset();

    $('body').on('change', '.update-request-qty', function () {
        var input = $(this);
        var catId = input.data('cat-id');
        var quantity = input.val();
        var url = input.data('url');
        var iconLoading = $('#update_qty_loading');
        if (quantity > 20) {
            bootbox.alert({
                message: 'Too large quantity!',
                className: 'modal-danger',
            });
            return;
        }

        if (!catId || !quantity || !url || !iconLoading.hasClass('hidden')) {
            return;
        }

        iconLoading.removeClass('hidden');
        input.prop('disabled', true);
        $.ajax({
           type: 'POST',
           url: url,
           data: {
               _token: siteConfigGlobal.token,
               cat_id: catId,
               quantity: quantity,
           },
           success: function () {
               var trCats = $('#list-asset-category tr[data-cat="'+ catId +'"]');
               trCats.eq(0).find('td.rowspan').each(function () {
                    $(this).attr('rowspan', quantity);
               });
               var trLen = trCats.length;
               if (trLen > quantity) {
                   for (var i = trLen; i > quantity; i--) {
                       trCats.eq(i - 1).remove();
                   }
               } else if (trLen < quantity) {
                    var trCatLast = trCats.last();
                    for (var i = 0; i < quantity - trLen; i++) {
                        var trItem = trCatLast.clone();
                        trItem.find('td.rowspan').remove();
                        trItem.find('td .select2-container').remove();
                        trItem.find('td select').val('');
                        trItem.find('td select option').remove();
                        trItem.insertAfter(trCatLast);
                    }
               } else {
                   //none
               }
               initSelectAsset();
           },
           error: function (error) {
               bootbox.alert({
                   className: 'modal-danger',
                   message: error.responseJSON,
               });
           },
           complete: function () {
               iconLoading.addClass('hidden');
               input.prop('disabled', false);
           },
        });
    });

    /*
     * add new item
     */
    $('#btn_add_item').click(function (e) {
        e.preventDefault();
        var listTable = $('#list-asset-category tbody');

        var dupCatIds = [];
        listTable.find('tr.request-item .category').each(function (e) {
            dupCatIds.push($(this).val());
        });
        var item = $('#asset_allowcate_item_tmp tbody tr:first').clone().attr('data-cat', 0);
        var itemCat = item.find('.category');
        var newCatId = null;
        itemCat.find('option').each(function () {
            var optionVal = $(this).attr('value');
            if (!newCatId && dupCatIds.indexOf(optionVal) < 0) {
                newCatId = optionVal;
                return false;
            }
        });
        if (!newCatId) {
            bootbox.alert({
               className: 'modal-danger',
               message: textOutOfCatQuantity,
            });
            return false;
        }
        itemCat.val(newCatId);
        var assetSelect = item.find('.select-search-asset');
        assetSelect.removeClass('select2-hidden-accessible');
        assetSelect.next('.select2').remove();
        item.appendTo(listTable);
        itemCat.trigger('change');
    });

    /*
     * remove item
     */
    $('body').on('click', '.btn-del-item', function (e) {
        e.preventDefault();
        if ($('#list-asset-category tbody tr.request-item').length < 2) {
            return false;
        }
        var tr = $(this).closest('tr');
        bootbox.confirm({
            message: textAreYouWantoDelete,
            className: 'modal-warning',
            callback: function (result) {
                if (result) {
                    var catSelect = tr.find('.category');
                    catSelect.prepend('<option value="-1"></option>');
                    catSelect.val('-1');
                    catSelect.trigger('change');
                }
            }
        });
    });

    //Submit request to warehouse
    $('#btn_req_warehouse').click(function (e) {
        e.preventDefault();
        var $modalConfirm = $('#modal-choose-branch');
        $modalConfirm.modal('show');
    });
    $('#type-branch').change(function (e) {
        e.preventDefault();
        var typeBranch = $('#type-branch').val();
        $('.js-type-branch').val(typeBranch);
    });
    $('#send-choose-branch').click(function (e) {
        e.preventDefault();
        $('.js-err-cateId').html("");
        var typeBranch = $('#type-branch').val();
        if (typeBranch == 0) {
            $('.message-choose-branch').removeClass('hidden');
            return false;
        }
        $('.message-choose-branch').addClass('hidden');
        $(this).prop("disabled", true);
        var url = $(this).data('url');
        var $form = $('#form-asset-submit');
        var formData = new FormData($form[0]);
        var $button = $('#send-choose-branch');
        
        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            processData: false,
            contentType: false,
        }).done(function (data) {
            $('#modal-choose-branch').modal('hide');
            if (!data.success) {
                $.each(data.errors, function (i, val) {
                    $(`.js-err-cateId[data-cat-id='${i}']`).html(val.mess);
                });
            }
            bootbox.alert({
                message: data.message,
                className: data.className
            });
            $button.prop("disabled", false);
        });
    });

    $('#btn_req_allocation').click(function (e) {
        e.preventDefault();
        $('.js-err-cateId').html('');
        var form = $(this).closest('form');
        var mess = $(this).data('noti');
        var messBranch = $(this).data('noti_branch');
        var error = false;
        var errorBranch = false;
        $('#list-asset-category [name="asset_id[]"]').each(function () {
            if (!$(this).val()) {
                error = true;
                return false;
            }
        });
        if (!error) {
            $('#list-asset-category select[name="asset_id[]"]').each(function() {
                var selectDom = $(this);
                var regionCode = selectDom.find(':selected').text().substr(0, 2);
                if (regionOfEmp.toLowerCase() != regionCode.toLowerCase()) {
                    errorBranch = true;
                    return false;
                }
                
            });
        }
        if (error) {
            bootbox.alert({
                className: 'modal-danger',
                message: mess
            });
            $(this).prop('disabled', false);
            return false;
        }
        if (errorBranch) {
            bootbox.confirm({
                className: 'modal-warning',
                message: messBranch,
                callback: function (result) {
                    if (result) {
                        // Submit if OK
                        form[0].submit();
                    }
                }
            });
            return false;
        }
        // if error false
        form[0].submit();
    });
    //check delete request
        var requestId = []
        $checkboxes = $('.table-request-asset tbody td input[type="checkbox"]');
        $checkboxes.change(function () {
            var countChecked = $checkboxes.filter(':checked').length;
            if ($(this).is(':checked')) {
                requestId.push($(this).val());
            } else {
                var index = requestId.indexOf($(this).val());
                if (index > -1) {
                    requestId.splice(index, 1);
                }
            }

            $('.btn-delete-request').addClass('disabled');
            if (countChecked > 0) {
                $('.btn-delete-request').removeClass('disabled');
            }
            if (countChecked === $checkboxes.length) {
                $('.btn-delete-all').prop('checked', true);
            } else {
                $('.btn-delete-all').prop('checked', false);
            }
            $('#form-delete-request #request_ids').val(requestId);
        });
        $('.btn-delete-all').on('change', function () {
            $checkboxes.prop('checked', true);
            if ($(this).is(':checked')) {
                $checkboxes.each(function () {
                    requestId.push($(this).val());
                });
                $(this).prop('checked', true);
                $('.btn-delete-request').removeClass('disabled');
            } else {
                $checkboxes.prop('checked', false);
                requestId = [];
                $('.btn-delete-request').addClass('disabled');
            }
            $('#form-delete-request #request_ids').val(requestId);
        });
});

