<?php

use Rikkei\Core\Model\EmailQueue;

$layout = EmailQueue::getLayoutConfig();
extract($data);
?>
@extends($layout)
@section('content')
  <p>Chào {{ $name }},</p>
  <p>Chúng tôi thấy bạn chưa thêm bất kỳ một chứng chỉ nào vào hệ thống. Mong bạn hãy dành vài phút để thêm nhé.</p>
  <p>Xin chân thành cảm ơn.</p>
  <p>Intranet team.</p>
@endsection
