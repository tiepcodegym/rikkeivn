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
    //L???y text t??? th??? input title
    title = document.getElementById("title").value;
    //?????i ch??? hoa th??nh ch??? th?????ng
    slug = title.toLowerCase();
    //?????i k?? t??? c?? d???u th??nh kh??ng d???u
    slug = slug.replace(/??|??|???|???|??|??|???|???|???|???|???|??|???|???|???|???|???/gi, 'a');
    slug = slug.replace(/??|??|???|???|???|??|???|???|???|???|???/gi, 'e');
    slug = slug.replace(/i|??|??|???|??|???/gi, 'i');
    slug = slug.replace(/??|??|???|??|???|??|???|???|???|???|???|??|???|???|???|???|???/gi, 'o');
    slug = slug.replace(/??|??|???|??|???|??|???|???|???|???|???/gi, 'u');
    slug = slug.replace(/??|???|???|???|???/gi, 'y');
    slug = slug.replace(/??/gi, 'd');
    //X??a c??c k?? t??? ?????t bi???t
    slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
    //?????i kho???ng tr???ng th??nh k?? t??? g???ch ngang
    slug = slug.replace(/ /gi, "-");
    //?????i nhi???u k?? t??? g???ch ngang li??n ti???p th??nh 1 k?? t??? g???ch ngang
    //Ph??ng tr?????ng h???p ng?????i nh???p v??o qu?? nhi???u k?? t??? tr???ng
    slug = slug.replace(/\-\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-\-/gi, '-');
    slug = slug.replace(/\-\-\-/gi, '-');
    slug = slug.replace(/\-\-/gi, '-');
    //X??a c??c k?? t??? g???ch ngang ??? ?????u v?? cu???i
    slug = '@' + slug + '@';
    slug = slug.replace(/\@\-|\-\@|\@/gi, '');
    //In slug ra textbox c?? id ???slug???
    document.getElementById('slug').value = slug;
}
