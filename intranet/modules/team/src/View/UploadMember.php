<?php

namespace Rikkei\Team\View;

use Illuminate\Support\Facades\Artisan;
use Rikkei\Core\View\PublishQueueToJob;
use Rikkei\Core\View\View as ViewCore;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\View\TeamConst;
use Rikkei\Core\View\BaseHelper;
use Illuminate\Support\Facades\File;
use Rikkei\Team\Model\Country;
use Illuminate\Support\Str;
use Rikkei\Team\Model\RelationNames;
use Rikkei\Team\Model\MilitaryPosition;
use Rikkei\Team\Model\PartyPosition;
use Rikkei\Team\Model\UnionPosition;
use Rikkei\Team\Model\MilitaryRank;
use Rikkei\Team\Model\MilitaryArm;
use Rikkei\Team\Model\EmployeeMilitary;
use Rikkei\Team\Model\EmployeeHealth;
use Rikkei\Team\Model\EmployeeSchool;
use Rikkei\Team\Model\QualityEducation;
use Rikkei\Team\Model\EmployeeEducation;
use Rikkei\Resource\View\getOptions;
use Maatwebsite\Excel\Collections\RowCollection;
use Storage;
use Rikkei\Team\Model\EmployeeRelationship;

class UploadMember
{
    use BaseHelper;

    const FOLDER_UPLOAD = 'uploadmember';
    const FOLDER_APP = 'app';
    const EXTENSION_FILE = 'xlsx';
    const FOLDER_CORE_MEMBER = 'member';
    const FILE_MEMBER_SPLIT = 'member_split';
    const ACCESS_FILE = 'public';
    const FILE_PROCESS = 'uploading_member';
    const FOLDER_PROCESS = 'process';
    const FOLDER_SUCESS = 'success';

    const RUN_WAIT = 1;
    const RUN_PROCESS = 2;
    const RUN_ERROR = 3;

    const ACCESS_FOLDER = 0777;
    protected $file;
    protected $folks = [];
    /**
     *  upload file member csv
     *  @param object
     */
    public function uploadMember($file = null)
    {
        if (!$file->isValid()) {
            $this->$file = null;
            return false;
        }
        $this->file = $file;
        $result = $this->updateMember();
        File::delete($file->getRealPath());
        return $result;
    }

    /*
     * convert working type from excel file to defined in system
     */
    public static function convertContractType()
    {
        return [
            1 => getOptions::WORKING_UNLIMIT,
            2 => getOptions::WORKING_OFFICIAL,
            3 => getOptions::WORKING_PARTTIME,
            4 => getOptions::WORKING_PROBATION,
            5 => getOptions::WORKING_INTERNSHIP,
            6 => getOptions::WORKING_BORROW,
        ];
    }

    /**
     * Create directory
     * @param string $folder
     */
    public function createFolder($folder = self::FOLDER_UPLOAD)
    {
        if (!Storage::exists($folder)) {
            Storage::makeDirectory($folder, self::ACCESS_FOLDER);
            @chmod(storage_path('app/' . $folder), self::ACCESS_FOLDER);
        } else {
            $files = Storage::files($folder);
            if ($files) {
                Storage::delete($files);
            }
        }
    }

    /**
     * move file uploaded
     * @param string $file
     */
    public function storeFile($file)
    {
        $this->createFolder();
        Storage::put(
            self::FOLDER_UPLOAD . '/' . auth()->id() . '.' . $file->getClientOriginalExtension(),
            file_get_contents($file->getRealPath()),
            'public'
        );
        if (Storage::exists(self::FOLDER_SUCESS . '/uploadmember/' . auth()->id() . '.json')) {
            Storage::delete(self::FOLDER_SUCESS . '/uploadmember/' . auth()->id() . '.json');
        }
    }

    /**
     * checking processing data
     * @return boolean
     */
    public function checkProcessing()
    {
        if (Storage::exists(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS)) {
            return true;
        }
        return false;
    }

    /**
     * cronjob do update member
     * @return void
     * @throws Exception
     */
    public function doUpdateMember()
    {
        if ($this->checkProcessing()) {
            return;
        }
        $files = Storage::files(self::FOLDER_UPLOAD);
        if (!$files) {
            return;
        }
        $file = $files[0];
        Storage::put(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS, 1,self::ACCESS_FILE);
        try {
            $this->file = storage_path('app/' . $file);
            $result = $this->updateMember();

            // create file success
            $this->createFolder(self::FOLDER_SUCESS);
            $userId = explode('.', $file)[0];
            $successText = json_encode($result);
            $successFile = self::FOLDER_SUCESS . '/' . $userId . '.json';
            if (Storage::exists($successFile)) {
                Storage::delete($successFile);
            }
            Storage::put($successFile, $successText, 'public');

            Storage::delete($file);
            Storage::delete(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS);
        } catch (\Exception $ex) {
            Storage::delete($file);
            Storage::delete(self::FOLDER_PROCESS . '/' . self::FILE_PROCESS);
            throw $ex;
        }
    }

    public function checkUploaded()
    {
        $successFile = self::FOLDER_SUCESS . '/uploadmember/' . auth()->id() . '.json';
        if (!Storage::exists($successFile)) {
            return false;
        }
        $pathFile = storage_path('app/' . $successFile);
        $results = file_get_contents($pathFile);
        Storage::delete($successFile);
        return json_decode($results, true);
    }

