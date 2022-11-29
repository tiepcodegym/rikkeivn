<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(\Rikkei\Team\Seeds\DeleteAclSeeder::class);
        $this->call(\Rikkei\Team\Seeds\ActionSeeder::class);
        $this->call(\Rikkei\Core\Seeds\MenuItemsSeeder::class);
//        $this->call(\Rikkei\News\Seeds\BlogCategoriesSeeder::class);
//        $this->call(\Rikkei\Assets\Seeds\AssetSC::class);
//        $this->call(\Rikkei\Core\Seeds\ACoreSC::class);
         $this->call(\Rikkei\Team\Seeds\ATeamSC::class);
//        $this->call(\Rikkei\Project\Seeds\AProjSC::class);
//        $this->call(Rikkei\Resource\Seeds\WorkPlaceSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\UpdatePriorityIdSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\RequestsPrioritySeeder::class);
//
//        $this->call(Rikkei\Resource\Seeds\PermissionPublishSeeder::class);
//        $this->call(Rikkei\News\Seeds\ChangeConstantStatusNewsSeed::class);
//        $this->call(Rikkei\News\Seeds\OpinionSeed::class);
//        $this->call(Rikkei\Resource\Seeds\UpdateTypeCandidateSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\WorkPlaceSeeder::class);
//
//
//        $this->call(Rikkei\Help\Seeds\HelpCheckpointSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\ContentToJobQualificationsTableRequestsSeeder::class);
//        $this->call(Rikkei\Test\Seeds\ResultTestSeeder::class);
//
        $this->call(Rikkei\Sales\Seeds\CssTemplateDefaultVNSeeder::class);
        $this->call(Rikkei\Sales\Seeds\CssUpdateTemplateDefaultJapanV2Seeder::class);
        // $this->call(Rikkei\Sales\Seeds\CssTemplateDefaultSeeder::class);
        // $this->call(Rikkei\Sales\Seeds\CssTemplateDefaultJapanSeeder::class);
        // $this->call(Rikkei\Sales\Seeds\CssUpdateTemplateDefaultSeeder::class);
        // $this->call(Rikkei\Sales\Seeds\CssUpdateTemplateDefaultJapanSeeder::class);
        // $this->call(Rikkei\Sales\Seeds\CssTemplateDefaultAddQsExplainSeeder::class);
        // $this->call(Rikkei\Sales\Seeds\CssTemplateDefaultJapanAddQsExplainSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssCategorySeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssCategoryUpdateSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssQuestionSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssQuestionUpdateSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\ProjectTypeSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\ProjectTypeUpdateSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssMailConfigSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssCategoryEnglishSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssQuestionEnglishSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\CssCatQuestionSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\UpdateTypeCompanySeeder::class);


//        $this->call(Rikkei\Recruitment\Seeds\RecruitmentCampaignsSeeder::class);
//        $this->call(Rikkei\Recruitment\Seeds\RecruitmentAppliesSeeder::class);
//
//        $this->call(Rikkei\Test\Seeds\TypeSeeder::class);
//        $this->call(Rikkei\Test\Seeds\TestTypeSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\CandidateGmatTypeSeeder::class);
//        $this->call(Rikkei\Test\Seeds\TestTempSeeder::class);
//        $this->call(Rikkei\Test\Seeds\ResultDetailSeeder::class);
//        $this->call(Rikkei\Test\Seeds\TestHelpSeeder::class);

        $this->call(Rikkei\Resource\Seeds\UpdateProgramsSeeder::class);
//
//        $this->call(Rikkei\Resource\Seeds\LanguagesSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\ProgramsSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\ChannelSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\RequestTeamSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\AssetsTypesSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\AssetsItemSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\TeamFeatureSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\CandidateMoveRequestTeamPositionSeeder::class);
//
//        $this->call(\Rikkei\Resource\Seeds\TeamsFeatureUpdateSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\ProgramsUpdatePrimaryChartSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\TeamsFeatureUpdateTeamAliasSeeder::class);
//
//        $this->call(Rikkei\Resource\Seeds\LanguageLevelSeeder::class);
//
//        $this->call(Rikkei\Ticket\Seeds\AttributeSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\CandidateCvRenameSeeder::class);
//
//        $this->call(Rikkei\Tag\Seeds\TagSetSeeder::class);
       $this->call(Rikkei\Tag\Seeds\ProjectFieldListSeeder::class);
       $this->call(Rikkei\Tag\Seeds\TagDatabaseSeeder::class);
