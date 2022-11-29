<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\Project;

class ProjectState extends CoreSeeder
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
        $status = Project::lablelState();
        $keyStates = array_keys($status);
        $collection = Project::select('id', 'state')
            ->whereNotIn('state', $keyStates)
            ->get();
        if (!count($collection)) {
            return;
        }
        foreach ($collection as $item) {
            $item->state = Project::STATE_PROCESSING;
            $item->save();
        }
        $this->insertSeedMigrate();
    }
}
