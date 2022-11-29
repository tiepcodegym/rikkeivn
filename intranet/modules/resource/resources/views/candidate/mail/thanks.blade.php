<?php
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\Model\Team;

?>

<p>Dear anh/chị {{ $data['candidateName'] }},</p>
<p>Rikkeisoft chân thành cảm ơn anh/chị đã dành thời gian đến tham gia phỏng vấn tại công ty.</p>
<p>Kết quả pass phỏng vấn và Thư mời làm việc sẽ được Bộ phận Nhân sự thông báo tới anh/chị qua email và điện thoại.</p>
<p>Trong trường hợp không nhận được thông báo trong 01 tuần kể từ ngày phỏng vấn thì rất tiếc ở thời điểm hiện tại anh/chị chưa thực sự phù hợp với vị trí mà Rikkei đang cần tuyển. Chúng tôi xin được lưu thông tin và liên hệ lại với anh/chị khi có cơ hội phù hợp.</p>
<p>Hy vọng sẽ được hợp tác cùng anh/chị tại Rikkeisoft.</p>
<p>Xin chân thành cảm ơn!</p>
<div style="font-family: monospace;">
    <p style="font-size: 12px;margin:0px;"> -- </p>
    <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
    <p style="font-size: 12px;margin:0px;">Thanks & Best Regards,</p>
    <p style="font-size: 12px;margin:0px;">{{ $data['name'] }} | HR</p>
    <p style="font-size: 12px;margin:0px;">Rikkeisoft Co,. Ltd.</p>
    <p style="font-size: 12px;margin:0px;">Mobile: {{ $data['phone'] }}</p>
    <p style="font-size: 12px;margin:0px;">Skype: {{ $data['skype'] }}</p>
    <p style="font-size: 12px;margin:0px;">Email: {{ $data['email'] }}</p>
    <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
    <p style="font-size: 12px;margin:0px;">Head Office: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi</p>
    <p style="font-size: 12px;margin:0px;">Tel: (+84) 243 623 1685</p>
    <p style="font-size: 12px;margin:0px;">Page: https://www.facebook.com/rikkeisoft?fref=ts</p>
    <p style="font-size: 12px;margin:0px;">Website: http://rikkeisoft.com/</p>
</div>
