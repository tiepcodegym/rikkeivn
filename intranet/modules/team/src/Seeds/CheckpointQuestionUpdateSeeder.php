<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\CheckpointQuestion;

class CheckpointQuestionUpdateSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //DEVELOPER
        DB::table('checkpoint_question')->where('id',4)
                ->update([
                    'rank2_text' => '1 chứng chỉ chuyên ngành',
                    'rank3_text' => '2 chứng chỉ chuyên ngành',
                    'rank4_text' => '3 chứng chỉ chuyên ngành'
                ]);
        
        DB::table('checkpoint_question')->whereIn('id',[8, 15])
                ->update(['weight' => '4']);
        DB::table('checkpoint_question')->whereIn('id',[10, 11, 26, 31])
                ->update(['weight' => '2']);
        DB::table('checkpoint_question')->where('id',25)
                ->update(['content' => 'Kỹ năng giao tiếp']);
        DB::table('checkpoint_question')->where('id',26)
                ->update(['content' => 'Kỹ năng làm việc nhóm']);
        DB::table('checkpoint_question')->where('id',27)
                ->update([
                    'content' => 'Kỹ năng làm việc độc lập', 
                    'sort_order' => 5
                ]);
        DB::table('checkpoint_question')->where('id',28)
                ->update(['content' => 'Tham gia các hoạt động tập thể của team']);
        DB::table('checkpoint_question')->whereIn('id',[48,49,50,51])
                ->update([
                    'rank4_text' => 'Xuất sắc', 
                ]);
        DB::table('checkpoint_question')->whereIn('id',[32,33])
                ->update([
                    'rank1_text' => '< 18 tháng', 
                ]);
        
        //Insert new questions
        $dataInsert = [
            [
                'content' => 'Kỹ năng quản lý',
                'category_id' => '15',
                'sort_order' => 3,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ],
            [
                'content' => 'Kỹ năng thuyết trình',
                'category_id' => '15',
                'sort_order' => 4,
                'weight'     => 2,
                'rank1_text' => 'Bình thường',
                'rank2_text' => 'Khá',
                'rank3_text' => 'Tốt',
                'rank4_text' => 'Xuất sắc',
            ]
        ];
        foreach ($dataInsert as $data) {
            if (!CheckpointQuestion::where('content', $data['content'])->count()) {
                
                $model = new CheckpointQuestion();
                $model->setData($data);
                $model->save();
            }
        }
        $this->insertSeedMigrate();
    }
}
