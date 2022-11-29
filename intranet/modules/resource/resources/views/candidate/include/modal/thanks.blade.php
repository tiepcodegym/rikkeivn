<?php

use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Resource\Model\CandidateMail;
use Rikkei\Team\Model\Employee;

$email = isset($data->recruiter) ? $data->recruiter : null;
$empContact = EmployeeContact::getTableName();
$isSendMailThanks = CandidateMail::getLastSend($data->email, Candidate::MAIL_THANKS);
if ($email) {
    $hr = Employee::where('email', $email)
        ->join($empContact, 'employees.id', '=', $empContact . '.employee_id')
        ->select($empContact . '.mobile_phone', $empContact . '.skype', 'employees.name')
        ->first();
}

?>

<p>{{ trans('resource::view.Dear :name,', ['name' => $data->fullname]) }}</p>
<p>Bộ Phận Tuyển dụng - Công Ty cổ phần Rikkeisoft chân thành cảm ơn Anh/Chị đã dành thời gian đến tham gia phỏng vấn tại
    Công ty.</p>
<p>Kết quả trúng tuyển sẽ được Chúng tôi thông báo tới Anh/Chị qua email hoặc điện thoại trong khoảng từ 07-10 ngày làm
    việc kể từ ngày phỏng vấn.</p>
<p>Nếu Anh/Chị có bất cứ nhận xét hay đóng góp ý kiến sau buổi phỏng vấn vui lòng cho Chúng tôi biết bằng cách thực hiện
    khảo sát <a
            href="https://docs.google.com/forms/d/e/1FAIpQLSeJaM5ACV66pQzHoZUJg1I8RQu7AfTDrpCtDrJWjOz-9ICOeA/viewform"
            target="_blank">Tại đây</a></p>
<p>Hy vọng sẽ được làm việc cùng Anh/Chị tại Rikkeisoft.</p>
<p>Trân trọng cảm ơn!</p>
<div style="font-family: monospace;">
    <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
    <p style="font-size: 12px;margin:0px;">Thanks & Best Regards,</p>
    <p style="font-size: 12px;margin:0px;">{{ isset($hr->name) ? $hr->name : null }} | HR</p>
    <p style="font-size: 12px;margin:0px;">Rikkeisoft Co,. Ltd.</p>
    <p style="font-size: 12px;margin:0px;">Mobile: {{ isset($hr->mobile_phone) ? $hr->mobile_phone : null}}</p>
    <p style="font-size: 12px;margin:0px;">Skype: {{ isset($hr->skype) ? $hr->skype : null }}</p>
    <p style="font-size: 12px;margin:0px;">Email: {{ $email }}</p>
    <p style="font-size: 12px;margin:0px;">--------------------------------------------</p>
    <p style="font-size: 12px;margin:0px;">Head Office: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi</p>
    <p style="font-size: 12px;margin:0px;">Tel: (+84) 243 623 1685</p>
    <p style="font-size: 12px;margin:0px;">Page: https://www.facebook.com/rikkeisoft?fref=ts</p>
    <p style="font-size: 12px;margin:0px;">Website: http://rikkeisoft.com/</p>
</div>
