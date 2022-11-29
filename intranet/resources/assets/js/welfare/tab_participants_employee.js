var isAjaxing = false;
$('.detail_item').on('click', function () {
    if(isAjaxing) return;
    isAjaxing = true;
    var URL = this.parentElement.getAttribute('href');
    var id = this.parentElement.getAttribute('id');
//     $.ajax({
//         method: "GET",
//         url: URL,
//         data: {id: id},
//         success: function (data) {
//             for (var key in data) {
//                 var attrValue = data[key];
//                 var html = '<td  class="number-format">' + data[key] + ' </td>';
//                 var arr = ['fee_total_actual', 'fee_total','empl_trial_fee', 'empl_trial_company_fee', 'empl_offical_company_fee'];
//                 //console.log(html);
//                 if (typeof $('[name="' + key + '"]'))
//                     if (arr.includes(key)) {
//                         html = html + '<span> VND</span>';
//                     }
//                 $('[name="' + key + '"]').append(html);
//             }
//             $("#myModal").modal("show");
//         }
//     });
    window.location.href = URL;
});
$("#myModal").on("hidden.bs.modal", function () {
    $('.modal-body tr').find(':not(:first)').remove();
    isAjaxing = false;
});
$(document).on('change','#view-list-is-register-online',function() {
    isAjaxing = true;
    if($(this).is(':checked')) {
        is_register_online = 1;
    } else {
        is_register_online = 0;
    }
    $.ajax({
        headers: {
              'X-CSRF-Token': $('input[name="_token"]').val()
        },
        type: 'post',
        url: url,
        data: {'is_register_online': is_register_online,
            'welfare_id': $(this).data('id')
        },
        success: function (data) {
            $('#modal-success-notification .modal-title').text('Thông báo');
            $('#modal-success-notification .text-default').css('padding','10px');
            if(data['status'] == true) {
                if(data['type'] == "1") {
                    $('#modal-success-notification .text-default').text('Cho phép đăng ký trực tuyến');
                } else {
                    $('#modal-success-notification .text-default').text('Không cho phép đăng ký trực tuyến');
                }
            } else {
                    $('#modal-success-notification .text-default').text('Có lỗi xảy ra vui lòng thử lại');
            }
            $('#modal-success-notification').modal('show');
            isAjaxing = false;
        }
    });
});