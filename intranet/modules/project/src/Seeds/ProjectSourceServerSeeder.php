<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\SourceServer;

class ProjectSourceServerSeeder extends CoreSeeder
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
        SourceServer::where('status', '!=' ,1)
            ->delete();
        $sourceServer = SourceServer::get();
        foreach ($sourceServer as $item) {
            $item->status = null;
            $item->parent_id = null;
            $item->task_id = null;
            $item->save();
        }
        $this->insertSeedMigrate();
    }
}
