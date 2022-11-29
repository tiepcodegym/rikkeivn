$(document).ready(function() { 
    $('.date').datepicker({
       todayBtn: "linked",
       language: "it",
       autoclose: true,
       todayHighlight: true,
       format: 'yyyy-mm-dd' 
    });
    $('#birthday').datepicker('setEndDate', '-1d');
});