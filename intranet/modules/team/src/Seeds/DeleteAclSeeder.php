<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\Action;
use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;

class DeleteAclSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Action::join('actions as parent', 'parent.id', '=', 'actions.parent_id')
                    ->where('parent.name', 'admin.monthly.report')
                    ->where(function($query) {
                        $query->where('actions.route', 'project::monthly.report.*')
                            ->orWhere('actions.route', 'project::monthly.report.update');
                    })
         */
        if ($this->checkExistsSeed(103)) {
            return true;
        }
        $dataFilePath = RIKKEI_TEAM_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' .
                DIRECTORY_SEPARATOR .  'acl-delete.php';
        if (!file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require_once $dataFilePath;
        if (!$dataDemo || !count($dataDemo)) {
            return;
        }
        $collection = Action::select('id')
                ->whereIn('name', $dataDemo)
                ->get();
        if (!count($collection)) {
            $this->insertSeedMigrate();
            return true;
        }
        DB::beginTransaction();
        try {
            foreach ($collection as $item) {
                $item->delete();
            }
            DB::delete('DELETE FROM `migrations` WHERE migration LIKE "seed-ActionSeeder%";');
            DB::delete('DELETE FROM `permissions` WHERE deleted_at IS NOT NULL;');
            DB::delete('DELETE FROM `permissions` WHERE action_id IN ('
                . 'SELECT id FROM `actions` WHERE deleted_at IS NOT NULL);');
            DB::delete('DELETE FROM `actions` WHERE deleted_at IS NOT NULL;');
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
