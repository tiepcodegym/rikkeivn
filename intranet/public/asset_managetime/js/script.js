$(function() {
    $.fn.selectSearchEmployeeOtDisallow = function (teamElement) {

        $(this).select2({
            id: function(response){
                return response.id;
            },
            ajax: {
                url: $(this).data('remote-url'),
                dataType: "JSON",
                delay: 250,
                data: function (params) {
                    $type = $($(this)[0]).attr('data-type');
                    $team = $('#'+teamElement).val();                    
                    return {
                        t: $team,
                        q: params.term,
                        page: params.page,
                        type: $type
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 5) < data.total_count
                        }
                    };
                },
                cache: true,
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            minimumInputLength: 2,
            templateResult: formatReponse,
            templateSelection: formatReponseSelection,
        });
    };
    $.fn.selectSearchEmployee = function () {
        $(this).select2({
            id: function(response){ 
                return response.id;
            },
            ajax: {
                url: $(this).data('remote-url'),
                dataType: "JSON",
                delay: 250,
                data: function (params) {
                    $type = $($(this)[0]).attr('data-type');
                    return {
                        q: params.term,
                        page: params.page,
                        type: $type
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 5) < data.total_count
                        }
                    };
                },
                cache: true,
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            minimumInputLength: 2,
            templateResult: formatReponse,
            templateSelection: formatReponseSelection,
        });
    };

    $.fn.selectSearchEmployeeNoPagination = function () {
        $(this).select2({
            id: function(response){ 
                return response.id;
            },
            ajax: {
                url: $(this).data('remote-url'),
                dataType: "JSON",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.items,
                    };
                },
                cache: true,
            },
            escapeMarkup: function (markup) {
                return markup;
            },
            minimumInputLength: 2,
            templateResult: formatReponse,
            templateSelection: formatReponseSelection,
        });
    };

    $('[data-toggle="tooltip"]').tooltip(); 
});

function formatReponse (response) {
    if (response.loading) {
        return response.text;
    }

    return markup = (response.avatar_url) ? 
        "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__title'>" + 
                "<img style=\"margin-right:8px;max-width: 32px;max-height: 32px;border-radius: 50%;\" src=\""+
                response.avatar_url+"\">" + response.text + 
            "</div>" +
        "</div>" 
        : 
        "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__title'>" + 
                "<i style='margin-right:8px' class='fa fa-user-circle fa-2x' aria-hidden='true'></i>" +
                response.text + 
            "</div>" +
        "</div>"; 
}

function formatReponseSelection (response, domSpan) {
    if (typeof response.dataMore === 'object') {
        var domSelect = domSpan.closest('.select2.select2-container')
            .siblings('select').first();
        $.each(response.dataMore, function (key, value) {
            domSelect.data('select2-more-' + key, value);
        });
    }
    return  response.text;
}