//
//        $this->call(Rikkei\Resource\Seeds\AddRoleCandidateSearchSeeder::class);
//
//        $this->call(\Rikkei\Resource\Seeds\ResourceDashboardYearSeeder::class);
//
        //seed error employee code (temp)

//        $this->call(\Rikkei\Resource\Seeds\EmployeeCardIdSeeder::class);
//        $this->call(\Rikkei\Sales\Seeds\RemoveAclCssPreviewFromCssDetailSeeder::class);
//        $this->call(Rikkei\Resource\Seeds\WorkPlaceSeeder::class);
//
//        $this->call(\Rikkei\Resource\Seeds\ResourceDashboardUpdateSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\CandidateGmatPointSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\HrWeeklyReportMenuSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\HrWeeklyReportPermissionSeeder::class);
//        $this->call(Rikkei\Sales\Seeds\ContractSeeder::class);
//        $this->call(\Rikkei\Event\Seeds\SalaryPermissionSeeder::class);
//        $this->call(\Rikkei\Event\Seeds\MailLangSeeder::class);
//        $this->call(\Rikkei\Event\Seeds\UpdateConfigEventEmailSeeder::class);
       $this->call(\Rikkei\Event\Seeds\SalaryAcademySeeder::class);

       $this->call(\Rikkei\Event\Seeds\UpdateLangBirthCustSeeder::class);
//
        // module help
//        $this->call(\Rikkei\Help\Seeds\HelpItemUpdateSeeder::class);
//        $this->call(\Rikkei\Help\Seeds\HelpUpdateSlugSeeder::class);
//        $this->call(Rikkei\Help\Seeds\HelpItemsSeeder::class);
//
//        $this->call(\Rikkei\Resource\Seeds\CddMoreInfoPermissionSeeder::class);
//        $this->call(\Rikkei\Sales\Seeds\RemoveAclEditForAddCustomerPermissionSeeder::class);
//
//        $this->call(\Rikkei\Document\Seeds\DocumentTypeSeeder::class);
//        $this->call(\Rikkei\Document\Seeds\DocumentPermissionSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\CddOfferResultSeeder::class);
//
//        $this->call(\Rikkei\Resource\Seeds\RemoveActionIdMenuCandidateListSeeder::class);
//
        // new run the last seeder
//        $this->call(Rikkei\News\Seeds\BlogRecountMetaSeeder::class);
//
//        $this->call(Rikkei\Resource\Seeds\CandidateLeavedOffSeeder::class);
//
        // statistic
//        $this->call(\Rikkei\Statistic\Seeders\GitlabUpdateUrlSeeder::class);
//        $this->call(\Rikkei\Resource\Seeds\ExportCddSearchPermissionSeeder::class);
//
        //profile
//
        //mail group seeder
//
//        $this->call(\Rikkei\Resource\Seeds\CandidateAddressSeeder::class);
//
        // call all seeder of module Manage time
