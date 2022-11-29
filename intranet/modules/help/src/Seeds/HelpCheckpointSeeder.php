<?php
namespace Rikkei\Help\Seeds;

use Rikkei\Help\Model\Help;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;

class HelpCheckpointSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('HelpCheckpointSeeder-v4')) {
            return true;
        }

        DB::beginTransaction();
        try {
            Help::where('title', 'Checkpoint')->delete();
            $help = new Help();
            $help->title = 'Checkpoint';
            $help->active = Help::STATUS_ACTIVE;
            $help->content = view('help::items.checkpoint');
            $help->save();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
