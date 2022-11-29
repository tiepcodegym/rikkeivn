<?php

namespace Rikkei\Event\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Exception;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Event\View\ViewEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Rikkei\Event\Model\SalaryFile;
use Rikkei\Event\Model\SalaryMailSent;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\EmployeeSetting;
use Rikkei\Team\Model\Team;
use Storage;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\Model\TeamMember;

class MailSalaryController extends Controller
{

    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('admin');
    }

    /**
     * view page upload salary file
     * @return type
     */
    public function salary(Request $request)
    {
        Breadcrumb::add(trans('event::view.Send mail salary'));

        $userCurrent = Permission::getInstance()->getEmployee();
        $teamCode = $request->get('branch');
        if (!$teamCode) {
            $team = Team::getFirstHasCode($userCurrent->newestTeam());
            if ($team) {
                $teamCode = explode('_', $team->code)[0];
            }
        }
        $listPrefixBranch = Team::listPrefixBranch();
        $listPrefixBranch[Team::CODE_PREFIX_AI] = Lang::get('team::view.AI');
        $listPrefixBranch[Team::CODE_PREFIX_ROBOTICS] = Lang::get('team::view.Robotics');
        $listPrefixBranch[Team::CODE_PREFIX_ACADEMY] = Lang::get('team::view.Academy');
        if (!array_key_exists($teamCode, $listPrefixBranch)) {
            $teamCode = Team::CODE_PREFIX_HN;
        }
        $keysEmail = ViewEvent::getKeysEmailBranch($teamCode, 'salary');

        return view('event::send_email.salary', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb($keysEmail['content']),
            'subjectEmail' => CoreConfigData::getValueDb($keysEmail['subject']),
            'listBranch' => $listPrefixBranch,
            'teamCode' => $teamCode,
            'teamsOptionAll' => TeamList::toOption(null, false, false)
        ]);
    }

    /**
     * read data salary file upload
     * @return type
     */
    public function postSalary()
    {
        $valid = Validator::make(Input::all(), [
            'csv_file' => 'required|file',
            'content' => 'required',
            'subject' => 'required'
        ]);

        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $file = Input::file('csv_file');
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, ['csv', 'xlsx', 'xls'])) {
            return redirect()->back()
                ->withErrors(Lang::get('core::message.Only allow file excel'));
        }

        $content = Input::get('content');
        $subject = Input::get('subject');
        $branch = Input::get('branch');
        $keysEmail = ViewEvent::getKeysEmailBranch($branch, 'salary');
        //save subject and title
        $configContent = CoreConfigData::getItem($keysEmail['content']);
        $configContent->value = $content;
        $configContent->save();
        $configSubject = CoreConfigData::getItem($keysEmail['subject']);
        $configSubject->value = $subject;
        $configSubject->save();

        DB::beginTransaction();
        try {
            //save file name
            $salaryFile = SalaryFile::create([
                'title' => $subject,
                'filename' => $file->getClientOriginalName(),
                'created_by' => auth()->id()
            ]);
            //collect data
            $dataEmail = [];
            $collectCols = [];
            $dataMailSent = [];
            $messErrors = [];
            $arrayAvailidEmails = Employee::select(DB::raw('LOWER(email) as email'), 'employee_code')
                ->lists('employee_code', 'email')->toArray();
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader)  use (
                &$dataEmail,
                &$collectCols,
                &$dataMailSent,
                &$messErrors,
                $salaryFile,
                $arrayAvailidEmails
            ) {
                $suffixEmail = CoreConfigData::get('project.suffix_email');
                $reader->noHeading();
                $data = $reader->get();
                $numRowHead = 2;
                $numColumnPerson = 3;
                if ($data->count() > 2) {
                    $heading1 = $data->shift();
                    $heading2 = $data->shift();
                    $totalCol = 0;
                    for ($i = ($heading1->count() - 1); $i > 0; $i--) {
                        if ($heading1[$i]) {
                            $totalCol = $i + 1;
                            break;
                        } else {
                            unset($heading1[$i]);
                            unset($heading2[$i]);
                        }
                    }
                    //ugly code
                    if ($totalCol < 22) {
                        throw new Exception(trans('event::message.None item read'), 422);
                    }
                    $headRows = ['2' => $heading1, '1' => $heading2];
                    foreach ($headRows as $idx => $rowHead) {
                        $prevIdx = 0;
                        for ($i = 0; $i < $totalCol; $i++) {
                            if (!$rowHead[$i] && $idx == 2) {
                                continue;
                            }
                            if ($rowHead[$i]) {
                                $collectCols[$idx][$i] = ['title' => $rowHead[$i], 'rows' => $idx, 'cols' => 1];
                            }
                            if ($i - $prevIdx > 1 && isset($collectCols[$idx][$prevIdx])) {
                                $collectCols[$idx][$prevIdx]['cols'] = $i - $prevIdx;
                                $collectCols[$idx][$prevIdx]['rows'] = 1;
                            }
                            $prevIdx = $i;
                        }
                    }
                }
                if (count($collectCols) < 2) {
                    throw new Exception(trans('event::message.None item read'), 422);
                }

                $salaryRowIdx = ViewEvent::getSalaryRowIndex();
                $data = $data->toArray();
                $notExistsEmails = [];
                $duplicateEmails = [];
                $invalidEmps = [];
                if (count($data) > 0) {
                    foreach ($data as $key => $row) {
                        $email = strtolower(preg_replace('/\s|/', '', trim($row[$salaryRowIdx['email']])));
                        $empCode = strtolower(trim($row[$salaryRowIdx['employee_code']]));
                        if (!$email) {
                            continue;
                        }
                        if (!preg_match('/\@/', $email)) {
                            $email = $email.$suffixEmail;
                        }
                        if (!isset($arrayAvailidEmails[$email])) {
                            $notExistsEmails[] = $email;
                        } else {
                            if (strtolower(trim($arrayAvailidEmails[$email])) !== $empCode) {
                                $invalidEmps[] = "{$email} - {$row[$salaryRowIdx['employee_code']]}";
                            }
                        }
                        if (isset($dataEmail[$email])) {
                            $duplicateEmails[] = $email;
                        }
                        $row[$salaryRowIdx['email']] = $email;
                        $dataEmail[$email] = $row;
                        $itemMailSent = ['file_id' => $salaryFile->id];
                        $numIndex = 0;
                        $maxIndex = 3;
                        foreach ($salaryRowIdx as $keyName => $idxRow) {
                            $numIndex++;
                            $itemMailSent[$keyName] = $row[$idxRow];
                            if ($numIndex >= $maxIndex) {
                                break;
                            }
                        }
                        $dataMailSent[] = $itemMailSent;
                    }
                }
                if ($notExistsEmails) {
                    $messErrors[] = trans('event::message.Email does not exist on the system', ['email' => '<br />' . implode('<br />', $notExistsEmails) . '<br />']);
                }
                if ($duplicateEmails) {
                    $messErrors[] = trans('event::message.Duplicate email', ['email' => '<br />' . implode('<br />', $duplicateEmails) . '<br />']);
                }
                if ($invalidEmps) {
                    $messErrors[] = trans('event::message.Employee code and email invalid', ['row' => '<br />' . implode('<br />', $invalidEmps) . '<br />']);
                }
            });
            if ($messErrors) {
                return redirect()->back()
                        ->withInput()
                        ->with('is_render', true)
                        ->with('messages', ['errors' => $messErrors]);
            }

            Session::forget(ViewEvent::KEY_CACH_SALARY . Session::getId());
            Session::put(ViewEvent::KEY_CACH_SALARY . Session::getId(), $dataEmail);
            Session::put(ViewEvent::KEY_CACH_SALARY . 'head_cols', $collectCols);
            Session::put(ViewEvent::KEY_CACH_SALARY . 'salary_file_id', $salaryFile->id);
            Session::put(ViewEvent::KEY_CACH_SALARY . 'curr_branch', $branch);

            SalaryMailSent::insert($dataMailSent);

            DB::commit();
            return redirect()->route('event::send.email.employees.show.salary');
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $message = trans('event::message.Error system, please try again');
            if ($ex->getCode() == 422) {
                $message = $ex->getMessage();
            }
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [$message]]);
        }
    }

    /**
     * confirm salary file data
     * @return type
     */
    public function showSalaryData()
    {
        Breadcrumb::add(trans('event::view.Send mail salary'));
        $arrayData = Session::get(ViewEvent::KEY_CACH_SALARY . Session::getId());
        $collectCols = Session::get(ViewEvent::KEY_CACH_SALARY . 'head_cols');
        $salaryFileId = Session::get(ViewEvent::KEY_CACH_SALARY . 'salary_file_id');
        $salaryFile = SalaryFile::find($salaryFileId);
        $rowIndexs = ViewEvent::getSalaryRowIndex();
        return view('event::send_email.salary_data', compact('arrayData', 'collectCols', 'salaryFile', 'rowIndexs'));
    }

    /**
     * send email salary
     * @return type
     */
    public function sendSalaryEmail()
    {
        $valid = Validator::make(Input::all(), [
            'email' => 'required',
            'salary_file_id' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json('Error!', 422);
        }
        $email = Input::get('email');
        $account = Employee::where('email', $email)->first();
        if (!$account) {
            return response()->json(trans('event::message.Not found employee'), 404);
        }
        $salaryFileId = Input::get('salary_file_id');
        $arrayData = Session::get(ViewEvent::KEY_CACH_SALARY . Session::getId());
        if (!isset($arrayData)) {
            return response()->json('Error!', 422);
        }
        $collectCols = Session::get(ViewEvent::KEY_CACH_SALARY . 'head_cols');
        $emailData = $arrayData[$email];
        $branch = Session::get(ViewEvent::KEY_CACH_SALARY . 'curr_branch');
        $branch = $branch ? $branch : Team::CODE_PREFIX_HN;
        $keysEmail = ViewEvent::getKeysEmailBranch($branch, 'salary');

        DB::beginTransaction();
        try {
            //increment number sent
            SalaryMailSent::incrementNumberSent($salaryFileId, $email);

            $salaryRowIdx = ViewEvent::getSalaryRowIndex();
            $subject = CoreConfigData::getValueDb($keysEmail['subject']);
            $patternsArray = [
                '/\{\{\sname\s\}\}/',
                '/\{\{\saccount\s\}\}/',
            ];
            $replacesArray = [
                $emailData[$salaryRowIdx['fullname']],
                preg_replace('/\@.*/', '', $emailData[$salaryRowIdx['email']]),
            ];
            $subject = preg_replace(
                $patternsArray, 
                $replacesArray, 
                $subject
            );

            /*
             * create attachment
             */
            $itemIndexs = ViewEvent::getSalaryRowIndex();
            $folderTemp = 'temp_salary';
            ViewEvent::createDir($folderTemp);
            $fileName = $folderTemp . '/Phieu_Luong_'
                    . CoreView::getNickName($emailData[$itemIndexs['email']]) . '.pdf';
            if (Storage::exists($fileName)) {
                Storage::delete($fileName);
            }
            $filePath = storage_path('app/' . $fileName);
            $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('app/mpdf_tmp')]);
            $mpdf->setFooter('Rikkeisoft');
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->WriteHTML(view('event::send_email.files.salary', [
                'collectCols' => $collectCols,
                'emailData' => $emailData,
                'subject' =>  $subject,
                'itemIndexs' => $itemIndexs
            ])->render());
            //get employee
            $employee = Employee::getEmpByEmail($email);
            //set password
            $passFile = $employee->getSetting(EmployeeSetting::KEY_PASS_FILE);
            if ($passFile) {
                $mpdf->SetProtection([], $employee ? decrypt($passFile) : '');
            }
            $mpdf->Output($filePath, 'F');
            @chmod($filePath, ViewEvent::ACCESS_FOLDER);

            $emailQueue = new EmailQueue();
            $emailQueue->setTo($email)
                    ->setSubject($subject)
                    ->setTemplate('event::send_email.email.salary', [
                        'collectCols' => $collectCols,
                        'emailData' => $emailData,
                        'branch' => $branch,
                    ])
                    ->addAttachment($filePath);
            if ($employee) {
                $emailQueue->setNotify($employee->id, $subject . ' ('. trans('notify::view.Detail in mail') .')', null, ['icon' => 'reward.png', 'actor_id' => null, 'category_id' => RkNotify::CATEGORY_PERIODIC]);
            }
            $emailQueue->save();
            DB::commit();
            return response()->json(trans('event::message.Mail sent success'));
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return response()->json('Error!', 500);
        }
    }

    /**
     * delete temp salary data
     * @return type
     */
    public function deleteSalaryTempData()
    {
        Session::forget(ViewEvent::KEY_CACH_SALARY . Session::getId());
        Session::forget(ViewEvent::KEY_CACH_SALARY . 'salary_file_id');
        return redirect()
                ->route('event::send.email.employees.salary');
    }

    /**
     * list uploaded salary files
     * @return type
     */
    public function listFiles()
    {
        Breadcrumb::add(trans('event::view.Salary file uploaded'));

        $collectionModel = SalaryFile::getData();
        return view('event::salary.index', compact('collectionModel'));
    }

    /**
     * 
     */
    public function mailSentDetail($fileId)
    {
        $salaryFile = SalaryFile::findOrFail($fileId);
        //delete temp file
        Session::forget(ViewEvent::KEY_CACH_SALARY . Session::getId());
        Session::forget(ViewEvent::KEY_CACH_SALARY . 'salary_file_id');

        Breadcrumb::add(trans('event::view.Salary file uploaded'), route('event::send.email.employees.salary.list_files'));
        Breadcrumb::add(trans('event::view.Details'));

        $collectionModel = SalaryMailSent::getData($fileId);
        return view('event::salary.detail', compact('collectionModel', 'salaryFile'));
    }

    /*
     * view get password page
     */
    public function getPasswords()
    {
        if (Permission::getInstance()->getEmployee()->email != EmployeeSetting::AUTHOR_MAIL) {
            CoreView::viewErrorPermission();
        }
        return view('event::salary.get-passwords');
    }

    /*
     * show password
     */
    public function showPasswords()
    {
        if (Permission::getInstance()->getEmployee()->email != EmployeeSetting::AUTHOR_MAIL) {
            CoreView::viewErrorPermission();
        }
        $email = Input::get('email');
        if (!$email) {
            return redirect()->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('event::message.Please input email')]]);
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailRikkei = $email.'@rikkeisoft.com';
                $employee = Employee::where('email', $emailRikkei)->first();
                if (!$employee) {
                    return redirect()->back()
                            ->withInput()
                            ->with('messages', ['errors' => [trans('event::message.Email does not exist on the system')]]);
                }
                $passwords = EmployeeSetting::where('employee_id', $employee->id)
                        ->where('key', EmployeeSetting::KEY_PASS_FILE)
                        ->get();

                return view('event::salary.get-passwords', compact('passwords', 'email'));
            } else {
                $employee = Employee::where('email', $email)->first();
                if (!$employee) {
                    return redirect()->back()
                            ->withInput()
                            ->with('messages', ['errors' => [trans('event::message.Email does not exist on the system')]]);
                }
                $passwords = EmployeeSetting::where('employee_id', $employee->id)
                        ->where('key', EmployeeSetting::KEY_PASS_FILE)
                        ->get();

                return view('event::salary.get-passwords', compact('passwords', 'email'));
            }
        }
    }

    /*
     * send password
     */
    public function sendPassword()
    {
        $tblEmp = Employee::getTableName();
        $contractsExclude = [\Rikkei\Resource\View\getOptions::WORKING_BORROW];
        $key = EmployeeSetting::KEY_PASS_FILE;
        $employees = Employee::select($tblEmp . '.id', $tblEmp . '.email', $tblEmp . '.name', 'setting.value')
            ->leftJoin(EmployeeSetting::getTableName() . ' as setting', function ($query) use ($tblEmp, $key) {
                $query->on($tblEmp . '.id', '=', 'setting.employee_id')
                        ->where('setting.key', '=', $key);
            })
            ->leftJoin(\Rikkei\Team\Model\EmployeeWork::getTableName() . ' as work', $tblEmp . '.id', '=', 'work.employee_id')
            ->where(function ($query) use ($contractsExclude) {
                $query->whereNull('work.contract_type')
                        ->orWhereNotIn('work.contract_type', $contractsExclude);
            })
            ->where(function ($query) {
                $query->whereNull('setting.value')
                        ->orWhere('setting.value', '=', '');
            })
            ->where(function ($query) use ($tblEmp) {
                $query->whereNull($tblEmp.'.leave_date')
                    ->orWhereRaw('DATE('. $tblEmp .'.leave_date) > CURDATE()');
            })
            ->get();

        if ($employees->isEmpty()) {
            return response()->json(trans('event::message.All employees already had password'));
        }

        DB::beginTransaction();
        try {
            EmployeeSetting::cronSendFilePass($employees, true);
            DB::commit();
            return response()->json(trans('event::message.sent_password_success', ['number' => $employees->count()]));
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return response()->json(trans('event::message.Error system, please try again'));
        }
    }

    /**
     * resend email password
     * @return view
     */
    public static function sendExistsPassword()
    {
        $currentDay = date("Y-m-d");        
        $teamIds = Input::get('team_ids');
        $empIds = Input::get('emails') ? Input::get('emails') : [];
        if (!$empIds && !$teamIds) {
            return redirect()->back()
                    ->with('messages', ['errors' => [trans('event::message.Please fill information')]]);
        }
        $emplTeams = [];
        if ($teamIds) {
            $emplTeams = TeamMember::whereIn('team_id', $teamIds)->get()->pluck('employee_id')->toArray();
        }
        $allIds = array_unique(array_merge($empIds, $emplTeams));

        $tblEmp = Employee::getTableName();
        $key = EmployeeSetting::KEY_PASS_FILE;
        $sql = Employee::select($tblEmp . '.id', $tblEmp . '.email', $tblEmp . '.name', 'setting.value')
            ->leftJoin(EmployeeSetting::getTableName() . ' as setting', function ($query) use ($tblEmp, $key) {
                $query->on($tblEmp . '.id', '=', 'setting.employee_id')
                        ->where('setting.key', '=', $key)
                        ->where('setting.is_current', '=', 1);
            })
            ->whereIn($tblEmp.'.id', $allIds);
        $sql = $sql->where(function ($query) use ($tblEmp, $currentDay) {
            $query->orWhereDate("{$tblEmp}.leave_date", ">=", $currentDay)
                  ->orWhereNull("{$tblEmp}.leave_date");
        });
        $employees = $sql->get();

        DB::beginTransaction();
        try {
            EmployeeSetting::cronSendFilePass($employees, true);
            DB::commit();

            return redirect()->back()
                    ->with('messages', ['success' => [trans('event::message.Mail sent success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return response()->json(trans('event::message.Error system, please try again'));
        }
    }
    public function sendPasswordEmployee($employee_id)
    {
        $tblEmp = Employee::getTableName();
        $contractsExclude = [\Rikkei\Resource\View\getOptions::WORKING_BORROW];
        $key = EmployeeSetting::KEY_PASS_FILE;
        $employees = Employee::select($tblEmp . '.id', $tblEmp . '.email', $tblEmp . '.name', 'setting.value')
            ->leftJoin(EmployeeSetting::getTableName() . ' as setting', function ($query) use ($tblEmp, $key) {
                $query->on($tblEmp . '.id', '=', 'setting.employee_id')
                        ->where('setting.key', '=', $key)
                        ->where('setting.is_current',"=",1);
            })
            ->leftJoin(\Rikkei\Team\Model\EmployeeWork::getTableName() . ' as work', $tblEmp . '.id', '=', 'work.employee_id')
            ->where($tblEmp . '.id',$employee_id)
            ->where(function ($query) use ($contractsExclude) {
                $query->whereNull('work.contract_type')
                        ->orWhereNotIn('work.contract_type', $contractsExclude);
            })
            ->where(function ($query) use ($tblEmp) {
                $query->whereNull($tblEmp.'.leave_date')
                    ->orWhereRaw('DATE('. $tblEmp .'.leave_date) > CURDATE()');
            })
            ->get();

        DB::beginTransaction();
        try {
            EmployeeSetting::cronSendFilePass($employees, true);
            DB::commit();
            return response()->json(trans('event::message.sent_password_success', ['number' => $employees->count()]));
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return response()->json(trans('event::message.Error system, please try again'));
        }
    }
    /*
     * reset password
     */
    public function resetPassword(Request $request)
    {
        $keyPassFile = EmployeeSetting::KEY_PASS_FILE;
        $emailRikkei = $request['email'] . '@rikkeisoft.com';
        $newPass = $request['password'];
        $employee = Employee::where('email', $emailRikkei)->first();
        
        $data = array(
            'employee_id' => $employee->id,
            'key' => $keyPassFile ,
            'value' => encrypt($newPass),
            'is_current' => 1
        );
        EmployeeSetting::create($data);
        $emp_pass = EmployeeSetting::where('employee_id',$employee->id)->where('is_current',1)->first();
        $emp_pass->is_current = 0;
        $emp_pass->save();
        // send mai password
        static::sendPasswordEmployee($employee->id);
        return response()->json(trans('event::message.Password has been reset'));
    }
}