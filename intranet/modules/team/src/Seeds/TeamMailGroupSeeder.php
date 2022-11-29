<?php
namespace Rikkei\Team\Seeds;

use DB;
use Rikkei\Team\Model\Team;
use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\View\MeView;

class TeamMailGroupSeeder extends CoreSeeder
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

        $arrayGroups = [
            'BOD' => 'bod',
            'Rikkei - Hanoi' => 'hanoi',
            'HC - TH' => 'hanoi',
            'Hanoi - IT' => 'hanoi_it',
            'Production' => 'prod',
            'D0' => 'division0',
            'D1' => 'division1',
            'D2' => 'division2',
            'D3' => 'division3',
            'D5' => 'division5',
            'D6' => 'division6',
            'QA' => 'qa',
            'Rikkei - Danang' => 'danang',
            'Rikkei - Japan' => 'japan',
        ];
        $teams = Team::all();
        if ($teams->isEmpty()) {
            return;
        }
        $mailDomain = '@rikkeisoft.com';
        $isEnvProd = (app()->environment() == 'production');
        try {
            foreach ($teams as $team) {
                if (isset($arrayGroups[$team->name])) {
                    $prefix = $arrayGroups[$team->name];
                    if (!$isEnvProd) {
                        $prefix = 'dev_' . $prefix;
                    }
                    $team->mail_group = $prefix . $mailDomain;
                    $team->save();
                }
            }
            //config data
            $devTeams = Team::select('mail_group')
                    ->whereNotNull('mail_group')
                    ->where('is_soft_dev', 1)
                    ->get();
            $defaultMail = CoreConfigData::getValueDb(MeView::KEY_MAIL_ACTIVITY);
            if (!$defaultMail) {
                CoreConfigData::create([
                    'key' => MeView::KEY_MAIL_ACTIVITY,
                    'value' => $devTeams->implode("mail_group", "\r\n")
                ]);
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
        }
    }
}
