<script>
    (function ($) {
        var refreshAccUrl = '{{ route("core::refresh.account") }}';
        //request twice per day update avatar
        setInterval(function () {
            $.ajax({
                type: 'GET',
                url: refreshAccUrl,
                success: function (data) {
                    $('[data-dom-flag="profile-avatar"] img').attr('src', data.avatar);
                },
                error: function (error) {
                    console.log(error.responseJSON);
                },
            });
        }, 12 * 60 * 60 * 1000);
    })(jQuery);
</script>