    /**
     * update position member in team
     * 
     * @return boolean
     */
    public function updateMember()
    {
        $errors = [];
        $count = 0;
        DB::beginTransaction();
        try {
            Excel::selectSheetsByIndex(0)
                ->load($this->file,  function ($reader) use 
                (&$errors, &$count)
            {
                $reader->formatDates(true, 'Y-m-d');
                $this->excuteFile($reader, $errors, $count);
            });
            DB::commit();
            return [
                'errors' => $errors,
                'count' => $count
            ];
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();
            throw $ex;
        }
    }

    public function excuteFile($reader, &$errors, &$count)
    {
        //OptionCore::setMemoryMax();
        $this->folks = $this->slugArray(EmpLib::getInstance()->folk());
        $this->religions = $this->slugArray(EmpLib::getInstance()->relig());
        $this->countries = $this->slugArray(Country::getAll());
        $this->relations = $this->slugArray(RelationNames::getAllRelations());
        $this->militaryPos = $this->slugArray(MilitaryPosition::getAll());
        $this->partyPos = $this->slugArray(PartyPosition::getAll());
        $this->unionPos = $this->slugArray(UnionPosition::getAll());
        $this->militaryRank = $this->slugArray(MilitaryRank::getAll());
        $this->militaryArm = $this->slugArray(MilitaryArm::getAll());
        $this->militaryArm = $this->slugArray(MilitaryArm::getAll());
        $this->militaryLevel = [
            EmployeeMilitary::SOLDIER_LEVEL_1 => 'hang-14',
            EmployeeMilitary::SOLDIER_LEVEL_2 => 'hang-24',
            EmployeeMilitary::SOLDIER_LEVEL_3 => 'hang-34',
            EmployeeMilitary::SOLDIER_LEVEL_4 => 'hang-44',
        ];
        $this->eduDegree = [
            EmployeeSchool::EDU_DEG_GREAT => 'gioi',
            EmployeeSchool::EDU_DEG_MIDDLE => 'kha',
            EmployeeSchool::EDU_DEG_ABOVE_AVERAGE => 'trung-binh-kha',
            EmployeeSchool::EDU_DEG_MEDIUM => 'trung-binh',
            EmployeeSchool::EDU_DEG_EXCELLENT => 'xuat-sac',
        ];
        $this->eduQuality = $this->slugArray(QualityEducation::getAll());
        $this->healthBlood = $this->slugArray(EmployeeHealth::toOptionBloodType());
        $this->workContract = [
            1 => 'hop-dong-khong-xac-dinh-thoi-han',
            2 => 'hop-dong-xac-dinh-thoi-han',
            3 => 'hop-dong-mua-vu',
            4 => 'thu-viec',
            5 => 'hoc-viec',
            6 => 'thue-ngoai'
        ];
        $this->teamsFlag = static::getTeamplateInFlagTeamCode("code");
        $this->positionsFlag = $this->getFlagPositionCode();
        $this->teamCodesFlag = array_keys($this->teamsFlag);

        $dataRecord = $reader->get();
        $emailsIgnore = $this->ignoreEmail();
        $cols = $this->excelCols();
        $emailsInFile = [];
        $codesInFile = [];
        //all cols fillable employee + id
        $empFillableCols = Employee::getFillableCols();
        array_unshift($empFillableCols, 'id');
        //all employees
        $employeesDB = Employee::select($empFillableCols)->get();
        $codesInDB = $employeesDB->lists('id', 'employee_code')->toArray();
        $employeesDBEmail = $employeesDB->groupBy('email');
        foreach ($dataRecord as $key => $itemRow) {
            //require fields
            if (!$itemRow->ma_nhan_vien &&
                !$itemRow->ho_va_ten && 
                !($itemRow->email_co_quan || $itemRow->email_rikkei)
            ) {
                continue;
            }
            if (!$itemRow->ma_nhan_vien ||
                !$itemRow->ho_va_ten || 
                !($itemRow->email_co_quan || $itemRow->email_rikkei)
            ) {
                $errors[] = trans('team::messages.Row :row: miss id, name or email', ['row' => $key + 2]);
                continue;
            }
            $rowEmail = strtolower(trim($itemRow->email_co_quan));
            if (!$rowEmail) {
                $rowEmail = strtolower(trim($itemRow->email_rikkei));
            }
            $employee = isset($employeesDBEmail[$rowEmail]) ? $employeesDBEmail[$rowEmail]->first() : null;
            //if has employee then update else create
            if (!$employee) {
                if (!$itemRow->ma_vi_tri_cong_viec &&
                    !$itemRow->ma_don_vi_cong_tac
                ) {
                    continue;
                }
                if (!$itemRow->ma_vi_tri_cong_viec ||
                    !$itemRow->ma_don_vi_cong_tac ||
                    in_array($rowEmail, $emailsIgnore)
                ) {
                    $errors[] = trans('team::messages.Row :row: miss team', ['row' => $key + 2]);
                    continue;
                }
            } else {
                //if employee code not match then return error
                $rowEmpCode = trim($itemRow->ma_nhan_vien);
                if ($rowEmpCode != $employee->employee_code) {
                    $errors[] = trans('team::messages.Row :row: employee code not match', ['row' => $key + 2]);
                    continue;
                }
            }
            if (!ViewCore::isEmailAllow($rowEmail)) {
                $errors[] = trans('team::messages.Row :row: email isnot Rikkeisoft email', ['row' => $key+2]);
                continue;
            }
            if (in_array($rowEmail, $emailsInFile)) {
                $errors[] = trans('team::messages.Row :row: email :email duplicate', ['row' => $key + 2, 'email' => $rowEmail]);
                continue;
            }
            if (in_array($itemRow->ma_nhan_vien, $codesInFile)) {
                $errors[] = trans('team::messages.Row :row: employee code :code duplicate', ['row' => $key + 2, 'code' => $itemRow->ma_nhan_vien]);
                continue;
            }
            $emailsInFile[] = $rowEmail;
            $codesInFile[] = $itemRow->ma_nhan_vien;
            $itemRow = $itemRow->toArray();
            $data = $this->excelColToDb($itemRow, $cols);
            // save basic info
            if (!$employee) {
                $employee = new Employee();
                $employee->nickname = $data['employees']['email'];
                $employee->join_date = date("Y-m-d 0:0:0");
            }
            if (isset($codesInDB[$data['employees']['employee_code']]) &&
                $codesInDB[$data['employees']['employee_code']] != $employee->id
            ) {
                $errors[] = trans('team::messages.Row :row: employee code :code duplicate with employee id :id', [
                    'row' => $key+1, 'code' => $data['employees']['employee_code'], 'id' => $codesInDB[$data['employees']['employee_code']]
                ]);
                continue;
            }
            // exec join date and offical date
            // $this->execJoindate($data, $employee); ignore this
            // update employee
            $employee->deleted_at = null;
            $dataEmployee = array_only($data['employees'], $employee->getDBColumns());
            $employee->setData($dataEmployee, true);
            $employee->save();
            $this->updateRelative($employee, $data, 'contact');
            $this->updateRelative($employee, $data, 'work');
            $this->updateRelative($employee, $data, 'politic');
            $this->updateRelative($employee, $data, 'military');
            $this->updateRelative($employee, $data, 'health');
            $this->updateRelative($employee, $data, 'hobby');
            try{
                PublishQueueToJob::makeInstance()->cacheRole($employee->id);
            }
            catch (\Exception $exception){
                Log::error($exception->getMessage());
                continue;
            }

            // update education
            if (isset($data['employee_educations'])) {
                $this->insertEducation($employee, $data['employee_educations']);
            }
            // update team position
            if (isset($data['team'])) {
                $this->insertTeam($employee, $data['team']);
            }
            $count++;           
            /*$labelTeam = Team::getLabelTeamSpecial();
            if (array_key_exists($data['ten_vi_tri_cong_viec'], $labelTeam)) {
                $positionInfo = Team::teamSpecial($data['ten_vi_tri_cong_viec']);
                if ($positionInfo && $positionInfo['team_id'] && $positionInfo['role_id']) {
                    if(!TeamMember::checkTeamMember($positionInfo['team_id'], $employee->id, $positionInfo['role_id'])) {
                        $teamMember = new TeamMember();
                        $teamMember->team_id = $positionInfo['team_id'];
                        $teamMember->employee_id = $employee->id;
                        $teamMember->role_id = $positionInfo['role_id'];
                        $teamMember->save();
                        if ($positionInfo['role_id'] == Team::ROLE_TEAM_LEADER) {
                            $team = Team::find($positionInfo['team_id']);
                            if (!$team->leader_id) {
                                $team->leader_id = $employee->id;
                                $team->save();
                            }
                        }
                    }
                } 
            }*/
            /*
            // save team leader
             * if (!$employee->isLeader()) {
                $role = Team::getPositionTeam($data['ten_vi_tri_cong_viec']);
                if(!TeamMember::checkTeamMember($teamIdInput, $employee->id, $role)) {
                    if ($teamIdInput) {
                        $teamMember = new TeamMember();
                        $teamMember->team_id = $teamIdInput;
                        $teamMember->employee_id = $employee->id;
                        $teamMember->role_id = $role;
                        $teamMember->save();
                        if ($teamMember->role_id == Team::ROLE_TEAM_LEADER) {
                            $team = Team::find($teamMember->team_id);
                            $team->leader_id = $employee->id;
                            $team->save();
                        }
                    }
                }
            }
            if (count($teamMembers)) {
                $role = Team::getPositionTeam($data['ten_vi_tri_cong_viec']);
                if ($teamIdInput) {
                    foreach ($teamMembers as $key => $teamMember) {
                        if($teamIdInput == $teamMember->team_id) {
                            $team = Team::find($teamIdInput);
                            if ($team && !$team->leader_id && $role == Team::ROLE_TEAM_LEADER) {
                                $team->leader_id = $employee->id;
                                $team->save();
                            }
                        }
                    }
                }
            } else {
                // save team of member and team leader
                $role = Team::getPositionTeam($data['ten_vi_tri_cong_viec']);
                if(!TeamMember::checkTeamMember($teamIdInput, $employee->id, $role)) {
                    if ($teamIdInput) {
                        $teamMember = new TeamMember();
                        $teamMember->team_id = $teamIdInput;
                        $teamMember->employee_id = $employee->id;
                        $teamMember->role_id = $role;
                        $teamMember->save();
                        if ($teamMember->role_id == Team::ROLE_TEAM_LEADER) {
                            $team = Team::find($teamMember->team_id);
                            if (!$team->leader_id) {
                                $team->leader_id = $employee->id;
                                $team->save();
                            }
                        }
                    }
                }
            }*/
        }
    }

