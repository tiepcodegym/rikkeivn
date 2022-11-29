$('#modal-add').click(function() {
    $('.modal-title').text(titleCreate);
    $('form').attr('action', urlCreate);
    $('#modal-create-edit').modal('show');
    $("#status").val(statusActive);
});

$('#close_form').on('click', function(e) {
    $('#modal-create-edit').modal('hide');
    $('#name_vi').val("");
    $('#name_en').val("");
    $('#name_ja').val("");

    $("#status option:selected").each(function() {
        $(this).removeAttr("selected");
    });
});


$('.btn-edit').on('click', function() {
    $('.modal-title').text(titleUpdate);
    var data = $(this).closest('tr');
    $('#id_cat').val(data.attr('row-id'));
    $('#name_vi').val(data.attr('row-name-vi'));
    $('#name_en').val(data.attr('row-name-en'));
    $('#name_ja').val(data.attr('row-name-ja'));
    $("#status option:selected").each(function() {
        $(this).removeAttr("selected");
    });
    $("#status").val(data.attr('row-status'));
    $('form').attr('action', urlUpdate);

    $('#modal-create-edit').modal('show');
});