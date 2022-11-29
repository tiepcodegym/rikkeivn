<?php
use Rikkei\Core\View\CoreUrl;
use Firebase\JWT\JWT;
use Carbon\Carbon;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Notify\View\NotifyView;
use Rikkei\Notify\Model\Notification;

$currUserId = auth()->id();
$notiConfig = config('notify');
$jwtToken = CacheHelper::get('jwt_token_' . $currUserId);
if (!$jwtToken) {
    $key = $notiConfig['private_key'];
    $expireHour = $notiConfig['token_valid_hour'];
    $expireIn = Carbon::now()->addHour($expireHour)->format('Y-m-d H:i:s');
    $payload = [
        'employee_id' => $currUserId,
        'expired_in' => $expireIn
    ];
    $jwtToken = JWT::encode($payload, $key);
    CacheHelper::put('jwt_token_' . $currUserId, $jwtToken, null, true, $expireHour * 60);
}
$protocol = $notiConfig['protocol'];
$host = $notiConfig['ws_host'];
$port = $notiConfig['port'];
?>
<script>
    var notifyConst = {
        load_notify_url: "{{ route('notify::load_data') }}",
        set_read_url: "{{ route('notify::set_read') }}",
        refresh_url: "{{ route('notify::refresh_data') }}",
        text_recently_update: '<?php echo trans('notify::view.recently_updated') ?>',
        text_minutes_ago: '<?php echo trans('notify::view.minutes_ago') ?>',
        text_hours_ago: '<?php echo trans('notify::view.hours_ago') ?>',
        text_days_ago: '<?php echo trans('notify::view.days_ago') ?>',
        max_id: '{{ RkNotify::getLastId() }}',
        refresh_minute: '{{ config("notify.refresh_minute") }}',
        env: '{{ app()->environment() }}',
        //config
        protocol: '{{ $protocol }}',
        host: '{{ $host }}',
        port: '{{ $port }}',
        employeeId: '{{ $currUserId }}',
        wsToken: '{{ $jwtToken }}',
        notiEnv: '{{ $notiConfig["noti_env"] }}',
        typePopup: '{!! NotifyView::TYPE_POPUP !!}',
    };
    var textShowMore = '';
    var textShowLess = '';
    var popupNotifications = [];
</script>
<ul class="hidden" id="notify_template">
    @include('notify::template.notify-item')
</ul>
<script src="{{ CoreUrl::asset('asset_notify/js/notify.js') }}"></script>
