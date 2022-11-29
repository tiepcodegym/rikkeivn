<?php
namespace Rikkei\Help\Seeds;

use Rikkei\Help\Model\Help;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Seeds\CoreSeeder;

class HelpUpdateSlugSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        DB::beginTransaction();        
        try {
            $this->updateSlugHelp();
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * update help => update slug
     *
     * @param int $parentId
     * @return boolean
     */
    protected function updateSlugHelp($parentId = null)
    {
        $helps = Help::select(['id', 'parent', 'slug', 'title']);
        if ($parentId === null) {
            $helps->whereNull('parent');
        } else {
            $helps->where('parent', $parentId);
        }
        $helps = $helps->get();
        if (!count($helps)) {
            return true;
        }
        $tblHelp = Help::getTableName();
        foreach ($helps as $help) {
            $slugOld = $help->slug;
            $slugNew = $help->getHelpSlug();
            if ($slugNew !== $slugOld) {
                DB::table($tblHelp)
                    ->where('id', $help->id)
                    ->update([
                        'slug' => $slugNew,
                    ]);
            }
            $this->updateSlugHelp($help->id);
        }
    }
}
