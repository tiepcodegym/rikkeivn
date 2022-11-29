jQuery(document).ready(function ($) {
    $('#fromDate').datetimepicker({
        format: 'DD/MM/Y',
        showClear: true
    });

    $('#toDate').datetimepicker({
        format: 'DD/MM/Y',
        showClear: true,
    });

    function reFormatDate(dateDisplay) {
        dateDisplay = moment(dateDisplay, 'DD/MM/YYYY');
        return moment(dateDisplay).format('YYYY-MM-DD');
    }

    $("#fromDate").on("dp.change", function (e) {
        $('#toDate').data("DateTimePicker").minDate(e.date);
        $('#altFromDate').val(reFormatDate(e.date));
    });

    $("#toDate").on("dp.change", function (e) {
        $('#fromDate').data("DateTimePicker").maxDate(e.date);
        $('#altToDate').val(reFormatDate(e.date));
    });

    CKFinder.config.resourceType = 'Images';
    CKFinder.config.rememberLastFolder = true;

    $('.btn-ckfinder-browse-file').click(function (event) {
        event.preventDefault();
        var idInput = $(this).data('element');
        if (!idInput || !$(idInput).length) {
            return false;
        }
        var finder = new CKFinder();
        finder.selectActionFunction = function (fileUrl) {
            fileUrl = fileUrl.replace(/^[\/]+|[\/]+$/gm, '');
            $(idInput).val(fileUrl);
            $(idInput).closest('.ckfinder-preview-wrapper').find('.ckfinder-img-preview').html('<img src=" ' + baseUrl + fileUrl + '" />');
        };
        finder.popup();
    });

    $('#form-post-edit').validate({
        rules: {
            'title': {
                required: true
            },
            'link': {
                required: true
            },
            'slug': {
                required: true
            },
            'order': {
                required: true
            },
            'image': {
                required: true
            },
            'start_at': {
                required: true
            },
            'end_at': {
                required: true
            },
        },
        messages: {
            'title': {
                required: messageValidate.required
            },
            'link': {
                required: messageValidate.required
            },
            'slug': {
                required: messageValidate.required
            },
            'order': {
                required: messageValidate.required
            },
            'image': {
                required: messageValidate.required
            },
            'start_at': {
                required: messageValidate.required
            },
            'end_at': {
                required: messageValidate.required
            },
        }
    });
});
$('#radioBtn a, #radioBtnIsGif a').on('click', function () {
    var sel = $(this).data('title');
    var tog = $(this).data('toggle');
    $('#' + tog).prop('value', sel);

    $('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
    $('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
});

function ChangeToSlug() {
    var title, slug;
    //Lấy text từ thẻ input title
    title = document.getElementById("title").value;
    //Đổi chữ hoa thành chữ thường
    slug = title.toLowerCase();
    //Đổi ký tự có dấu thành không dấu
    slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
    slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
    slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
    slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
    slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
    slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
    slug = slug.replace(/đ/gi, 'd');
    //Xóa các ký tự đặt biệt
    slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
    //Đổi khoảng trắng thành ký tự gạch ngang
    slug = slug.replace(/ /gi, "-");
    //Đổi nhiều ký tự gạch ngang liên tiếp thành 1 ký tự gạch ngang
    //Phòng trường hợp người nhập vào quá nhiều ký tự trắng
    slug = slug.replace(/\-\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-/gi, '-');
    slug = slug.replace(/\-\-/gi, '-');
    //Xóa các ký tự gạch ngang ở đầu và cuối
    slug = '@' + slug + '@';
    slug = slug.replace(/\@\-|\-\@|\@/gi, '');
    //In slug ra textbox có id “slug”
    document.getElementById('slug').value = slug;
}
