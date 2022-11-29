<?php

use Illuminate\Database\Seeder;

class ForgotTurnOffTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        for ($i = 1; $i <= 20; $i++){
            try {
                \Rikkei\Core\Model\ForgotTurnOff::create([
                    'employee_id' => rand(1,10),
                    'forgot_date' => $faker->dateTimeBetween('2019-07-01', '2019-07-31'),
                    'amount' => '20000',
                ]);
            } catch (Exception $e) {
                continue;
            }
        }
    }
}
