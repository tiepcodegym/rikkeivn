<?php
namespace Rikkei\Team\Seeds;

use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Team\Model\QualityEducation;
use const RIKKEI_TEAM_PATH;

class EducationQualitySeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(2)) {
            return true;
        }
        DB::beginTransaction();
        try {
            
            $dataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' . 
                    DIRECTORY_SEPARATOR .  'education-quality.php';
            if (! file_exists($dataFilePath)) {
                return;
            }
            $dataDemo = require $dataFilePath;
            if (! $dataDemo || ! count($dataDemo)) {
                return;
            }
            
            foreach($dataDemo as $data) {
                $model = QualityEducation::where('name', $data)
                    ->first();
                if (!$model) {
                    $model = new QualityEducation();
                }
                $model->setData(['name' => $data]);
                $model->save();
            }
            
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
