<!DOCTYPE html>
<html>
<head>
    <title>rikkei</title>
</head>

<body>
<div style="display: flex; margin-top: 30px">
    <div style="width: 15%;background-color: #ba2328"></div>
    <div style="width: 70%; text-align:center;"><p
                style="text-transform: uppercase; font-size: 50px;margin: 0px; color: #ba2328; font-weight: bold; padding-top: 10px;">
            người rikkei - đọc tin rikkei</p></div>
    <div style="width: 15%"><img src="{!! asset('common/images/logo-cv.png') !!}"
                                 style="vertical-align: middle; display: block;"></div>

</div>
<div style="text-align: center;"><p style="font-size: 30px; margin: 0px;">Đừng bỏ lỡ chuỗi tin tức HOT nhất Rikkei tuần
        {{ $week }}</p></div>
<div style="display: flex; margin-top: 30px">
    <div style="width: 30%">
        @if (isset($dataPost['more']) && count($dataPost['more']))
            @foreach ($dataPost['more'] as $post)
                <div>
                    <div style="display: flex; border-left: 7px solid #ba2328">
                        <div style="width: 50%; height: auto">
                            <img src="{{ $post->getImage(true) }}" alt="rikkeisoft"
                                 style="width: 90%; height: auto;padding-left: 10px; display: block;">
                        </div>
                        <div style="width: 40%;font-weight: bold;display: flex; font-size: 18px">
                            <a href="{{ $post->getUrl() }}"
                               style="text-decoration: none; color: black">{{ $post->title }}</a>
                        </div>
                    </div>
                    <div style="display: flex; margin: 10px 0 30px 0; width: 90%">
                        <div style="width: 70%;height: 5px;  background-color: #b0b0b0"></div>
                        <div style="width: 30%;height: 5px; background-color: #ba2328"></div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div style="width: 40%;">
        @if (isset($dataPost['feature']) && count($dataPost['feature']))
            @foreach ($dataPost['feature'] as $key=>$post)
                @if($key == 0)
                    <div style="padding-left: 0px; border-bottom: 10px solid #ba2328">
                        <img src="{{ $post->getImage(true) }}"
                             style="width: 100%; height:480px;display: block; margin-bottom: 20px">
                    </div>
                    <p style="font-weight: bold;font-size: 25px; margin: 0px; padding-top: 10px"><a
                                href="{{ $post->getUrl() }}"
                                style="text-decoration: none; color: black">{{ $post->title }}</a></p>
                @else
                    <div style="margin-top: 30px; margin-left: 30px;">
                        <div style="display: flex;  border-left: 7px solid #ba2328">
                            <div style="width: 40%; height: 150px"><img src="{{ $post->getImage(true) }}"
                                                                        style="width: 90%; height: auto;padding-left: 10px;display: block;">
                            </div>
                            <div style="width: 50%;font-weight: bold;display: flex; font-size: 20px">
                                <a href="{{ $post->getUrl() }}"
                                   style="text-decoration: none; color: black">{{ $post->title }}</a>
                            </div>
                        </div>
                        <div style="display: flex; margin-top: 10px; width: 90%">
                            <div style="width: 70%;height: 5px;  background-color: #b0b0b0"></div>
                            <div style="width: 30%;height: 5px; background-color: #ba2328"></div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
    <div style="width: 30%; padding-left: 30px">
        @if (isset($dataPost['week']) && count($dataPost['week']))
            @foreach ($dataPost['week'] as $post)
                <div>
                    <div style="margin-top:30px;">
                        <div>
                            <div style="display: flex;  border-left: 10px solid #ba2328">
                                <div style="width: 100%; height: 250px"><img src="{{ $post->getImage(true) }}"
                                                                             style="width: 90%; height: 100%;padding-left: 10px;display: block;">
                                </div>
                            </div>
                            <div style="padding-left: 30px; padding-right: 30px; font-weight: bold;font-size: 20px">
                                <a href="{{ $post->getUrl() }}"
                                   style="text-decoration: none; color: black">{{ $post->title }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
        <div>
            <div style="margin-top: 15px">
                <a href="https://www.facebook.com/rikkeisoft"><img src="{!! asset('common/images/fb.png') !!}"
                                                                   style="width: 20%; height: auto;padding-left: 10px;"></a>
                <a href="https://vn.linkedin.com/company/rikkeisoft"><img src="{!! asset('common/images/in.png') !!}"
                                                                          style="width: 20%; height: auto;padding-left: 10px;"></a>
                <a href="https://www.youtube.com/channel/UCg4sqAGemXn5basWdzxEbVg"><img
                            src="{!! asset('common/images/youtube.png') !!}"
                            style="width: 20%; height: auto;padding-left: 10px;"></a>
                <a href="mailto:pr@rikkeisoft.com"><img src="{!! asset('common/images/mail.png') !!}"
                                                        style="width: 20%; height: auto;padding-left: 10px;"></a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
