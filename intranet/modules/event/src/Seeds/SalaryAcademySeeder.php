<?php

namespace Rikkei\Event\Seeds;

use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Event\Model\EventBirthday;
use Rikkei\Core\Model\CoreConfigData;

class SalaryAcademySeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }
        $data = [
            [
                'key' => 'hr.email_content.salary.academy',
                'value' => '<p><strong>Dear&nbsp;{{ name }},</strong></p>

                <p>Ban TCKT xin gửi: Th&ocirc;ng b&aacute;o Phiếu Lương Th&aacute;ng 1.2021 lần 2&nbsp; như file đ&iacute;nh k&egrave;m b&ecirc;n dưới.</p>
                
                <p>Mọi thắc mắc (nếu c&oacute;) vui l&ograve;ng li&ecirc;n hệ:<br />
                1. Thắc mắc về ng&agrave;y c&ocirc;ng, giờ OT li&ecirc;n hệ :&nbsp;<a href="mailto:xuanntl@rikkeisoft.com" target="_blank">xuanntl@rikkeisoft.com</a>&nbsp;(skype: live:xuanntl85_1)<br />
                2. Thắc mắc về lương thưởng li&ecirc;n hệ:&nbsp;<a href="mailto:manhlk@rikkeisoft.com" target="_blank">manhlk@rikkeisoft.com</a>&nbsp;(skype:khacmanh.2511)<br />
                3. Thắc mắc về mật khẩu mở file phiếu lương li&ecirc;n hệ:&nbsp;<a href="mailto:hungnt2@rikkeisoft.com" target="_blank">hungnt2@rikkeisoft.com</a>&nbsp;(skype: hucabaly)</p>
                
                <p>Xin cảm ơn./.</p>
                
                ',
            ],
            [
                'key' => 'hr.email_subject.salary.academy',
                'value' => 'Phiếu lương tháng 07.2022 - {{ name }}',
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($data as $key) {
                if (! DB::table('core_config_datas')->select('id')->where('key', $key['key'])->get()) {
                    DB::table('core_config_datas')->insert($key);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
        }
    }
    

}
