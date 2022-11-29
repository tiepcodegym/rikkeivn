jQuery(document).ready(function($) {
    selectSearchReload();
});

/**
 * Filter dashboard by team, year
 */
$('#select-team, #select-year').change(function() {
    var teamVal = $('#select-team').val();
    var year = $('#select-year').val();
    var url = baseUrl + 'resource/dashboard/index/' + year + '/';
    if (teamVal != 0) {
       url += teamVal;
    }
    location.href = url;
});