<?php
use Rikkei\Sales\Model\CssMail;
use Carbon\Carbon;

?>
@if($data['lang'] == "ja")
    @if (isset($data['project_type_id']) && $data['project_type_id'] == 5)
        <p>{{$data['customerName']}} 様</p>

        <p style="margin-top: 25px;">いつもお世話になっております。</p>
        <p>Rikkeisoft 品質管理部でございます。</p>

        <!-- resend mail -->
        @if (isset($data['resend']))
        <p style="margin-top: 25px;">前回、弊社よりお送りさせて頂いた以下のURLですが、</p>
        <p>ご確認いただけましたでしょうか？</p>
        <!-- /resend mail -->

        <!-- send mail first -->
        @else 
        <p style="margin-top: 25px;">開発ラボ案件におきましては、ご協力の機会をいただき、誠にありがとうございます。</p>

        <p style="margin-top: 25px;">さて、弊社ではよりよい品質及び生産性を目指すため、<br/>
            定期的にアンケートを実施しております。<br />
             頂きましたご意見は、今後のプロジェクトに活用させていただきます。
        </p>
        <p style="margin-top: 25px;"> お手数をお掛けしますが、以下の添付ファイルよりアンケートを開いていただき、<br />
            ご回答いただければ幸いです。</p>
        @endif

        <p><a style="word-break: break-all;" href="{{$data['hrefMake']}}">{{$data['hrefMake']}}</a></p>
        <p>お忙しいところ恐縮ですが、ご協力いただけますよう何卒お願い申し上げます。<br />
            それでは、今後とも引き続きよろしくお願いいたします。</p>
    @else
        @if (isset($data['isNotShowCompany']) && $data['isNotShowCompany'] == 0)
            <p>{{$data['company_name']}}</p>
        @endif
        <p>{{$data['customerName']}} 様</p>

        <p style="margin-top: 25px;">平素より大変お世話になっております。</p>
        <p>私は{{$data['project_name']}}のプロジェクト品質保証責任者の{{$data['employee']}}と申します。</p>

        <!-- resend mail -->
        @if (isset($data['resend']))
            <p style="margin-top: 25px;">前回、弊社よりお送りさせて頂いた以下のURLですが、</p>
            <p>ご確認いただけましたでしょうか？</p>
            <!-- /resend mail -->

            <!-- send mail first -->
        @else
            <p style="margin-top: 25px;">弊社ではお客様のニーズに対応するため、継続的な取り組みの一環として、<br/>
                弊社が対応した製品およびサービスの品質について、評価をお聞かせいただきたくご連絡差し上げました。<br/>
            </p>

            <p style="margin-top: 25px;">アンケートでは、案件における幅広い観点より</p>
            <p>お客様の忌憚ないご評価をいただければと思っております。</p>
            <p>いただいたご意見は、今後の品質改善に活用させていただきます。</p>
            <p>お手数おかけしますが、以下のURLよりアンケートページを開いていただき、</p>
            <p>ご回答いただけますようお願いいたします。</p>
        @endif

        <p><a style="word-break: break-all;" href="{{$data['hrefMake']}}">{{$data['hrefMake']}}</a></p>
        <p>なお、お忙しいところ恐縮ですが、</p>
        <p>本アンケートは{{$data['month']}}月{{$data['date']}}日までにご返答いただけますと幸いです。</p>
        <p>何卒ご協力いただけますようお願い申し上げます。</p>
        <p>それでは、今後とも引き続きよろしくお願いいたします。</p>

        <p>{{$data['project_name']}}のプロジェクト品質保証責任者</p>
        <p>{{$data['employee']}}</p>
    @endif
@endif
@if($data['lang'] == "en")
    @if (isset(CssMail::getGenderCustomer()[$data['gender']]))
        <p>Dear {{ CssMail::getGenderCustomer()[$data['gender']] }} {{$data['customerName']}}</p>
    @else
        <p>Dear Mr./Ms. {{$data['customerName']}}</p>
    @endif
        <!-- resend mail -->
    @if (isset($data['resend']))
    <p style="margin-top: 25px;">We wonder whether you have recieved our survey link or not:</p>

    <!-- send mail first -->
    @else
    <p style="margin-top: 25px;">At Rikkeisoft, we aim to further improve our services to better meet your expectations,<br>
        a small Customer Satisfaction survey will be conducted in order to enhance the quality of the project.</p>
    <p style="margin-top: 25px;">We appreciate all comments or suggestions you might have,<br>
        so would you please spend a several minutes completing the survey at the link below.</p>
    @endif

    <p><a style="word-break: break-all;" href="{{$data['hrefMake']}}">{{$data['hrefMake']}}</a></p>
    <p>Thank you very much for your cooperation. We're looking forward to working with your company in the long term.</p>
    <p>Yours faithfully,</p>
