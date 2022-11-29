<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig(3);
?>
@extends($layout)

@section('css')
<style>
    p {
        margin: 8px auto;
    }
</style>
@endsection

@section('content')
    <div style="font-family:arial,sans-serif">
<p>{{ $data['receiveCompanyName'] }}
  @if (!empty($data['receivePosition']))
    <br>
    {{ $data['receivePosition'] }}
  @endif  
<br>
{{ $data['receiveName'] }}<span lang="JA" style="font-family:arial,sans-serif">様</span></p>

<p>&nbsp;</p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">拝啓　時下ますます御清栄のこととお喜び申し上げます。平素は格別のご高配を賜り心より厚く御礼申し上げます。</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">　さて、弊社では2022年4月6日に、設立10周年を迎えることができました。これもひとえに皆様方のご協力とご支援の賜物と深くお礼申し上げます。</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">　つきましては、設立10周年を記念いたしまして　下記の通り式典及び祝賀会を開催いたします。ご多忙中恐れ入りますが、ご出席賜りますようご案内申し上げます。</span></tt></samp></p>

<p>&nbsp;</p>

<p dir="ltr" style="text-align: center;"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">記</span></tt></samp></p>

<p><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">&nbsp;</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">■日時：</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">【記念式典】7月17日（日）14:00〜16:30</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">【祝賀会】&nbsp;&nbsp;&nbsp;&nbsp;7月17日（日）19:00〜22:00</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※7月17日（日）午前中にハノイよりハロン湾へ移動するバスを手配しております。</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※ご参加いただける方は17日当日はホテルに宿泊を手配しております。</span></tt></samp></p>

<p><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">&nbsp;</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">■場所： FLC Grand Hotel Halong</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※翌日18日（月）にハロン湾よりハノイへ移動するバスを手配しております。</span></tt></samp></p>

<p><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">&nbsp;</span></tt></samp></p>

<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">お手数ですが、6月6日までにご出欠を以下のボタンよりお願いいたします。</span></tt></samp></p>
<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※複数名のお申し込みが可能です。</span></tt></samp></p>
<p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※上記日程のイベント以外にハロン湾近辺でゴルフやハロン湾クルーズなどオプショナルツアーを開催する予定です。</span></tt></samp></p>

<p style="font-family:arial,sans-serif">&nbsp;</p>

<p style="font-family:arial,sans-serif">&nbsp;</p>

<div style="margin: 30px 0 0 0; text-align: center"><a href="{{ $data['linkRegister'] }}" style="background: #bb2327; padding: 8px 15px 10px; color: white; font-weight: 600;" target="_blank">ご出席</a>&nbsp;<a href="{{ $data['linkRefuse'] }}" style="background: #959192; padding: 8px 15px 10px; color: white; font-weight: 600; margin-left: 15px;" target="_blank">ご欠席</a></div>
</div>

@endsection
