<?php

namespace Rikkei\Files\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Log;
use Lang;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Team;
use Exception;
use Auth;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Files\Model\ManageFileText;
use Rikkei\Files\Model\ManageFileTeam;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Storage;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Files\Model\ManageFileGroupEmail;

class FileManagerController extends Controller
{
    /**
     * Show data file list All
     *
     * @return [view]
     */
    public function index()
    {
        Breadcrumb::add('File');
        Breadcrumb::add('List');
        Menu::setActive('File');
        $collectionModel = ManageFileText::getGridDataAll();
        return view('files::item.file_register_list', [
            'collectionModel' => $collectionModel
        ]);
    }

    /**
     * Show data file list
     * @return [view]
     */
    public function listItem($type, Request $request)
    {
        Breadcrumb::add('File');
        Breadcrumb::add('List');
        Menu::setActive('File');
        $status = $request->status;
        $collectionModel = ManageFileText::getGridData($type, $status);
        return view('files::item.file_register_list', [
            'collectionModel' => $collectionModel
        ]);
    }

    /**
     * Show data file Manage to View
     * @return [view]
     */
    public function add($type)
    {
        Breadcrumb::add('File');
        Breadcrumb::add('Add');
        Menu::setActive('File');
        $groupEmail = CoreConfigData::getGroupEmailRegisterLeave();
        $getLastIdTypeTo = ManageFileText::getLastIdTypeTo();
        $getLastIdTypeGo = ManageFileText::getLastIdTypeGo();
        $numberTo = '';
        $numberGo = '';
        if (!empty($getLastIdTypeTo)) {
            $numberTo = $getLastIdTypeTo;
        }
        if (!empty($getLastIdTypeGo)) {
            $numberGo = $getLastIdTypeGo;
        }
        if ($type == ManageFileText::CVDI) {
            return view('files::item.add_file_go', [
                'groupEmail' => $groupEmail,
                'numberTo' => $numberTo,
                'numberGo' => $numberGo,
            ]);
        } else {
             return view('files::item.add_file_to', [
                'groupEmail' => $groupEmail,
                'numberTo' => $numberTo,
                'numberGo' => $numberGo,
            ]);
        }
    }