    public function excuteFileFamilyInfo($reader, &$errors, &$count)
    {
        $dataRecord = $reader->get();
        $data = [];
        foreach ($dataRecord as $key => $itemRow) {
            $keyItem = $key + 2;
            if (strlen($itemRow->employee_code) == 0 || strlen($itemRow->full_name) == 0 || strlen($itemRow->relationship) == 0) {
                $errors[] = 'Row '.$keyItem. ' missing Employee Code, Full name or Relationship';
                continue;
            }

            $employee = Employee::where('employee_code', $itemRow->employee_code)->first();
            if (!$employee) {
                $errors[] = 'Row '.$keyItem. ' This employee with code '.$itemRow->employee_code. ' does not exist';
                continue;
            }

            $arrRelations = array_keys(RelationNames::getAllRelations());
            if (!in_array($itemRow->relationship, $arrRelations)) {
                $errors[] = 'Row '.$keyItem. ' Relationship is invalid.';
                continue;
            }

            if (strlen($itemRow->nationality) != 0) {
                $arrNationalities = array_keys(Country::getAll());
                if (!in_array($itemRow->nationality, $arrNationalities)) {
                    $errors[] = 'Row '.$keyItem. ' Nationality is invalid.';
                    continue;
                }
            }
            
            if (strlen($itemRow->email) != 0) {
                $rowEmail = strtolower(trim($itemRow->email));
                $pattern = '/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+)(\.)([a-z0-9\-]+)$/ix';
                if (!preg_match($pattern, $rowEmail, $maches)){
                    $errors[] = 'Row '.$keyItem. ' Email is invalid.';
                    continue;
                }
            }

            if (strlen($itemRow->dependent_person) != 0) {
                if (!in_array($itemRow->dependent_person, ["0", "1"])) {
                    $errors[] = 'Row '.$keyItem. ' Dependent person is invalid.';
                    continue;
                }
            }

            if (strlen($itemRow->dead) != 0) {
                if (!in_array($itemRow->dead, ["0", "1"])) {
                    $errors[] = 'Row '.$keyItem. ' Dead person is invalid.';
                    continue;
                }
            }
            $count++;
            $data[] = [
                'employee_id' => $employee->id,
                'name' => $itemRow->full_name,
                'relationship' => $itemRow->relationship,
                'date_of_birth' => $itemRow->birthday,
                'national' => $itemRow->nationality,
                'id_number' => $itemRow->passport,
                'mobile' => $itemRow->mobile,
                'tel' => $itemRow->home_phone,
                'address' => $itemRow->address,
                'email' => $itemRow->email,
                'tax_code' => $itemRow->personal_tax_code,
                'career' => $itemRow->job,
                'is_dependent' => $itemRow->dependent_person,
                'is_die' => $itemRow->dead,
            ];
        }

        if (!$errors) {
            $insert_data = collect($data);
            $chunks = $insert_data->chunk(1000);
            foreach ($chunks as $chunk) {
                EmployeeRelationship::insert($chunk->toArray());
            }
        }
    }

