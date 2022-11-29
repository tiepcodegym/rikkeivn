<?php
namespace Rikkei\Resource\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\RequestPriority;

class UpdatePriorityIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('requests')->where('priority_id', 0)->update(['priority_id' => RequestPriority::PRIORITY_NORMAL]);
    }
}
