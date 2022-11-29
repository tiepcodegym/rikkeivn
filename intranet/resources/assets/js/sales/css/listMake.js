$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});

function showAllMake(elem, cssId, code, itemId) {
    var loaded = $(elem).data('loaded'); //check data loaded
    if (loaded == 'true') {
        var dir = $(elem).data('dir');
        if (dir == 'closed') {
            $(elem).data('dir', 'opened');
            $(elem).find('i.fa-circle').removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
        } else {
            $(elem).data('dir', 'closed');
            $(elem).find('i.fa-circle').removeClass('fa-chevron-circle-up').addClass('fa-chevron-circle-down');
        }
        $('tr[data-id='+itemId+']').toggle(500);
        return false;
    }
    $(elem).find('i.fa-refresh').removeClass('hidden');
    $.ajax({
        url: urlShowAllMake,
        type: 'post',
        dataType: 'html',
        data: {css_id: cssId, code: code, itemId: itemId}
    })
    .done(function (data) { 
        $(elem).data('dir', 'opened');
        $(elem).find('i.fa-refresh').addClass('hidden');
        $(elem).find('i.fa-circle').removeClass('fa-chevron-circle-down').addClass('fa-chevron-circle-up');
        $(elem).data('loaded', 'true');
        $(elem).closest('tr').after(data);
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
}