    /**
     * Not import employee have email
     * 
     * @return array
     */
    public function emailIgnore()
    {
        return [
            'intranet@rikkeisoft.com',
            'event@rikkeisoft.com'
        ];
    }

    /**
     * get label of team
     * @return array
     */
    public static function getFlagTeamCode()
    {
        return [
            'RDN-BGD' => ['code' => TeamConst::CODE_BOD, 'title' => 'Team BOD'],
            'RJP-BGD' => ['code' => TeamConst::CODE_BOD, 'title' => 'Team BOD'],
            'TCT-BGD' => ['code' => TeamConst::CODE_BOD, 'title' => 'Team BOD'],
            'D0' => ['code' => TeamConst::CODE_HN_D0, 'title' => 'Team D0'],
            'D1' => ['code' => TeamConst::CODE_HN_D1, 'title' => 'Team D1'],
            'D2' => ['code' => TeamConst::CODE_HN_D2, 'title' => 'Team D2'],
            'D3' => ['code' => TeamConst::CODE_HN_D3, 'title' => 'Team D3'],
            'D5' => ['code' => TeamConst::CODE_HN_D5, 'title' => 'Team D5'],
            'D6' => ['code' => TeamConst::CODE_HN_D6, 'title' => 'Team D6'],
            'D8' => ['code' => TeamConst::CODE_HN_D8, 'title' => 'Team D8'],
            'PTPM-QA' => ['code' => TeamConst::CODE_HN_QA, 'title' => 'Team đảm bảo chất lượng'],
            'BCN' => ['code' => TeamConst::CODE_HN_BCN, 'title' => 'Team BCN'],
            'TRAINING' => ['code' => TeamConst::CODE_HN_TRAINING, 'title' => 'Team Đào Tạo'],
            'VD' => ['code' => TeamConst::CODE_HN_VD, 'title' => 'Team VD'],
            'GD' => ['code' => TeamConst::CODE_HN_GD, 'title' => 'Team GD'],
            'SYSTENA' => ['code' => TeamConst::CODE_HN_SYSTENA, 'title' => 'Team SYSTENA'],
            'HC' => ['code' => TeamConst::CODE_HN_HC, 'title' => 'Team HC'],
            'PR' => ['code' => TeamConst::CODE_HN_PR, 'title' => 'Team PR'],
            'RDN-PTPM' => ['code' => TeamConst::CODE_DANANG, 'title' => 'Team Đà Nẵng'],
            'RDN-DN0' => ['code' => TeamConst::CODE_DN_D0, 'title' => 'Team DN0'],
            'RDN-DN1' => ['code' => TeamConst::CODE_DN_D1, 'title' => 'Team DN1'],
            'RDN-DN2' => ['code' => TeamConst::CODE_DN_D2, 'title' => 'Team DN2'],
            'RDN-DN3' => ['code' => TeamConst::CODE_DN_D3, 'title' => 'Team DN3'],
            'RDN-IT' => ['code' => TeamConst::CODE_DN_IT, 'title' => 'Team IT Đà Nẵng'],
//            'RDN-HCTH' => ['code' => TeamConst::CODE_DN_HCTH, 'title' => 'Team Đà Nẵng'],
            'PTPM-PROD' => ['code' => TeamConst::CODE_HN_PRODUCTION, 'title' => 'Team Sản phẩm'],
            'TCT-PKT' => ['code' => TeamConst::CODE_HN_HCTH, 'title' => 'Team hành chính tổng hợp Hà Nội'],
            'TCT-HCTH' => ['code' => TeamConst::CODE_HN_HCTH, 'title' => 'Team hành chính tổng hợp Hà Nội'],
            'RDN_HCTH' => ['code' => TeamConst::CODE_DANANG, 'title' => 'Team Đà Nẵng'],
            'TCT-NS' => ['code' => TeamConst::CODE_HN_HR, 'title' => 'Team nhân sự Hà Nội'],
            'RJP' => ['code' => TeamConst::CODE_JAPAN, 'title' => 'Team Nhật Bản'],
            'RJP-HCTH' => ['code' => TeamConst::CODE_JAPAN_HCTH, 'title' => 'Team Nhật Bản'],
            'RJP-SALE' => ['code' => TeamConst::CODE_JAPAN_SALE, 'title' => 'Team Sale Nhật Bản'],
            'RJP-DEV' => ['code' => TeamConst::CODE_JAPAN_DEV, 'title' => 'Team Nhật Bản'],
            'TCT-SALES' => ['code' => TeamConst::CODE_HN_SALES, 'title' => 'Team sale Hà Nội'],
            'TCT-IT' => ['code' => TeamConst::CODE_HN_IT, 'title' => 'Team IT Hà Nội'],
            'RDN' => ['code' => TeamConst::CODE_DANANG, 'title' => 'Team Đà Nẵng'],
            'RS' => ['code' => TeamConst::CODE_RS, 'title' => 'Team RS'],
            'AI' => ['code' => TeamConst::CODE_AI, 'title' => 'Team AI'],
            'RHCM' => ['code' => TeamConst::CODE_HCM, 'title' => 'Team Hồ Chí Minh'],
            'RHCM-PTPM' => ['code' => TeamConst::CODE_HCM_PTPM, 'title' => 'Team Hồ Chí Minh'],
            'RHCM-IT' => ['code' => TeamConst::CODE_HCM_IT, 'title' => 'Team IT Hồ Chí Minh'],
            'RHCM-HCTH' => ['code' => TeamConst::CODE_HCM_HCTH, 'title' => 'Team Hồ Chí Minh'],
        ];
    }

