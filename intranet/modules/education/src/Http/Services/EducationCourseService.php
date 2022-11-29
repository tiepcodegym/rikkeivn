<?php

namespace Rikkei\Education\Http\Services;

use Carbon\Carbon;
use Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Lang;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\User;
use Rikkei\Education\Model\EducationClass;
use Rikkei\Education\Model\EducationClassDetail;
use Rikkei\Education\Model\EducationClassDocument;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Model\EducationCourseTeam;
use Rikkei\Education\Model\EducationTeacher;
use Rikkei\Education\Model\EducationTeacherWithout;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;

class EducationCourseService
{

    const SCOPE_COMPANY = 1;
    const SCOPE_BRANCH = 2;
    const SCOPE_DIVISION = 3;

    const STATUS_NEW = 1;
    const STATUS_REGISTER = 2;
    const STATUS_OPEN = 3;
    const STATUS_PENDING = 4;
    const STATUS_FINISH= 5;

    const ROLE_EMPLOYEE = 1;
    const ROLE_TEACHER = 2;

    // From course
    const FORM_COURSE = 1;
    const FORM_VOCATIONAL = 2;

    const HR_ID = 11;

    const CACHE_FOLDER = 'Education/';
    const UPLOAD_DIR = 'education';

    /**
     * insert or update data
     * @param type $data
     * @return boolean
     */
    public static function insertOrUpdate($data = [], $id)
    {
        $uploadDir = trim(self::UPLOAD_DIR, '/');
        if ($data && count($data)) {
            foreach ($data as $key => $item) {
                $fileBase64 = $item['base64'];
                $mineType = $item['type'];
                $name = $item['name'];
                $nameExplode  = explode('.', $name);
                $extension = end($nameExplode);
                $reName = $name;
                if ($name) {
                    $reName = md5($name) . str_random();
                }

                Storage::disk('public')->put($uploadDir . '/' . $reName . '.' . $extension, base64_decode($fileBase64), 'public');
                $url = $reName . '.' . $extension;
                EducationClassDocument::create([
                    'class_id' => $id,
                    'name' => $name,
                    'url' => $url,
                    'content' => '',
                    'type' => 'file',
                    'minetype' => $mineType
                ]);
            }
        }
    }

    /**
     * delete image
     * @param type $data
     * @return boolean
     */
    public static function deleteImage($dataImage) {
        try {
            EducationClassDocument::whereIn('id', $dataImage)
                ->delete();
        } catch (\Exception $ex) {
            return response()->json($ex->getMessage(), 500);
        }
    }

    /**
     * insert data
     * @param $file = null, $id, $randName = true
     * @return boolean
     */
    public static function insertData($file = null, $id, $randName = true)
    {
        $fileUrl = null;
        if ($file) {
            //create folder upload
            DocConst::makeUploadDir();
            $uploadDir = trim(DocConst::UPLOAD_DIR, '/');
            $fileName = $file[0]->getClientOriginalName();
            $fileUrl = self::checkRename($fileName, $randName);
            $mimeType = $file[0]->getClientMimeType();
            $fileType = 'file';
            //move file upload
            Storage::disk('public')->put($uploadDir . '/' . $fileUrl, file_get_contents($file), 'public');
        } else {
            $fileName = $fileUrl;
            $mimeType = 'link';
            $fileType = 'link';
        }
        $fileModel = self::create([
            'name' => $fileName,
            'url' => $fileUrl,
            'mimetype' => $mimeType,
            'type' => $fileType,
            'class_id' => $id
        ]);
        return $fileModel;
    }

    /**
     * check rename
     * @param $originalName, $randName = true
     * @return boolean
     */
    public static function checkRename($originalName, $randName = true)
    {
        $uploadDir = trim(DocConst::UPLOAD_DIR, '/');
        $arrName = explode('.', $originalName);
        $extension = array_pop($arrName);
        $name = str_slug(implode('.', $arrName));
        $reName = $name;
        if ($randName) {
            $reName = md5($name) . str_random();
        }
        $i = 1;
        while (Storage::disk('public')->exists($uploadDir . '/' . $reName . '.' . $extension)) {
            $reName = $name . '-' . $i;
            $i++;
        }
        return $reName . '.' . $extension;
    }

    /*
     * check branch company
     */
    public static function checkBranchCompany($multi_id)
    {
        return DB::table('teams')
            ->whereIn('teams.id', $multi_id)
            ->where('teams.is_branch', 0)
            ->select('*')
            ->get();
    }

    /**
     * get education scope
     * @return array
     */
    public static function getScopeTotal()
    {
        return [
            self::SCOPE_COMPANY => trans('education::view.Education.scope.Company'),
            self::SCOPE_BRANCH => trans('education::view.Education.scope.Branch'),
            self::SCOPE_DIVISION => trans('education::view.Education.scope.Division'),
        ];
    }

    /**
     * get course form
     * @return array
     */
    public static function getCourseForm()
    {
        return [
            self::FORM_COURSE => trans('education::view.Course'),
            self::FORM_VOCATIONAL => trans('education::view.Vocational'),
        ];
    }

