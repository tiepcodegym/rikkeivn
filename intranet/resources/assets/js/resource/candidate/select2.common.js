function formatRepo (repo) {
  if (repo.loading) {
    return repo.text;
  }

  var markup = "<div class='select2-result-repository clearfix'>" +
    "<div class='select2-result-repository__avatar' style='float: left;'><img style='border-radius: 50px;width: 50px; height: 50px;' src='" + repo.avatar + "' /></div>" +
    "<div class='select2-result-repository__meta select2_text' style='float: left; padding: 17px 10px 17px 10px; font-size: 13px;'>" +
      "<div class='select2-result-repository__title'>" + repo.text + "</div>" +
    "</div>" + 
    "</div>";

  
  return markup;
}

function formatRepoSelection (repo) {
  return repo.text;
}

function select2Employees(dom) {
    $(dom).select2({
        ajax: {
            url: $(dom).data('remote-url'),
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 5) < data.total_count
                    }
                };
            },
            cache: true
          },
          placeholder: chooseEmployeeText,
          escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
          minimumInputLength: 3,
          templateResult: formatRepo,
          templateSelection: formatRepoSelection,
          maximumSelectionSize: 5,
    });
}