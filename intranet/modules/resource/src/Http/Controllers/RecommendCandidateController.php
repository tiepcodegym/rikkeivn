<?php

namespace Rikkei\Resource\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config as SupportConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Resource\Http\Requests\RecommendPost;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\CandidateLanguages;
use Rikkei\Resource\Model\Channels;
use Rikkei\Resource\Model\Languages;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Permission as TeamPermission;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

class RecommendCandidateController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('resource');
        Breadcrumb::add('Candidate', route('resource::candidate.list.recommend'));
    }

    public function recommend()
    {
        $langs = Languages::getInstance()->getListWithLevel();
        $programs = Programs::getInstance()->getList();
        $langArray = Candidate::langWithLevel($langs);
        $allProgrammingLangs = null;
        $programmingLangs = null;
        return view('resource::candidate.recommend.create',
            compact([
                'langs', 'programs', 'langArray', 'allProgrammingLangs', 'programmingLangs'
            ])
        );
    }

    /**
     * Create a recommend candidate from user and send a notify to recruiter
     *
     * @param RecommendPost $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function createRecommend(RecommendPost $request)
    {
        $data = $request->only(
            'fullname',
            'email',
            'mobile',
            'birthday',
            'gender',
            'skype',
            'other_contact',
            'recruiter',
            'experience',
            'university',
            'certificate',
            'old_company',
            'comment',
            'programs',
            'inputYear',
            'languages'
        );
        $curEmp = Permission::getInstance()->getEmployee();
        $data['status'] = getOptions::CONTACTING;
        $data['found_by'] = $curEmp->id;
        $data['type_candidate'] = Candidate::TYPE_FROM_PRESENTER;

        // Khi ứng viên giới thiệu đã Fail hoặc nghỉ việc thì đc giới thiệu lại
        $listFailStatus = getOptions::getFailOrLeaveOptions();
        $checkEmail = Candidate::select('id')
            ->where('email', $data['email'])
            ->whereNotIn('status', $listFailStatus)
            ->count();

        if ($checkEmail) {
            return redirect()->back()->withInput()->withErrors(trans('resource::view.Recommend candidate is contacting'));
        }
        //save cv
        $file = $request->file('cv');
        $uploadCv = $this->uploadCV($file);
        if ($uploadCv) {
            $data['cv'] = $uploadCv['cv'];
            $data['received_cv_date'] = $uploadCv['received_cv_date'];
        }

        $checkCandidate = Candidate::where('email', $data['email'])->first();
        if ($checkCandidate) {
            $status = $checkCandidate->status;
            if (!in_array($status, getOptions::getFailOrLeaveOptions())) {
                return redirect()->back()->withInput()->withErrors(trans('resource::view.Recommend create failed'));
            }
            $data['parent_id'] = $checkCandidate->id;
        }

        // Get id from recruiter to send notify
        $mailNoti = $data['recruiter'];

        if (!$data['recruiter']) {
            $hrLeader = Team::getTeamByType(Team::TEAM_TYPE_HR)->getLeader();
            $data['recruiter'] = $mailNoti = $hrLeader->email;
        }
        $recruiter = Employee::getEmpByEmail($mailNoti);
        $data['created_by'] = $recruiter->id;
        $candidateLang = '';
        if ($data['languages']) {
            $arrayLanguage = array_map("trim", explode(",", $data['languages']));
            $candidateLang = Languages::getIdByName($arrayLanguage);
            unset($data['languages']);
        }
        DB::beginTransaction();
        try {
            // save and send notify
            $candidate = Candidate::getInstance()->insertOrUpdate($data);
            if ($candidateLang) {
                self::saveLanguagesData($candidateLang, $candidate->id);
            }
            if (isset($data['programs'])) {
                $arrayProgramLanguage = [];
                foreach ($data['programs'] as $key => $value) {
                    $arrayProgramLanguage[$value] = [
                        'exp_year' => 0,
                        'programming_id' => $value,
                    ];
                }
                $candidate->candidateProgramming()->sync($arrayProgramLanguage);
            }
            \RkNotify::put(
                $recruiter->id,
                trans('resource::view.The candidate :name has just been created then assign to you', ['name' => $data['fullname']]),
                route('resource::candidate.detail', ['id' => $candidate->id])
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(trans('resource::message.Create request error'), $exception->getMessage());
        }

        return redirect()->route('resource::candidate.list.recommend')->with('messages', ['success' => [trans('resource::view.Recommend candidate successful')]]);
    }

    public function listMyRecommend()
    {
        $langs = Languages::getInstance()->getListWithLevel();
        $programs = Programs::getInstance()->getList();
        $teamPermission = new TeamPermission();
        $hrAccounts = $teamPermission->getEmployeeByActionName('sendMailToCandidate.candidate')->pluck('email')->toArray();
        $langArray = Candidate::langWithLevel($langs);
        $allProgrammingLangs = null;
        $programmingLangs = null;
        $listFailStatus = getOptions::getFailOrLeaveOptions();
        $collectionModel = Candidate::getListRecommend();

        return view('resource::candidate.recommend.list',
            compact([
                'langs',
                'programs',
                'hrAccounts',
                'collectionModel',
                'langArray',
                'allProgrammingLangs',
                'programmingLangs',
                'listFailStatus'
            ]));
    }

    /**
     * Check unique candidate email recommend
     * @return json
     */
    public function checkMailRecommend()
    {
        $valid = false;
        if (Input::get('email')) {
            $id = Input::get('id');
            $email = Input::get('email');
            if (!Candidate::checkExistRecommend($email, $id)) {
                $valid = true;
            }
        }
        return response()->json($valid);
    }

    public function edit($id)
    {
        $recommendCandidate = Candidate::getCandidateById($id);
        if (!$recommendCandidate) {
            return redirect()->route('resource::candidate.list.recommend')->withErrors(trans('core::message.Not found entity'));
        }
        $curEmp = Permission::getInstance()->getEmployee()->id;
        if ($curEmp != $recommendCandidate->found_by) {
            return view('core::errors.permission_denied');
        }
        $branch = Team::getHrBranchByEmail($recommendCandidate->recruiter);
        $langs = Languages::getInstance()->getListWithLevel();
        $programs = Programs::getInstance()->getList();
        $hrAccounts = Team::getHrEmailByRegion($branch);
        $langArray = Candidate::langWithLevel($langs);
        $allLangs = CandidateLanguages::getListByCandidate($recommendCandidate->id);
        $langSelected = CandidateLanguages::getLangSelectedLabel($id);
        $pathFolder = url(SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER);
        $allProgrammingLangs = Candidate::getAllProgramOfCandidate($recommendCandidate);
        $programmingLangs = Candidate::getCandidateProgrammingById($id);
        return view('resource::candidate.recommend.create',
            compact([
                'langs',
                'programs',
                'hrAccounts',
                'langArray',
                'allProgrammingLangs',
                'programmingLangs',
                'recommendCandidate',
                'allLangs',
                'langSelected',
                'branch',
                'pathFolder'
            ])
        );
    }

    public function update(Request $request)
    {
        $data = $request->except('employee');
        $recommendCandidate = Candidate::find($data['candidate_id']);
        $curEmp = Permission::getInstance()->getEmployee()->id;

        if (!$recommendCandidate) {
            return redirect()->route('resource::candidate.list.recommend')->withErrors(trans('core::message.Not found entity'));
        }
        if ($curEmp != $recommendCandidate->found_by) {
            return view('core::errors.permission_denied');
        }

        DB::beginTransaction();
        try {
            //save cv
            $file = $request->file('cv');
            $uploadCv = $this->uploadCV($file);
            if ($uploadCv) {
                $data['cv'] = $uploadCv['cv'];
                $data['received_cv_date'] = $uploadCv['received_cv_date'];
            }

            $candidate = Candidate::getInstance()->insertOrUpdate($data);

            if (isset($data['languages'])) {
                $langOld = Candidate::getAllLangOfCandidate($candidate);
                $candidate->candidateLang()->detach($langOld);
                if ($data['languages']) {
                    $arrayLanguage = array_map("trim", explode(",", $data['languages']));
                    $candidateLang = Languages::getIdByName($arrayLanguage);
                    if ($candidateLang) {
                        self::saveLanguagesData($candidateLang, $candidate->id);
                    }
                }
                if (isset($data['programs'])) {
                    $arrayProgramLanguage = [];
                    foreach ($data['programs'] as $key => $value) {
                        $arrayProgramLanguage[$value] = [
                            'exp_year' => 0,
                            'programming_id' => $value,
                        ];
                    }
                    $candidate->candidateProgramming()->sync($arrayProgramLanguage);
                }
            }

            $recruiter = Employee::getEmpByEmail($data['recruiter']);
            \RkNotify::put(
                $recruiter->id,
                trans('resource::view.Information of candidate :name has just been updated', ['name' => $data['fullname']]),
                route('resource::candidate.detail', ['id' => $candidate->id])
            );
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(trans('resource::message.Update candidate error'), $exception->getMessage());
        }
        return redirect()->back()->with('messages', ['success' => [trans('resource::message.Update candidate success')]]);
    }

    public function reapplyEdit($id)
    {
        $recommendCandidate = Candidate::getCandidateById($id);
        if (!$recommendCandidate) {
            return redirect()->route('resource::candidate.list.recommend')->withErrors(trans('core::message.Not found entity'));
        }
        $curEmp = Permission::getInstance()->getEmployee()->id;
        if ($curEmp != $recommendCandidate->found_by) {
            return view('core::errors.permission_denied');
        }
        $checkEmail = $this->checkStatusIsFail($recommendCandidate->email);
        if (!$checkEmail) {
            return redirect()->back()->withInput()->withErrors(trans('resource::view.Recommend candidate is contacting'));
        }
        $branch = Team::getHrBranchByEmail($recommendCandidate->recruiter);
        $langs = Languages::getInstance()->getListWithLevel();
        $programs = Programs::getInstance()->getList();
        $hrAccounts = Team::getHrEmailByRegion($branch);
        $langArray = Candidate::langWithLevel($langs);
        $allLangs = CandidateLanguages::getListByCandidate($recommendCandidate->id);
        $langSelected = CandidateLanguages::getLangSelectedLabel($id);
        $pathFolder = url(SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER);
        $allProgrammingLangs = Candidate::getAllProgramOfCandidate($recommendCandidate);
        $programmingLangs = Candidate::getCandidateProgrammingById($id);
        $reapply = true;

        return view('resource::candidate.recommend.create',
            compact([
                'langs',
                'programs',
                'hrAccounts',
                'langArray',
                'allProgrammingLangs',
                'programmingLangs',
                'recommendCandidate',
                'allLangs',
                'langSelected',
                'pathFolder',
                'branch',
                'reapply'
            ])
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function reapplyRecommend(Request $request)
    {
        $data = $request->except('employee');
        $candidate = Candidate::find($data['candidate_id']);
        $curEmp = Permission::getInstance()->getEmployee()->id;
        if (!$candidate) {
            return redirect()->route('resource::candidate.list.recommend')->withErrors(trans('core::message.Not found entity'));
        }
        if ($curEmp != $candidate->found_by) {
            return view('core::errors.permission_denied');
        }

        $checkEmail = $this->checkStatusIsFail($candidate->email);
        if (!$checkEmail) {
            return redirect()->back()->withInput()->withErrors(trans('resource::view.Recommend candidate is contacting'));
        }
        $parent_id = Candidate::where('email', $candidate->email)->pluck('id')->first();

        $recruiter = Employee::getEmpByEmail($data['recruiter']);
        if (!$recruiter) {
            return redirect()->back()->withInput()->withErrors(trans('resource::view.Candidate.Create.Select recruiter'));
        }
        $data['status'] = getOptions::CONTACTING;
        $data['cv'] = $candidate->cv;
        $data['received_cv_date'] = $candidate->received_cv_date;
        $data['found_by'] = $candidate->found_by;
        $data['created_by'] = $recruiter->id;
        $data['type_candidate'] = Candidate::TYPE_FROM_PRESENTER;
        $data['parent_id'] = $parent_id;

        //save cv
        $file = $request->file('cv');
        $uploadCv = $this->uploadCV($file);
        if ($uploadCv) {
            $data['cv'] = $uploadCv['cv'];
            $data['received_cv_date'] = $uploadCv['received_cv_date'];
        }
        unset($data['candidate_id']);

        DB::beginTransaction();
        try {
            $reapplyCandidate = Candidate::getInstance()->insertOrUpdate($data);
            if ($request['programs']) {
                $arrayProgramLanguage = [];
                foreach ($request['programs'] as $key => $value) {
                    $arrayProgramLanguage[$value] = [
                        'exp_year' => 0,
                        'programming_id' => $value,
                    ];
                }
                $reapplyCandidate->candidateProgramming()->sync($arrayProgramLanguage);
            }
            if ($request['languages']) {
                $arrayLanguage = array_map("trim", explode(",", $data['languages']));
                $candidateLang = Languages::getIdByName($arrayLanguage);
                if ($candidateLang) {
                    self::saveLanguagesData($candidateLang, $reapplyCandidate->id);
                }
            }
            \RkNotify::put(
                $recruiter->id,
                trans('resource::view.【Rikkeisoft】 The candidate :name has just been re-apply', ['name' => $reapplyCandidate->fullname]),
                route('resource::candidate.detail', ['id' => $reapplyCandidate->id])
            );

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(trans('resource::message.Create request error'), $exception->getMessage());
        }

        return redirect()->route('resource::candidate.list.recommend')->with('messages', ['success' => [trans('resource::view.Recommend candidate successful')]]);
    }

    public function checkStatusIsFail($email)
    {
        $listFailStatus = getOptions::getFailOrLeaveOptions();
        $checkEmail = Candidate::where('email', $email)
            ->whereNotIn('status', $listFailStatus)
            ->count();

        if ($checkEmail) {
            return false;
        }
        return true;
    }

    /**
     *  Get list hr email by region
     * @return \Illuminate\Http\JsonResponse
     */
    public function SearchByRegion()
    {
        $data = Input::get();
        $teamCode = Team::listPrefixByRegion();
        if (isset($data['region'])) {
            $region = $teamCode[$data['region']];
        }
        $region = isset($region) ? $region : Team::CODE_PREFIX_HN;

        $hrAccount = Team::getHrEmailByRegion($region);

        return response()->json(['hrAccounts' => $hrAccount]);
    }

    public function uploadCV($file)
    {
        $data = '';
        $pathFolder = SupportConfig::get('general.upload_folder') . '/' . Candidate::UPLOAD_CV_FOLDER;
        if ($file) {
            $cv = View::uploadFile(
                $file,
                SupportConfig::get('general.upload_storage_public_folder') .
                '/' . Candidate::UPLOAD_CV_FOLDER,
                SupportConfig::get('services.file.cv_allow'),
                SupportConfig::get('services.file.cv_max')
            );
            $data = [
                'cv' => trim($pathFolder, '/') . '/' . $cv,
                'received_cv_date' => Carbon::now()->format('Y-m-d'),
            ];
        }
        return $data;
    }

    static function saveLanguagesData($data, $candidateId)
    {
        $insertData = [];
        foreach ($data as $levelId) {
            $insertData[] = [
                'candidate_id' => $candidateId,
                'lang_id' => $levelId,
                'lang_level_id' => null,
            ];
        }
        CandidateLanguages::insert($insertData);
    }

    public function ajaxGetListRecommendByChannel()
    {
        $data = Input::get();
        if (!$data['channel_id']) {
            return [];
        }
        $response = Channels::listRecommendByChannelId($data['channel_id'], $data['start'], $data['end']);

        return response()->json(['response' => $response]);
    }
}
