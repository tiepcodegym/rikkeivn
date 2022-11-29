<?php
namespace Rikkei\Resource\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use DB;
use File;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Resource\Model\Candidate;

class CandidateCvRenameSeeder extends CoreSeeder
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
        
        DB::beginTransaction();
        try {
            $pathFolder = SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER;
            $storaPath = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".Candidate::UPLOAD_CV_FOLDER);
            if (file_exists($storaPath)) {
                $files = File::allFiles();
                foreach ($files as $file)
                {   
                    $fileInfo = pathinfo($file);
                    $newName = str_random(5) . '_' . time() . '.' . $fileInfo['extension'];
                    File::move($file, $fileInfo['dirname'] . '/' . $newName);
                    Candidate::where('cv', $pathFolder . $fileInfo['basename'])
                            ->update(['cv' => $pathFolder . $newName]);
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
