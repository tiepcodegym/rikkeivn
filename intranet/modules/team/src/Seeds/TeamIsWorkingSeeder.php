<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Team\Model\EmployeeTeamHistory;

class TeamIsWorkingSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmployeeTeamHistory::updateTeamIsWorking();
    }
}
