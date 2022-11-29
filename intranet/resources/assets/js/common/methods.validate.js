(function($) {
$(document).ready(function() {
    $.validator.addMethod("greaterThan",
        function (value, element, params) {
            if (!value || !$(params).val()) {
                return true;
            }
            if (!/Invalid|NaN/.test(new Date(value))) {
                return new Date(value) > new Date($(params).val());
            }

            return isNaN(value) && isNaN($(params).val())
                    || (Number(value) > Number($(params).val()));
        }, 'Must be greater than {0}.'
    );
    $.validator.addMethod("greaterEqualThan",
        function (value, element, params) {
            if (!Array.isArray(params)) {
                params = [params];
            }
            var pass = true;
            $.each(params, function (i, param) {
                if (!value || !$(param).val()) {
                    return true;
                }
                if (!/Invalid|NaN/.test(new Date(value))) {
                    pass = new Date(value) >= new Date($(param).val());
                    if (!pass) {
                        return false;
                    }
                    return true;
                }

                pass = isNaN(value) && isNaN($(param).val())
                        || (Number(value) >= Number($(param).val()));
                if (!pass) {
                    return false;
                }
            });
            return pass;
        }, 'Must be greater or equal than {0}.'
    );
    $.validator.addMethod("ifNumberThenPositive",
        function (value, element, params) {
            if (!value || isNaN(value)) {
                return true;
            }
            return parseFloat(value) > 0;
        }, 'Must be greater 0.'
    );
    $.validator.addMethod("valueNotEquals", function(value, element, arg){
        if (value == null) {
            return false;
        }
        return arg != value;
    }, "Value must not equal arg.");

    $.validator.addMethod("requiredTrim", function(value){
        return value.trim() !== '';
    }, "Field is required");

    $.validator.addMethod("validEmail", function(value, element, arg){
        var regex = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return regex.test(value);
    }, "Invalid email.");
    
    $.validator.addMethod("validatePhone", function(value, element) {
      return validatePhone(value.trim());
    }, "phoneFormat");

    /**
     * number format
     */
    $.validator.addMethod("numberFormat", function(value){
        return !isNaN(value.replace(/\,/gi, ''));
    }, "Please enter a valid number.");
    var vMinFormatArg = 0;
    $.validator.addMethod("minFormat", function(value, element, arg){
        vMinFormatArg = arg;
        return value.replace(/\,/gi, '') >= arg;
    }, "Please enter a value greater than or equal to " + vMinFormatArg + ".");
});
})(jQuery);
function validatePhone(phone) {
  if (phone) {
    var regex = /^[0-9_-\s]+$/;
    return regex.test(phone);
  }
  return true;
}