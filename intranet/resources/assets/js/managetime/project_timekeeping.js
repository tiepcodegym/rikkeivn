 jQuery(document).ready(function ($) {
    selectSearchReload();
    RKfuncion.general.initDateTimePicker();
    $('.input-datepicker').on('dp.change', function () {
        window.location.href = urlIndex + '?month=' + $(this).val();
    });
});

$('.btn-reset-filter').click(function () {
    var location = window.location;
    window.history.pushState({}, document.title, location.origin + location.pathname);
});

$('.filter-date').datepicker({
        autoclose: true,
        format: 'dd-mm-yyyy',
        weekStart: 1,
        todayHighlight: true
    });

$('.filter-date').on('keyup', function(e) {
    e.stopPropagation();
    if (e.keyCode == 13) {
        $('.btn-search-filter').trigger('click');
    }
});

function createCookie(cookieName,cookieValue,daysToExpire)
{
  var date = new Date();
  date.setTime(date.getTime()+(daysToExpire*24*60*60*1000));
  document.cookie = cookieName + "=" + cookieValue + "; expires=" + date.toGMTString();
}

function accessCookie(cookieName)
{
  var name = cookieName + "=";
  var allCookieArray = document.cookie.split(';');
  for(var i=0; i<allCookieArray.length; i++)
  {
    var temp = allCookieArray[i].trim();
    if (temp.indexOf(name)==0)
    return temp.substring(name.length,temp.length);
  }
    return "";
}

$(':checkbox').each(function() {
    var listIds = accessCookie('empId' + page).split(",");
    if (listIds.indexOf($(this).val()) > 0) {
        this.checked = true;
    }
});

$('#check-all').click(function(event) {
    createCookie('empId' + page, accessCookie('empId' + page), 1);
    if(this.checked) {
        $(':checkbox').each(function() {
            this.checked = true;
            createCookie('empId' + page, accessCookie('empId' + page) + ',' + $(this).val(), 1);
        });
    } else {
        $(':checkbox').each(function() {
            this.checked = false;
            createCookie('empId' + page, accessCookie('empId' + page).replace(new RegExp($(this).val(), 'g'), ''), 1);
            createCookie('empId' + page, accessCookie('empId' + page).replace(new RegExp(',,', 'g'), ','), 1);
        });
    }
});

$('body').on('change', '#table_systena input[type=checkbox]', function () {
    if ($(this).is(":checked")) {
        createCookie('empId' + page, accessCookie('empId' + page) + ',' + $(this).val(), 1);
    } else {
        createCookie('empId' + page, accessCookie('empId' + page).replace(new RegExp($(this).val(), 'g'), ''), 1);
        createCookie('empId' + page, accessCookie('empId' + page).replace(new RegExp(',,', 'g'), ','), 1);
    }
});