    /*
     * add course
     */
    public static function addCourse($request)
    {
        if ($request) {
            $returnData = [
                'message' => '',
                'flag' => '',
                'data' => '',
                'url' => ''
            ];

            // Define send mail not yet
            $isSent = false;
            $courseExist = DB::table('education_courses')->where('course_code',$request['course_code'])->first();
            $oldData = $courseExist;
            if ($courseExist === null) {
                // Validate Course formality
                $courseForm = array_keys(self::getCourseForm());
                if (!in_array($request['course_form'], $courseForm)) {
                    $returnData['message'] = trans('education::message.The formality not exists');
                    $returnData['flag'] = false;
                    return $returnData;
                }

                // Check formality and is_mail_list checked
                if ($request['is_mail_list'] == "true") {
                    if ($request['course_form'] == self::FORM_VOCATIONAL && count($request['dataClass']) != 1 && count($request['dataClass'][0]['startCa']) != 1) {
                        $returnData['message'] = trans('education::message.The form of vocational training applies only to 1 classroom and 1 shift');
                        $returnData['flag'] = false;
                        return $returnData;
                    }
                }

                DB::beginTransaction();
                try {
                    // add data course
                    $addCourse = EducationCourse::create([
                        'course_code' => $request['course_code'],
                        'name' => $request['title'],
                        'status' => self::STATUS_NEW,
                        'hours' => $request['total_hours'],
                        'type' => $request['education_type'],
                        'description' => $request['description'],
                        'target' => $request['target'],
                        'hr_id' => $request['powerful_id'],
                        'scope_total' => $request['scope_total'],
                        'course_form' => $request['course_form'],
                        'is_mail_list' => $request['is_mail_list'] == 'true' ? '1' : '0'
                    ]);
                    $idCourse = $addCourse->id;
                    // process add team
                    if (isset($request['team_id']) && $request['team_id']) {
                        foreach ($request['team_id'] as $key => $value) {
                            EducationCourseTeam::create(
                                [
                                    'course_id' => $idCourse,
                                    'team_id' => $value
                                ]
                            );
                        }
                    }
                    // process for data class
                    if (count($request['dataClass']) > 0) {
                        foreach ($request['dataClass'] as $key => $value) {
                            // process check rent teacher
                            if ($value['rent_value'] != '') {
                                if ($value['is_rent'] == 1) {
                                    $addTeacherWithout = EducationTeacherWithout::create(
                                        [
                                            'name' => $value['rent_value']
                                        ]
                                    );
                                    $related_id = $addTeacherWithout->id;
                                    $related_name = 'teacher_without';
                                } else {
                                    $related_id = $value['rent_value'];
                                    $related_name = 'employee';
                                }
                            } else {
                                if ($value['is_rent'] == 1) {
                                    $related_id = '';
                                    $related_name = 'teacher_without';
                                } else {
                                    $related_id = '';
                                    $related_name = 'employee';
                                }
                            }
                            // process add class
                            $addClass = EducationClass::create(
                                [
                                    'course_id' => $idCourse,
                                    'class_code' => $value['class_code'],
                                    'course_code' => $request['course_code'],
                                    'class_name' => $value['class_title'],
                                    'related_id' => $related_id,
                                    'related_name' => $related_name,
                                    'start_date' => $value['commitment_value_start'],
                                    'end_date' => $value['commitment_value_end'],
                                    'is_commitment' => $value['is_commitment']
                                ]
                            );
                            // upload file
                            $idClass = $addClass->id;
//                            if (isset($value['files']) && $value['files']) {
//                                EducationCourseService::insertOrUpdate($value['files'], $idClass);
//
//                                // push mail/notify for file
//                                EducationCourseService::pushNotificationAndEmailForFile($idClass, $idCourse);
//                            }
                            // process add shift for class
                            if (count($value['startCa']) > 0) {
                                foreach ($value['startCa'] as $keyCa => $valueCa) {
                                    $addShift = EducationClassShift::create(
                                        [
                                            'class_id' => $idClass,
                                            'class_code' => $value['class_code'],
                                            'name' => $keyCa + 1,
                                            'start_date_time' => $valueCa,
                                            'end_date_time' => $value['endCa'][$keyCa],
                                            'event_id' => $value['event_id'][$keyCa],
                                            'location_name' => $value['location_name'][$keyCa],
                                            'end_time_register' => $value['end_time_register'][$keyCa],
                                            'calendar_id' => $value['calendar_id'][$keyCa],
                                        ]
                                    );
                                    $idShift = $addShift->id;
                                    if ($related_name == 'employee') {
                                        DB::table('education_class_details')->insert(
                                            [
                                                'employee_id' => $related_id,
                                                'class_id' => $idClass,
                                                'role' => self::ROLE_TEACHER,
                                                'shift_id' => $idShift
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                    }

                    if (isset($request['teaching_id']) && !empty($request['teaching_id'])) {
                        $items = EducationTeacher::find($request['teaching_id']);
                        $items->status = EducationTeacher::STATUS_ARRANGEMENT;
                        $items->course_id = $idCourse;
                        $items->class_id = $idClass;
                        $items->tranning_manage_id = $addCourse->hr_id;
                        $items->save();
                    }

                    // process sent mail
                    if (isset($request['team_id']) && $request['team_id']) {
                        // Check submit button Send ( send mail )
                        if (isset($request['send_mail']) && $request['send_mail'] == "true") {
                            $item = [
                                'title' => $request['title'],
                                'team_id' => $request['team_id'],
                                'old_data' => $oldData,
                                'course_id' => $idCourse,
                                'scope_total' => $request['scope_total'],
                                'hr_id' => $request['powerful_id'],
                                'data_class' => $request['dataClass'],
                                'course_form' => $request['course_form'],
                                'is_mail_list' => isset($request['is_mail_list']) ? $request['is_mail_list'] : false,
                                'template_mail' => isset($request['templateMail']) ? $request['templateMail'] : '',
                                'title_template_mail' => isset($request['titleTemplateMail']) ? $request['titleTemplateMail'] : '',
                                'is_rent_checked' => isset($request['is_rent_checked']) ? $request['is_rent_checked'] : '0',
                                'teacher_name' => (isset($request['is_rent_checked']) && $request['is_rent_checked'] == '1') ? $request['teacher_name'] : '',
                            ];

                            // Check send mail is success, update status is 'Open' class
                            $isSent = self::checkStatusSendMailOrNotifyByHr($request['send_mail'], $request['status'], $item);
                            if ($isSent && $request['status'] == self::STATUS_NEW) {
                                DB::table('education_courses')->where('id', $idCourse)
                                    ->update(['status' => self::STATUS_REGISTER]);
                            }
                        }
                    }

                    DB::commit();
                    $returnData['message'] = trans('education::view.Education.Course success');
                    $returnData['flag'] = true;
                    $returnData['data'] = $idCourse;
                    $returnData['url'] = route('education::education.detail', ['id' => $idCourse, 'flag' => 1]);
                    return $returnData;
                } catch (Exception $ex) {
                    DB::rollback();
                    Log::info($ex->getMessage());
                    $returnData['message'] = trans('education::view.Education.Error system');
                    $returnData['flag'] = false;
                    return $returnData;
                }
            } else {
                $returnData['message'] = trans('education::view.Education.Course exist');
                $returnData['flag'] = false;
                return $returnData;
            }
        } else {
            $returnData['message'] = trans('education::view.Education.Error system');
            $returnData['flag'] = false;
            return $returnData;
        }
    }

    /*
     * update course
     */
    public static function updateCourse($request)
    {
        DB::beginTransaction();
        try {
            if (isset($request['dataCourseUpdate']) && $request['dataCourseUpdate']) {
                // process update course
                DB::table('education_courses')
                    ->where('id', $request['dataCourseUpdate']['id'])
                    ->update([
                        'education_cost' => str_replace(',', '', $request['dataCourseUpdate']['education_cost']),
                        'teacher_cost' => str_replace(',', '', $request['dataCourseUpdate']['teacher_cost']),
                        'teacher_feedback' => $request['dataCourseUpdate']['teacher_feedback'],
                        'hr_feedback' => $request['dataCourseUpdate']['hr_feedback']
                    ]);
                // process update shift
                DB::table('education_class_shifts')
                    ->join("education_class", "education_class.id", '=', "education_class_shifts.class_id")
                    ->join("education_courses", "education_courses.id", '=', "education_class.course_id")
                    ->where('education_courses.id', $request['dataCourseUpdate']['id'])
                    ->update([
                        'is_finish' => 0,
                    ]);
                if (isset($request['dataCourseUpdate']['is_finish']) && $request['dataCourseUpdate']['is_finish']) {
                    DB::table('education_class_shifts')
                        ->whereIn('id', $request['dataCourseUpdate']['is_finish'])
                        ->update([
                            'is_finish' => 1,
                        ]);
                }
            }
            // process delete class details
            if (isset($request['delete']) && $request['delete']) {
                foreach ($request['delete'] as $key => $value) {
                    DB::table('education_class_details')
                        ->join("education_class", "education_class.id", '=', "education_class_details.class_id")
                        ->join("education_courses", "education_courses.id", '=', "education_class.course_id")
                        ->where('education_courses' . '.id', $request['dataCourseUpdate']['id'])
                        ->where('education_class_details.employee_id', $value)
                        ->where('education_class_details.role', self::ROLE_EMPLOYEE)
                        ->delete();
                }
            }
            // process insert or update class detail
            if (isset($request['updateOrCreate']) && $request['updateOrCreate']) {
                foreach ($request['updateOrCreate'] as $key => $value) {
                    // get data class detail exist
                    DB::table('education_class_details')
                        ->join("education_class", "education_class.id", '=', "education_class_details.class_id")
                        ->join("education_courses", "education_courses.id", '=', "education_class.course_id")
                        ->where('education_courses' . '.id', $request['dataCourseUpdate']['id'])
                        ->where('education_class_details' . '.employee_id', $value['employee_id'])
                        ->delete();
                    if (isset($value['class_id']) && $value['class_id']) {
                        if ($value['class_id'][0] == ',') {
                            $dataRmComma = substr($value['class_id'], 1);
                        } else {
                            $dataRmComma = $value['class_id'];
                        }
                        $arrayConvert = explode(',', $dataRmComma);
                        // process add shift
                        foreach ($arrayConvert as $keyClass => $valueClass) {
                            $valueClassIm = explode("-", $valueClass)[0];
                            $valueShiftIm = explode("-", $valueClass)[1];
                            DB::table('education_class_details')->insert(
                                [
                                    'employee_id'               => $value['employee_id'],
                                    'class_id'                  => $valueClassIm,
                                    'shift_id'                  => $valueShiftIm,
                                    'feedback_teacher_point'    => $value['teacher_point'],
                                    'feedback_company_point'    => $value['company_point'],
                                    'feedback'                  => $value['feedback'],
                                    'is_attend'                 => 1,
                                    'is_hr_added'               => 1
                                ]
                            );
                        }
                    }
                    // process add feedback
                    if (isset($value['class_attend_id']) && $value['class_attend_id']) {
                        $arrayConvert = explode(',', $value['class_attend_id']);
                        foreach ($arrayConvert as $keyClass => $valueClass) {
                            $valueClassIm = explode("-", $valueClass)[0];
                            $valueShiftIm = explode("-", $valueClass)[1];
                            DB::table('education_class_details')
                                ->where([
                                    'employee_id' => $value['employee_id'],
                                    'class_id' => $valueClassIm,
                                    'shift_id' => $valueShiftIm
                                ])
                                ->update([
                                        'feedback_teacher_point' => $value['teacher_point'],
                                        'feedback_company_point' => $value['company_point'],
                                        'feedback' => $value['feedback'],
                                        'is_attend' => 1
                                    ]
                                );
                        }
                    }
                }
            }

            if (isset($request['teaching_id'])) {
                $items = EducationTeacher::find($request['teaching_id']);
                $items->status = EducationTeacher::STATUS_ARRANGEMENT;
                $items->save();
            }

            DB::commit();
            $returnData['message'] = trans('education::view.Education.Course save success');
            $returnData['flag'] = true;
            $returnData['data'] = '';
            $returnData['url'] = '';
            return $returnData;
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            $returnData['message'] = '';
            $returnData['flag'] = false;
            $returnData['data'] = '';
            $returnData['url'] = '';
            return $returnData;
        }
    }

    /*
     * update course information
     */
    public static function updateCourseInfo($request)
    {
        if ($request) {
            $returnData = [
                'message' => '',
                'flag' => '',
                'data' => '',
                'url' => ''
            ];
            $courseExist = DB::table('education_courses')->where('id', $request['id'])->first();
            // check course exist
            if ($courseExist === null) {
                $returnData['message'] = trans('education::view.Education.Course not exist');
                $returnData['flag'] = false;
                return $returnData;
            } else {
                // Validate Course formality
                $courseForm = array_keys(self::getCourseForm());
                if (!in_array($request['course_form'], $courseForm)) {
                    $returnData['message'] = trans('education::message.The formality not exists');
                    $returnData['flag'] = false;
                    return $returnData;
                }

                // Check formality and is_mail_list checked
                if ($request['is_mail_list'] == "true") {
                    if ($request['course_form'] == self::FORM_VOCATIONAL && count($request['dataClass']) != 1 && count($request['dataClass'][0]['startCa']) != 1) {
                        $returnData['message'] = trans('education::message.The form of vocational training applies only to 1 classroom and 1 shift');
                        $returnData['flag'] = false;
                        return $returnData;
                    }
                }
                DB::beginTransaction();
                try {
                    // update course
                    $oldData = $courseExist;
                    DB::table('education_courses')
                        ->where('id', $request['id'])
                        ->update([
                            'course_code' => $request['course_code'],
                            'name' => $request['title'],
                            'status' => $request['status'],
                            'hours' => $request['total_hours'],
                            'type' => $request['education_type'],
                            'description' => $request['description'],
                            'target' => $request['target'],
                            'hr_id' => $request['powerful_id'],
                            'scope_total' => $request['scope_total'],
                            'course_form' => $request['course_form'],
                            'is_mail_list' => $request['is_mail_list'] == 'true' ? '1' : '0'
                        ]);
                    $idCourse = $courseExist->id;
                    // delete course team
                    DB::table('education_course_teams')
                        ->where('course_id', $idCourse)
                        ->delete();
                    // insert team id
                    if ($request['team_id'] == '') {
                        DB::table('education_course_teams')->insert(
                            [
                                'course_id' => $idCourse,
                                'team_id' => 0
                            ]
                        );
                    } else {
                        foreach ($request['team_id'] as $key => $value) {
                            DB::table('education_course_teams')->insert(
                                [
                                    'course_id' => $idCourse,
                                    'team_id' => $value
                                ]
                            );
                        }
                    }
                    // delete class
                    if (isset($request['dataClassDelete']) && count($request['dataClassDelete']) > 0) {
                        foreach ($request['dataClassDelete'] as $key => $value) {
                            DB::table('education_class')
                                ->where('id', $value)
                                ->delete();
                        }
                    }
                    // delete shift
                    if (isset($request['dataShiftDelete']) && count($request['dataShiftDelete']) > 0) {
                        foreach ($request['dataShiftDelete'] as $key => $value) {
                            DB::table('education_class_shifts')
                                ->where('id', $value)
                                ->delete();
                        }
                    }
                    // check class exist
                    if (count($request['dataClass']) > 0) {
                        foreach ($request['dataClass'] as $key => $value) {
                            // check class update or new
                            if ($value['class_id']) {
                                // get data class old
                                $dataClass = DB::table('education_class')
                                    ->where('education_class.id', $value['class_id'])
                                    ->get();
                                // process for teacher rent
                                if ($value['is_rent'] == 1 && $dataClass[0]->related_name == 'teacher_without') {
                                    DB::table('education_teacher_withouts')
                                        ->where('id', $dataClass[0]->related_id)
                                        ->update([
                                            'name' => $value['rent_value'],
                                        ]);
                                    $related_id = $dataClass[0]->related_id;
                                    $related_name = 'teacher_without';
                                } else if ($value['is_rent'] == 1 && $dataClass[0]->related_name == 'employee') {
                                    $addTeacherWithout = EducationTeacherWithout::create(
                                        [
                                            'name' => $value['rent_value']
                                        ]
                                    );
                                    $related_id = $addTeacherWithout->id;
                                    $related_name = 'teacher_without';
                                } else if ($value['is_rent'] == 0 && $dataClass[0]->related_name == 'teacher_without') {
                                    DB::table('education_teacher_withouts')
                                        ->where('id', $dataClass[0]->related_id)
                                        ->delete();
                                    $related_id = $value['rent_value'];
                                    $related_name = 'employee';
                                } else if ($value['is_rent'] == 0 && $dataClass[0]->related_name == 'employee') {
                                    $related_id = $value['rent_value'];
                                    $related_name = 'employee';
                                } else if ($value['is_rent'] == 1 && $dataClass[0]->related_name == '') {
                                    $addTeacherWithout = EducationTeacherWithout::create(
                                        [
                                            'name' => $value['rent_value']
                                        ]
                                    );
                                    $related_id = $addTeacherWithout->id;
                                    $related_name = 'teacher_without';
                                } else if ($value['is_rent'] == 0 && $dataClass[0]->related_name == '') {
                                    $related_id = $value['rent_value'];
                                    $related_name = 'employee';
                                }
                                // update class
                                DB::table('education_class')
                                    ->where('id', $value['class_id'])
                                    ->update([
                                        'class_code' => $value['class_code'],
                                        'course_code' => $request['course_code'],
                                        'class_name' => $value['class_title'],
                                        'related_id' => $related_id,
                                        'related_name' => $related_name,
                                        'start_date' => $value['commitment_value_start'],
                                        'end_date' => $value['commitment_value_end'],
                                        'is_commitment' => $value['is_commitment']
                                    ]);
                                $idClass = $value['class_id'];
                                //delete class detail
                                DB::table('education_class_details')
                                    ->where('class_id', $idClass)
                                    ->where('role', self::ROLE_TEACHER)
                                    ->delete();
                                // upload file
                                if (isset($request['dataImageDelete']) && count($request['dataImageDelete'])) {
                                    EducationCourseService::deleteImage($request['dataImageDelete']);
                                }
                                if (isset($value['files']) && $value['files']) {
                                    $files = $value['files'];
                                    if ($files) {
                                        EducationCourseService::insertOrUpdate($files, $idClass);

                                        // push mail/notify for file
                                        EducationCourseService::pushNotificationAndEmailForFile($idClass, $idCourse);
                                    }
                                }
                                // process update shift
                                if (isset($value['startCa']) && count($value['startCa']) > 0) {
                                    foreach ($value['startCa'] as $keyCa => $valueCa) {
                                        $tourist = EducationClassShift::updateOrCreate([
                                            'class_id' => $value['class_id'],
                                            'name' => $value['className'][$keyCa]
                                        ], [
                                            'end_time_register' => $value['end_time_register'][$keyCa]
                                        ]);
                                        $tourist->save();
                                        if ($value['event_id'][$keyCa] != '0') {
                                            $tourist = EducationClassShift::updateOrCreate([
                                                'class_id' => $value['class_id'],
                                                'name' => $value['className'][$keyCa]
                                            ], [
                                                'class_code' => $value['class_code'],
                                                'start_date_time' => $valueCa,
                                                'end_date_time' => $value['endCa'][$keyCa],
                                                'event_id' => $value['event_id'][$keyCa],
                                                'location_name' => $value['location_name'][$keyCa],
                                                'calendar_id' => $value['calendar_id'][$keyCa],
                                            ]);
                                            $tourist->save();
                                            $idShift = $tourist->id;
                                            if ($related_name == 'employee') {
                                                DB::table('education_class_details')->insert(
                                                    [
                                                        'employee_id' => $related_id,
                                                        'class_id' => $idClass,
                                                        'role' => self::ROLE_TEACHER,
                                                        'shift_id' => $idShift
                                                    ]
                                                );
                                            }
                                        }
                                    }
                                }
                            } else {
                                // process for teacher rent
                                if ($value['is_rent'] == 1) {
                                    $addTeacherWithout = EducationTeacherWithout::create(
                                        [
                                            'name' => $value['rent_value']
                                        ]
                                    );
                                    $related_id = $addTeacherWithout->id;
                                    $related_name = 'teacher_without';
                                } else {
                                    $related_id = $value['rent_value'];
                                    $related_name = 'employee';
                                }
                                // process add class
                                $addClass = EducationClass::create(
                                    [
                                        'course_id' => $idCourse,
                                        'class_code' => $value['class_code'],
                                        'course_code' => $request['course_code'],
                                        'class_name' => $value['class_title'],
                                        'related_id' => $related_id,
                                        'related_name' => $related_name,
                                        'start_date' => $value['commitment_value_start'],
                                        'end_date' => $value['commitment_value_end'],
                                        'is_commitment' => $value['is_commitment']
                                    ]
                                );
                                $idClass = $addClass->id;
                                // upload file
                                if (isset($value['files']) && $value['files']) {
                                    EducationCourseService::insertOrUpdate($value['files'], $idClass);

                                    // push mail/notify for file
                                    EducationCourseService::pushNotificationAndEmailForFile($idClass, $idCourse);
                                }
                                // process add shift
                                if (count($value['startCa']) > 0) {
                                    foreach ($value['startCa'] as $keyCa => $valueCa) {
                                        $addShift = EducationClassShift::create(
                                            [
                                                'class_id' => $idClass,
                                                'class_code' => $value['class_code'],
                                                'name' => $keyCa + 1,
                                                'start_date_time' => $valueCa,
                                                'end_date_time' => $value['endCa'][$keyCa],
                                                'event_id' => $value['event_id'][$keyCa],
                                                'location_name' => $value['location_name'][$keyCa],
                                                'end_time_register' => $value['end_time_register'][$keyCa],
                                                'calendar_id' => $value['calendar_id'][$keyCa],
                                            ]
                                        );
                                        $idShift = $addShift->id;
                                        if ($related_name == 'employee') {
                                            DB::table('education_class_details')->insert(
                                                [
                                                    'employee_id' => $related_id,
                                                    'class_id' => $idClass,
                                                    'role' => self::ROLE_TEACHER,
                                                    'shift_id' => $idShift
                                                ]
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Check send mail is success, update status is 'Open' class
                    if ($request['team_id'] != '') {
                        $item = [
                            'title' => $request['title'],
                            'team_id' => $request['team_id'],
                            'old_data' => (array)$oldData,
                            'course_id' => $idCourse,
                            'scope_total' => $request['scope_total'],
                            'hr_id' => $request['powerful_id'],
                            'template_mail' => isset($request['templateMail']) ? $request['templateMail'] : '',
                            'title_template_mail' => isset($request['titleTemplateMail']) ? $request['titleTemplateMail'] : '',
                            'data_class' => $request['dataClass'],
                            'course_form' => $request['course_form'],
                            'is_mail_list' => isset($request['is_mail_list']) ? $request['is_mail_list'] : false,
                            'is_rent_checked' => isset($request['is_rent_checked']) ? $request['is_rent_checked'] : '0',
                            'teacher_name' => (isset($request['is_rent_checked']) && $request['is_rent_checked'] == '1') ? $request['teacher_name'] : '',
                        ];
                        if (isset($request['send_mail']) && $request['send_mail']) {
                            $isSent = self::checkStatusSendMailOrNotifyByHr($request['send_mail'], $request['status'], $item);
                            // Check click button send and status is create new
                            if ($isSent && $request['status'] == self::STATUS_NEW) {
                                DB::table('education_courses')->where('id', $idCourse)
                                    ->update(['status' => self::STATUS_REGISTER]);
                            }
                        }
                    }

                    DB::commit();
                    $returnData['message'] = trans('education::view.Education.Course save success');
                    $returnData['flag'] = true;
                    $returnData['data'] = $idCourse;
                    $returnData['url'] = route('education::education.detail', ['id' => $idCourse, 'flag' => 0]);
                    return $returnData;
                } catch (Exception $ex) {
                    DB::rollback();
                    Log::info($ex->getMessage());
                    $returnData['message'] = trans('education::view.Education.Error system');
                    $returnData['flag'] = false;
                    return $returnData;
                }
            }
        } else {
            $returnData['message'] = trans('education::view.Education.Error system');
            $returnData['flag'] = false;
            return $returnData;
        }
    }

    /*
     * get detail by course_id
     */
    public static function getDetailByCourseId($dataSearch, $urlFilter, $isExport = false)
    {

        $pager = Config::getPagerData($urlFilter);
        if ($dataSearch['course_id']) {
            $id = $dataSearch['course_id'];
        }
        // process filter data
        $collection = DB::table('education_class_details')
            ->join(DB::raw('(
                SELECT 
                    employees.id AS employee_id,
                    employees.name AS employees_name,
                    employees.employee_code,
                    employees.nickname,
                    employees.email,
                    GROUP_CONCAT(teams.name) AS team_names,
                    GROUP_CONCAT(teams.id) AS team_id
                FROM employees
                INNER JOIN team_members ON team_members.employee_id = employees.id
                INNER JOIN teams ON team_members.team_id = teams.id
                GROUP BY employees.id ) AS team_names'), 'team_names.employee_id', '=', 'education_class_details.employee_id')
            ->leftJoin(DB::raw('(
                SELECT
                    education_class_details.id AS attend_id,
                    education_class.class_code AS class_attend_code,
                    education_class.id AS class_attend_id,
                    education_class.class_name,
                    education_class_shifts.name AS shift_attend_name,
                    education_class_shifts.id AS shift_attend_id
                FROM education_class_details
                INNER JOIN education_class ON education_class.id = education_class_details.class_id
                LEFT JOIN education_class_shifts ON education_class_shifts.id = education_class_details.shift_id
                INNER JOIN education_courses ON education_courses.id = education_class.course_id
                WHERE education_class_details.is_attend = 1 AND education_courses.id = ' . $id . ') AS class_attend'), 'class_attend.attend_id', '=', 'education_class_details.id')
            ->join("education_class", "education_class.id", "=", "education_class_details.class_id")
            ->join("education_courses", "education_courses.id", "=", "education_class.course_id")
            ->leftjoin("education_class_shifts", "education_class_shifts.id", "=", "education_class_details.shift_id")
            ->where('education_courses.id', $id)
            ->where('education_class_details.role', self::ROLE_EMPLOYEE)
            ->select(
                'education_courses.name as course_name',
                'education_class.class_name',
                'education_class_details.id',
                'education_class_details.class_id',
                'team_names.employee_id',
                'team_names.employees_name as employees_name',
                'team_names.employee_code',
                'team_names.nickname',
                'team_names.email',
                'team_names.team_names',
                DB::raw('group_concat(CONCAT(education_class.id, "' . "-" . '", education_class_shifts.id) SEPARATOR ",") as class_group_id'),
                DB::raw('group_concat(CONCAT(class_attend.class_attend_id, "' . "-" . '", class_attend.shift_attend_id) SEPARATOR ",") as class_group_attend'),
                DB::raw('group_concat(CONCAT(education_class.class_code, "' . " " . trans('education::view.Education.Class') . " " . '", class_attend.class_name) SEPARATOR ",") as class_concat'),
                DB::raw('group_concat(CONCAT(class_attend.class_attend_code, "' . " " . trans('education::view.Education.Ca2') . " " . '", class_attend.shift_attend_name) SEPARATOR ",") as class_attend'),
                DB::raw('education_class_details.feedback_teacher_point as feedback_teacher_point'),
                DB::raw('education_class_details.feedback_company_point as feedback_company_point'),
                DB::raw('education_class_details.feedback as feedback'),
                'education_class_shifts.name as shift_name',
                'education_class_shifts.id as shift_id'
            )
            ->groupBy("education_courses.id")
            ->groupBy("team_names.employee_id");
        // filter data with email
        if (!empty($dataSearch['email'])) {
            $employee_email = $dataSearch['email'];
            $collection->where('team_names.email', 'LIKE', "%{$employee_email}%");
        }
        // filter data with employee name
        if (!empty($dataSearch['employee_name'])) {
            $employee_name = $dataSearch['employee_name'];
            $collection->where('team_names.employees_name', 'LIKE', "%{$employee_name}%");
        }
        // filter data with employee code
        if (!empty($dataSearch['employee_code'])) {
            $employee_code = $dataSearch['employee_code'];
            $collection->where('team_names.employee_code', 'LIKE', "%{$employee_code}%");
        }
        // filter data with division
        if (!empty($dataSearch['division'])) {
            $team_id = $dataSearch['division'];
            $collection->whereIn('team_names.team_id', $team_id);
        }
        // filter data with class id
        if (!empty($dataSearch['class_id'])) {
            $totalClass = array();
            $totalShift = array();
            foreach ($dataSearch['class_id'] as $key => $value) {
                if ($value) {
                    $classAndShift = explode("-", $value);
                    $class_id = $classAndShift[0];
                    $shift_id = isset($classAndShift[1]) ? $classAndShift[1] : null;
                    array_push($totalClass, $class_id);
                    array_push($totalShift, $shift_id);
                }
            }
            if (array_filter($totalClass)) $collection->whereIn('education_class.id', $totalClass);
            if (array_filter($totalShift)) $collection->whereIn('education_class_shifts.id', $totalShift);
        }
        // filter data with class attend
        if (!empty($dataSearch['class_attend'])) {
            $totalClass = array();
            $totalShift = array();
            foreach ($dataSearch['class_attend'] as $key => $value) {
                if ($value) {
                    $classAndShift = explode("-", $value);
                    $class_attend = $classAndShift[0];
                    $shift_id = $classAndShift[1];
                    array_push($totalClass, $class_attend);
                    array_push($totalShift, $shift_id);
                }
            }
            $collection->whereIn('education_class.id', $totalClass)->where('education_class_details.is_attend', 1);
            $collection->whereIn('education_class_shifts.id', $totalShift)->where('education_class_details.is_attend', 1);
        }
        $collection->orderBy('education_class_details.id');
        // Apply filter
        EducationCourse::filterGrid($collection);
        // Export data
        if ($isExport) {
            return $collection->get();
        }
        // Apply pagination
        EducationCourse::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }


    /*
     * get course by id
     */
    public static function getCourseById($id)
    {
        $data = DB::table('education_courses')
            ->join("employees", "employees.id", "=", "education_courses.hr_id")
            ->join("team_members", "team_members.employee_id", "=", "employees.id")
            ->join("teams", "teams.id", "=", "team_members.team_id")
            ->where('education_courses.id', $id)
            ->select('education_courses.*', 'employees.*', 'education_courses.name as course_name', 'education_courses.id as course_id')
            ->get();
        return $data;
    }

    /*
     * get team id selected
     */
    public static function getTeamIdSelected($id)
    {
        return DB::table('education_course_teams')
            ->where('education_course_teams.course_id', $id)
            ->pluck('team_id');
    }

    /*
     * get class by course_id
     */
    public static function getClassByCourseId($id)
    {
        return DB::table('education_class')
            ->where('education_class.course_id', $id)
            ->get();
    }

    /*
     * get shift by class model
     */
    public static function getShiftbyClassModel($id)
    {
        return DB::table('education_class_shifts')
            ->where('education_class_shifts.class_id', $id)
            ->get();
    }

    /*
     * get list image
     */
    public static function listImageDetail($id)
    {
        $collection = DB::table('education_class_documents')
            ->join("education_class", "education_class.id", "=", "education_class_documents.class_id")
            ->where('education_class_documents.class_id', $id)
            ->select('education_class_documents.id', 'education_class_documents.url', 'education_class_documents.name')
            ->get();
        return $collection;
    }

    /*
     * get max id
     */
    public static function checkCourseExist($id)
    {
        $data = DB::table('education_courses')->where('id', $id)->get();
        return $data;
    }

    /*
     * get max id
     */
    public static function getMaxId($table, $course_code = null)
    {
        if ($course_code) {
            $maxCodeClass = DB::table($table)->where('course_code', $course_code)->max('class_code');
            $maxCodeClassSplit = explode("_", $maxCodeClass);
            if (count($maxCodeClassSplit) == 1) {
                $maxId = 0;
            } else {
                $maxId = $maxCodeClassSplit[1];
            }
        } else {
            $maxId = DB::table($table)->max('id');
        }
        return $maxId;
    }

    /*
     * get education type
     */
    public static function educationTypes()
    {
        $data = DB::table('education_types')->get();
        return $data;
    }

    /*
     * get team by course id
     */
    public static function getTeamByCourseId($id)
    {
        return DB::table('education_course_teams')
            ->where('education_course_teams.course_id', $id)
            ->get();
    }

    /*
     * get class by course and employee_id class
     */
    public static function getClassByCourseAndEmpIdClass($id)
    {
        return DB::table('education_class')
            ->leftJoin("education_class_details", "education_class_details.class_id", '=', "education_class.id")
            ->where('education_class.course_id', $id)
            ->groupBy('education_class.id')
            ->select('education_class.*', 'education_class_details.*', 'education_class.id as class_id')
            ->get();
    }

    /*
     * check register
     */
    public static function checkRegister($user, $shift_id)
    {
        return DB::table('education_class_details')
            ->where('education_class_details.employee_id', $user)
            ->where('education_class_details.shift_id', $shift_id)
            ->get();
    }

    /*
     * get class by course and employee_id
     */
    public static function getClassByCourseAndEmpId($id, $user)
    {
        $data = DB::table('education_class')
            ->join("education_class_details", "education_class_details.class_id", '=', "education_class.id")
            ->where('education_class.course_id', $id)
            ->where('education_class_details.employee_id', $user)
            ->groupBy('education_class.id')
            ->select('education_class.id as class_id', 'education_class.class_code', 'education_class.class_name', 'education_class_details.*')
            ->get();
        return $data;
    }

    /*
     * get name teacher model
     */
    public static function getNameTeacherModel($id, $type)
    {
        if ($type == 1 && $id > 0) {
            $table = 'employees';
        } else if ($type == 2 && $id > 0) {
            $table = 'education_teacher_withouts';
        } else {
            return '';
        }
        $data = DB::table($table)
            ->where($table . '.id', $id)
            ->select($table . '.name')
            ->get();
        return $data[0]->name;
    }

    /*
     * get employees code by id
     */
    public static function getEmpCodeById($id)
    {
        $data = DB::table('employees')
            ->join("team_members", "team_members.employee_id", "=", "employees.id")
            ->join("teams", "teams.id", "=", "team_members.team_id")
            ->where('employees.id', $id)
            ->groupBy("employees.id")
            ->select(DB::raw('group_concat(teams.name) as teams_name'), 'employees.*')
            ->get();
        return $data;
    }

    /*
     * check email is teacher
     */
    public static function checkEmailTeacher($request)
    {
        $returnData = [
            'message' => '',
            'flag' => '',
            'data' => '',
            'url' => ''
        ];

        $checkTeacher = false;
        if (isset($request['class_id']) && $request['class_id']) {
            foreach ($request['class_id'] as $item => $value) {
                if ($value) {
                    $valueClassIm = explode("-", $value)[0];
                    $data = DB::table('education_class_details')
                        ->where('employee_id', $request['id'])
                        ->where('class_id', $valueClassIm)
                        ->where('role', self::ROLE_TEACHER)
                        ->get();
                    if (count($data) > 0) {
                        $checkTeacher = true;
                    }
                }
            }
        }
        $returnData['flag'] = $checkTeacher;
        return $returnData;
    }

    /*
     * register shift
     */
    public static function registerShift($shift_id, $user_id, $course_id)
    {
        if ($shift_id) {
            DB::beginTransaction();
            try {
                // delete class detail
                DB::table('education_class_details')
                    ->join("education_class", "education_class.id", '=', "education_class_details.class_id")
                    ->join("education_courses", "education_courses.id", '=', "education_class.course_id")
                    ->where('education_class_details.employee_id', $user_id)
                    ->where('education_courses.id', $course_id)
                    ->where('role', self::ROLE_EMPLOYEE)
                    ->delete();
                // add data class shift
                foreach ($shift_id as $key => $value) {
                    $dataClass = DB::table('education_class_shifts')
                        ->where('id', $value)
                        ->get();

                    $dataShift = DB::table('education_class_details')
                        ->where('employee_id', $user_id)
                        ->where('class_id', $dataClass[0]->class_id)
                        ->where('shift_id', $value)
                        ->where('role', self::ROLE_TEACHER)
                        ->get();
                    if (count($dataShift) == 0) {
                        DB::table('education_class_details')->insert(
                            [
                                'employee_id' => $user_id,
                                'class_id' => $dataClass[0]->class_id,
                                'shift_id' => $value,
                                'role' => self::ROLE_EMPLOYEE
                            ]
                        );
                    }
                }
                DB::commit();
                $returnData['message'] = trans('education::view.Education.Register success');
                $returnData['flag'] = true;
                return $returnData;
            } catch (Exception $ex) {
                DB::rollback();
                Log::info($ex->getMessage());
                $returnData['message'] = trans('education::view.Education.Error system');
                $returnData['flag'] = false;
                return $returnData;
            }
        } else {
            DB::table('education_class_details')
                ->where('employee_id', $user_id)
                ->where('role', self::ROLE_EMPLOYEE)
                ->delete();
            $returnData['message'] = trans('education::view.Education.Register success');
            $returnData['flag'] = true;
            return $returnData;
        }
    }

    /*
     * send feedback
     */
    public static function sendFeedback($request, $user_id)
    {
        if ($request) {
            foreach ($request as $key => $value) {

                $result = DB::table('education_class_details')
                    ->where('employee_id', $user_id)
                    ->where('shift_id', $value['shift_id'])
                    ->update([
                        'feedback_teacher_point' => $value['feedback_teacher_point'],
                        'feedback_company_point' => $value['feedback_company_point'],
                        'feedback' => $value['feedback']
                    ]);

                // push mail or notification
                if($result) {
                    $educationClass = self::getEducationClass($value['class_id']);
                    $educationCourse = self::getEducationCourse($value['course_id']);
                    // Check role of class ( teacher | member )
                    $data['global_subject'] = trans('education::mail.Comment from teacher');
                    $data['global_view'] =  'education::template-mail.education-detail-teacher-feedback';
                    if ($value['class_role'] == self::ROLE_EMPLOYEE) {
                        $data['global_subject'] = trans('education::mail.Comment from student');
                        $data['global_view'] = 'education::template-mail.education-detail-employee-feedback';
                    }
                    $data['global_link'] =  URL::route('education::education-profile.detail', ['id' => $value['course_id'], 'flag' => '0#infomation_tab']);
                    $globalItem = Employee::select(['id', 'name', 'email'])->where('id', $educationCourse->hr_id)->get()->toArray();
                    $data['global_item'] = $globalItem;
                    if (isset($data['global_item']) && !empty($data['global_item'])) {
                        $data['global_creator'] = Permission::getInstance()->getEmployee()->name;
                        $data['global_title'] = $educationClass->class_name;
                        $patternsArr = ['/\{\{\stitle\s\}\}/'];
                        $replacesArr = ['global_title'];
                        self::pushNotificationAndEmail($data, $patternsArr, $replacesArr);
                    }
                }
            }
            $returnData['message'] = trans('education::view.Education.Feedback success');
            $returnData['flag'] = true;
            return $returnData;
        } else {
            $returnData['message'] = trans('education::view.Education.Error system');
            $returnData['flag'] = false;
            return $returnData;
        }
    }

    public static function getEducationClass($id) {
        $response = EducationClass::find($id);

        return $response;
    }

    public static  function getEducationCourse($id) {
        $response = EducationCourse::find($id);

        return $response;
    }

    public static function getEmployeeWithEmpAssignedByHr($courseId)
    {
        $collection = EducationClassDetail::join("education_class", "education_class.id", '=', "education_class_details.class_id")
            ->join("education_courses", "education_courses.id", '=', "education_class.course_id")
            ->join("employees", "employees.id", '=', "education_class_details.employee_id")
            ->where('education_courses.id', $courseId)
            ->where('education_class_details.role', self::ROLE_EMPLOYEE)
            ->where('education_class_details.is_hr_added', 0)
            ->select(['employees.id', 'employees.name', 'employees.email', 'education_class_details.id as detail_id'])
            ->get()
            ->toArray();

        return $collection;
    }

    public static function updateEmployeeWithEmpAssignedByHr($employeeIds)
    {
        $collection = EducationClassDetail::whereIn('id', $employeeIds)->update(['is_hr_added' => 1]);

        return $collection;
    }

    /**
     * Get employee detail by course id
    */
    public static function getEmployeeDetailByCourseId($id)
    {
        $collection = EducationClassDetail::join("education_class", "education_class.id", '=', "education_class_details.class_id")
            ->join("education_courses", "education_courses.id", '=', "education_class.course_id")
            ->join("employees", "employees.id", '=', "education_class_details.employee_id")
            ->where('education_courses.id', $id)
            ->where('education_class_details.role', self::ROLE_EMPLOYEE)
            ->select(['employees.id', 'employees.name', 'employees.email'])
            ->get()
            ->toArray();

        return $collection;
    }

    public static function getTeacherDetailByCourseId($id)
    {
        $collection = EducationClass::join("employees", "employees.id", '=', "education_class.related_id")
            ->where('related_name', 'employee')
            ->where('course_id', $id)
            ->select(['employees.id', 'employees.name', 'employees.email'])
            ->get()
            ->toArray();

        return $collection;
    }

    /**
     * get employee with scope company
     */
    public static function getEmployeeWithScopeCompany()
    {
        $result = Employee::select(['id', 'name', 'email'])->get()->toArray();

        return $result;
    }

    /**
     * get employee with scope branch
     */
    public static function getEmployeeWithScopeBranch($scopeArr, $isMail = false)
    {
        $selectField = ['id', 'name'];
        if ($isMail) {
            $selectField = ['id', 'name', 'email'];
        }
        $branchCode = Team::whereIn('id', array_values($scopeArr))->groupBy('branch_code')->lists('branch_code');
        $teamId = Team::whereIn('branch_code', $branchCode)->lists('id');
        $employeeId = TeamMember::whereIn('team_id', $teamId)->lists('employee_id');
        $result = Employee::whereIn('id', $employeeId)->select($selectField)->get()->toArray();

        return $result;
    }

    /**
     * get employee with scope division
     */
    public static function getEmployeeWithScopeDivision($scopeArr)
    {
        $selectField = ['id', 'name', 'email'];
        $employeeId = TeamMember::whereIn('team_id', $scopeArr)->lists('employee_id');
        $result = Employee::whereIn('id', $employeeId)->select($selectField)->get()->toArray();

        return $result;
    }
    /**
     * Check status send mail with employee assigned by Hr
    */
    public static function checkSendMailWithEmpAssignedByHr(array $item = [])
    {
        $data['global_subject'] = trans('education::mail.Add student to class');
        $data['global_view'] = 'education::template-mail.education-add-employee-to-class';
        $data['global_link'] =  route('education::education-profile.detail' , ['id' => $item['course_id'], 'flag' => '0#infomation_tab']);
        $data['global_item'] = [];
        $employeeItems = self::getEmployeeWithEmpAssignedByHr($item['course_id']);
        if (isset($employeeItems) && !empty($employeeItems)) {
            $data['global_item'] = array_map(function ($item) {
                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'email' => $item['email']
                ];
            }, $employeeItems);
        }

        if (isset($data['global_item']) && !empty($data['global_item'])) {
            $data += [
                'global_title' => $item['title'],
            ];
            $patternsArr = ['/\{\{\stitle\s\}\}/'];
            $replacesArr = ['global_title'];
            $response = self::pushNotificationAndEmail($data, $patternsArr, $replacesArr);
            if ($response) {
                $employeeIds = array_column($employeeItems, 'detail_id');
                self::updateEmployeeWithEmpAssignedByHr($employeeIds);
            }
        }
    }

    public static function checkStatusSendMailOrNotifyByHr($isSend = true, $status, array $item = [])
    {
        // Check status create new
        if ($isSend) {
            // Check status open and send mail/notify to employee assigned by hr
            if ($status == self::STATUS_OPEN) {
                self::checkSendMailWithEmpAssignedByHr($item);
            }

            if ($item['old_data'] && $item['old_data']['status'] != $status) {
                $data['global_subject'] = trans('education::mail.Update status class');
                $data['global_view'] = 'education::template-mail.education-course-update-status';
                switch ($status) {
                    case self::STATUS_NEW:
                        $globalStatus = trans('education::view.Education.Create new');
                        break;
                    case self::STATUS_REGISTER:
                        $globalStatus = trans('education::view.Education.Register');
                        break;
                    case self::STATUS_OPEN:
                        $globalStatus = trans('education::view.Education.Open class');
                        break;
                    case self::STATUS_PENDING:
                        $globalStatus = trans('education::view.Education.Pending');
                        break;
                    case self::STATUS_FINISH:
                        $globalStatus = trans('education::view.Education.Finish');
                        break;
                }
                $data['global_status'] =  $globalStatus;
            } else {
                $data['global_subject'] = trans('education::mail.Update content class');
                $data['global_view'] =  'education::template-mail.education-course-update-infomation';
            }
            $data['global_link'] =  route('education::education-profile.detail' , ['id' => $item['course_id'], 'flag' => '0#infomation_tab']);

            // Check status is open or close
            if ($item['is_mail_list'] == "true" || $status == self::STATUS_OPEN || $status == self::STATUS_PENDING || $status == self::STATUS_FINISH ) {
                $globalItem1 = self::getEmployeeDetailByCourseId($item['course_id']);
                $globalItem2 = self::getTeacherDetailByCourseId($item['course_id']);
                if(count($globalItem1) > 0 || count($globalItem2) > 0) {
                    $globalItem1 = isset($globalItem1) && !empty($globalItem1) ? $globalItem1 : [];
                    $globalItem2 = isset($globalItem2) && !empty($globalItem2) ? $globalItem2 : [];
                    $data['global_item'] = array_merge($globalItem1, $globalItem2);
                }
            }

            // Check status is create new or register or pending
            if ($item['is_mail_list'] != "true" && $status == self::STATUS_NEW || $item['is_mail_list'] != "true" && $status == self::STATUS_REGISTER) {
                $scopeArr = $item['team_id'];
                // Check scope company
                if ($item['scope_total'] == self::SCOPE_COMPANY) {
                    if(count($scopeArr) > 0) {
                        // get employee division when select SCOPE_COMPANY and teamId
                        $globalItem = self::getEmployeeWithScopeDivision($scopeArr);
                    } else {
                        // get all employee
                        $globalItem = self::getEmployeeWithScopeCompany();
                    }
                }

                // Check scope branch
                if ($item['scope_total'] == self::SCOPE_BRANCH) {
                    $globalItem = self::getEmployeeWithScopeBranch($scopeArr, true);
                }

                // Check scope division
                if ($item['scope_total'] == self::SCOPE_DIVISION) {
                    $globalItem = self::getEmployeeWithScopeDivision($scopeArr);
                }

                $data['global_item'] = [];
                if (isset($globalItem) && !empty($globalItem)) {
                    $data['global_item'] = array_reduce($globalItem, function ($carry, $item) {
                        $carry[] = $item;
                        return $carry;
                    });
                }
            }

            // Check status is finish
            if ($status == self::STATUS_FINISH) {
                $data['global_subject'] = $item['title_template_mail'];
                $data['global_view'] =  'education::template-mail.education-thank';
                // Get course name
                $course = EducationCourse::where('id', $item['course_id'])->first();
                $data['global_course'] = isset($course->name) && !empty($course->name) ? $course->name : "";
                $data['global_content'] = $item['template_mail'];
                $patternsArr = ['/\{\{\sname\s\}\}/', '/\{\{\scourse\s\}\}/'];
                $replacesArr = ['name', 'global_course'];

                if (isset($data['global_item']) && !empty($data['global_item'])) {
                    return self::pushEmail($data, $patternsArr, $replacesArr);
                }
            }

            // Check status create new to make content
            if ($status == self::STATUS_NEW) {
                $data['global_content'] = $item['template_mail'];
                $data['global_link'] = URL::route('education::education-profile.detail', ['id' => $item['course_id'], 'flag' => 0]) . '/';

                // Get course name
                $course = EducationCourse::where('id', $item['course_id'])->first();
                $data['global_course'] = isset($course->name) && !empty($course->name) ? $course->name : "";
                if ($item['course_form'] == self::FORM_VOCATIONAL) {
                    $data['global_subject'] = $item['title_template_mail'];
                    $data['global_view'] = 'education::template-mail.education-vocational';

                    // Get template mail
                    $data['global_time'] = $item['data_class'][0]['startCa'][0];
                    $data['global_location'] = isset($item['data_class'][0]['location_name']) ? $item['data_class'][0]['location_name'][0] : $item['data_class'][0]['location_name_shift'][0];

                    //get teacher
                    if ($item['is_rent_checked'] == '1') {
                        $data['global_teacher'] = $item['teacher_name'];
                    } else {
                        $globalTeacher = '';
                        $data['global_teacher'] = '';
                        $educationRequestService = new EducationRequestService();
                        if (isset($globalItem2) && !empty($globalItem2)) {
                            $positionEmp = $educationRequestService->getPosition($globalItem2[0]['id']);
                            $divisionEmp = $educationRequestService->getDivision($globalItem2[0]['id']);
                            $globalTeacher = $globalItem2[0]['name'] . '. ' . trans('education::view.Education.Position') . ': ' . $positionEmp->position . '. ' . trans('education::view.Education.Division') . ': ' . $divisionEmp->division;
                        } else {
                            $teacher = self::getTeacherDetailByCourseId($item['course_id']);
                            if (isset($teacher) && !empty($teacher)) {
                                $positionEmp = $educationRequestService->getPosition($teacher[0]['id']);
                                $divisionEmp = $educationRequestService->getDivision($teacher[0]['id']);
                                $globalTeacher = $teacher[0]['name'] . '. ' . trans('education::view.Education.Position') . ': ' . $positionEmp->position . '. ' . trans('education::view.Education.Division') . ': ' . $divisionEmp->division;
                            }
                        }

                        if (!empty($globalTeacher)) {
                            $data['global_teacher'] = $globalTeacher;
                        }
                    }
                    $patternsArr = ['/\{\{\sname\s\}\}/', '/\{\{\scourse\s\}\}/', '/\{\{\stime\s\}\}/', '/\{\{\slocation\s\}\}/', '/\{\{\steacher\s\}\}/', '/\{\{\slink\s\}\}/'];
                    $replacesArr = ['name', 'global_course', 'global_time', 'global_location', 'global_teacher', 'global_link'];
                } else {
                    $data['global_subject'] = $item['title_template_mail'];
                    $data['global_view'] = 'education::template-mail.education-invite';

                    // Lấy Người phụ trách
                    $hr = Employee::where('id', $item['hr_id'])->select(['name', 'email', 'mobile_phone'])->first();

                    // Get template mail
                    $data['global_assigned_name'] = isset($hr->name) && !empty($hr->name) ? $hr->name : "";
                    $data['global_assigned_mail'] = isset($hr->email) && !empty($hr->email) ? $hr->email : "";
                    $data['global_assigned_phone'] = isset($hr->mobile_phone) && !empty($hr->mobile_phone) ? $hr->mobile_phone : "";
                    $patternsArr = ['/\{\{\sname\s\}\}/', '/\{\{\scourse\s\}\}/', '/\{\{\sassigned_name\s\}\}/', '/\{\{\sassigned_mail\s\}\}/', '/\{\{\sassigned_phone\s\}\}/', '/\{\{\slink\s\}\}/'];
                    $replacesArr = ['name', 'global_course', 'global_assigned_name', 'global_assigned_mail', 'global_assigned_phone', 'global_link'];
                }

                if (isset($data['global_item']) && !empty($data['global_item'])) {
                    return self::pushEmail($data, $patternsArr, $replacesArr);
                }

                // is_mail_list checked and no recipient mail
                return true;
            }

            if (isset($data['global_item']) && !empty($data['global_item'])) {
                $data += [
                    'global_title' => $item['title'],
                ];

                // Define {{ name }} or {{ title }}
                $patternsArr = ['/\{\{\stitle\s\}\}/'];
                $replacesArr = ['global_title'];

                return self::pushNotificationAndEmail($data, $patternsArr, $replacesArr);
            }
        }

        return false;
    }

    /**
     * Push Email
     * @param [array] $data
     * @param [array] $patternsArr
     * @param [array] $replacesArr
     * @return boolean
     */
    public static function pushEmail(array $data, array $patternsArr, array $replacesArr) {
        try {
            $dataInsert = [];
            foreach ($data['global_item'] as $item) {
                $newReplaceArr = [];
                foreach ($replacesArr as $index) {
                    if (array_key_exists($index, $item)) {
                        $newReplaceArr[] = $item[$index];
                    } else {
                        if (array_key_exists($index, $data)) {
                            $newReplaceArr[] = $data[$index];
                        }
                    }
                }
                $subject = preg_replace($patternsArr, $newReplaceArr, $data['global_subject']);
                $content = preg_replace($patternsArr, $newReplaceArr, $data['global_content']);
                // Check isset, send mail
                if (isset($item['email']) && !empty($item['email'])) {
                    $templateData = [
                        'content' => $content
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($item['email'], $item['name'])
                        ->setSubject($subject)
                        ->setTemplate($data['global_view'], $templateData);
                    $dataInsert[] = $emailQueue->getValue();
                }
            }

            EmailQueue::insert($dataInsert);

            return true;
        } catch (Exception $ex) {
            Log::info($ex);
        }

        return false;
    }

    public static function pushNotificationAndEmailForFile($classId, $courseId)
    {
        $courseTable = EducationCourse::getTableName();
        $classTable = EducationClass::getTableName();
        $employeeTable = Employee::getTableName();
        $classDetailTable = EducationClassDetail::getTableName();

        $data['global_view'] = 'education::template-mail.education-teacher-update-file';
        $data['global_link'] =  route('education::education-profile.detail', ['id' => $courseId, 'flag' => 0]);
        $data['global_item'] = [];

            // Get current course
            $response = EducationCourse::join($classTable, $classTable . '.course_id', '=', $courseTable . '.id')
                ->join($employeeTable, $employeeTable . '.id', '=', $classTable . '.related_id')
                ->where($classTable . '.id', $classId)
                ->select([$classTable . '.class_name', $employeeTable . '.name', $courseTable . '.hr_id'])
                ->first();
            if (!$response) return false;
            // Get student of class
            $globalItem1 = EducationClassDetail::join($employeeTable, $employeeTable . '.id', '=', $classDetailTable . '.employee_id')
                ->where('class_id', $classId)
                ->select([$employeeTable . '.id', $employeeTable . '.name', $employeeTable . '.email'])
                ->get()
                ->toArray();

            $data['global_item'] = $globalItem1;
            // Check hr_id exits
            if ($response && !in_array($response->hr_id, array_column($globalItem1, 'id'))) {
                // Get current hr
                $globalItem2 = Employee::where('id', $response->hr_id)->select(['id', 'name', 'email'])->get()->toArray();
                $data['global_item'] = array_merge($globalItem1, $globalItem2);
            }

            if(isset($data['global_item']) && !empty($data['global_item'])) {
                $data['global_subject'] = trans('education::mail.Update document class');
                $data['global_creator'] = $response->name;
                $data['global_title'] = $response->class_name;
                $patternsArr = ['/\{\{\stitle\s\}\}/'];
                $replacesArr = ['global_title'];
                self::pushNotificationAndEmail($data, $patternsArr, $replacesArr);
            }
    }

    /**
     * Push Notification or Email
     * @param [array] $data
     * @param [array] $patternsArr
     * @param [array] $replacesArr
     * @return boolean
     */
    public static function pushNotificationAndEmail(array $data, array $patternsArr, array $replacesArr)
    {
        try {
            $dataInsert = [];
            $receiverIds = [];
            $receiverEmails = [];
            $newReplaceArr = [];
            foreach ($replacesArr as $index) {
                if (array_key_exists($index, $data)) {
                    $newReplaceArr[] = $data[$index];
                }
            }
            $subject = preg_replace($patternsArr, $newReplaceArr, $data['global_subject']);
            $dataShort = $data;
            unset($dataShort['global_item']);
            foreach ($data['global_item'] as $item) {
                $receiverIds[] = $item['id'];

                // Not send email when define email for employees
                if (isset($item['email']) && !empty($item['email'])) {
                    $receiverEmails[] = $item['email'];
                    $templateData = [
                        'data' => $dataShort
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($item['email'], $item['name'])
                        ->setSubject($subject)
                        ->setTemplate($data['global_view'], $templateData);
                    $dataInsert[] = $emailQueue->getValue();
                }
            }

            // Send notification
            \Rikkei\Notify\Facade\RkNotify::put(
                $receiverIds,
                $subject,
                $data['global_link'],
                ['actor_id' => null, 'icon' => 'reward.png']
            );

            // Send email
            EmailQueue::insert($dataInsert);

            return true;
        } catch (Exception $ex) {
            Log::info($ex);
        }

        return false;
    }

    /**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @return [array]
     */
    public static function searchEmployeeAjaxEmail($keySearch, array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->email,
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

    /**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @return [array]
     */
    public static function searchEmployeeAjaxEmailList($keySearch, array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->email,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->email,
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

    /**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @return [array]
     */
    public static function searchEmployeeAjaxNameList($keySearch, array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->name,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->name,
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

    /**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @return [array]
     */
    public static function searchEmployeeAjaxNameCodeList($keySearch, array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->join("{$tblTeamMember}", "{$tblTeamMember}.employee_id", '=', "{$tblEmployee}.id")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.employee_code", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->employee_code,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->employee_code,
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

    /**
     * Search employee ajax
     * @param [string] $keySearch
     * @param [array] $config
     * @return [array]
     */
    public static function searchHrAjaxList($keySearch, array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 5,
        ];

        $tblEmployee = Employee::getTableName();
        $tblUser = User::getTableName();
        $config = array_merge($configDefault, $config);
        $now = Carbon::now();
        $collection = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.employee_code", "{$tblEmployee}.name", "{$tblEmployee}.email", "{$tblUser}.avatar_url")
            ->leftJoin("{$tblUser}", "{$tblUser}.employee_id", '=', "{$tblEmployee}.id")
            ->where(function ($query) use ($tblEmployee, $keySearch) {
                $query->orWhere("{$tblEmployee}.name", 'LIKE', '%' . $keySearch . '%');
                $query->orWhere("{$tblEmployee}.email", 'LIKE', '%' . $keySearch . '%');
                $query->orWhere("{$tblEmployee}.employee_code", 'LIKE', '%' . $keySearch . '%');
            })
            ->orderBy("{$tblEmployee}.id")
            ->distinct("{$tblEmployee}.id")
            ->where(function ($query) use ($now) {
                $query->orWhereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        Employee::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'employee_code' => $item->employee_code,
                'employee_name' => $item->name,
                'text' => $item->name,
                'avatar_url' => $item->avatar_url,
            ];
        }
        return $result;
    }

}
