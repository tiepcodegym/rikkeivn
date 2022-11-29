<html>

<head>
    <style>
    </style>
</head>

<body>
<div class="body">
    <h3>Thân gửi: {{ $data['employeeName'] }}</h3>
    <p>Lời đầu tiên, xin cảm ơn bạn đã dành thời gian quan tâm và đăng ký tôi trở thành Mentor của bạn.</p>
    @if($data['status'] == 2)
        <p>Tôi rất sẵn lòng hỗ trợ và giúp đỡ bạn trong quá trình cố vấn.</p>
        <p>Đừng ngần ngại kết nối với tôi qua email: {{ $data['mentorEmail'] }} để làm quen và trao đổi nguyện
            vọng của bạn
            nhé!</p>
        <p> Hy vọng chúng ta sẽ có những trải nghiệm tuyệt vời trên hành trình sắp tới!</p>
    @else
        <p>Tuy nhiên, hiện tại do yêu cầu công việc nên tôi chưa thể sắp xếp thời gian cố vấn. </p>
        <p>Rất mong thời gian tới sẽ có cơ hội được làm việc và hỗ trợ bạn. </p>
        <p>Phòng L&D sẽ liên hệ trong thời gian sớm nhất để hỗ trợ bạn tìm kiếm Mentor phù hợp với nguyện vọng. </p>
        <p>Mọi thắc mắc vui lòng gửi về hòm mail: daotao@rikkeisoft.com </p>
        <p>Chúc bạn có những trải nghiệm học hỏi bổ ích và thành công! </p>
    @endif
    <p>Trân trọng,</p>
    <p>{{ $data['mentorName'] }}</p>
</div>
</body>
</html>
