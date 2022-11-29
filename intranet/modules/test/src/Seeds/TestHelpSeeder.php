<?php

namespace Rikkei\Test\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Help\Model\Help;
use Illuminate\Support\Facades\DB;

class TestHelpSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed(7)) {
            return;
        }
        $pages = [
            'no-file' => 'Test',
            'import-test' => 'Import test',
            'multi-lang' => 'Test multi-language',
        ];
        DB::beginTransaction();
        try {
            //insert help page
            $testParentId = null;
            foreach ($pages as $file => $title) {
                $testHelp = Help::where('title', $title)->first();
                if (!$testHelp) {
                    $testHelp = new Help();
                }
                $testHelp->title = $title;
                $testHelp->active = 1;
                $testHelp->created_by = 1;
                $testHelp->order = 10;
                $testHelp->parent = $testParentId;
                if ($file !== 'no-file') {
                    $testHelp->content = view('test::help.' . $file)->render();
                }
                $testHelp->save();
                if ($file === 'no-file') {
                    $testParentId = $testHelp->id;
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

}
