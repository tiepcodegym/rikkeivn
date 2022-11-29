<?php
namespace Rikkei\Core\Seeds;

class ACoreSC extends CoreSeeder
{
    public function run()
    {   
        $this->call(MenusSeeder::class);
        $this->call(MenuItemsSeeder::class);
//        $this->call(ApiTokenSeeder::class);
//        $this->call(HttpsSeeder::class);
//        $this->call(AnnualHolidaysSeeder::class);
//        $this->call(SystemDataConfigSeeder::class);
//        $this->call(SettingAutoApproveCommentSeeder::class);
//        $this->call(SeedDataSpecialHolidays::class);
    }
}

