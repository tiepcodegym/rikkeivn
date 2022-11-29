<?php

namespace Rikkei\Event\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Event\Model\ForgotTurnOff;
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
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Illuminate\Http\Request;
use Rikkei\Event\View\TimekeepingHelper;
use Rikkei\Core\View\View as CoreView;

class SendEmailController extends Controller
{
    const FLAG_NAME_EMAIL_SEND = 'hr';

    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('admin');
    }

    /**
     * show upload file view
     */
    public function tetBonuses()
    {
        Breadcrumb::add('Send email');
        return view('event::send_email.tet', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb('event.send.email.tet.content'),
            'subjectEmail' => CoreConfigData::getValueDb('event.send.email.tet.subject')
        ]);
    }

    /**
     * send email for employees
     */
    public function tetBonusesPost()
    {
        $config = Input::get('cc');
        $validator = Validator::make($config, [
            'event.send.email.tet.subject' => 'required',
            'event.send.email.tet.content' => 'required'
        ]);
        $file = Input::file('csv_tet');
        if ($validator->fails() || !$file) {
            return redirect()
                ->route('event::send.email.employees.tet.bonuses')
                ->withErrors(Lang::get('event::message.Please fill information'));
        }
        $configItem = CoreConfigData::getItem('event.send.email.tet.subject');
        $configItem->value = $config['event.send.email.tet.subject'];
        $configItem->save();
        $configItem = CoreConfigData::getItem('event.send.email.tet.content');
        $configItem->value = $config['event.send.email.tet.content'];
        $configItem->save();
        $patternsArray = [
            '/\{\{\sname\s\}\}/',
            '/\{\{\saccount\s\}\}/',
        ];
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, [
            'csv',
            'xlsx',
            'xls'
        ])
        ) {
            return redirect()
                ->route('event::send.email.employees.tet.bonuses')
                ->withErrors(Lang::get('core::message.Only allow file csv'));
        }
        $titleIndex = ViewEvent::getHeadingIndexTetBonus();
        $accountEmailsReplace = CoreConfigData::getAccountToEmail(2);
        $suffixEmail = CoreConfigData::get('project.suffix_email');
        $subjectDefault = $config['event.send.email.tet.subject'];
        $count = 0;
        $dataInsert = [];
        $dataEmails = [];
        try {
            Excel::selectSheetsByIndex(0)->load($file->path(), function ($reader) use (
                $titleIndex, $accountEmailsReplace, $patternsArray,
                $config, $suffixEmail, $subjectDefault, &$count, &$dataInsert,
                &$dataEmails
            ) {
                $reader->noHeading();
                $dataRecord = $reader->get();
                foreach ($dataRecord as $row) {
                    if (!isset($row->{$titleIndex['no']}) ||
                        !$row->{$titleIndex['no']} ||
                        !is_numeric($row->{$titleIndex['no']}) ||
                        !isset($row->{$titleIndex['full_name']}) ||
                        !$row->{$titleIndex['full_name']} ||
                        !isset($row->{$titleIndex['id']}) ||
                        !$row->{$titleIndex['id']} ||
                        !isset($row->{$titleIndex['email']}) ||
                        !$row->{$titleIndex['email']}
                    ) {
                        continue;
                    }
                    $email = strtolower($row->{$titleIndex['email']});
                    $email = preg_replace('/\s|/', '', $email);
                    if (!preg_match('/\@/', $email)) {
                        if (isset($accountEmailsReplace[$email])) {
                            $email = $accountEmailsReplace[$email];
                        } else {
                            $email = $email . $suffixEmail;
                        }
                    }
                    $dataEmails[] = $email;
                    $replacesArray = [
                        $row->{$titleIndex['full_name']},
                        preg_replace('/\@.*/', '', $row->{$titleIndex['email']}),
                    ];
                    $subjectMail = preg_replace(
                        $patternsArray,
                        $replacesArray,
                        $subjectDefault
                    );
                    $dataTemplateEmail = [
                        'reg_replace' => [
                            'patterns' => $patternsArray,
                            'replaces' => $replacesArray
                        ],
                        'bonus' => $row
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue
                        //->setFrom(self::FLAG_NAME_EMAIL_SEND)
                        ->setTo($email, $row->{$titleIndex['full_name']})
                        ->setSubject($subjectMail)
                        ->setTemplate('event::send_email.email.tet_bonus', $dataTemplateEmail);
                    $dataInsert[] = $emailQueue->getValue();
                    $count++;
                }
            });
        } catch (Exception $ex) {
            Log::info($ex);
            return redirect()
                ->route('event::send.email.employees.tet.bonuses')
                ->withErrors(Lang::get('event::message.Error read file excel, please try again'));
        }
        try {
            EmailQueue::insert($dataInsert);
            //set notify
            \RkNotify::put(
                Employee::whereIn('email', $dataEmails)->lists('id')->toArray(),
                trim(preg_replace('/\{\{(.*?)\}\}/si', '', $subjectDefault), '- '),
                'https://mail.google.com',
                ['actor_id' => null, 'icon' => 'reward.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );
        } catch (Exception $ex) {
            Log::info($ex);
            return redirect()
                ->route('event::send.email.employees.tet.bonuses')
                ->withErrors(Lang::get('core::message.Error system'));
        }
        $messages = [
            'success' => [
                trans('event::message.The system will send mail in a moment'),
                trans('event::message.Number email: :number', ['number' => $count])
            ]
        ];
        return redirect()
            ->route('event::send.email.employees.tet.bonuses')
            ->with('messages', $messages);
    }

    /**
     * upload file ngay nghi
     * @return type
     */
    public function getUploadSabbFile()
    {
        Breadcrumb::add('Send mail');
        return view('event::send_email.sabb', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb('sabbatical.email_content'),
            'subjectEmail' => CoreConfigData::getValueDb('sabbatical.email_subject')
        ]);
    }

    /**
     * save file and email content
     */
    public function postUploadSabbFile()
    {
        $messages = [
            'csv_file.required' => trans('core::view.This field is required'),
            'content.required' => trans('event::view.The content field is required'),
            'subject.required' => trans('core::view.This field is required')
        ];
        $valid = validator()->make(Input::all(), [
            'csv_file' => 'required|file',
            'content' => 'required',
            'subject' => 'required'
        ], $messages);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $file = Input::file('csv_file');
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, [
            'csv'
        ])
        ) {
            return redirect()->back()
                ->withErrors(Lang::get('core::message.Only allow file csv'));
        }

        $content = Input::get('content');
        $subject = Input::get('subject');
        DB::beginTransaction();

        $configItem = CoreConfigData::getItem('sabbatical.email_content');
        $configItem->value = $content;
        $configItem->save();
        $configItem = CoreConfigData::getItem('sabbatical.email_subject');
        $configItem->value = $subject;
        $configItem->save();
        $count = 0;
        try {
            $dataEmail = [];
            $arrayEmails = [];
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) use (
                $subject, &$dataEmail, &$count, &$arrayEmails
            ) {
                $titleIndex = ViewEvent::getHeadingIndexSabbatical();
                $suffixEmail = CoreConfigData::get('project.suffix_email');
                $reader->noHeading();
                $rows = $reader->get();
                $accountEmailsReplace = CoreConfigData::getAccountToEmail(2);
                $patternsArray = [
                    '/\{\{\sname\s\}\}/',
                    '/\{\{\saccount\s\}\}/',
                ];
                $columnsHeading = $rows->first()->toArray();
                foreach ($rows as $key => $row) {
                    if ($key == 0) {
                        continue;
                    }
                    $row = $row->toArray();
                    if (!isset($row[$titleIndex['id']]) ||
                        !$row[$titleIndex['id']] ||
                        !isset($row[$titleIndex['full_name']]) ||
                        !$row[$titleIndex['full_name']] ||
                        !isset($row[$titleIndex['email']]) ||
                        !$row[$titleIndex['email']]
                    ) {
                        continue;
                    }
                    $email = strtolower($row[$titleIndex['email']]);
                    $email = preg_replace('/\s|/', '', $email);
                    if (!preg_match('/\@/', $email)) {
                        if (isset($accountEmailsReplace[$email])) {
                            $email = $accountEmailsReplace[$email];
                        } else {
                            $email = $email . $suffixEmail;
                        }
                    }
                    $arrayEmails[] = $email;
                    $replacesArray = [
                        $row[$titleIndex['full_name']],
                        preg_replace('/\@.*/', '', $row[$titleIndex['email']]),
                    ];
                    $subjectMail = preg_replace(
                        $patternsArray,
                        $replacesArray,
                        $subject
                    );
                    $dataTemplateEmail = [
                        'reg_replace' => [
                            'patterns' => $patternsArray,
                            'replaces' => $replacesArray
                        ],
                        'data' => $row,
                        'columnsHeading' => $columnsHeading
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($email, $row[$titleIndex['full_name']])
                        ->setSubject($subjectMail)
                        ->setTemplate('event::send_email.email.sabb_mail', $dataTemplateEmail);
                    $dataEmail[] = $emailQueue->getValue();
                    $count++;
                }

            });
            EmailQueue::insert($dataEmail);
            //set notify
            \RkNotify::put(
                Employee::whereIn('email', $arrayEmails)->lists('id')->toArray(),
                trim(preg_replace('/\{\{(.*?)\}\}/si', '', $subject), '- '),
                'https://mail.google.com',
                ['actor_id' => null, 'icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );

            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('event::message.Upload successful, system will send email momentarily'),
                    Lang::get('event::message.Number email: :number', ['number' => $count])
                ]
            ];
            return redirect()->back()->withInput()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages',
                ['errors' => [trans('event::message.Error system, please try again')]]);
        }
    }

    /**
     * show upload file view
     */
    public function toMale()
    {
        $subject = CoreConfigData::getValueDb('event.send.email.to_male.subject');
        if (!$subject) {
            $subject = '[TB] Một lời chúc gửi ngàn yêu thương!';
        }
        Breadcrumb::add('Send email');
        return view('event::send_email.to_male', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb('event.send.email.to_male.content'),
            'subjectEmail' => $subject
        ]);
    }

    /**
     * send email for employees
     */
    public function toMalePost()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $response = [];
        $config = Input::get('cc');
        $validator = Validator::make($config, [
            'event.send.email.to_male.subject' => 'required',
            'event.send.email.to_male.content' => 'required'
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Please fill information');
            return $response;
        }
        $configItem = CoreConfigData::getItem('event.send.email.to_male.subject');
        $configItem->value = $config['event.send.email.to_male.subject'];
        $configItem->save();
        $configItem = CoreConfigData::getItem('event.send.email.to_male.content');
        $configItem->value = $config['event.send.email.to_male.content'];
        $configItem->save();
        $patternsArray = [
            '/\{\{\sname\s\}\}/',
            '/\{\{\saccount\s\}\}/',
        ];
        $subjectDefault = $config['event.send.email.to_male.subject'];
        $employees = Employee::getAllEmployeesOfTeam(2, ['gender' => Employee::GENDER_MALE]);
        if (!count($employees)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('event::message.Not found item');
            return $response;
        }
        $dataEmail = [];
        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $replacesArray = [
                    $employee->name,
                    preg_replace('/\@.*/', '', $employee->email),
                ];
                $subjectMail = preg_replace(
                    $patternsArray,
                    $replacesArray,
                    $subjectDefault
                );
                $dataTemplateEmail = [
                    'reg_replace' => [
                        'patterns' => $patternsArray,
                        'replaces' => $replacesArray
                    ],
                ];
                $emailQueue = new EmailQueue();
                $emailQueue
                    ->setTo($employee->email, $employee->name)
                    ->setSubject($subjectMail)
                    ->setTemplate('event::send_email.email.to_male', $dataTemplateEmail);
                $dataEmail[] = $emailQueue->getValue();
            }
            EmailQueue::insert($dataEmail);
            //set notify
            \RkNotify::put(
                $employees->lists('id')->toArray(),
                trim(preg_replace('/\{\{(.*?)\}\}/si', '', $subjectDefault), '- '),
                'https://mail.google.com',
                ['actor_id' => null, 'icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );

            DB::commit();
            $response['success'] = 1;
            $response['message'] = Lang::get('event::message.Number email: :number', ['number' => count($dataEmail)]);
            return $response;
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollback();
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error system, please try later!');
            return $response;
        }
    }

    /**
     * upload file total timekeeping
     */
    public function totalTimekeeping()
    {
        Breadcrumb::add('Send mail');
        return view('event::send_email.total_timekeeping', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'contentEmail' => CoreConfigData::getValueDb('event.total_timekeeping.email_content'),
            'subjectEmail' => CoreConfigData::getValueDb('event.total_timekeeping.email_subject')
        ]);
    }

    /**
     * save file and email content
     */
    public function totalTimekeepingPost()
    {
        $valid = validator()->make(Input::all(), [
            'csv_file' => 'required|file',
            'content' => 'required',
            'subject' => 'required'
        ]);

        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $file = Input::file('csv_file');
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, [
            'csv'
        ])
        ) {
            return redirect()->back()
                ->withErrors(Lang::get('core::message.Only allow file csv'));
        }

        $content = Input::get('content');
        $subject = Input::get('subject');
        DB::beginTransaction();
        try {
            $configItem = CoreConfigData::getItem('event.total_timekeeping.email_content');
            $configItem->value = $content;
            $configItem->save();
            $configItem = CoreConfigData::getItem('event.total_timekeeping.email_subject');
            $configItem->value = $subject;
            $configItem->save();
            $titleIndex = ViewEvent::getHeadingIndexTotalTimekeeping();
            $dataEmail = [];
            $arrayEmails = [];
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) use (
                $subject, $titleIndex, &$dataEmail, &$arrayEmails
            ) {
                $suffixEmail = CoreConfigData::get('project.suffix_email');
                $reader->noHeading();
                $rows = $reader->get();
                $accountEmailsReplace = CoreConfigData::getAccountToEmail(2);
                $patternsArray = [
                    '/\{\{\sname\s\}\}/',
                    '/\{\{\saccount\s\}\}/',
                ];
                $columnsHeading = $rows->first()->toArray();
                foreach ($rows as $key => $row) {
                    if ($key == 0) {
                        continue;
                    }
                    $row = $row->toArray();
                    if (!isset($row[$titleIndex['id']]) ||
                        !$row[$titleIndex['id']] ||
                        !isset($row[$titleIndex['full_name']]) ||
                        !$row[$titleIndex['full_name']] ||
                        !isset($row[$titleIndex['email']]) ||
                        !$row[$titleIndex['email']]
                    ) {
                        continue;
                    }
                    $email = strtolower($row[$titleIndex['email']]);
                    $email = preg_replace('/\s|/', '', $email);
                    if (!preg_match('/\@/', $email)) {
                        if (isset($accountEmailsReplace[$email])) {
                            $email = $accountEmailsReplace[$email];
                        } else {
                            $email = $email . $suffixEmail;
                        }
                    }
                    $arrayEmails[] = $email;
                    $replacesArray = [
                        $row[$titleIndex['full_name']],
                        preg_replace('/\@.*/', '', $row[$titleIndex['email']]),
                    ];
                    $subjectMail = preg_replace(
                        $patternsArray,
                        $replacesArray,
                        $subject
                    );
                    $dataTemplateEmail = [
                        'reg_replace' => [
                            'patterns' => $patternsArray,
                            'replaces' => $replacesArray
                        ],
                        'data' => $row,
                        'columnsHeading' => $columnsHeading
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($email, $row[$titleIndex['full_name']])
                        ->setSubject($subjectMail)
                        ->setTemplate('event::send_email.email.total_timekeeping',
                            $dataTemplateEmail);
                    $dataEmail[] = $emailQueue->getValue();
                }
            });
            EmailQueue::insert($dataEmail);
            //set notify
            \RkNotify::put(
                Employee::whereIn('email', $arrayEmails)->lists('id')->toArray(),
                trim(preg_replace('/\{\{(.*?)\}\}/si', '', $subject), '- '),
                'https://mail.google.com',
                ['actor_id' => null, 'icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );

            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('event::message.Upload successful, system will send email momentarily'),
                    Lang::get('event::message.Number email: :number', ['number' => count($dataEmail)])
                ]
            ];
            return redirect()->back()->withInput()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages',
                ['errors' => [trans('event::message.Error system, please try again')]]);
        }
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
     * save file and email content
     */
    public function taxPost()
    {
        $valid = validator()->make(Input::all(), [
            'csv_file' => 'required|file',
            'content' => 'required',
            'subject' => 'required'
        ]);

        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $file = Input::file('csv_file');
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, [
            'csv',
            'xlsx',
            'xls'
        ])
        ) {
            return redirect()->back()
                ->withErrors(Lang::get('core::message.Only allow file csv'));
        }

        $content = Input::get('content');
        $subject = Input::get('subject');
        DB::beginTransaction();
        try {
            $configItem = CoreConfigData::getItem('event.tax.email_content');
            $configItem->value = $content;
            $configItem->save();
            $configItem = CoreConfigData::getItem('event.tax.email_subject');
            $configItem->value = $subject;
            $configItem->save();
            $titleIndex = ViewEvent::getHeadingIndexTax();
            $dataEmail = [];
            $count = 0;
            $arrayEmails = [];
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) use (
                $subject, $titleIndex, &$dataEmail, &$count, &$arrayEmails
            ) {
                $suffixEmail = CoreConfigData::get('project.suffix_email');
                $reader->noHeading();
                $rows = $reader->get();
                $accountEmailsReplace = CoreConfigData::getAccountToEmail(2);
                $patternsArray = [
                    '/\{\{\sname\s\}\}/',
                    '/\{\{\saccount\s\}\}/',
                ];
                $columnsHeading = array_slice($rows->first()->toArray(), 0, 12);
                foreach ($rows as $key => $row) {
                    if ($key == 0) {
                        continue;
                    }
                    $row = array_slice($row->toArray(), 0, 12);
                    if (!isset($row[$titleIndex['id']]) ||
                        !$row[$titleIndex['id']] ||
                        !isset($row[$titleIndex['full_name']]) ||
                        !$row[$titleIndex['full_name']] ||
                        !isset($row[$titleIndex['email']]) ||
                        !$row[$titleIndex['email']]
                    ) {
                        continue;
                    }
                    $email = strtolower($row[$titleIndex['email']]);
                    $email = preg_replace('/\s|/', '', $email);
                    if (!preg_match('/\@/', $email)) {
                        if (isset($accountEmailsReplace[$email])) {
                            $email = $accountEmailsReplace[$email];
                        } else {
                            $email = $email . $suffixEmail;
                        }
                    }
                    $arrayEmails[] = $email;
                    $replacesArray = [
                        $row[$titleIndex['full_name']],
                        preg_replace('/\@.*/', '', $row[$titleIndex['email']]),
                    ];
                    $subjectMail = preg_replace(
                        $patternsArray,
                        $replacesArray,
                        $subject
                    );
                    $dataTemplateEmail = [
                        'reg_replace' => [
                            'patterns' => $patternsArray,
                            'replaces' => $replacesArray
                        ],
                        'data' => $row,
                        'columnsHeading' => $columnsHeading
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($email, $row[$titleIndex['full_name']])
                        ->setSubject($subjectMail)
                        ->setTemplate('event::send_email.email.tax',
                            $dataTemplateEmail);
                    $dataEmail[] = $emailQueue->getValue();
                    $count++;
                }
            });
            EmailQueue::insert($dataEmail);
            //set notify
            \RkNotify::put(
                Employee::whereIn('email', $arrayEmails)->lists('id')->toArray(),
                trim(preg_replace('/\{\{(.*?)\}\}/si', '', $subject), '- '),
                'https://mail.google.com',
                ['actor_id' => null, 'icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );

            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('event::message.Upload successful, system will send email momentarily'),
                    Lang::get('event::message.Number email: :number', ['number' => $count])
                ]
            ];
            return redirect()->back()->withInput()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages',
                ['errors' => [trans('event::message.Error system, please try again')]]);
        }
    }

    /**
     * upload file tax
     */
    public function fines(Request $request)
    {
        Breadcrumb::add('Send mail');
        $userCurrent = Permission::getInstance()->getEmployee();
        $teamCode = $request->get('branch');
        if (!$teamCode) {
            $team = Team::getFirstHasCode($userCurrent->newestTeam());
            if ($team) {
                $teamCode = explode('_', $team->code)[0];
            }
        }
        $listPrefixBranch = Team::listPrefixBranch();
        if (!array_key_exists($teamCode, $listPrefixBranch)) {
            $teamCode = Team::CODE_PREFIX_HN;
        }
        $keysEmail = ViewEvent::getKeysEmailBranch($teamCode, 'fines');
        return view('event::send_email.fines', [
            'userCurrent' => $userCurrent,
            'contentEmail' => CoreConfigData::getValueDb($keysEmail['content']),
            'subjectEmail' => CoreConfigData::getValueDb($keysEmail['subject']),
            'titleHead' => Lang::get('event::view.Send mail fines infomation'),
            'listBranch' => $listPrefixBranch,
            'teamCode' => $teamCode
        ]);
    }

    /**
     * save file and email content
     */
    public function finesPost()
    {
        $valid = validator()->make(Input::all(), [
            'csv_file' => 'required|file',
            'content' => 'required',
            'subject' => 'required'
        ]);

        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $file = Input::file('csv_file');
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, [
            'csv'
        ])
        ) {
            return redirect()->back()
                ->withErrors(Lang::get('core::message.Only allow file csv'));
        }
        $content = Input::get('content');
        $subject = Input::get('subject');
        $branch = Input::get('branch');
        $keyEmails = ViewEvent::getKeysEmailBranch($branch, 'fines');
        DB::beginTransaction();
        try {
            $configItem = CoreConfigData::getItem($keyEmails['content']);
            $configItem->value = $content;
            $configItem->save();
            $configItem = CoreConfigData::getItem($keyEmails['subject']);
            $configItem->value = $subject;
            $configItem->save();
            $titleIndex = ViewEvent::getHeadingIndexFines();
            $dataEmail = [];
            $arrayEmails = [];
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) use (
                $subject,
                $titleIndex,
                &$dataEmail,
                &$arrayEmails,
                $branch
            ) {
                $suffixEmail = CoreConfigData::get('project.suffix_email');
                $reader->noHeading();
                $rows = $reader->get();
                $accountEmailsReplace = CoreConfigData::getAccountToEmail(2);
                $patternsArray = [
                    '/\{\{\sname\s\}\}/',
                    '/\{\{\saccount\s\}\}/',
                ];
                foreach ($rows as $key => $row) {
                    $row = $row->toArray();
                    if (!isset($row[$titleIndex['email']]) ||
                        !$row[$titleIndex['email']] ||
                        !isset($row[$titleIndex['ho_ten']]) ||
                        !$row[$titleIndex['ho_ten']] ||
                        !isset($row[$titleIndex['id']]) ||
                        !$row[$titleIndex['id']] ||
                        !isset($row[$titleIndex['tien_di_muon']]) ||
                        !isset($row[$titleIndex['tien_quen_cham_cong']]) ||
                        !isset($row[$titleIndex['tien_quen_tat_may']]) ||
                        !isset($row[$titleIndex['tong']]) ||
                        !$row[$titleIndex['tong']]
                    ) {
                        continue;
                    }
                    $email = strtolower($row[$titleIndex['email']]);

                    //remove space in email
                    $email = preg_replace('/\s|/', '', $email);
                    if (!preg_match('/\@/', $email)) {
                        if (isset($accountEmailsReplace[$email])) {
                            $email = $accountEmailsReplace[$email];
                        } else {
                            // if not has @rikkeisoft.com => add into
                            $email = $email . $suffixEmail;
                        }
                    }
                    $arrayEmails[] = $email;
                    $replacesArray = [
                        $row[$titleIndex['ho_ten']],
                        preg_replace('/\@.*/', '', $row[$titleIndex['email']]),
                    ];
                    $subjectMail = preg_replace(
                        $patternsArray,
                        $replacesArray,
                        $subject
                    );
                    $dataTemplateEmail = [
                        'reg_replace' => [
                            'patterns' => $patternsArray,
                            'replaces' => $replacesArray
                        ],
                        'employee' => $row,
                        'branch' => $branch,
                    ];

                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($email, $row[$titleIndex['ho_ten']])
                        ->setSubject($subjectMail)
                        ->setTemplate('event::send_email.email.fines',
                            $dataTemplateEmail);
                    $dataEmail[] = $emailQueue->getValue();
                }
            });

            EmailQueue::insert($dataEmail);
            //set notify
            \RkNotify::put(
                Employee::whereIn('email', $arrayEmails)->lists('id')->toArray(),
                trim(preg_replace('/\{\{(.*?)\}\}/si', '', $subject), '- '),
                'https://mail.google.com',
                ['actor_id' => null, 'icon' => 'employee.png', 'category_id' => RkNotify::CATEGORY_PERIODIC]
            );

            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('event::message.Upload successful, system will send email momentarily'),
                    Lang::get('event::message.Number email: :number', ['number' => count($dataEmail)])
                ]
            ];
            return redirect()->back()->withInput()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages',
                ['errors' => [trans('event::message.Error system, please try again')]]);
        }
    }

    /*
    public function tsToFines()
    {
        if (!Permission::getInstance()->isAllow('event::send.email.employees.total.timekeeping')) {
            return CoreView::viewErrorPermission();
        }
        if (app('request')->isMethod('post')) {
            return $this->tsToFinesPost();
        }
        Breadcrumb::add('Send email');
        return view('event::send_email.ts_to_fines', [
            'userCurrent' => Permission::getInstance()->getEmployee(),
            'emailContentTimekeeping' => CoreConfigData::getValueDb('hr.email_content.timekeeping'),
            'subjectEmail' => CoreConfigData::getValueDb('hr.email_subject.timekeeping')
        ]);
    }

    public function tsToFinesPost()
    {
        $validator = Validator::make(Input::all(), [
            'excel_file' => 'required|mimes:csv,xls,xlsx',
            'date' => 'required'
        ], [
            'excel_file.mimes' => trans('validation.mimes', ['attribute' => 'File', 'mimes' => 'csv'])
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors());
        }
        $file = Input::file('excel_file');
        try {
            TimekeepingHelper::getInstance()
                ->uploadFile($file, Permission::getInstance()->getEmployee(), Input::get('date'));
            return redirect()->back()->with('messages', [
                'success' => [
                    Lang::get('event::message.ts to fines upload success'),
                ]
            ]);
        } catch (Exception $ex) {
            Log::error($ex);
            return redirect()->back()->with('messages', [
                'errors' => [$ex->getMessage()]
            ]);
        }
    }*/
    /**
     *  send email for forgot turn off
     */
    public function forgotTurnOff(Request $request)
    {
        Breadcrumb::add('Send mail');
        $userCurrent = Permission::getInstance()->getEmployee();
        $teamCode = $request->get('branch');
        if (!$teamCode) {
            $team = Team::getFirstHasCode($userCurrent->newestTeam());
            if ($team) {
                $teamCode = explode('_', $team->code)[0];
            }
        }

        // Danh sach chi nhanh.
        $listPrefixBranch = Team::listPrefixBranch();
        // Tạm bỏ qua team AI theo y/c của c Hạnh
        if (isset($listPrefixBranch['ai'])) {
            unset($listPrefixBranch['ai']);
        }

        if (!array_key_exists($teamCode, $listPrefixBranch)) {
            $teamCode = Team::CODE_PREFIX_HN;
        }
        $keysEmail = ViewEvent::getKeysEmailForgotTurnOff($teamCode);

        return view('event::send_email.forgot_turn_off', [
            'userCurrent' => $userCurrent,
            'contentEmail' => CoreConfigData::getValueDb($keysEmail['content']),
            'subjectEmail' => CoreConfigData::getValueDb($keysEmail['subject']),
            'titleHead' => Lang::get('event::view.Send mail forgot turn off information'),
            'listBranch' => $listPrefixBranch,
            'teamCode' => $teamCode
        ]);
    }

    public function forgotTurnOffPost()
    {
        ini_set('max_execution_time', 180);
        $valid = validator()->make(Input::all(), [
            'csv_file' => 'required|file',
            'content' => 'required',
            'subject' => 'required'
        ]);

        $content = Input::get('content');
        $subject = Input::get('subject');
        $branch = Input::get('branch');

        $keyEmails = ViewEvent::getKeysEmailForgotTurnOff($branch);
        $configItem = CoreConfigData::getItem($keyEmails['content']);
        $configItem->value = $content;
        $configItem->save();
        $configItem = CoreConfigData::getItem($keyEmails['subject']);
        $configItem->value = $subject;
        $configItem->save();

        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $file = Input::file('csv_file');
        $fileFinesExtension = $file->getClientOriginalExtension();
        if (!in_array($fileFinesExtension, [
            'xlsx',
            'xls',
            'csv'
        ])
        ) {
            return redirect()->back()
                ->withErrors(Lang::get('core::message.Only allow file csv or excel'));
        }
        // Get file name to take date
        $fileName = $file->getClientOriginalName();

        try {
            preg_match('/\d+/', $fileName, $fileDate);
            $date = date('Y-m-d', strtotime($fileDate['0']));
            $month = date('m', strtotime($date));
            $checkDate = date('Ymd', strtotime($fileDate['0']));
            if ($checkDate != $fileDate['0']) {
                return redirect()->back()->withInput()->with('messages',
                    ['errors' => [trans('event::message.incorrect date')]]);
            }
        } catch (Exception $e) {
            Log::info($e);
            return redirect()->back()->withInput()->with('messages',
                ['errors' => [trans('event::message.File names must follow the form')]]);
        }

        DB::beginTransaction();
        try {
            $wrongAccount = [];
            Excel::selectSheetsByIndex(0)->load($file, function ($reader) use (
                $subject,
                &$dataEmail,
                &$arrayEmails,
                &$countFail,
                $branch,
                $date,
                $month,
                &$wrongAccount
            ) {
                $suffixEmail = CoreConfigData::get('project.suffix_email');
                $patternsArray = [
                    '/\{\{\sname\s\}\}/',
                    '/\{\{\saccount\s\}\}/',
                    '/\{\{\sdate\s\}\}/',
                    '/\{\{\slistDate\s\}\}/',
                    '/\{\{\sn\s\}\}/',
                    '/\{\{\smonth\s\}\}/',
                ];

                $results = $reader->all()->toArray();
                $accountName = collect($results)->pluck('account')->toArray();
                $accountName = array_filter($accountName);
                foreach ($accountName as $item) {
                    $arrayEmails[] = strtolower($item . $suffixEmail);
                }
                // K cho import khi số lượng email lớn hơn 1000
                if (count($arrayEmails) >= ForgotTurnOff::MAX_EMAIL_IMPORT) {
                    $countFail = true;
                    return;
                }

                $employee = Employee::whereIn('email', $arrayEmails)
                    ->select('id', 'email', 'name')
                    ->whereNull('deleted_at')
                    ->get()
                    ->toArray();

                $logOff = $this->getListTurnOffData($employee, $date);
                $existsEmail = collect($employee)->pluck('email')->toArray();

                foreach ($results as $key => $row) {
                    if ($row['account']) {
                        $email = strtolower($row['account'] . $suffixEmail);
                        if (in_array($email, $existsEmail)) {
                            $row['month'] = $month;
                            $row['date'] = $row['listDate'] = $date;
                            $row['n'] = 1;

                            foreach ($logOff as $value) {
                                if ($email == $value->email) {
                                    $row['n'] = $value->quantity + $row['n'];
                                    $row['listDate'] = $value->list_date;
                                }
                            }
                            foreach ($employee as $item) {
                                if ($email == $item['email']) {
                                    $row['employee_id'] = $item['id'];
                                    $row['name'] = $item['name'];
                                }
                            }

                            $replacesArray = [
                                $row['name'],
                                $row['account'],
                                $row['date'],
                                $row['listDate'],
                                $row['n'],
                                $row['month']
                            ];

                            $subjectMail = preg_replace(
                                $patternsArray,
                                $replacesArray,
                                $subject
                            );

                            $dataTemplateEmail = [
                                'reg_replace' => [
                                    'patterns' => $patternsArray,
                                    'replaces' => $replacesArray
                                ],
                                'employee' => $row,
                                'branch' => $branch
                            ];

                            $emailQueue = new EmailQueue();
                            $emailQueue->setTo($email, $row['account'])
                                ->setSubject($subjectMail)
                                ->setTemplate('event::send_email.email.forgot_turn_off',
                                    $dataTemplateEmail);
                            $dataEmail[] = $emailQueue->getValue();
                            ForgotTurnOff::insertForgotDate($row);
                        } else {
                            $wrongAccount[] = $row['account'];
                        }
                    }
                }
            });

            if ($dataEmail) {
                EmailQueue::insert($dataEmail);

                //set notify
                \RkNotify::put(
                    Employee::whereIn('email', $arrayEmails)->lists('id')->toArray(),
                    trim(preg_replace('/\{\{(.*?)\}\}/si', '', $subject), '- '),
                    'https://mail.google.com',
                    ['actor_id' => null, 'icon' => 'employee.png']
                );
            }

            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('event::message.Upload successful, system will send email momentarily'),
                    Lang::get('event::message.Number email: :number', ['number' => count($dataEmail)])
                ]
            ];
            if ($wrongAccount) {
                foreach ($wrongAccount as $account) {
                    $messages['success'][] = Lang::get('event::message.account not found: :account',
                        ['account' => $account]);
                }
            }
            if ($countFail) {
                return redirect()->back()->withErrors(trans('event::message.max email import'));
            }

            return redirect()->back()->withInput()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages',
                ['errors' => [trans('event::message.Error system, please try again')]]);
        }
    }

    public function getListTurnOffData(array $data, $date)
    {
        $employeeIds = collect($data)->pluck('id')->toArray();
        $month = date('m', strtotime($date));
        $logOff = ForgotTurnOff::WhereIn('employee_id', $employeeIds)
            ->where('forgot_date', '!=', $date)
            ->get();
        $employee = [];
        if ($logOff) {
            foreach ($logOff as $value) {
                $value['quantity'] = 1;
                // Get total day and list date by employee_id.
                if (isset($value->employee_id)) {
                    $date = $value->forgot_date;
                    $date = date('Y-m-d', strtotime($date));

                    if (date('m', strtotime($date)) == $month) {
                        if (isset($employee[$value->employee_id])) {
                            $employee[$value->employee_id]['quantity'] += 1;
                            $employee[$value->employee_id]['list_date'] .= ', ' . $date;
                        } else {
                            $employee[$value->employee_id] = $value;
                            $employee[$value->employee_id]['list_date'] = $date;
                        }
                    }
                }
            }
        }

        foreach ($employee as $key => $product) {
            foreach ($data as $item) {
                if ($item['id'] == $product['employee_id']) {
                    $employee[$key]['email'] = $item['email'];
                }
            }
        }

        return array_values($employee);
    }
}
