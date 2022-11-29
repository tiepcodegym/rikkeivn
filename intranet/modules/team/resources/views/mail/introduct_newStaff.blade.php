<?php
use Rikkei\Team\Model\Team;
use Carbon\Carbon;

extract($data);
?>

<style type="text/css">
   .container {
      width: 700px;
      margin: 0 auto;
   }

   .logo {
      padding-left: 330px;
      padding-bottom: 70px;
      padding-top: 30px;
      padding-bottom: 30px;
   }

   .background_1 {
      margin-top: -88px;
   }

   .title {
      background-repeat: no-repeat; 
      background-position: top right;
      min-height: 102px;
   }

   .title h1 {
      padding-left: 65px;
      color: #9b9696;
      font: 30px arial, sans-serif;
      font-weight: bold;
   }

   .title h1 span {
      padding-left: 25px;
   }

   .wrapper {
      margin-top: -15px;
   }

   .content {
      border: 2px solid #ed2d2d;
      margin-left: 45px;
      margin-bottom: 15px;
      width: 600px;
      height: 220px;
      border-radius: 15px;
   }
   
   .avatar {
      width: 180px;

   }

   .avatar img {
      width: 140px;
      height: 140px;
      border-radius: 50%;
      padding-left: 22px;
      padding-right: 20px;
   }

   .introduction_staff {
      width: 900px;
      height: 100px;
   }

   .name_staff {
      font: 28px arial, sans-serif;
      font-weight: bold;
      color: #bd1e2c;
      text-align: left;
      padding-top: 15px;
   }

   .introduction {
      font-size: 13px;
   }

   .quote {
      font-size: 13px;
      margin-right: 10px;
   }

   .blink {
          margin-right: 8px;
   }

   .division {
      color: #bd1e2c;
   }
</style>
<table class="container">
   <tr class="header">
      <td class="logo">
         <img src="{{ URL::asset('team/images/logo.png') }}" alt="error image">
      </td>
   </tr>
   <tr>
      <td class="title" style="background-image: url({{ URL::asset('team/images/img_head.png') }});
      ">
         <h1><span>Thông Báo</span><br> Nhân Viên Mới</h1>
      </td>
   </tr>
   <tr style="background-image: url({{ URL::asset('team/images/shadow.png') }}); height: 13px;">
         <td></td>
      </tr>
   <tr>
      <td>
         <p style="padding: 28px 75px; font-size: 18px;">Chào đón các thành viên mới<br> Gia Nhập <strong class="division">{{$teamName}}</strong> chúng ta!</p>
      </td>
   </tr>
   <tr class="wrapper" background ="{{ URL::asset('team/images/background.png') }}">
      <td>
         @foreach($newStaffs as $new)
         <table class="content" style="background-color: #ffffff;">
            <tr>
               @if (isset($new['avatar_url']))
                  <td class="avatar">
                     <img src="{{ $new['avatar_url'] }}">
                  </td>
               @else
                  <td class="avatar">
                     <img src="{{ URL::asset('team/images/logo.png') }}">
                  </td>
               @endif
               <td></td>
               <td class="introduction_staff">
                 <table style="margin-bottom: 20px;">
                    <tr>
                       <th class="name_staff">{{ $new['name'] }}</th>
                    </tr>
                    <tr>
                       <td><span class="introduction">Ngày sinh: {{ Carbon::parse($new['birthday'])->format('d/m/Y') }}</span></td>
                    </tr>
                    <tr>
                       <td><span class="introduction">Quê quán: {{ $new['native_addr'] }}</span></td>
                    </tr>
                    <tr>
                       <td><span class="introduction">Mobile: {{ $new['mobile_phone'] }}</span></td>
                    </tr>
                    <tr>
                       <td><span class="introduction">Email: {{ $new['email'] }}</span></td>
                    </tr>
                    <tr>
                       <td><span class="introduction">Skype: {{ $new['skype'] }}</span></td>
                    </tr>
                 </table> 
               </td>
               <td></td>
            </tr>
            <tr>
               <td></td>
               <td><span class="blink"><img src="{{ URL::asset('team/images/blink_1.png') }}" style="padding-bottom: 20px;"></td>
               <td>
                  </span><span class="quote">Khi bạn muốn tìm một người có thể thay đổi cuộc đời bạn . Hãy nhìn vào gương!</span><span class="blink"><img src="{{ URL::asset('team/images/blink_2.png') }}"></span>
               </td>
           </tr>   
           <tr style="height: 10px;"></tr>
         </table>
         @endforeach
      </td>
   </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="700" id="template_footer" style="background: #bd1e2c; color: white; margin: 0px auto;">
    <tr>
        <td width="50">&nbsp;</td>
        <td>
            <table border="0" cellpadding="0" cellspacing="0" width="600">
                <tr width="540" height="10">
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td width="380" style="text-align: left;">
                        <h1 style="font-size: 15px; color: white;">RIKKEISOFT Co., Ltd.</h1>
                        <div style="font-size: 9px;margin-top: 14px;margin-bottom: 22px; color: white;">
                            <p>Head Office: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi.</p>
                            <p>Da Nang Office: 11th Floor, VietNam News Agency Building, 81 Quang Trung St., Hai Chau Dist., Da Nang.</p>
                            <p>Ho Chi Minh Office: 7th Floor Maritime Safety South Building, 42 Tu Cuong St., Ward 4, Tan Binh Dist., Ho Chi Minh City, Vietnam</p>
                            <p>Japan Office: 3F, Tamachi 16th Fujishima Building, 4-13-4 Shiba, Minato-ku, Tokyo, Japan.</p>
                        </div>
                        <address style="font-size: 9px; color: white;">Copyright &copy; 2016 RikkeiSoft. All rights reserved.</address>
                    </td>

                    <td width="160" style="text-align: right;">
                        <div>
                            <table>
                                <tr>
                                    <td width="40">&nbsp;</td>
                                    <td>
                                        <a href="http://rikkeisoft.com/" style="text-decoration: none;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/web.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                    <td width="10">&nbsp;</td>
                                    <td>
                                        <a href="https://www.facebook.com/rikkeisoft/" style="text-decoration: none;display: inline-block;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/face.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                    <td width="10">&nbsp;</td>
                                    <td>
                                        <a href="https://www.youtube.com/channel/UCg4sqAGemXn5basWdzxEbVg" style="text-decoration: none;display: inline-block;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/youtube.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div style="font-size: 9px;color: white;">
                            <p>
                                <a href="mailto:contact@rikkeisoft.com" style="color: white;text-decoration: none;">contact@rikkeisoft.com</a>
                            </p>
                            <p style="color: white;">
                                Phone: (04) 3-623-1685
                            </p>
                            <p style="color: white;">
                                Fax: (04) 3-623-1686
                            </p>
                            <span style="background-color: #bd1e2c; color: #ffff">{{ date('Y-m-d H:i:s') }}</span>
                        </div>
                    </td>
                </tr>
                <tr width="540" height="15">
                    <td>&nbsp;</td>
                </tr>
            </table>
        </td>
        <td width="30">&nbsp;</td>
    </tr>
</table>