    /**
     * get label of team
     * @return array
     */
    public static function getFlagPositionCode()
    {
        return [
            'PTPM-DEV' => Team::ROLE_MEMBER,
            'QA-MEMBER' => Team::ROLE_MEMBER,
            'RDN-DEV' => Team::ROLE_MEMBER,
            'KT-MEMBER' => Team::ROLE_MEMBER,
            'IT-MEMBER' => Team::ROLE_MEMBER,
            'HCKTDN' => Team::ROLE_MEMBER,
            'HR-MEMBER' => Team::ROLE_MEMBER,
            'HR-GVTN' => Team::ROLE_MEMBER,
            'HCTH-TV' => Team::ROLE_MEMBER,
            'SALES-MEMBER' => Team::ROLE_MEMBER,
            'HR-PR' => Team::ROLE_MEMBER,
            'HR-TRANING' => Team::ROLE_MEMBER,
            'Member' => Team::ROLE_MEMBER,
            
            'VCOO' => Team::ROLE_SUB_LEADER,
            'PTPM-SUBLEAD' => Team::ROLE_SUB_LEADER,
            'QA-SUBLEAD' => Team::ROLE_SUB_LEADER,
            'PTGD' => Team::ROLE_SUB_LEADER,
            'TGD' => Team::ROLE_SUB_LEADER,
            'Sub-Leader' => Team::ROLE_SUB_LEADER,
            
            'CTHDQT' => Team::ROLE_TEAM_LEADER,
            'GDDN' => Team::ROLE_TEAM_LEADER,
            'HR-LEAD' => Team::ROLE_TEAM_LEADER,
            'PTPM-LEADER' => Team::ROLE_TEAM_LEADER,
            'KT-LEAD' => Team::ROLE_TEAM_LEADER,
            'Team Leader' => Team::ROLE_TEAM_LEADER,

            'RS-MEMBER' => Team::ROLE_MEMBER,
            'RS-SUBLEAD' => Team::ROLE_SUB_LEADER,
            'RS-LEAD' => Team::ROLE_TEAM_LEADER,
            'AI-MEMBER' => Team::ROLE_MEMBER,
            'AI-SUBLEAD' => Team::ROLE_SUB_LEADER,
            'AI-LEAD' => Team::ROLE_TEAM_LEADER,
        ];
    }

    /**
     * ignore email import
     * 
     * @return array
     */
    protected function ignoreEmail()
    {
        return [
            'intranet@rikkeisoft.com',
            'event@rikkeisoft.com',
        ];
    }

