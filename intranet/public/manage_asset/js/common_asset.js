function checkValidFile(){$("#form-import-supplier").validate({rules:{file_upload:{required:!0,validFileImport:!0}},messages:{file_upload:{required:requiredText,validFileImport:invalidExFile}}}),jQuery.validator.addMethod("validFileImport",function(e,r){var i=e.replace(/^.*\./,"");return jQuery.inArray(i,["xls","xlsx","csv"])!==-1},"File extension not invalid")}function readMore(){$(".read-more").shorten({showChars:200,moreText:"See more",lessText:"Less"})}