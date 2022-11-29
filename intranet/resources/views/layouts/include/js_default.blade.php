<?php
use Rikkei\Core\View\CoreUrl;
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-141664412-2"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-141664412-2');
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fastclick/1.0.6/fastclick.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/js/app.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mouse0270-bootstrap-notify/3.1.7/bootstrap-notify.min.js"></script>
<?php /*
<!-- jQuery 2.2.0 -->
<script src="{{ URL::asset('adminlte/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<!-- jQuery UI -->
<script src="{{ URL::asset('adminlte/plugins/jQueryUI/jquery-ui.min.js') }}"></script>
<!-- Bootstrap 3.3.6 -->
<script src="{{ URL::asset('adminlte/bootstrap/js/bootstrap.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ URL::asset('adminlte/plugins/fastclick/fastclick.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ URL::asset('adminlte/dist/js/app.min.js') }}"></script>
<!-- Slimscroll -->
<script src="{{ URL::asset('adminlte/plugins/slimScroll/jquery.slimscroll.min.js') }}"></script>
*/ ?>
@include('notify::template.notify-script')
@include('core::include.account-script')
<script src="{{ CoreUrl::asset('common/js/script.js') }}"></script>
<script src="{{ CoreUrl::asset('common/js/external.js') }}"></script>
<script>
    var textConfirm = '{{ trans('core::view.Confirm') }}';
    var confirmYes = '{{ trans('core::view.Yes') }}';
    var confirmNo = '{{ trans('core::view.Cancel') }}';
    var textCancel = '{{ trans('core::view.Cancel') }}';
    var requiredText = '{{trans("project::view.This field is required.")}}';
    var textGreater = '{{trans('project::view.Please enter a value greater than or equal Created date')}}';
    var requiredCmt = '{{ trans('resource::message.Kindly add comments') }}';
    $(document).on('click', '.item-language', function () {
        var lang = $(this).attr('data-lang');
        $.ajax({
            url: '{{ route('core::switchLang') }}',
            data: {
                lang: lang,
                _token: '{{ csrf_token() }}',
            },
            method: 'post',
            success: function () {
                location.reload();
            }
        });
    });
</script>
