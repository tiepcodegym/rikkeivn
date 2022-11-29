<?php

namespace Rikkei\Event\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
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
use Storage;

class MailTaxController extends Controller
{

    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('admin');
    }

    /**
     * upload file tax
     */
    public function tax()
    {
        Breadcrumb::add('Send mail');
        return view('event::send_email.tax', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb('event.tax.email_content'),
            'subjectEmail' => CoreConfigData::getValueDb('event.tax.email_subject')
        ]);
    }

    /**
     * read data salary file upload
     * @return type
     */
    public function postTax()
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
        $isFormatNumber = Input::get('format_number');
        //save subject and title
        $configContent = CoreConfigData::getItem('event.tax.email_content');
        $configContent->value = $content;
        $configContent->save();
        $configSubject = CoreConfigData::getItem('event.tax.email_subject');
        $configSubject->value = $subject;
        $configSubject->save();

        DB::beginTransaction();
        try {
            //save file name
            $taxFile = SalaryFile::create([
                'title' => $subject,
                'filename' => $file->getClientOriginalName(),
                'type' => ViewEvent::FILE_TYPE_TAX,
                'created_by' => auth()->id()
            ]);
            //collect data
            $dataEmail = [];
            $collectCols = [];
            $dataMailSent = [];
            $messErrors = [];
            $arrayAvailidEmails = Employee::select(DB::raw('LOWER(email) as email'), 'employee_code')
                ->lists('employee_code', 'email')->toArray();
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) use (
                &$dataEmail,
                &$collectCols,
                &$dataMailSent,
                &$messErrors,
                $taxFile,
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
                        if ($heading1[$i] || $heading2[$i]) {
                            $totalCol = $i + 1;
                            break;
                        } else {
                            unset($heading1[$i]);
                            unset($heading2[$i]);
                        }
                    }

                    $headRows = ['2' => $heading1, '1' => $heading2];
                    foreach ($headRows as $idx => $rowHead) {
                        $prevIdx = 0;
                        for ($i = 0; $i < $totalCol; $i++) {
                            if ($rowHead[$i]) {
                                $collectCols[$idx][$i] = ['title' => $rowHead[$i], 'rows' => $idx, 'cols' => 1];
                            }
                            if ($i - $prevIdx > 1 && isset($collectCols[$idx][$prevIdx])) {
                                $collectCols[$idx][$prevIdx]['cols'] = $i - $prevIdx + 1;
                                $collectCols[$idx][$prevIdx]['rows'] = 1;
                            }
                            if ($rowHead[$i]) {
                                $prevIdx = $i;
                            }
                        }
                    }
                }
                if (count($collectCols) < 2) {
                    throw new Exception(trans('event::message.None item read'), 422);
                }

                $taxRowIdx = ViewEvent::getTaxRowIndex();
                $data = $data->toArray();
                $notExistsEmails = [];
                $duplicateEmails = [];
                $invalidEmps = [];
                if (count($data) > 0) {
                    foreach ($data as $key => $row) {
                        $email = strtolower(preg_replace('/\s|/', '', $row[$taxRowIdx['email']]));
                        $empCode = strtolower(trim($row[$taxRowIdx['employee_code']]));
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
                                $invalidEmps[] = "{$email} - {$row[$taxRowIdx['employee_code']]}";
                            }
                        }
                        if (isset($dataEmail[$email])) {
                            $duplicateEmails[] = $email;
                        }
                        $row[$taxRowIdx['email']] = $email;
                        $dataEmail[$email] = $row;
                        $itemMailSent = ['file_id' => $taxFile->id];
                        $numIndex = 0;
                        $maxIndex = 3;
                        foreach ($taxRowIdx as $keyName => $idxRow) {
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

            Session::forget(ViewEvent::KEY_CACH_TAX . Session::getId());
            Session::put(ViewEvent::KEY_CACH_TAX . Session::getId(), $dataEmail);
            Session::put(ViewEvent::KEY_CACH_TAX . 'head_cols', $collectCols);
            Session::put(ViewEvent::KEY_CACH_TAX . 'tax_file_id', $taxFile->id);
            Session::put(ViewEvent::KEY_CACH_TAX . 'format_number', $isFormatNumber);

            SalaryMailSent::insert($dataMailSent);

            DB::commit();
            return redirect()->route('event::send.email.employees.show.tax');
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $message = trans('event::message.Error system, please try again');
            if ($ex instanceof \PHPExcel_Calculation_Exception && $ex->getCode() === 0) {
                // format: /(title sheet)!(coordinate) -> (message)/
                preg_match_all("/^(.+?)!([A-Z0-9]+) -> (.+?)$/", $ex->getMessage(), $matches);
                $message = count($matches) === 4 ? Lang::get('event::message.The file is wrong format in cell :cell', ['cell' => $matches[2][0]]) : $message;
            } elseif ($ex->getCode() == 422) {
                $message = $ex->getMessage();
            } else {
                // nothing
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
    public function showTaxData()
    {
        Breadcrumb::add(trans('event::view.Send mail salary'));
        $arrayData = Session::get(ViewEvent::KEY_CACH_TAX . Session::getId());
        $collectCols = Session::get(ViewEvent::KEY_CACH_TAX . 'head_cols');
        $taxFileId = Session::get(ViewEvent::KEY_CACH_TAX . 'tax_file_id');
        $isFormatNumber = Session::get(ViewEvent::KEY_CACH_TAX . 'format_number');
        $taxFile = SalaryFile::find($taxFileId);
        $rowIndexs = ViewEvent::getTaxRowIndex();
        return view('event::send_email.tax_data', compact('arrayData', 'collectCols', 'taxFile', 'rowIndexs', 'isFormatNumber'));
    }

    /**
     * send email salary
     * @return type
     */
    public function sendTaxEmail()
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
        $arrayData = Session::get(ViewEvent::KEY_CACH_TAX . Session::getId());
        if (!isset($arrayData)) {
            return response()->json('Error!', 422);
        }
        $collectCols = Session::get(ViewEvent::KEY_CACH_TAX . 'head_cols');
        $emailData = $arrayData[$email];

        DB::beginTransaction();
        try {
            //increment number sent
            SalaryMailSent::incrementNumberSent($salaryFileId, $email);

            $itemIndexs = ViewEvent::getTaxRowIndex();
            $subject = CoreConfigData::getValueDb('event.tax.email_subject');
            $patternsArray = [
                '/\{\{\sname\s\}\}/',
                '/\{\{\saccount\s\}\}/',
            ];
            $replacesArray = [
                $emailData[$itemIndexs['fullname']],
                preg_replace('/\@.*/', '', $emailData[$itemIndexs['email']]),
            ];
            $subject = preg_replace(
                $patternsArray,
                $replacesArray,
                $subject
            );

            /*
             * create attachment
             */
            $folderTemp = 'temp_tax';
            ViewEvent::createDir($folderTemp);
            $fileName = $folderTemp . '/' . CoreView::getNickName($emailData[$itemIndexs['email']]) . '_' . $emailData[$itemIndexs['employee_code']] . '.pdf';
            if (Storage::exists($fileName)) {
                Storage::delete($fileName);
            }
            $filePath = storage_path('app/' . $fileName);
            $mpdf = new \Mpdf\Mpdf(['tempDir' => storage_path('app/mpdf_tmp')]);
            $mpdf->setFooter('Rikkeisoft');
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->WriteHTML(view('event::send_email.files.tax', [
                'collectCols' => $collectCols,
                'emailData' => $emailData,
                'subject' =>  $subject,
                'itemIndexs' => $itemIndexs,
                'isFormatNumber' => Session::get(ViewEvent::KEY_CACH_TAX . 'format_number'),
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
                    ->setTemplate('event::send_email.email.tax', [
                        'collectCols' => $collectCols,
                        'emailData' => $emailData
                    ])
                    ->addAttachment($filePath);
            if ($employee) {
                $emailQueue->setNotify($employee->id, $subject . ' ('. trans('notify::view.Detail in mail') .')', null, ['icon' => 'reward.png', 'actor_id' => null]);
            }
            $emailQueue->save();
            DB::commit();
            return response()->json(trans('event::message.Mail sent success'));
        } catch (\Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return response()->json('Error!', 500);
        }
    }

    /**
     * delete temp salary data
     * @return type
     */
    public function deleteTaxTempData()
    {
        Session::forget(ViewEvent::KEY_CACH_TAX . Session::getId());
        Session::forget(ViewEvent::KEY_CACH_TAX . 'tax_file_id');
        return redirect()
                ->route('event::send.email.employees.tax');
    }

    /**
     * list uploaded salary files
     * @return type
     */
    public function listFiles()
    {
        Breadcrumb::add(trans('event::view.Tax file uploaded'));

        $collectionModel = SalaryFile::getData(ViewEvent::FILE_TYPE_TAX);
        return view('event::tax.index', compact('collectionModel'));
    }

    /**
     * send mail detail
     */
    public function mailSentDetail($fileId)
    {
        $taxFile = SalaryFile::findOrFail($fileId);
        //delete temp file
        Session::forget(ViewEvent::KEY_CACH_TAX . Session::getId());
        Session::forget(ViewEvent::KEY_CACH_TAX . 'tax_file_id');

        Breadcrumb::add(trans('event::view.Tax file uploaded'), route('event::send.email.employees.tax.list_files'));
        Breadcrumb::add(trans('event::view.Details'));

        $collectionModel = SalaryMailSent::getData($fileId);
        return view('event::tax.detail', compact('collectionModel', 'taxFile'));
    }

}