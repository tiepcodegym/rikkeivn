$(function() {
    var pgurl = window.location.href.substr(window.location.href);
    var url = $(".menu-ticket #set_menu").val();
    
    if(pgurl == url)
    {
        $(".menu-ticket #menu_all").addClass("active");
    }

    $(".menu-ticket li a").each(function(){
        if($(this).attr("href") == pgurl || $(this).attr("href") == '' )
            $(this).addClass("active");
    });
});