    /**
     * format excel col
     *
     * @return array
     */
    protected function excelCols()
    {
        //format table, column, callback function convert data
        return [
            //base
            'ma_nhan_vien' =>           ['employees', 'employee_code'],
            'ma_cham_cong' =>           ['employees', 'employee_card_id'],
            'ho_va_ten' =>              ['employees', 'name'],
            'gioi_tinh' =>              ['employees', 'gender', 'convertTextGender'],
            'ten_jp' =>                 ['employees', 'japanese_name'],
            'ngay_sinh' =>              ['employees', 'birthday', 'convertTextDate'],
            'so_cmnd' =>                ['employees', 'id_card_number'],
            'ngay_cap_cmnd' =>          ['employees', 'id_card_date', 'convertTextDate'],
            'noi_cap_cmnd' =>           ['employees', 'id_card_place'],
            'so_ho_chieu' =>            ['employees', 'passport_number'],
            'ngay_cap_ho_chieu' =>      ['employees', 'passport_date_start', 'convertTextDate'],
            'ngay_het_han' =>           ['employees', 'passport_date_exprie', 'convertTextDate'],
            'noi_cap_ho_chieu' =>       ['employees', 'passport_addr'],
            'tinh_trang_hon_nhan' =>    ['employees', 'marital', 'convertTextMarital'],
            'dan_toc' =>                ['employees', 'folk', 'convertTextFolk'],
            'ton_giao' =>               ['employees', 'religion', 'convertTextReligions'],
            'email_co_quan' =>          ['employees', 'email'],
            'email_rikkei' =>           ['employees', 'email'],
            'ngay_thu_viec' =>          ['employees', 'trial_date', 'convertTextDate'],
            'ngay_chinh_thuc' =>        ['employees', 'offcial_date', 'convertTextDate'],
            'ngay_nghi_viec' =>         ['employees', 'leave_date', 'convertTextDate'],
            'ngay_ket_thuc_thu_viec' => ['employees', 'trial_end_date', 'convertTextDate'],
            'ngay_gia_nhap' =>          ['employees', 'join_date', 'convertTextDate'],
            'ly_do_nghi' =>             ['employees', 'leave_reason'],
            //contact
            'dt_co_quan' =>                         ['contact', 'office_phone'],
            'dt_di_dong' =>                         ['contact', 'mobile_phone'],
            'dt_nha_rieng' =>                       ['contact', 'home_phone'],
            'dt_khac' =>                            ['contact', 'other_phone'],
            'email_ca_nhan' =>                      ['contact', 'personal_email'],
            'email_khac' =>                         ['contact', 'other_email'],
            'yahoo_id' =>                           ['contact', 'yahoo'],
            'skype_id' =>                           ['contact', 'skype'],
            'ho_khau_thuong_tru' =>                 ['contact', 'native_addr'],
            'quoc_gia_ho_khau_thuong_tru' =>        ['contact', 'native_country', 'convertTextCountry'],
            'tinhthanh_pho_ho_khau_thuong_tru' =>   ['contact', 'native_province'],
            'quanhuyen_ho_khau_thuong_tru' =>       ['contact', 'native_district'],
            'xaphuong_ho_khau_thuong_tru' =>        ['contact', 'native_ward'],
            'cho_o_hien_nay' =>                     ['contact', 'tempo_addr'],
            'quoc_gia_cho_o_hien_nay' =>            ['contact', 'tempo_country', 'convertTextCountry'],
            'tinhthanh_pho_cho_o_hien_nay' =>       ['contact', 'tempo_province'],
            'quanhuyen_cho_o_hien_nay' =>           ['contact', 'tempo_district'],
            'xaphuong_cho_o_hien_nay' =>            ['contact', 'tempo_ward'],
            'ho_va_ten_nguoi_lien_he_khan_cap' =>   ['contact', 'emergency_contact_name'],
            'quan_he_nguoi_lien_he_khan_cap' =>     ['contact', 'emergency_relationship', 'convertTextRelations'],
            'dt_nha_rieng_nguoi_lien_he_khan_cap' =>['contact', 'emergency_contact_mobile'],
            'dt_di_dong_nguoi_lien_he_khan_cap' =>  ['contact', 'emergency_mobile'],
            'dia_chi_nguoi_lien_he_khan_cap' =>     ['contact', 'emergency_addr'],
            //education
            'noi_dao_tao' =>        ['employee_educations', 'school'],
            'khoa' =>               ['employee_educations', 'faculty'],
            'chuyen_nganh' =>       ['employee_educations', 'majors'],
            'nam_tot_nghiep' =>     ['employee_educations', 'end_at'],
            'xep_loai' =>           ['employee_educations', 'degree', 'convertTextEduDegree'],
            'trinh_do_dao_tao' =>   ['employee_educations', 'quality', 'convertTextEduQuality'],
            // team
            'ma_vi_tri_cong_viec' =>    ['team', 'position'],
            'ma_don_vi_cong_tac' =>     ['team', 'team'],
            //work
            'ma_so_thue_ca_nhan' =>             ['work', 'tax_code'],
            'tai_khoan_ngan_hang' =>            ['work', 'bank_account'],
            'ma_ngan_hang' =>                   ['work', 'bank_name'],
            'loai_hop_dong' =>                  ['work', 'contract_type', 'convertTextWorkContract'],
            'so_so_bhxh' =>                     ['work', 'insurrance_book'],
            'tham_gia_bao_hiem_tu' =>           ['work', 'insurrance_date', 'convertTextDate'],
            'ti_le_dong' =>                     ['work', 'insurrance_ratio'],
            'so_the_bhyt' =>                    ['work', 'insurrance_h_code'],
            'ngay_het_han_bhyt' =>              ['work', 'insurrance_h_expire', 'convertTextDate'],
            'ma_noi_dang_ky_kham_chua_benh' =>  ['work', 'register_examination_place'],
            //politic
            'la_dang_vien' =>       ['politic', 'is_party_member', 'convertTextBoolean'],
            'ngay_vao_dang' =>      ['politic', 'party_join_date', 'convertTextDate'],
            'chuc_vu_dang' =>       ['politic', 'party_position', 'convertTextPoliticPartyPos'],
            'noi_ket_nap_dang' =>   ['politic', 'party_join_place'],
            'la_doan_vien' =>       ['politic', 'is_union_member', 'convertTextBoolean'],
            'ngay_vao_doan' =>      ['politic', 'union_join_date', 'convertTextDate'],
            'chuc_vu_doan' =>       ['politic', 'union_poisition', 'convertTextPoliticUnionPos'],
            'noi_ket_nap_doan' =>   ['politic', 'union_join_place'],
            //military
            'la_quan_nhan' =>               ['military', 'is_service_man', 'convertTextBoolean'],
            'ngay_nhap_ngu' =>              ['military', 'join_date', 'convertTextDate'],
            'chuc_vu_trong_quan_doi' =>     ['military', 'position', 'convertTextMilitaryPosition'],
            'cap_bac_trong_quan_doi' =>     ['military', 'rank', 'convertTextMilitaryRank'],
            'binh_chung_trong_quan_doi' =>  ['military', 'arm', 'convertTextMilitaryArm'],
            'don_vi_trong_quan_doi' =>      ['military', 'branch'],
            'ngay_xuat_ngu' =>              ['military', 'left_date', 'convertTextDate'],
            'ly_do_xuat_ngu' =>             ['military', 'left_reason'],
            'la_thuong_binh_benh_binh' =>   ['military', 'is_wounded_soldier', 'convertTextBoolean'],
            'ngay_tham_gia_cach_mang' =>    ['military', 'revolution_join_date', 'convertTextDate'],
            'hang_thuong_benh_binh' =>      ['military', 'wounded_soldier_level', 'convertTextMilitaryLevel'],
            'ti_le_suy_giam_lao_dong' =>    ['military', 'num_disability_rate'],
            'huong_che_do' =>               ['military', 'is_martyr_regime', 'convertTextBoolean'],
            //health
            'nhom_mau' =>               ['health', 'blood_type', 'convertTextHealthBlood'],
            'chieu_cao' =>              ['health', 'height'],
            'can_nang' =>               ['health', 'weigth'],
            'tinh_trang_suc_khoe' =>    ['health', 'health_status'],
            'can_luu_y' =>              ['health', 'health_note'],
            'la_nguoi_khuyet_tat' =>    ['health', 'is_disabled', 'convertTextBoolean'],
            //hobby
            'muc_tieu_ca_nhan' =>   ['hobby', 'personal_goal'],
            'so_thich' =>           ['hobby', 'hobby_content'],
            'diem_manh' =>          ['hobby', 'forte'],
            'diem_yeu' =>           ['hobby', 'weakness'],
        ];
    }

