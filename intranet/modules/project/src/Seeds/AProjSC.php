<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;

class AProjSC extends CoreSeeder
{
    public function run()
    {   
//        $this->call(ProjectPointCssNullSeeder::class);
        $this->call(ProjectInformationSeeder::class);
//        $this->call(ProjectPointDefault::class);
//        $this->call(ProjectPointCssSeeder::class);
//        $this->call(ProjectCodeAutoSeeder::class);
//        $this->call(ProjectSourceServerSeeder::class);
//        $this->call(ProjectMemberFlatResource::class);
//        $this->call(ProjectReportResetPointSeeder::class);
//        $this->call(MEBaselineDateSeeder::class);
//        $this->call(ProjectBaselinePlanEffortPointSeeder::class);
//        $this->call(ProjectBaselineFirstReportSeeder::class);
//        $this->call(ProjectBaselineQualityDefectValueColorSeeder::class);
//        //Sửa lại level important project nếu không trong khoảng thì sửa về Low => 1
//        $this->call(EditLevelProjectRisk::class);
//        $this->call(AddSalerToProjectSeeder::class);
//        $this->call(RemoveSalerOfProjectDraftSeeder::class);
//
////        $this->call(EvaluationUpdatePoint::class);
//        //ME cập nhật lại điểm đi muộn + điểm tổng sau khi upload bảng chấm công
//        $this->call(EvaluationUpdateTimeSheet::class);
//        $this->call(EvaluationAttribute::class);
//        $this->call(EvaluationAssignee::class);
//        //ME convert lại điểm ME của các tiêu chí individual index
//        //$this->call(MEConvertPoint::class);
//        $this->call(MEAttributesTypeSeeder::class);
//       //remove null late_time ME
//        //$this->call(METimeSheetSeeder::class);
//        $this->call(MeActivityPermissionSeeder::class);
//
//        $this->call(RewardMeChangeStatus::class);
//        $this->call(ProjectRewardDefectUpdateSeeder::class);
//        $this->call(ProjectRewardBudgetCreateSeeder::class);
//        $this->call(ProjRewardMetasSeeder::class);
////        $this->call(OpportunityMenuSeeder::class);
//        //clone timesheet.upload update permisison to timesheet.eval.upload
//        $this->call(TimesheetPermissionSeeder::class);
        
    }
}
