<?php
namespace Rikkei\Team\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;

class ATeamSC extends CoreSeeder
{
    public function run()
    {   
//        $this->call(DeleteAclSeeder::class);
//        $this->call(ActionSeeder::class);
//        $this->call(TeamSeeder::class);
//        $this->call(TeamUpdateSeeder::class);
//        $this->call(EmplAttrRemoveSeeder::class);
//        $this->call(EditEmployeeCodeSeeder::class);
//        $this->call(TeamTypePqaSeeder::class);
//        $this->call(UpdateTeamTypeQaSeeder::class);
//        $this->call(RelationsNameSeeder::class);
//        $this->call(LibCountrySeeder::class);
//        $this->call(EducationQualitySeeder::class);
//        $this->call(PositionPoliticSeeder::class);
//        $this->call(MilitarySeeder::class);
//        $this->call(CheckpointTooltipSeeder::class);
//        $this->call(CheckpointResultSeeder::class);
//        $this->call(PositionSeeder::class);
//        $this->call(UserSeeder::class);
//        $this->call(PermissionSeeder::class);
//        $this->call(CheckpointTypeSeeder::class);
//        $this->call(CheckpointCategorySeeder::class);
//        $this->call(CheckpointQuestionSeeder::class);
//        $this->call(CheckpointQuestionUpdateSeeder::class);
//        /*$this->call(CheckpointTimeSeeder::class);
//        $this->call(CheckpointTime3_2018Seeder::class);*/
//        $this->call(TeamTypeHrSeeder::class);
//        $this->call(TeamRenameSeeder::class);
//        $this->call(TeamAddIsSoftDevSeeder::class);
//        $this->call(EmployeeTeamHistorySeeder::class);
//        $this->call(UpdateDeletedEmployeeTeamHistorySeeder::class);
//        $this->call(ClearMonthlyReportTableSeeder::class);
//        $this->call(ClearMonthlyReportTableV2Seeder::class);
//        $this->call(ChangeMaNVSeeder::class);
//        $this->call(UpdateTeamTypeSaleSeeder::class);
//        $this->call(ProfileEnglishSeeder::class);
//        $this->call(SchoolSeeder::class);
//        $this->call(SMajorSeeder::class);
//        $this->call(SFacutlySeeder::class);
//        $this->call(ProvinceViSeeder::class);
//        $this->call(EditProjectDashboardAclSeeder::class);
//        $this->call(UpdateEmployeeWorkSeeder::class);
//        $this->call(ExportMemberPermissionSeeder::class);
//        $this->call(RemoveActionIdMenuCheckpointListSeeder::class);
//        $this->call(AppPasswordSeeder::class);
//        $this->call(AddRoleToTeamHistory::class);
//        $this->call(EmployeeAttachDefaultSeeder::class);
//        $this->call(TeamMailGroupSeeder::class);
//        $this->call(PassFileMailSeeder::class);
//        $this->call(SendPassFileMailSeeder::class);
//       $this->call(SynchronizeEmployeeProjExpresSeeder::class);
        $this->call(TeamUpdateFullNameSeeder::class);
        $this->call(RoleAddRolesSeeder::class);
    }
}

