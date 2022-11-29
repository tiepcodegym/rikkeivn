$('#proposedFeedback').on('change', function (e) {
    var label = $('#proposedAnswerContentRequiredLabel');
    var answerContent = $('#proposedAnswerContent');
    if (e.target.value == 2) {
        label.show();
        answerContent.attr('required', 'required')
    } else {
        label.hide();
        answerContent.removeAttr('required');
    }
});