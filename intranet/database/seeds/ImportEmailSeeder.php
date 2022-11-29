<?php

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Team\Model\Employee;

class ImportEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Excel::load('email_import.xlsx', function ($reader) {
            $reader->each(function($sheet) {    
                foreach ($sheet->toArray() as $row) {
                    if ($row['email_co_quan'] !== null) {
                        if(!Employee::checkEmailExist($row['email_co_quan'])) {
                            $nickname = str_replace('@rikkeisoft.com', '', $row['email_co_quan']);
                            $nickname = str_replace('@gmail.com', '', $nickname);
                            if(Employee::checkNicknameExist($nickname)) {
                                $nickname .= '2';
                            }
                            
                            $data = [
                                'employee_card_id' => 0,
                                'name'  => $row['ho_va_ten'],
                                'email' => $row['email_co_quan'],
                                'nickname' => $nickname,
                                'join_date' => '2016-01-01',
                            ];
                            $employee = new Employee();
                            $employee->setData($data);
                            $employee->save();
                        }
                    }
                }
            });
        });
    }
    
    
}