@endif
{{-- @if($data['lang'] == "vi")
    @if (isset(CssMail::getGenderCustomer()[$data['gender']]))
        <p>Kính gửi {{ CssMail::getGenderCustomer($data['lang'])[$data['gender']] }} {{$data['customerName']}},</p>
    @else
        <p>Kính gửi Ông/Bà {{$data['customerName']}},</p>
    @endif
        <!-- resend mail -->
    @if (isset($data['resend']))
    <p style="margin-top: 25px;">Chúng tôi băn khoăn không biết Quý vị đã nhận được phiếu khảo sát của chúng tôi hay chưa<br />
        Nếu Quý vị vẫn chưa nhận được, vui lòng truy cập liên kết sau để giúp chúng tôi hoàn thành phiếu khảo sát:</p>
    <!-- /resend mail -->

    <!-- send mail first -->
    @else 
    <p style="margin-top: 25px;">Rikkeisoft xin gửi lời cảm ơn chân thành cơ hội được hợp tác và làm việc cùng Quý Công ty trong suốt thời gian qua.</p>
    <p style="margin-top: 25px;">Ở Rikkeisoft, Chúng tôi luôn hướng tới việc cải thiện các dịch vụ ngày càng tốt hơn, đáp ứng kì vọng của khách hàng. </p>

    <p style="margin-top: 25px;">Chúng tôi luôn muốn được lắng nghe và thực sự trân trọng những ý kiến đóng góp, phản hồi quý giá từ phía khách hàng!
    <br>Vì vậy, mong Quý vị vui lòng dành một vài phút để hoàn thành phiếu khảo sát của chúng tôi tại liên kết dưới đây:</p>
    @endif

    <p><a style="word-break: break-all;" href="{{$data['hrefMake']}}">{{$data['hrefMake']}}</a></p>
    @if (isset($data['resend']))
    <p>Xin cảm ơn sự hợp tác của Quý vị! Rikkei rất mong tiếp tục nhận được sự ủng hộ của Quý vị và Quý công ty trong tương lai.</p>
    @else
    <p>Rikkeisoft xin chân thành cảm ơn sự hợp tác và giúp đỡ của Quý vị và Quý Công ty.
        <br>Chúng tôi mong muốn được tiếp tục đồng hành với Quý Công ty trong tương lai. </p>
    @endif
    <p>Trân trọng,</p>
@endif --}}
@if($data['lang'] == "vi")
    <p>Kính gửi Quý khách hàng,</p>
    
    <p style="margin-top: 25px;">Tên tôi là {{ $data['employee'] }} là nhân viên đảm bảo chất lượng của dự án {{ $data['project_name'] }}
        <br>Ở Rikkeisoft, Chúng tôi luôn hướng tới việc cải thiện các dịch vụ ngày càng tốt hơn, đáp ứng kì vọng của khách hàng.</p>

    <p style="margin-top: 25px;">Chúng tôi luôn muốn được lắng nghe và thực sự trân trọng những ý kiến đóng góp, phản hồi quý giá từ phía khách hàng!
        <br>Vì vậy, mong Quý vị vui lòng dành một vài phút để hoàn thành phiếu khảo sát của chúng tôi trong file đính kèm <a style="word-break: break-all;" href="{{$data['hrefMake']}}">{{$data['hrefMake']}}</a></p>

    <p style="margin-top: 25px;">Chúng tôi rất mong muốn được nhận phản hồi từ phía quý khách hàng @if ($data['time_reply']) trước ngày {{ Carbon::parse($data['time_reply'])->format('d-m-Y') }} @endif để những nỗ lực nâng cao chất lượng dịch vụ sẽ được thực hiện sớm nhất có thể.</p>
    
    <p style="margin-top: 25px;">Rikkeisoft xin chân thành cảm ơn sự hợp tác và giúp đỡ của Quý vị và Quý Công ty.
        <br>Chúng tôi mong muốn được tiếp tục đồng hành với Quý Công ty trong tương lai.</p>
    
    <p>Trân trọng,</p>
@endif

