@extends('layouts.guest')
<?php
use Rikkei\Sales\Model\Css;
use Illuminate\Support\Facades\Config as SupportConfig;

$lang = SupportConfig::get('langs.'.$langCode);
if ($lang == null) {
    $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
}
?>
@section('title')
    {{trans('sales::view.Customer service survey',[],'',$lang)}}
@endsection

@section('content')
<div class="success-body">
    <h1 class="success-title">
        <img src="{{ URL::asset('common/images/logo-rikkei.png') }}" />
    </h1><!-- /.login-logo -->
    <div class="success-action">
    @if($lang == "ja")
        <p>
            ご協力ありがとうございました。  
        </p>
        <p>
            頂いたご意見・ご要望は今後のプロジェクトの改善等に反映させていただきます。
        </p>
        <p>
            今後とも何卒よろしくお願いいたします。 
        </p>
    @elseif ($lang == 'vi')
    <div class="font-viet">
        <p>Cảm ơn bạn đã dành thời gian để tham gia vào cuộc khảo sát của chúng tôi.</p>
        <p>Chúng tôi thực sự coi trọng thông tin bạn đã cung cấp. Phản hồi của bạn rất quan trọng trong việc giúp chúng tôi cải thiện hiệu suất trong các dự án tiếp theo.</p>
        <p>Chúng tôi chân thành mong muốn hợp tác lâu dài với bạn và chúc bạn đạt được thành công hơn nữa trong tương lai.</p>
    </div>
    @else
        <p>
            Thank you for taking time out to participate in our survey.   
        </p>
        <p>
            We truly value the information you have provided.Your responses are vital in helping us to improve our performance in the next projects.
        </p>
        <p>
            We sincerely look forward to establishing a long-term relationship with you and wish you achievement of further success in the future. 
        </p>
    @endif
    </div><!-- /.login-box-action -->
</div><!-- /.login-wrapper -->
@endsection

<!-- Styles -->
@section('css')
<link href="{{ asset('sales/css/css_customer.css') }}" rel="stylesheet" type="text/css" >
@endsection

@section('script')
<script src="{{ URL::asset('lib/js/jquery.backstretch.min.js') }}"></script>
<script src="{{ asset('sales/js/css/customer.js') }}"></script>
<script src="{{ asset('sales/js/css/success.js') }}"></script>
<script>
    jQuery(document).ready(function($) {
        $.backstretch('{{ URL::asset('common/images/login-background.png') }}');
    });
</script>
@endsection