    /**
     * convert data from excel to data db
     *
     * @param array $dataExcel
     * @param array $cols
     * @return array
     */
    protected function excelColToDb($dataExcel, $cols)
    {
        $data = [];
        foreach ($cols as $col => $coldb) {
            if (!array_key_exists($col, $dataExcel)) {
                continue;
            }
            $value = $dataExcel[$col];
            if (isset($coldb[2])) {
                $value = $this->{$coldb[2]}($value);
            }
            if (trim($value) === '') {
                $value = null;
            }
            $data[$coldb[0]][$coldb[1]] = $value;
        }
        return $data;
    }

    /**
     * convert text to boolean
     *
     * @param string $text
     * @return int
     */
    protected function convertTextBoolean($text)
    {
        if (Str::slug($text) === 'co') {
            return 1;
        }
        return 0;
    }

    /**
     * convert text to gender
     *
     * @param string $text
     * @return int
     */
    protected function convertTextGender($text)
    {
        if (Str::slug($text) === 'nu') {
            return Employee::GENDER_FEMALE;
        }
        return Employee::GENDER_MALE;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextDate($text)
    {
        if (!$text) {
            return null;
        }
        if ($text instanceof Carbon) {
            return $text->__toString();
        }
        if (preg_match('/^[0-9]{4}(\-)[0-9]{1,2}(\-)[0-9]{1,2}$/', $text)) {
            return $text;
        }
        try {
            //return Carbon::createFromFormat('m/d/Y', $text)->__toString();
            return Carbon::createFromFormat('d/m/Y', $text)->__toString();
        } catch (Exception $ex) {
            try {
                //return Carbon::createFromFormat('m-d-Y', $text)->__toString();
                return Carbon::createFromFormat('d-m-Y', $text)->__toString();
            } catch (Exception $e1) {}
        }
        return null;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    public function convertTextMarital($text)
    {
        switch (Str::slug($text, '-')) {
            case 'doc-than':
                return Employee::MARITAL_SINGLE;
            case 'da-co-gia-dinh':
                return Employee::MARITAL_MARRIED;
            case 'chua-xac-dinh':
                return Employee::MARITAL_WIDOWED;
            case 'ly-di':
                return Employee::MARITAL_SEPARATED;
        }
        return null;
    }

    /**
     * convert text to folk
     *
     * @param string $text
     * @return int
     */
    protected function convertTextFolk($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->folks);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextReligions($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->religions);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextCountry($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->countries);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextRelations($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->relations);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextMilitaryPosition($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->militaryPos);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextPoliticPartyPos($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->partyPos);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextPoliticUnionPos($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->unionPos);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextMilitaryRank($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->militaryRank);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextMilitaryArm($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->militaryArm);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextMilitaryLevel($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->militaryLevel);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextHealthBlood($text)
    {
        if (!$text) {
            return null;
        }
        $textSlug = Str::slug($text, '-');
        $result = array_search($textSlug, $this->healthBlood);
        if ($result === false) {
            return null;
        }
        return $text;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextWorkContract($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->workContract);
        if ($result === false) {
            return null;
        }
        $convertWorkingTypes = static::convertContractType();
        if (isset($convertWorkingTypes[$result])) {
            return $convertWorkingTypes[$result];
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextEduDegree($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->eduDegree);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * convert text to date
     *
     * @param string $text
     * @return int
     */
    protected function convertTextEduQuality($text)
    {
        if (!$text) {
            return null;
        }
        $text = Str::slug($text, '-');
        $result = array_search($text, $this->eduQuality);
        if ($result === false) {
            return null;
        }
        return $result;
    }

    /**
     * update employee relative
     *
     * @param model $employee
     * @param array $data
     * @param string $key
     * @return boolean
     */
    protected function updateRelative($employee, $data, $key)
    {
        if (!isset($data[$key])) {
            return true;
        }
        $relateItem = $employee->getItemRelate($key);
        $relateItem->setData(array_only($data[$key], $relateItem->getDbColumns()), true);
        $relateItem->employee_id = $employee->id;
        $relateItem->save();
    }

    /**
     * slug array value
     *
     * @param type $array
     */
    protected function slugArray($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$key] = Str::slug($value, '-');
        }
        return $result;
    }

    /**
     * update education of employee
     *
     * @param model $employee
     * @param array $data
     * @return boolean
     */
    protected function insertEducation($employee, $data)
    {
        if (!isset($data['school']) || !$data['school']) {
            return false;
        }
        $education = EmployeeEducation::select('id')
            ->where('school', $data['school'])
            ->where('employee_id', $employee->id)
            ->first();
        if ($education) {
            return true;
        }
        $education = new EmployeeEducation();
        if (isset($data['end_at']) && $data['end_at']) {
            if (is_numeric($data['end_at'])) {
                $data['end_at'] = Carbon::createFromFormat('Y', $data['end_at'])
                    ->setDate($data['end_at'], 01, 01)
                    ->__toString();
            } else {
                $data['end_at'] = $this->convertTextDate($data['end_at']);
            }
            $data['is_graduated'] = 1;
            $data['awarded_date'] = $data['end_at'];
        }
        $education->employee_id = $employee->id;
        $education->setData(array_only($data, $education->getFillable()), true);
        $education->save();
    }

    /**
     * update team
     *
     * @param model $employee
     * @param array $data
     * @return boolean
     */
    protected function insertTeam($employee, $data)
    {
        if (!isset($data['team']) || !isset($this->teamsFlag[$data['team']])) {
            return true;
        }
        // employy have team => not update
        if (TeamMember::select(['team_id'])->where('employee_id', $employee->id)->first()) {
            return true;
        }
        // insert team position
        $teamCode = $this->teamsFlag[$data['team']];
        $posId = (isset($data['position']) && isset($this->positionsFlag[$data['position']])) ?
            $this->positionsFlag[$data['position']] : Team::ROLE_MEMBER;
        if (!$data['team'] && !isset($data['position'])) {
            return true;
        }
        $team = Team::select('id')->where('code', $teamCode)
            ->first();
        if (!$team) {
            return true;
        }
        $teamMember = new TeamMember();
        $teamMember->team_id = $team->id;
        $teamMember->employee_id = $employee->id;
        $teamMember->role_id = $posId;
        $teamMember->save();

        //insert team history
        $teamHistory = new EmployeeTeamHistory();
        $teamHistory->team_id = $team->id;
        $teamHistory->employee_id = $employee->id;
        $teamHistory->role_id = $posId;
        $teamHistory->start_at = Carbon::now();
        $teamHistory->save();
    }

    /**
     * exec join date, trial date, offcial date
     *
     * @param type $data
     * @param type $employee
     * @return boolean
     */
    protected function execJoindate(&$data, $employee)
    {
        if (!array_key_exists('trial_date', $data['employees']) ||
            !array_key_exists('offcial_date', $data['employees'])
        ) {
            return true;
        }
        if ($data['employees']['trial_date'] &&
            $data['employees']['offcial_date']
        ) {
            if (!$employee->join_date) {
                $data['employees']['join_date'] = $data['employees']['trial_date'];
            }
            return true;
        }
        if (!$data['employees']['trial_date'] &&
            $data['employees']['offcial_date']
        ) {
            $data['employees']['trial_date'] = $data['employees']['offcial_date'];
        }
        if ($data['employees']['trial_date'] &&
            !$data['employees']['offcial_date']
        ) {
            $data['employees']['offcial_date'] = $data['employees']['trial_date'];
        }
        if (!$employee->join_date) {
            $data['employees']['join_date'] = $data['employees']['trial_date'];
        }
    }

    /**
     * return templade with code or name of Team Code
     *
     * @param string $type
     * @return array
     */
    public static function getTeamplateInFlagTeamCode($type = "")
    {
        $collection  = collect(static::getFlagTeamCode());
        $FlagTeamCode = $collection->map(function ($item, $key) use ($type) {
            return $item[$type];
        });
        return $FlagTeamCode->all();
    }

    /**
     * return position code templade
     *
     * @return array
     */
    public static function getTeamplateInFlagPositionCode()
    {
        $collection  = collect(static::getFlagPositionCode());
        $FlagTeamCode = $collection->map(function ($item, $key) {
            return $item  != Team::ROLE_TEAM_LEADER ?
                    $item  != Team::ROLE_SUB_LEADER ?
                    $item  != Team::ROLE_MEMBER ? "" : "Member" : "Sub leader" : "Leader";
        });
        return $FlagTeamCode->all();
    }
}
