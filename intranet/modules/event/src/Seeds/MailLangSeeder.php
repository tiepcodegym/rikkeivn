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
                                                            <p dir="ltr" style="text-align: left;">Beside BPO, we are bringing in another new service ??? CAD. Having on board a team of much experienced and expert engineers, especially in cars and construction, Rikkeisoft takes pride in always catering for the customers??? demands in the field. For the time being, the CAD service we have on offer includes:</p>
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
                'value' => 'C??ng ty C??? ph???n Rikkeisoft: B??? sung d???ch v??? BPO v?? CAD'
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
                    <p>K??nh g???i Qu?? kh??ch h??ng,</p>
                    <p>L???i ?????u ti??n, Rikkeisoft xin ch??n th??nh c???m ??n s??? tin t?????ng c???a Qu?? kh??ch h??ng ???? d??nh cho ch??ng t??i trong th???i gian qua. V???i mong mu???n ??a d???ng h??a c??ng nh?? ti???p t???c mang t???i kh??ch h??ng nh???ng d???ch v??? ch???t l?????ng, Rikkeisoft ???? ch??nh th???c b??? sung <span style="color: #ff0000">ba</span> m???ng d???ch v??? BPO (Business Process Outsourcing), CAD (Computer-Aided Design) v?? AWS (Amazon Web Services) v??o ho???t ?????ng kinh doanh. </p>
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
                                                        <div style="text-align: center; padding-top: 8px;"><strong><span style="color:#a73230; font-weight: bold">D???ch v??? BPO</span> </strong></div>
                                                    </td>
                                                    <td style="border-top: 1px dashed red;
                                                        border-right: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 0 20px 20px 0px;
                                                        padding: 30px;    padding-left: 5px;padding-top: 15px;padding-bottom: 15px;">
                                                        <p dir="ltr" style="text-align: left;">Hi???n t???i, BPO c???a Rikkeisoft t???p trung v??o c??c d???ch v??? thu th???p v?? d??n nh??n d??? li???u (h??nh ???nh, video, ??m thanh, v??n b???n???) ph???c v??? cho h???c m??y ??? AI training. Ngo??i ra, Rikkeisoft c??ng cung c???p d???ch v??? Nh???p li???u v?? i-Reporter (s??? h??a v??n b???n). ?????c bi???t, ch??ng t??i ???? ph??t tri???n th??nh c??ng c??ng c??? d??n nh??n d??? li???u ???ng d???ng AI: Rikano; nh??? ???? ?????m b???o ch???t l?????ng d???ch v???, n??ng cao hi???u su???t v?? ????? ch??nh x??c l??n ?????n 99%.</p>
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
                                                        <div style="text-align: center; padding-top: 8px;"><span style="color:#a73230; font-weight: bold">D???ch v??? CAD</span></div>
                                                    </td>
                                                    <td style="border-top: 1px dashed red;
                                                        border-right: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 0 20px 20px 0px;
                                                        padding: 30px;  padding-left: 5px;padding-top: 15px;padding-bottom: 15px;">
                                                        <p dir="ltr" style="text-align: left;">B??n c???nh BPO, CAD c??ng l?? d???ch v??? m???i m?? Rikkeisoft t???p trung ph??t tri???n. Nh??? ?????i ng?? nh??n s??? gi??u kinh nghi???m v?? c?? tr??nh ????? chuy??n m??n cao, Rikkeisoft t??? tin ho??n th??nh nh???ng y??u c???u c???a kh??ch h??ng ??? m???ng d???ch v??? n??y. Hi???n t???i Rikkeisoft c?? th??? t???o m?? h??nh 3D t??? nh???ng b???n v??? tay, b???n v??? 2D ho???c theo mong mu???n c???a kh??ch h??ng, ?????ng th???i ch??ng t??i c??ng c?? th??? d???ng b???n v??? s???n ph???m ????n l??? v?? b???n v??? l???p r??p t??? m?? h??nh 3D.</p>
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
                                                        <div dir="ltr" style="text-align: center; padding-top: 12px;"><strong><span style="color:#a73230; font-weight: bold; display: block;">D???ch v??? AWS</span></strong></div>
                                                    </td>
                                                    <td style="border-top: 1px dashed red;
                                                        border-right: 1px dashed red;
                                                        border-bottom: 1px dashed red;
                                                        border-radius: 0 20px 20px 0px;
                                                        padding: 30px;  padding-left: 5px;padding-top: 15px;padding-bottom: 15px;">
                                                        <p dir="ltr" style="text-align: left; color: #ff0000;">Cu???i c??ng, v???i t?? c??ch l?? ?????i t??c t?? v???n ch??nh th???c thu???c AWS Partner Network (APN), Rikkeisoft t??? tin c?? th??? cung c???p t???t c??? c??c d???ch v??? v?? h??? tr??? 24/7 cho kh??ch h??ng. Ch??ng t??i c?? kinh nghi???m th???c hi???n nhi???u d??? ??n nh?? x??y d???ng m??i tr?????ng c?? s??? h??? t???ng tr??n AWS, ph??t tri???n h??? th???ng th??ch h???p AWS, tri???n khai h??? th???ng tr??n AWS, gi??m s??t, v???n h??nh v?? b???o tr?? s??? d???ng AWS.</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                
                                        <div style="margin-left: 20px; font-size: 14px;">
                                            <p style="text-align: left;">Sau khi ph??t tri???n th??m <span style="color: #ff0000">c??c</span> m???ng d???ch v??? m???i, Rikkeisoft hy v???ng s??? ti???p t???c l?? ?????i t??c tin c???y v?? ???????c ?????ng h??nh c??ng s??? ph??t tri???n c???a Qu?? kh??ch h??ng trong th???i gian t???i. Xin ch??n th??nh c???m ??n!</p>
                                            <p dir="ltr" style="text-align: left; margin-top: 20px;">Tr??n tr???ng.</p>
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
