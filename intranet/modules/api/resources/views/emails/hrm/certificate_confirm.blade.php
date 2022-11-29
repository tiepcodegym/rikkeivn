<html>

<head>
</head>

<body>
<div class="body">
    <p>Thân gửi Leader,</p>
    <p>Anh/Chị vừa nhận được đề nghị phê duyệt chứng chỉ từ {{ $data['employee_name'] }}.</p>
    <p>Vui lòng phê duyệt tại đây: <a href="{{ config('services.hrm_url').'/hrm/accounting-administration/certificate/'.$data['id'] }}">Duyệt chứng chỉ</a></p>
    <p>Trân trọng!</p>
</div>
</body>
</html>
