jQuery(function () {
    $("#error-content").hide();
    $("#mail-error").hide();

    $('.daredevel-tree-anchor').removeClass("ui-icon");

    $('.tokenize-callable-to-other').tokenize2({
        tokensAllowCustom: true,
        delimiter: [' '],
    });

    $("#getEmployees").removeAttr("hidden");
    $("#content").removeAttr("hidden");

    editor = CKEDITOR.replace('content');
    CKEDITOR.config.title = false;

    editor.on('change',function()
    {
        check_comment = $('#check_comment').val();

        if(check_comment == 'true')
        {
            content = trimAll(CKEDITOR.instances.content.getData().replace(/<[^>]*>/gi, ''));
            if(content.trim().length == 0)
            {
                $("#error-content").show();
            } else {
                $("#error-content").hide();
            }
        }
    });

    $(".token-search input").keyup(function() 
    {
        $("#title-error").hide();
        $("#mail-error").hide();      

        check_comment = $('#check_comment').val();

        if(check_comment == 'true')
        {
            var favorite = [];
            var check_mail = true;
            $('.tokens-container li.token').each(function (i) 
            {
                var value = $(this).attr('data-value');
                favorite.push($(this).attr('data-value'));
                check_mail = validateEmail(value);
            });
            $('#to_other').val(favorite.join(", "));

            team_list = $('#team_list').val();
            to_other = $('#to_other').val();

            setInterval(function() { 
                $('.tokens-container li.token').each(function (i) 
                {
                    var value = $(this).attr('data-value');
                    favorite.push($(this).attr('data-value'));
                });
                $('#to_other').val(favorite.join(", "));
                to_other = $('#to_other').val(); 
                input_search = $('.token-search input').val();
                if(input_search.trim().length == 0)
                {
                    if(to_other.trim().length != 0)
                    {
                        $("#mail-error").hide();

                        $('.tokens-container li.token').each(function (i) 
                        {
                            var value = $(this).attr('data-value');
                            check_mail = validateEmail(value);
                        });  
                    }
                }
            }, 500);

            input_search = $('.token-search input').val();
            if(input_search.trim().length == 0)
            {
                if(team_list.trim().length == 0 && to_other.trim().length == 0 && file.trim().length == 0)
                {
                    $("#title-error").show();
                }
                else {
                    $("#title-error").hide();
                }
            } else {
                check_mail = validateEmail(input_search);
                if(check_mail)
                {
                    $("#mail-error").show();  
                }
            }
        }
    });

    $("#subject").keyup(function() 
    {
        check_comment = $('#check_comment').val();
        if(check_comment == 'true')
        {   
            if($("#error-subject").show())
            {
                $("#error-subject").hide();
            }
            subject = $("#subject").val().trim();
            if(subject.length == 0){
                $("#error-subject").show();
            } else {
                $("#error-subject").hide();
            }
        }
    });
});

function getMailOther() 
{
    var favorite = [];
    $('.tokens-container li.token').each(function (i) {
        var text = $(this).text();
        var value = $(this).attr('data-value');
        favorite.push($(this).attr('data-value'));
        validateEmail(value);
    });
    document.getElementById('to_other').value = favorite.join(", ");
}

function val()
{
    if (trimAll(CKEDITOR.instances.content.getData().replace(/<[^>]*>/gi, '')) === '')
    {
        $(".btn-add").prop("disabled", false); 
        $(".errorContent").prop("hidden", false); 
        var error_content = document.getElementById('error-content');
        error_content.style.display = 'block';
        event.preventDefault();
    } else {
        $(".errorContent").attr("hidden","true");
        var error_content = document.getElementById('error-content');
        error_content.style.display = 'none';
    }
} 
//Remove space star end string
function trimAll(sString)
{
    while (sString.substring(0,1) == ' ')
    {
        sString = sString.substring(1, sString.length);
    }
    while (sString.substring(sString.length-1, sString.length) == ' ')
    {
        sString = sString.substring(0,sString.length-1);
    }
    return sString;
}

function validateEmail(email)
{
    var filter = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!filter.test(email)) {
        $("#mail-error").show();
        return false;
    }
}

