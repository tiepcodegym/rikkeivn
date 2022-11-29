<?php
namespace Rikkei\Ticket\Seeds;

use Rikkei\Ticket\Model\Action;
use DB;

class AttributeSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed('AttributeSeeder-v2')) {
            return true;
        }
        $dataFilePath = RIKKEI_TICKET_PATH . 'data-sample' . DIRECTORY_SEPARATOR . 'seed' . 
                DIRECTORY_SEPARATOR .  'attribute.php';
        if (! file_exists($dataFilePath)) {
            return;
        }
        $dataDemo = require $dataFilePath;
        if (! $dataDemo || ! count($dataDemo)) {
            return;
        }
        DB::beginTransaction();
        try {
            $this->createAttributeTicket($dataDemo);
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * create action acl recurive
     * 
     * @param array $data
     * @param int $parentId
     * @param int $sortOrder
     */
    protected function createAttributeTicket($dataDemo)
    {
        foreach ($dataDemo as $key) {
           DB::table('ticket_attributes')->insert($key);
        }
    }
}