//        $this->call(\Rikkei\ManageTime\Seeds\AManageTimeSC::class);
//        $this->call(Rikkei\Assets\Seeds\AssetInventoryTaskStatusSeeder::class);
//
//        $this->call(\Rikkei\Team\Seeds\TeamIsWorkingSeeder::class);
//        $this->call(\Rikkei\Team\Seeds\UpdateContentAchievement::class);
//        $this->call(\Rikkei\ManageTime\Seeds\SupplementReasonsSeeder::class);
//        $this->call(\Rikkei\ManageTime\Seeds\LeaveDayReasonSeeder::class);
//        $this->call(\Rikkei\Sales\Seeds\CssQuestionUpdateSeederV1::class);
//        $this->call(\Rikkei\Team\Seeds\CvProjectLangSeeder::class);
//        $this->call(\Rikkei\Team\Seeds\SFacutlySeeder::class);
//        $this->call(\Rikkei\ManageTime\Seeds\UpdateValueLeaveDayObonSeeder::class);
//        $this->call(\Rikkei\Team\Seeds\SkillProjResConvertSeeder::class);
//        $this->call(\Rikkei\Document\Seeds\DocumentPublishSeeder::class);
//        $this->call(\Rikkei\Sales\Seeds\UpdateDeletedAtCssTableSeeder::class);
//        $this->call(\Rikkei\Assets\Seeds\UpdateTeamPrefixOfEmployeeAssetRequest::class);
//        $this->call(\Rikkei\Project\Seeds\EvaluationAttribute::class);
//        $this->call(\Rikkei\HomeMessage\Seeds\InitHomeMessageSeeder::class);
//        $this->call(\Rikkei\Notify\Seeds\NotificationCategoriesSeeder::class);
//        $this->call(\Rikkei\Notify\Seeds\NotificationCategories2Seeder::class);
//        $this->call(\Rikkei\Notify\Seeds\NotifyFlagsSeeder::class);
//        $this->call(\Rikkei\HomeMessage\Seeds\WeekDaysTableSeeder::class);
//        $this->call(\Rikkei\Proposed\Seeds\ProposedCategoryTableSeeder::class);
//        $this->call(\Rikkei\Project\Seeds\UpdateCompanyProject::class);
//        $this->call(\Rikkei\Tag\Seeds\ConvertTagSkillSheetSeeder::class);
//        $this->call(\Rikkei\Tag\Seeds\MoveProjTagTypeSkillsheetSeeder::class);
//        $this->call(\Rikkei\Team\Seeds\AddEmplAttrValueProjectSeeder::class);
//        $this->call(\Rikkei\ManageTime\Seeds\UpdateLeaveDayReasonsTableSeeder::class);
       $this->call(\Rikkei\Project\Seeds\LeaveDayReasonCode::class);
//        $this->call(\Rikkei\Test\Seeds\LangGroupSeeder::class);
//        $this->call(\Rikkei\Test\Seeds\TestTypeCategoryLanguageSeeder::class);

//        $this->call(Rikkei\ManageTime\Seeds\workPlace::class);
//        $this->call(\Rikkei\Team\Seeds\UpdateTeamBOSeeder::class);
//        $this->call(\Rikkei\Project\Seeds\ProjectKindSeed::class);
//        $this->call(\Rikkei\ManageTime\Seeds\UpdateNameEnLeaveDayReasonsTableSeeder::class);
//        $this->call(\Rikkei\ManageTime\Seeds\UpdateNameJaLeaveDayReasonsTableSeeder::class);
//        $this->call(\Rikkei\ManageTime\Seeds\TimekeepingDataNotLateSeeder::class);
//        $this->call(\Rikkei\ManageTime\Seeds\TimekeepingInsertTimeInSeeder::class);
//        $this->call(\Rikkei\Core\Seeds\SetTeamEmployeeBusinessTripSeeder::class);
//       $this->call(\Rikkei\Project\Seeds\ProjectPointCssSeeder::class);
       $this->call(\Rikkei\Project\Seeds\ProjectBusinessSeed::class);
       $this->call(\Rikkei\Project\Seeds\ProjectBusinessUpdateSeeder::class);
       $this->call(\Rikkei\Project\Seeds\ProjectCategorySeed::class);
       $this->call(\Rikkei\Project\Seeds\ProjectSectorSeed::class);
       $this->call(\Rikkei\Project\Seeds\ProjectClassificationSeed::class);
       $this->call(\Rikkei\Project\Seeds\ProjectClassificationOtherSeeder::class);
       $this->call(\Rikkei\Project\Seeds\ProjectKindSeedOnsite::class);
       $this->call(\Rikkei\Project\Seeds\ProjectScopeSeed::class);
       $this->call(\Rikkei\Contract\Seeds\UpdateHrmContractId::class);
       $this->call(\Rikkei\News\Seeds\News10yearsSeed::class);
    }
}
