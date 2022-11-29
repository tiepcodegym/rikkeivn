<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;

class ProgramsSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $dataDemo = [
            [
                'name' => 'PHP',
            ],
            [
                'name' => 'Java',
            ],
            [
                'name' => 'JavaScript',
            ],
            [
                'name' => 'Python',
            ],
            [
                'name' => 'C#',
            ],
            [
                'name' => 'C++',
            ],
            [
                'name' => 'Ruby',
            ],
            [
                'name' => 'CSS',
            ],
            [
                'name' => 'C',
            ],
            [
                'name' => 'Assembly',
            ],
            [
                'name' => 'Perl',
            ],
            [
                'name' => 'Shell',
            ],
            [
                'name' => 'Groovy',
            ],
            [
                'name' => 'Visual Basic .NET',
            ],
            [
                'name' => 'Scala',
            ],
            [
                'name' => 'Objective C',
            ],
            [
                'name' => 'Swift',
            ],
            [
                'name' => 'Java web',
            ],
            [
                'name' => 'Java Android',
            ],
        ];
        DB::beginTransaction();
        try {
            foreach ($dataDemo as $data) {
                if (! DB::table('programming_languages')->select('id')->where('name', $data['name'])->get()) {
                    DB::table('programming_languages')->insert($data);
                }
            }

            //Update color
            $colors = [
                'PHP' => "#2ecc71", 
                'Java' => "#3498db", 
                'JavaScript' => "#95a5a6", 
                'Python' => "#f1c40f", 
                'C#' => "#e74c3c", 
                'C++' => "#34495e", 
                'Ruby' => "#f2dede", 
                'C' => "#dff0d8", 
                'Assembly' => "#d9edf7", 
                'Perl' => "#fcf8e3", 
                'Shell' => "#39CCCC", 
                'Groovy' => "#f39c12", 
                'Visual Basic .NET' => "#D81B60", 
                'Scala' => "#605ca8", 
                'CSS' => "#9b59b6", 
            ];   
            $progs = DB::table('programming_languages')->select('name')->get();
            if ($progs) {
                foreach ((array)$progs as $prog) {
                    if (isset($colors[$prog->name])) {
                        DB::table('programming_languages')
                            ->where('name', $prog->name)
                            ->update(['color' => $colors[$prog->name]]);
                    }
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        
    }
}
