$("#link-make").click(function() {
    $("#link-make").selectText();
});

/**
 * Set row height question
 */

$(window).resize(function() {
    fixHeight();
});

$(document).ready(function() {
    fixHeight();
    hoverHelp();
});