(function ($) {
    
    //pause/stop player
    $('body').on('click', '.play-icon', function (e) {
        e.preventDefault();
        var _this = $(this);
        
        var audioBox = _this.closest('.audio-player');
        if (audioBox.hasClass('audio-error')) {
            return false;
        }
        var audio = audioBox.prev('video')[0];
        var playing;
        var currBar = audioBox.find('.playing-bar .curr-time');
        var fullBarWidth = audioBox.find('.playing-bar .full-time').width();
        var handlePoint = audioBox.find('.playing-bar .handle-time');
        var currTime = audioBox.find('.duration-bar .curr-dur');
        if (!audio.played || audio.paused || audio.ended) {
            audio.play();
            playing = setInterval(function () {
                if (audio.ended) {
                    clearInterval(playing);
                    _this.addClass('pause');
                    currBar.css('width', fullBarWidth);
                    handlePoint.css('left', fullBarWidth);
                    currTime.text(convertTime(audio.duration));
                } else {
                    var percent = audio.currentTime / audio.duration;
                    currBar.width(percent * fullBarWidth);
                    handlePoint.css('left', percent * fullBarWidth);
                    currTime.text(convertTime(audio.currentTime));
                }
            }, 100);
            _this.removeClass('pause');
        } else {
            audio.pause();
            clearInterval(playing);
            _this.addClass('pause');
        }
    });
    
    //seed player
    $('body').on('click', '.playing-bar .full-time', function (e) {
        e.preventDefault();
        var _this = $(this);
        var audioBox = _this.closest('.audio-player');
        if (audioBox.hasClass('audio-error')) {
            return false;
        }
        var currWidth = e.pageX - _this.offset().left;
        var currBar = _this.find('.curr-time');
        var handlePoint = _this.find('.handle-time');
        var currTime = _this.closest('.audio-player').find('.curr-dur');
        var audio = _this.closest('.audio-player').prev('video')[0];
        audio.currentTime = currWidth / _this.width() * audio.duration;
        currBar.width(currWidth);
        handlePoint.css('left', currWidth);
        currTime.text(convertTime(audio.currentTime));
    });
    
    //mute/unmute volume
    $('body').on('click', '.volume-icon', function (e) {
        e.preventDefault();
        var _this = $(this);
        var audioBox = _this.closest('.audio-player');
        if (audioBox.hasClass('audio-error')) {
            return false;
        }
        
        var audio = audioBox.prev('video')[0];
        if (audio.muted) {
            audio.muted = false;
            _this.removeClass('mute');
        } else {
            audio.muted = true;
            _this.addClass('mute');
        }
    });
    
    $(document).ready(function () {
       initAudioPlayer();
    });
    
})(jQuery);

//init player
function initAudioPlayer(){
    $('video.test-audio').each(function () {
        if ($(this).next('.audio-player').length > 0) {
            return;
        }
         var audioTpl = $('#audio_template').clone().removeAttr('id').removeClass('hidden');
         $(this).after(audioTpl);
         var handle = audioTpl.find('.playing-bar .handle-time');
         var fullBar = audioTpl.find('.playing-bar .full-time');
         var currBar = audioTpl.find('.playing-bar .curr-time');
         var currTime = audioTpl.find('.duration-bar .curr-dur');
         var audio = this;
        $(audio).on('error', function (e) {
            audioTpl.after('<p class="error">Audio not found</p>');
            audioTpl.addClass('audio-error');
        });
         setTimeout(function () {
            if (!audioTpl.hasClass('audio-error')) {
                audioTpl.find('.duration-bar .full-dur').text(convertTime(audio.duration));
            }
         }, 300);
         handle.draggable({
             axis: 'x',
             containment: fullBar,
             drag: function () {
                 if (audioTpl.hasClass('audio-error')) {
                     return false;
                 }
                 var posLeft = $(this).position().left;
                 var percent = posLeft / fullBar.width();
                 currBar.width(posLeft);
                 audio.currentTime = percent * audio.duration;
                 currTime.text(convertTime(audio.currentTime));
             },
         });
    });
}

//convert time mm:ii
function convertTime(time) {
    if (!$.isNumeric(time)) {
        return '00:00';
    }
    time = Math.round(time);
    var second = time % 60;
    var minute = 0;
    if (second > 59) {
        minute = Math.floor(second / 60);
        second = second % 60;
    }
    return twoDigits(minute) + ':' + twoDigits(second);
}

//convert two digits number
function twoDigits(number) {
    return number < 10 ? '0' + number : number;
}