function submitForm()
{
    var filter = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    var error_title = document.getElementById('title-error');
    var error_subject = document.getElementById('error-subject');
    var error_content = document.getElementById('error-content');
    var error_mail = document.getElementById('mail-error');

    error_mail.style.display = 'none';
    error_subject.style.display = 'none';
    document.getElementById('check_comment').value = 'true';

    var check_mail = true;
    var favorite = [];
    $('.tokens-container li.token').each(function (i) 
    {
        var text = $(this).text();
        var value = $(this).attr('data-value');
        favorite.push($(this).attr('data-value'));

        check_mail = validateEmail(value);
    });
    document.getElementById('to_other').value = favorite.join(", ");

    team_list = document.getElementById('team_list').value;
    to_other = document.getElementById('to_other').value;
    file = document.getElementById('csv_file').value;
    subject = document.getElementById('subject').value;
    content = trimAll(CKEDITOR.instances.content.getData().replace(/<[^>]*>/gi, ''));

    if(to_other.trim().length != 0)
    {
        input_search = $('.token-search input').val();
        if(input_search.trim().length != 0)
        {
            if (!filter.test(input_search)) {
                $("#mail-error").show();
                return false;
            }
        }
    }

    if(team_list.trim().length != 0)
    {
        input_search = $('.token-search input').val();
        if(input_search.trim().length != 0)
        {
            if (!filter.test(input_search)) {
                $("#mail-error").show();
                return false;
            }
        }
    }

    if(file.trim().length != 0)
    {
        input_search = $('.token-search input').val();
        if(input_search.trim().length != 0)
        {
            if (!filter.test(input_search)) {
                $("#mail-error").show();
                return false;
            }
        }
    }

    if (team_list.trim().length == 0 && to_other.trim().length == 0 && file.trim().length == 0) {
        input_search = $('.token-search input').val();
        if(input_search.trim().length != 0)
        {
            if (!filter.test(input_search)) {
                $("#mail-error").show();
            }
        }

        if (subject.trim().length == 0) 
        {
            if (content.trim().length == 0) 
            {
                if(input_search.trim().length != 0)
                {
                    if (!filter.test(input_search)) {
                        error_mail.style.display = 'block';
                    }                                   
                } else {
                    error_title.style.display = 'block';
                }
                error_subject.style.display = 'block';
                error_content.style.display = 'block';
                return false;
            } else {
                if(input_search.trim().length != 0)
                {
                    if (!filter.test(input_search)) {
                        error_mail.style.display = 'block';
                    } 
                } else {
                    error_title.style.display = 'block';
                }
                error_subject.style.display = 'block';
                error_content.style.display = 'none'; 
                return false;
            }
        } else if(content.trim().length == 0) {
            if(input_search.trim().length != 0)
            {
                if (!filter.test(input_search)) {
                    error_mail.style.display = 'block';
                } 
            } else {
                error_title.style.display = 'block';
            }
            error_subject.style.display = 'none';
            error_content.style.display = 'block'; 
            return false;
        } else {
            if(input_search.trim().length != 0)
            {
                if (!filter.test(input_search)) {
                    error_mail.style.display = 'block';
                } 
            } else {
                error_title.style.display = 'block';
            }
            error_subject.style.display = 'none';
            error_content.style.display = 'none';
            return false;
        }
    } else {
        error_title.style.display = 'none';
    }

    if (subject.trim().length == 0) 
    {
        if (content.trim().length == 0) 
        {
            error_subject.style.display = 'block';
            error_content.style.display = 'block';
            return false;
        } else {
            error_subject.style.display = 'block';
            error_content.style.display = 'none';
            return false;
        }
    } else {
        error_subject.style.display = 'none';
    }

    if (content.trim().length == 0) 
    {
        error_content.style.display = 'block';
        return false;
    } else {
        error_content.style.display = 'none';
    }

    // val();

    return check_mail;
}

function hideErrorTitle()
{
    var error_title = document.getElementById('title-error');
    var error_mail = document.getElementById('mail-error');
    var error_select_team = document.getElementById('error-select-team');
    error_title.style.display = 'none';
    error_mail.style.display = 'none';
    error_select_team.style.display = 'none';
}

