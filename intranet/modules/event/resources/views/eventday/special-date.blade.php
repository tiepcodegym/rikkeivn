<?php
extract($data);
?>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .bg {
            background-image: url('https://rikkei.vn/storage/ckfinder/images/image_2020_11_03T03_36_09_184Z.png');
            width: 412px;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 741px;
            margin: auto;
        }

        .content {
            font-size: 13px;
            padding: 55% 15% 0 15%;
            line-height: 1.5;
            text-align: justify;
        }
    </style>
</head>
<body>
<div class="bg">
    <div class="content">
        {!! $content !!}
    </div>
</div>
</body>
</html>
