<?php

namespace Rikkei\Event\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\DB;

class MailLangSeeder extends CoreSeeder
{
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return;
        }
        $subjectEn = CoreConfigData::where('key', 'event.eventday.company.subject.en')->first();
        $contentEn = CoreConfigData::where('key', 'event.eventday.company.content.en')->first();
        $subjectVn = CoreConfigData::where('key', 'event.eventday.company.subject.vn')->first();
        $contentVn = CoreConfigData::where('key', 'event.eventday.company.content.vn')->first();
        
        $data = [
            [
                'key' => 'event.eventday.company.subject.en',
                'value' => 'Rikkeisoft: Now Offering BPO and CAD Services'
            ],
            [
                'key' => 'event.eventday.company.content.en',
                'value' => '<div style=" 
                font-family:arial,sans-serif;
                padding-left: 45px;
                padding-right: 45px;
                position: relative;
                top: -45px;
                ">
                <div style="margin-left: 20px; font-weight: normal; font-size: 14px;">
                    <p>Dear Customers,</p>
                    <p>First of all, on behalf of Rikkeisoft, we would like to reach out to you with the deepest gratitude for your trust in our services throughout the years. To keep up the good work, we proceed to offer you a wider range of high-quality services. As stated, we are informing you of three new services, namely BPO (Business Process Outsourcing), CAD (Computer-Aided Design) and AWS (Amazon Web Services).</p>
                </div>
                
                <table style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:small; font-style:normal; font-variant-ligatures:normal; font-variant-caps:normal; font-weight:400; letter-spacing:normal; orphans:2; text-transform:none; white-space:normal; widows:2; word-spacing:0px; -webkit-text-stroke-width:0px;  text-decoration-thickness:initial; text-decoration-style:initial; text-decoration-color:initial; text-align:center; border:none">
                    <tbody>
                        <tr>
                            <td colspan="2" style=" border:none">
                            <table style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:small; font-style:normal; font-variant-ligatures:normal; font-variant-caps:normal; font-weight:400; letter-spacing:normal; orphans:2; text-transform:none; white-space:normal; widows:2; word-spacing:0px; -webkit-text-stroke-width:0px;  text-decoration-thickness:initial; text-decoration-style:initial; text-decoration-color:initial; text-align:center; border:none">
                                <tbody>
                                    <tr>
                                        <td colspan="2" style="width:647.5pt; border:none">
                                            <table cellpadding="1" cellspacing="1" style=" margin-top: 20px; font-size: 14px;">
                                                <tbody>
                                                    <tr style="display: block; margin-bottom: 20px;">
                                                        <td style="border-top: 1px dashed red;
                                                            border-left: 1px dashed red;
                                                            border-bottom: 1px dashed red;
                                                            border-radius: 20px 0 0 20px;
                                                            padding: 30px;
                                                            width: 130px;
                                                            padding-right: 2px;
                                                            padding-left: 0;
                                                            padding-top: 15px;
                                                            padding-bottom: 15px;">
                                                            <div style="text-align:center; margin:0 auto;"><img alt="" height="77" width="90" src="https://rikkei.vn/common/images/email/BPO-32.png" /></div>
                                                            <div style="text-align: center; padding-top: 8px;"><strong><span style="color:#a73230; font-weight: bold">BPO service</span> </strong></div>
                                                        </td>
                                                        <td style="border-top: 1px dashed red;
                                                            border-right: 1px dashed red;
                                                            border-bottom: 1px dashed red;
                                                            border-radius: 0 20px 20px 0px;
                                                            padding: 20px;  padding-left: 0; padding-top: 15px;padding-bottom: 15px;">
                                                            <p dir="ltr" style="text-align: left;">As of now, our BPO service falls into three main categories:</p>
                                                            <p dir="ltr" style="text-align: left;">- Collecting and labeling data (image, video, audio, text) for machine learning and AI training</p>
                                                            <p dir="ltr" style="text-align: left;">- Data Entry </p>
                                                            <p dir="ltr" style="text-align: left;">- i-Reporter (digitized text) services.</p>
                                                            <p dir="ltr" style="text-align: left;">Alongside, we have developed an in-house data labeling tool called Rikano which, thanks to the support of AI, achieves accuracies of 99% and improves work performance as compared to traditional tools.</p>
                                                        </td>
                                                    </tr>
                                                    <tr style="display: block; margin-bottom: 20px;">
                                                        <td style="border-top: 1px dashed red;
                                                            border-left: 1px dashed red;
                                                            border-bottom: 1px dashed red;
                                                            border-radius: 20px 0 0 20px;
                                                            padding: 30px;
                                                            width: 130px;
                                                            padding-right: 2px;
                                                            padding-left: 0;
                                                            padding-top: 0;
                                                            height: 225px;
                                                            padding-bottom: 0;">
                                                            <div dir="ltr" style="text-align:center; margin:0 auto;"><img alt="" height="65" width="90" src="https://rikkei.vn/common/images/email/CAD-32.png" /></div>
                                                            <div style="text-align: center; padding-top: 8px;"><span style="color:#a73230; font-weight: bold">CAD service</span></div>
                                                        </td>
                                                        <td style="border-top: 1px dashed red;
                                                            border-right: 1px dashed red;
                                                            border-bottom: 1px dashed red;
                                                            border-radius: 0 20px 20px 0px;
                                                            padding: 20px;  padding-left: 0; padding-top: 15px;padding-bottom: 15px;">
                                                            <p dir="ltr" style="text-align: left;">Beside BPO, we are bringing in another new service – CAD. Having on board a team of much experienced and expert engineers, especially in cars and construction, Rikkeisoft takes pride in always catering for the customers’ demands in the field. For the time being, the CAD service we have on offer includes:</p>
                                                            <p dir="ltr" style="text-align: left;">- Creating 3D models from hand-drawn pictures, 2D images, etc. all tailored-made to suit customer needs. </p>
                                                            <p dir="ltr" style="text-align: left;">- Building single product drawings and assembly drawings from 3D models.</p>
                                                        </td>
                                                    </tr>
                                                    <tr style="display: block; margin-bottom: 20px;">
                                                        <td style="border-top: 1px dashed red;
                                                            border-left: 1px dashed red;
                                                            border-bottom: 1px dashed red;
                                                            border-radius: 20px 0 0 20px;
                                                            padding: 30px;
                                                            width: 130px;
                                                            padding-right: 2px;
                                                            padding-left: 0;
                                                            padding-top: 15px;
                                                            height: 175px;
                                                            padding-bottom: 15px;">
                                                            <div dir="ltr" style="text-align:center; margin:0 auto;"><img alt="" height="47" width="93" src="https://rikkei.vn/common/images/email/amazon-web-services-01.png" /></div>
                                                            <div dir="ltr" style="text-align: center; padding-top: 12px;"><strong><span style="color:#a73230; font-weight: bold; display: block;">AWS service</span></strong></div>
                                                        </td>
                                                        <td style="border-top: 1px dashed red;
                                                            border-right: 1px dashed red;
                                                            border-bottom: 1px dashed red;
                                                            border-radius: 0 20px 20px 0px;
                                                            padding: 20px;  padding-left: 0; padding-top: 15px; padding-bottom: 15px;">
                                                            <p dir="ltr" style="text-align: left;">Finally, as an official partner in the AWS Partner Network (APN), we have all the resources to provide all-around services as well as 24/7 customer support. Our team has had years implementing projects such as:|</p>
                                                            <p dir="ltr" style="text-align: left;">- AWS-based infrastructure development </p>
                                                            <p dir="ltr" style="text-align: left;">- Developing integrated AWS systems</p>
                                                            <p dir="ltr" style="text-align: left;">- Deploying on AWS</p>
                                                            <p dir="ltr" style="text-align: left;">- Monitoring/maintaining AWS systems.</p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                
                                        <div style="margin-left: 20px; font-size: 14px;">
                                            <p style="text-align: left;">
                                                Our best hopes are that Rikkeisoft continues to be the place you turn to with your wishes and needs moving forward. <br>
                                                As always, please stay in touch and let us know what Rikkeisoft can do to help your business!
                                            </p>
                                            <p dir="ltr" style="text-align: left; margin-top: 20px;">Yours faithfully,</p>
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="width:647.5pt; border:none">
                                        <p style="margin: 0px; padding: 0in 5.4pt; text-align: left;">&nbsp;</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:234.75pt; border:none">
                                        <p style="margin: 0px; padding: 0in 5.4pt; text-align: left;"><meta charset="utf-8" /></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
                '
            ],
            [
                'key' => 'event.eventday.company.subject.vn',
                'value' => 'Công ty Cổ phần Rikkeisoft: Bổ sung dịch vụ BPO và CAD'
            ],
            [
                'key' => 'event.eventday.company.content.vn',
                'value' => '<div style=" 
                font-family:arial,sans-serif;
                padding-left: 45px;
                padding-right: 45px;
                position: relative;
                top: -45px;
                ">
                <div style="margin-left: 20px; font-weight: normal; font-size: 14px;">
                    <p>Kính gửi Quý khách hàng,</p>
                    <p>Lời đầu tiên, Rikkeisoft xin chân thành cảm ơn sự tin tưởng của Quý khách hàng đã dành cho chúng tôi trong thời gian qua. Với mong muốn đa dạng hóa cũng như tiếp tục mang tới khách hàng những dịch vụ chất lượng, Rikkeisoft đã chính thức bổ sung <span style="color: #ff0000">ba</span> mảng dịch vụ BPO (Business Process Outsourcing), CAD (Computer-Aided Design) và AWS (Amazon Web Services) vào hoạt động kinh doanh. </p>
                </div>
                
                <table style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:small; font-style:normal; font-variant-ligatures:normal; font-variant-caps:normal; font-weight:400; letter-spacing:normal; orphans:2; text-transform:none; white-space:normal; widows:2; word-spacing:0px; -webkit-text-stroke-width:0px;  text-decoration-thickness:initial; text-decoration-style:initial; text-decoration-color:initial; text-align:center; border:none">
                    <tbody>
                        <tr>
                            <td colspan="2" style=" border:none">
                            <table style="color:#222222; font-family:Arial, Helvetica, sans-serif; font-size:small; font-style:normal; font-variant-ligatures:normal; font-variant-caps:normal; font-weight:400; letter-spacing:normal; orphans:2; text-transform:none; white-space:normal; widows:2; word-spacing:0px; -webkit-text-stroke-width:0px;  text-decoration-thickness:initial; text-decoration-style:initial; text-decoration-color:initial; text-align:center; border:none">
                                <tbody>
                                    <tr>
                                        <td colspan="2" style="width:647.5pt; border:none">
                                        <table cellpadding="1" cellspacing="1" style=" margin-top: 20px; font-size: 14px;">
                                            <tbody>
                                                <tr style="display: block; margin-bottom: 20px;">
                                                    <td style="border-top: 1px dashed red;
                                                        border-left: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 20px 0 0 20px;
                                                        padding: 30px;
                                                        width: 160px;
                                                        padding-right: 2px;
                                                        padding-left: 2px;
                                                        padding-top: 15px;
                                                        height: 175px;
                                                        padding-bottom: 15px;">
                                                        <div style="text-align:center; margin:0 auto;"><img alt="" height="77" src="https://rikkei.vn/common/images/email/BPO-32.png" width="90" /></div>
                                                        <div style="text-align: center; padding-top: 8px;"><strong><span style="color:#a73230; font-weight: bold">Dịch vụ BPO</span> </strong></div>
                                                    </td>
                                                    <td style="border-top: 1px dashed red;
                                                        border-right: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 0 20px 20px 0px;
                                                        padding: 30px;    padding-left: 5px;padding-top: 15px;padding-bottom: 15px;">
                                                        <p dir="ltr" style="text-align: left;">Hiện tại, BPO của Rikkeisoft tập trung vào các dịch vụ thu thập và dán nhãn dữ liệu (hình ảnh, video, âm thanh, văn bản…) phục vụ cho học máy – AI training. Ngoài ra, Rikkeisoft cũng cung cấp dịch vụ Nhập liệu và i-Reporter (số hóa văn bản). Đặc biệt, chúng tôi đã phát triển thành công công cụ dán nhãn dữ liệu ứng dụng AI: Rikano; nhờ đó đảm bảo chất lượng dịch vụ, nâng cao hiệu suất và độ chính xác lên đến 99%.</p>
                                                    </td>
                                                </tr>
                                                <tr style="display: block; margin-bottom: 20px;">
                                                    <td style="border-top: 1px dashed red;
                                                        border-left: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 20px 0 0 20px;
                                                        padding: 30px;
                                                        width: 160px;
                                                        padding-right: 2px;
                                                        padding-left: 2px;
                                                        padding-top: 0;
                                                        height: 205px;
                                                        padding-bottom: 0;">
                                                        <div dir="ltr" style="text-align:center; margin:0 auto;"><img alt="" height="65" src="https://rikkei.vn/common/images/email/CAD-32.png" width="90" /></div>
                                                        <div style="text-align: center; padding-top: 8px;"><span style="color:#a73230; font-weight: bold">Dịch vụ CAD</span></div>
                                                    </td>
                                                    <td style="border-top: 1px dashed red;
                                                        border-right: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 0 20px 20px 0px;
                                                        padding: 30px;  padding-left: 5px;padding-top: 15px;padding-bottom: 15px;">
                                                        <p dir="ltr" style="text-align: left;">Bên cạnh BPO, CAD cũng là dịch vụ mới mà Rikkeisoft tập trung phát triển. Nhờ đội ngũ nhân sự giàu kinh nghiệm và có trình độ chuyên môn cao, Rikkeisoft tự tin hoàn thành những yêu cầu của khách hàng ở mảng dịch vụ này. Hiện tại Rikkeisoft có thể tạo mô hình 3D từ những bản vẽ tay, bản vẽ 2D hoặc theo mong muốn của khách hàng, đồng thời chúng tôi cũng có thể dựng bản vẽ sản phẩm đơn lẻ và bản vẽ lắp ráp từ mô hình 3D.</p>
                                                    </td>
                                                </tr>
                                                <tr style="display: block; margin-bottom: 20px;">
                                                    <td style="border-top: 1px dashed red;
                                                        border-left: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 20px 0 0 20px;
                                                        padding: 30px;
                                                        width: 160px;
                                                        padding-right: 2px;
                                                        padding-left: 2px;
                                                        padding-top: 15px;
                                                        height: 175px;
                                                        padding-bottom: 15px;">
                                                        <div dir="ltr" style="text-align:center; margin:0 auto;"><img alt="" height="50" src="https://rikkei.vn/common/images/email/amazon-web-services-01.png" width="107" /></div>
                                                        <div dir="ltr" style="text-align: center; padding-top: 12px;"><strong><span style="color:#a73230; font-weight: bold; display: block;">Dịch vụ AWS</span></strong></div>
                                                    </td>
                                                    <td style="border-top: 1px dashed red;
                                                        border-right: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 0 20px 20px 0px;
                                                        padding: 30px;  padding-left: 5px;padding-top: 15px;padding-bottom: 15px;">
                                                        <p dir="ltr" style="text-align: left; color: #ff0000;">Cuối cùng, với tư cách là đối tác tư vấn chính thức thuộc AWS Partner Network (APN), Rikkeisoft tự tin có thể cung cấp tất cả các dịch vụ và hỗ trợ 24/7 cho khách hàng. Chúng tôi có kinh nghiệm thực hiện nhiều dự án như xây dựng môi trường cơ sở hạ tầng trên AWS, phát triển hệ thống thích hợp AWS, triển khai hệ thống trên AWS, giám sát, vận hành và bảo trì sử dụng AWS.</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                
                                        <div style="margin-left: 20px; font-size: 14px;">
                                            <p style="text-align: left;">Sau khi phát triển thêm <span style="color: #ff0000">các</span> mảng dịch vụ mới, Rikkeisoft hy vọng sẽ tiếp tục là đối tác tin cậy và được đồng hành cùng sự phát triển của Quý khách hàng trong thời gian tới. Xin chân thành cảm ơn!</p>
                                            <p dir="ltr" style="text-align: left; margin-top: 20px;">Trân trọng.</p>
                                        </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="width:647.5pt; border:none">
                                        <p style="margin: 0px; padding: 0in 5.4pt; text-align: left;">&nbsp;</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width:234.75pt; border:none">
                                        <p style="margin: 0px; padding: 0in 5.4pt; text-align: left;"><meta charset="utf-8" /></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>'
            ],
        ];

        DB::beginTransaction();
        try {
            if ($subjectEn) $subjectEn->delete();
            if ($contentEn) $contentEn->delete();
            if ($subjectVn) $subjectVn->delete();
            if ($contentVn) $contentVn->delete();
            CoreConfigData::insert($data);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
}
