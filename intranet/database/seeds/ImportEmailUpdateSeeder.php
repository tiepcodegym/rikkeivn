<?php

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Team\Model\Employee;

class ImportEmailUpdateSeeder extends Seeder
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
                    if (!$row || !isset($row['email_co_quan']) || !$row['email_co_quan']) {
                        continue;
                    }
                    $employee = Employee::where('email',$row['email_co_quan'])
                        ->first();
                    if (!$employee) {
                        $employee = new Employee();
                    }
                    $nickname = str_replace('@rikkeisoft.com', '', $row['email_co_quan']);
                    $nickname = str_replace('@gmail.com', '', $nickname);
                    if(!Employee::checkNicknameExist($nickname)) {
                        $employee->nickname = $nickname;
                    } else {
                        $employee->nickname = $row['email_co_quan'];
                    }
                    $code = $row['ma_nhan_vien'];
                    $code = preg_replace('/[a-zA-Z]+/', '', $code);
                    $code = (int) $code;
                    $data = [
                        'employee_card_id' => $code,
                        'name'  => $row['ho_va_ten'],
                        'email' => $row['email_co_quan'],
                        'join_date' => '2016-01-01',
                    ];
                    $employee->setData($data);
                    $employee->save([], ['code' => $code]);
                }
            });
        });
    }
    
    
}
