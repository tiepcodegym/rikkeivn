<?php

namespace Rikkei\Test\Seeds;

use DB;
use Rikkei\Core\Seeds\CoreSeeder;

class PasswordSeeder extends CoreSeeder {

    public function run() {
        try {
            DB::table('md_test_password')
                    ->insert(['id' => 10, 'password' => '123456']);
        } catch (\Exception $e) {
            
        }
    }

}
