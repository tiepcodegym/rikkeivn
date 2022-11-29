/**
 * Merge 2 or more customer in one
 *
 * @returns {void} reload page
 */
function merge() {
    var arrayMerge = [];
    $('.check-child:checked').each(function () {
        arrayMerge.push(this.value);
    });
    $.ajax({
        url: routeMerge,
        type: 'post',
        dataType: 'json',
        data: {
            idsMerge: arrayMerge,
            idMergeIn: $('.select-merge-in').val(),
            _token: token,
        },
        success: function () {
            location.reload();
        },
    });
}

/**
 * Merge confirm event
 * After choose customers to merge, click `Merge` button.
 * Then show modal display customer list to merge.
 * Choose primary customer
 *
 * @returns {void} show modal confirm
 */
function mergeConfirm() {

    $('#modal-merge .modal-body .list-group').html('');
    $('#modal-merge .modal-body .select-merge-in').html('');
    $('.check-child:checked').each(function () {
        $('#modal-merge .modal-body .list-group').append('<li class="list-group-item">' + $(this).data('name') + '</li>');
        $('#modal-merge .modal-body .select-merge-in').append('<option value="' + this.value + '">' + $(this).data('name') + '</option>');
    });
    $('#modal-merge').modal('show');
}

/**
 * Check all checkbox event
 *
 * @param {dom} self
 * @returns {void}
 */
function parentCheck(self) {
    self = $(self);
    if (self.is(":checked")) {
        $('.check-child').prop('checked', true);
    } else {
        $('.check-child').prop('checked', false);
    }
    disableMerge();
}

/**
 * Check item event
 *
 * @returns {void}
 */
function childCheck() {
    if ($('.check-child:checked').length == $('.check-child').length) {
        $('.check-parent').prop('checked', true);
    } else {
        $('.check-parent').prop('checked', false);
    }

    disableMerge();
}

/**
 * Enable/disable merge button
 * Enable if choose 2 or more customers
 *
 * @returns {void}
 */
function disableMerge() {
     if ($('.check-child:checked').length > 1) {
        $('.btn-merge').prop('disabled', false);
    } else {
        $('.btn-merge').prop('disabled', true);
    }
}