    /**
     * save data file Manage to View
     * @return [view]
     */
    public function postAddFile(Request $request)
    {
        $dataRequest = $request->all();
        // check validate
        $rules = [
            'codeText' => 'required',
            'quote_text' => 'required'
        ];
        $messages = [
            'codeText.required' => Lang::get('files::view.The field is required'),
            'quote_text.required' => Lang::get('files::view.The field quote is required')
        ];
        $validator = Validator::make($dataRequest, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        //check exsit code_file
        if (ManageFileText::where('code_file', $request->codeText)->first()) {
            return redirect()->back()->withErrors(Lang::get('files::view.code file exsit'))->withInput();
        }

        // check validate and save file
        if ($request->hasFile('file_content')) {
            $file = $request->file_content;
            $extensions = array("csv", "xlsx", "doc", "pdf", "xls");
            $result = array($file->getClientOriginalExtension());
            if (!in_array($result[0], $extensions)) {
                return redirect()->back()
                        ->withErrors(Lang::get('files::view.Only allow file csv, xlsx, doc, xls, pdf'))->withInput();
            } else {
                $name = $file->getClientOriginalName();
                $fileExtension = $file->getClientOriginalExtension();
                Storage::disk('public')->put('filemanager/'.$name, file_get_contents($file));
            }
        }

        DB::beginTransaction();
        try {
            $type = $dataRequest['type'];
            $registerFile = new ManageFileText;
            $registerFile->type = $type;
            $registerFile->code_file = $dataRequest['codeText'];
            $registerFile->type_file = $dataRequest['type_file'];
            $registerFile->date_file = $dataRequest['date_file'];
            $registerFile->number_go = !empty($dataRequest['numberGo']) ? $dataRequest['numberGo'] : null;
            $registerFile->number_to = !empty($dataRequest['numberTo']) ? $dataRequest['numberTo'] : null;
            $registerFile->date_released = !empty($dataRequest['date_released']) ? $dataRequest['date_released'] : null;
            $registerFile->file_from = !empty($dataRequest['file_from']) ? $dataRequest['file_from'] : null;
            $registerFile->signer = !empty($dataRequest['signer']) ? $dataRequest['signer'] : null;
            $registerFile->tick = !empty($dataRequest['tick']) ? $dataRequest['tick'] : null;
            $registerFile->date_file_send = !empty($dataRequest['date_file_send']) ? $dataRequest['date_file_send'] : null;
            $registerFile->file_to = !empty($dataRequest['file_to']) ? $dataRequest['file_to'] : null;
            $registerFile->save_file = $dataRequest['save_file'];
            $registerFile->team_id = $dataRequest['groupTeam'];
            $registerFile->status = ManageFileText::UNAPPROVAL;
            $registerFile->quote_text = $dataRequest['quote_text'];
            $registerFile->note_text = $dataRequest['note_text'];
            if (!empty($dataRequest['content'])) {
                if ($dataRequest['type_file'] == ManageFileText::TYPEFILE_QD) {
                    $registerFile->content = null;
                } else {
                    $registerFile->content = $dataRequest['content'];
                }
            }
            $registerFile->file_content = !empty($name) ? $name : null;
            $registerFile->created_by = Auth::user()->employee_id;
            $data = [];

            if ($registerFile->save()) {
                // save and sendmail for approver
                if (!empty($dataRequest['related_persons_list'])) {
                    $registerFile->action = ManageFileText::ACTION_SUBMIT;
                    $registerRecordNew = ManageFileText::getInformationRegister($registerFile->id);
                    $approver = Employee::getEmpById($dataRequest['approver']);
                    $dataSendMail['to_id'] = $approver->id;
                    $dataSendMail['mail_to'] = $approver->email;
                    $dataSendMail['mail_name'] = $approver->name;
                    $dataSendMail['mail_title'] = Lang::get('files::view.approval required mail', ['code' => $registerRecordNew->code_file]);
                    $dataSendMail['noti_content'] = $dataSendMail['mail_title'];
                    $dataSendMail['link'] = route('file::file.editApproval', ['id' => $registerRecordNew->id]);
                    $template = 'files::item.mail.mail_send_notification_to_leader';
                    $notificationData = [
                        'category_id' => RkNotify::CATEGORY_ADMIN
                    ];
                    ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                }
                // save and sendmail with employee
                $files = $request->file_content;
                $relatedPersonsId = $request->related_persons_list;
                $registerRecordNew = ManageFileText::getInformationRegister($registerFile->id);
                if (!empty($relatedPersonsId)) {
                    $relatePersons = \Rikkei\Team\Model\Employee::getEmpByIds($relatedPersonsId);
                    foreach ($relatePersons as $person) {
                        $registerRelaters [] = array('register_id' => $registerFile->id, 'relater_id'=> $person->id);
                        $data['mail_to'] = $person->email;
                        $data['mail_name'] = $person->name;
                        $data['mail_title'] = Lang::get('files::view.Bạn nhận được văn bản đến:').' '.$registerRecordNew->code_file;
                        $data['quote_text'] = $registerRecordNew->quote_text;
                        $data['to_id'] = $person->id;
                        $data['signer'] = (!empty($registerRecordNew->signer)) ? Employee::getEmpById($registerRecordNew->signer)->name : null;
                        $data['team_id'] = Team::getTeamById($registerRecordNew->team_id)->name;
                        $data['code_file'] = $registerRecordNew->code_file;
                        $data['noti_content'] = $data['mail_title'];
                        $data['tick'] = $registerRecordNew->tick;
                        $data['note_text'] = $registerRecordNew->note_text;
                        $data['type'] = $registerRecordNew->type;
                        $data['content'] = $registerRecordNew->content;
                        $data['related_person_name'] = $person->name;
                        $data['link'] = route('file::file.editApproval', ['id' => $registerRecordNew->id]);
                        $data['file'] = $registerRecordNew->file_content;
                        $data['position'] = (!empty($registerRecordNew->signer)) ? Employee::getEmpById($registerRecordNew->signer)->position : null;
                        $template = 'files::item.mail.mail_register_manage_text';
                        if ($dataRequest['type'] == ManageFileText::CVDEN) {
                            $notificationData = [
                                'category_id' => RkNotify::CATEGORY_ADMIN
                            ];
                            ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);
                            DB::table('manage_file_text')->where('id', $registerFile->id)->update(
                                ['action' => ManageFileText::EMP_APPROVED],
                                ['status' => ManageFileText::APPROVAL]);
                        }
                    }
                    ManageFileTeam::insert($registerRelaters);
                    // update column status after sendmail
                    DB::table('manage_file_text')->where('id', $registerFile->id)->update(['status' => ManageFileText::APPROVAL]);
                }

                // save and sendmail with group email
                $groupEmail = $request->group_email;
                $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
                if (!empty($groupEmail)) {
                    $emailGroup = '';
                    $data['mail_title'] = Lang::get('files::view.Bạn nhận được văn bản đến:').' '.$registerRecordNew->code_file;
                    $data['quote_text'] = $registerRecordNew->quote_text;
                    $data['code_file'] = $registerRecordNew->code_file;
                    $data['to_id'] = $person->id;
                    $data['team_id'] = Team::getTeamById($registerRecordNew->team_id)->name;
                    $data['note_text'] = $registerRecordNew->note_text;
                    $data['signer'] = (!empty($registerRecordNew->signer)) ? Employee::getEmpById($registerRecordNew->signer)->name : null;
                    $data['tick'] = $registerRecordNew->tick;
                    $data['related_person_name'] = $person->name;
                    $data['content'] = $registerRecordNew->content;
                    $data['noti_content'] = $data['mail_title'];
                    $data['link'] = '#';
                    $data['type'] = $registerRecordNew->type;
                    $data['position'] = (!empty($registerRecordNew->signer)) ? Employee::getEmpById($registerRecordNew->signer)->position : null;
                    $data['file'] = $registerRecordNew->file_content;
                    $template = 'files::item.mail.mail_register_manage_text';

                    foreach ($groupEmail as $value) {
                        if (in_array($value, $groupEmailRegister)) {
                            $emailGroup = $emailGroup.$value.';';
                            $data['mail_to'] = $value;
                            $data['mail_name'] = strstr($value, '@', true);
                            ManageTimeCommon::pushEmailToQueue($data, $template);
                        }
                    }
                    $leaveDayGroupEmail = [
                        'register_id' => $registerFile->id,
                        'group_email' => rtrim($emailGroup, ";")
                    ];
                    ManageFileGroupEmail::insert($leaveDayGroupEmail);
                }
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('files::view.Register success'),
                ]
            ];
            return redirect()->route('file::file.editApproval', ['id' => $registerFile->id])->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('files::view.An error occurred')]]);
        }
    }

    /**
     * ajax get leader Team
     * @return [data]
     */
    public function getLeaderTeam(Request $request)
    {
        $code = $request->code;
        if ($code == 'bod') {
            $memberOfBod = Team::getMemberOfBod();
            if (!empty($memberOfBod)) {
                $data = $memberOfBod;
            } else {
                $data = null;
            }
        } else {
            $idLeader = Team::getLeaderOfTeam($request->idTeam);
            $info = Team::getLeaderById($idLeader);
            if (!empty($info)) {
                $data = ['name' => $info->name, 'id' => $info->id, 'position' => $info->position];
            } else {
                $data = null;
            }
        }
        return $data;
    }

    /**
     * ajax check code exist
     * @return [data]
     */
    public function checkCodeExist(Request $request)
    {
        if (ManageFileText::where('code_file', $request->codeText)->first()) {
            $data = ['error' => Lang::get('files::view.code file exsit')];
        } else {
            $data = [];
        }
        return $data;
    }

    /**
     * ajax get ceo company
     * @return [data]
     */
    public function getCeoCompany()
    {
        return ManageFileText::getCeoCompany();
    }

    /*
     * delete files
     */
    public function delete()
    {
        $id = Input::get('id');
        $file = ManageFileText::getFileById($id);

        if (! $file) {
            return redirect()->back()->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $file->delete();
        $messages = [
            'success'=> [
                Lang::get('team::messages.Delete item success!'),
            ]
        ];
        return redirect()->back()->with('messages', $messages);
    }

    /*
     * View edit files
     */
    public function editApproval(Request $request)
    {
        Breadcrumb::add('File');
        Breadcrumb::add('Detail');
        Menu::setActive('File');
        $id = $request->id;
        $file = ManageFileText::getFileById($id);
        // check empty $id _file
        if (! $file) {
            return redirect()->back()->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $item = ManageFileText::getAllFileById($id);
        $type = $item->type;
        $status = $item->status;
        $groupTeam = Team::getAllTeam();
        $groupEmail = CoreConfigData::getGroupEmailRegisterLeave();
        $groupEmailEdit = ManageFileGroupEmail::getGroupEmail($id);
        $groupTeamEdit = ManageFileTeam::getGroupTeam($id);
        if ($type == ManageFileText::CVDI) {
            return view('files::item.edit.edit_file_go', [
                'itemNumberGo' => $item,
                'status' => $status,
                'type' => $type,
                'groupTeam' => $groupTeam,
                'groupEmail' => $groupEmail,
                'groupEmailEdit' => $groupEmailEdit,
                'groupTeamEdit' => $groupTeamEdit
            ]);
        } else {
            return view('files::item.edit.edit_file_to', [
                'itemNumberTo' => $item,
                'status' => $status,
                'type' => $type,
                'groupTeam' => $groupTeam,
                'groupEmail' => $groupEmail,
                'groupEmailEdit' => $groupEmailEdit,
                'groupTeamEdit' => $groupTeamEdit
            ]);
        }
    }

    /*
     * update data  files
     */
    public function postEditFile(Request $request)
    {
        $dataRequest = $request->all();
        // check validate
        $rules = [
            'codeText' => 'required',
            'quote_text' => 'required'
        ];
        $messages = [
            'codeText.required' => Lang::get('files::view.The field is required'),
            'quote_text.required' => Lang::get('files::view.The field quote is required')
        ];
        $validator = Validator::make($dataRequest, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        //check exits item files
        $fileId = $request->file_id;
        if (!$fileId) {
            return redirect()->back()->withErrors(Lang::get('files::view.Not found item'));
        }

        // check validate and save file
        if ($request->hasFile('file_content')) {
            $file = $request->file_content;
            $extensions = array("csv", "xlsx", "doc", "pdf", "xls");
            $result = array($file->getClientOriginalExtension());
            if (!in_array($result[0], $extensions)) {
                return redirect()->back()
                        ->withErrors(Lang::get('files::view.Only allow file csv, xlsx, doc, xls, pdf'))->withInput();
            } else {
                $name = $file->getClientOriginalName();
                $fileExtension = $file->getClientOriginalExtension();
                Storage::disk('public')->put('filemanager/'.$name, file_get_contents($file));
            }
        }

        //get data file  by id
        $dataFile = ManageFileText::getFileById($fileId);
        DB::beginTransaction();
        try {
            $dataFile->code_file = !empty($dataRequest['codeText']) ? $dataRequest['codeText'] : null;
            $dataFile->type_file = !empty($dataRequest['type_file']) ? $dataRequest['type_file'] : null;
            $dataFile->date_file = !empty($dataRequest['date_file']) ? $dataRequest['date_file'] : null;
            $dataFile->quote_text = !empty($dataRequest['quote_text']) ? $dataRequest['quote_text'] : null;
            $dataFile->file_to = !empty($dataRequest['file_to']) ? $dataRequest['file_to'] : null;
            $dataFile->team_id = $dataRequest['groupTeam'];
            $dataFile->date_released = !empty($dataRequest['date_released']) ? $dataRequest['date_released'] : null;
            $dataFile->file_from = !empty($dataRequest['file_from']) ? $dataRequest['file_from'] : null;
            $dataFile->signer = !empty($dataRequest['signer']) ? $dataRequest['signer'] : null;
            $dataFile->save_file = $dataRequest['save_file'];
            $dataFile->tick = !empty($dataRequest['tick']) ? $dataRequest['tick'] : null;
            $dataFile->note_text = !empty($dataRequest['note_text']) ? $dataRequest['note_text'] : null;
            !empty($dataRequest['file_content']) ? $dataFile->file_content = $name : '';
            if (!empty($dataRequest['content'])) {
                if ($dataRequest['type_file'] == ManageFileText::TYPEFILE_QD) {
                    $dataFile->content = null;
                } else {
                    $dataFile->content = $dataRequest['content'];
                }
            }
            $dataFile->date_file_send = !empty($dataRequest['date_file_send']) ? $dataRequest['date_file_send'] : null;
            $dataFile->save();
            $data = [];
            if ($dataFile->save()) {
                // save and sendmail with employee
                $files = $request->file_content;
                $relatedPersonsId = $request->related_persons_list;
                $updateRecordNew = ManageFileText::getInformationRegister($dataFile->id);
                if (!empty($relatedPersonsId)) {
                    $relatePersons = \Rikkei\Team\Model\Employee::getEmpByIds($relatedPersonsId);
                    foreach ($relatePersons as $person) {
                        $registerRelaters [] = array('register_id' => $dataFile->id, 'relater_id'=> $person->id);
                        $data['mail_to'] = $person->email;
                        $data['mail_title'] = Lang::get('files::view.Bạn nhận được văn bản đến:').' '.$updateRecordNew->code_file;
                        $data['quote_text'] = $updateRecordNew->quote_text;
                        $data['to_id'] = $person->id;
                        $data['mail_name'] = $person->name;
                        $data['signer'] = (!empty($updateRecordNew->signer)) ? Employee::getEmpById($updateRecordNew->signer)->name : null;
                        $data['team_id'] = Team::getTeamById($updateRecordNew->team_id)->name;
                        $data['code_file'] = $updateRecordNew->code_file;
                        $data['noti_content'] = $data['mail_title'];
                        $data['tick'] = $updateRecordNew->tick;
                        $data['note_text'] = $updateRecordNew->note_text;
                        $data['type'] = $updateRecordNew->type;
                        $data['related_person_name'] = $person->name;
                        $data['content'] = $updateRecordNew->content;
                        $data['link'] = route('file::file.editApproval', ['id' => $updateRecordNew->id]);;
                        $data['file'] = $updateRecordNew->file_content;
                        $data['position'] = (!empty($updateRecordNew->signer)) ? Employee::getEmpById($updateRecordNew->signer)->position : null;
                        $template = 'files::item.mail.mail_register_manage_text';
                        if ($dataRequest['type'] == ManageFileText::CVDEN) {
                            $notificationData = [
                                'category_id' => RkNotify::CATEGORY_ADMIN
                            ];
                            ManageTimeCommon::pushEmailToQueue($data, $template, true, $notificationData);

                            DB::table('manage_file_text')->where('id', $dataFile->id)->update(
                                ['status' => ManageFileText::APPROVAL],
                                ['action' => ManageFileText::EMP_APPROVED]);
                        }
                    }
                    ManageFileTeam::insert($registerRelaters);
                    // update column status after sendmail
                    DB::table('manage_file_text')->where('id', $dataFile->id)->update(['status' => ManageFileText::APPROVAL]);
                }

                // save and sendmail with group email
                $groupEmail = $request->group_email;
                $groupEmailRegister = CoreConfigData::getGroupEmailRegisterLeave();
                if (!empty($groupEmail)) {
                    $emailGroup = '';
                    $data['mail_title'] = Lang::get('files::view.Bạn nhận được văn bản đến:').' '.$updateRecordNew->code_file;
                    $data['quote_text'] = $updateRecordNew->quote_text;
                    $data['code_file'] = $updateRecordNew->code_file;
                    $data['to_id'] = $person->id;
                    $data['team_id'] = Team::getTeamById($updateRecordNew->team_id)->name;
                    $data['note_text'] = $updateRecordNew->note_text;
                    $data['signer'] = !empty($updateRecordNew->signer) ? Employee::getEmpById($updateRecordNew->signer)->name : null;
                    $data['tick'] = $updateRecordNew->tick;
                    $data['type'] = $updateRecordNew->type;
                    $data['related_person_name'] = $person->name;
                    $data['noti_content'] = $data['mail_title'];
                    $data['content'] = $updateRecordNew->content;
                    $data['link'] = '#';
                    $data['position'] = !empty($updateRecordNew->signer) ? Employee::getEmpById($updateRecordNew->signer)->position : null;
                    $data['file'] = $updateRecordNew->file_content;
                    $template = 'files::item.mail.mail_register_manage_text';

                    foreach ($groupEmail as $value) {
                        if (in_array($value, $groupEmailRegister)) {
                            $emailGroup = $emailGroup.$value.';';
                            $data['mail_to'] = $value;
                            $data['mail_name'] = strstr($value, '@', true);
                            ManageTimeCommon::pushEmailToQueue($data, $template);
                        }
                    }
                    $leaveDayGroupEmail = [
                        'register_id' => $dataFile->id,
                        'group_email' => rtrim($emailGroup, ";")
                    ];
                    ManageFileGroupEmail::insert($leaveDayGroupEmail);
                }
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('files::view.Update success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }
}
