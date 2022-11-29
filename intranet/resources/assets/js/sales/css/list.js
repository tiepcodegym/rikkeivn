function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).attr('data-href')).select();
    document.execCommand("copy");
    $temp.remove();

    $("#modal-clipboard").modal('show');
}

jQuery(document).ready(function($) {
    $('.css-list-copy-url').click(function() {
        this.selectionStart = 0;
        this.selectionEnd = $(this).val().length;
    });
    $(document).on('keyup keydown keypress', 'input.css-list-copy-url', function(e) {
        e.preventDefault();
        return false;
    });
});

$('.btn-reset-css').click(function(){
    if (!confirm('Are you sure?')) return false;
    
    $.ajax({
        url: baseUrl + '/css/reset',
        type: 'get',
    })
    .done(function (data) { 
        // Reload page
        location.href = currentUrl;
    })
    .fail(function () {
        alert("Ajax failed to fetch data");
    })
});