<?php
namespace Rikkei\Resource\Seeds;

use Illuminate\Database\Seeder;
use DB;
use Rikkei\Resource\Model\Candidate;

class UpdateTypeCandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('candidates')->where('type_candidate', 0)->update(['type_candidate' => Candidate::TYPE_FROM_INTRANET]);
    }
}
