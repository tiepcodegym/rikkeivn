<?php

namespace Rikkei\Team\View;

use Carbon\Carbon;
use Rikkei\Team\Model\EmplCvAttrValueText;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\EmployeeProjExper;
use Rikkei\Team\Model\EmplProjExperTag;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Core\View\Form;
use Illuminate\Support\Facades\Validator;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\Model\Field;
use Rikkei\Tag\View\TagConst;
use Illuminate\Support\Str;
use Rikkei\Core\View\View;

class ProfileImportHelper
{
    protected $lang = null;
    protected $option = [];
    protected $employee = null;
    protected $data = [];

    public function __construct($employee = null, $lang = 'ja', array $option = [])
    {
        if (!in_array($lang, ['ja', 'en'])) {
            $lang = 'ja';
        }
        $this->lang = $lang;
        $this->employee = $employee;
        $this->option = $option;
        $this->data = [];
    }

    public function import($data)
    {
        $tagsName = [];
        $tagsExits = [];
        if ($data['proj']) {
            $tagsName = $this->getTagNameProjInData($data['proj']);
        }
        if ($data['skill']) {
            $tagsName = array_merge($tagsName, $this->getTagNameSkillInData($data['skill']));
        }
        if ($tagsName) {
            $tagsExits = $this->getTagInDB($tagsName);
        }
        if ($data['proj']) {
            $dataEavProj = $this->saveProj($data['proj'], $tagsExits);
        }
        if ($data['skill']) {
            $this->saveSkill($data['skill'], $tagsExits);
        }
        if (isset($this->option['classProfile']) && $this->option['classProfile'] &&
            isset($data['employees']) && $data['employees']
        ) {
            $fieldsNotSave = ['name', 'birthday', 'gender'];
            foreach ($fieldsNotSave as $field) {
                unset($data['employees'][$field]);
            }
            $response['employees'] = $this->option['classProfile']->saveBase(false, true, $data['employees']);
        }
        $dataEav = [];
        if (isset($data['eav']) && $data['eav']) {
            $dataEav = $data['eav'];
        }
        if (isset($dataEavProj['eav']) && $dataEavProj['eav']) {
            $dataEav = array_merge($dataEavProj['eav'], $dataEav);
        }
        if ($dataEav) {
            $response['eav'] = $this->saveCvEav($dataEav, false, true);
        }
        // eav -text
        $dataEav = [];
        if (isset($data['eav_t']) && $data['eav_t']) {
            $dataEav = $data['eav_t'];
        }
        if (isset($dataEavProj['eav_t']) && $dataEavProj['eav_t']) {
            $dataEav = array_merge($dataEavProj['eav_t'], $dataEav);
        }
        if ($dataEav) {
            $response['eav_t'] = $this->saveCvEav($dataEav, true, true);
        }
        if (isset($data['eav_s']) && $data['eav_s']) {
            $response['eav_s'] = $this->saveCvEav($data['eav_s'], false, false);
        }
    }

    /**
     * save project from import file
     *
     * @param array $dataProj
     * @param array $tagExists
     * @return array $eav
     */
    public function saveProj($dataProj, array $tagExists = [])
    {
        $nameExists = $this->removeAllProj();
        $tagRes = $this->getTagTrans('res');
        if ($tagRes) {
            $tagExists['res'] = $tagRes;
        }
        if ($tagRoles = $this->getTagTrans('role')) {
            $tagExists['role'] = $tagRoles;
        }
        $dataProj = $this->convertTagDataProj($dataProj, $tagExists);
        $eav = [
            'eav' => [],
            'eav_s' => [],
            'eav_t' => [],
        ];
        foreach ($dataProj as $dataProjExper) {
            $idProj = null;
            if (isset($dataProjExper['name'])) {
                $nameLower = mb_strtolower($dataProjExper['name'], 'UTF-8');
                if (isset($nameExists[$nameLower])) {
                    $idProj = $nameExists[$nameLower];
                }
            }
            $responseFunc = $this->saveCvProjItem($idProj, $dataProjExper);
            if (!$responseFunc['status']) {
                continue;
            }
            // get name => eav
            $v = View::getValueArray($dataProjExper, ['name']);
            if ($v !== null) {
                if (is_array($v)) {
                    $v = implode('-', $v);
                }
                $eav['eav'][sprintf('proj_%s_%s', $responseFunc['proj_id'], 'name')]
                    = $v;
            }
            $v = View::getValueArray($dataProjExper, ['description']);
            if ($v !== null) {
                $eav['eav_t'][sprintf('proj_%s_%s', $responseFunc['proj_id'], 'description')]
                    = $v;
            }
        }
        return $eav;
    }

    /**
     * save skill from import file
     *
     * @param type $dataSkill
     * @return type
     */
    public function saveSkill($dataSkill, array $tagExists = [])
    {
        $this->removeAllSkill();
        $dataSkill = $this->convertTagDataSkill($dataSkill, $tagExists);
        foreach ($dataSkill as $data) {
            if (!$data) {
                continue;
            }
            $responseFunc = $this->saveCvSkillItem(null, $data);
            if (!$responseFunc['status']) {
                continue;
            }
        }
    }

    /**
     * get tag name of project
     *
     * @param type $dataProj
     * @return type
     */
    public function getTagNameProjInData($dataProj)
    {
        $tagsName = [];
        foreach ($dataProj as $item) {
            foreach (['os', 'other', 'lang', 'db'] as $field) {
                if (!isset($item[$field])) {
                    continue;
                }
                foreach ($item[$field] as $tagName) {
                    if (!in_array($tagName, $tagsName)) {
                        $tagsName[] = $tagName;
                    }
                }
            }
        }
        return $tagsName;
    }

    /**
     * get tag name of project
     *
     * @param type $dataSkill
     * @return type
     */
    public function getTagNameskillInData($dataSkill)
    {
        $tagsName = [];
        foreach ($dataSkill as $item) {
            foreach (['os', 'other', 'lang', 'db'] as $field) {
                if (!isset($item[$field]) || !isset($item[$field]['tag_id'])) {
                    continue;
                }
                if (!in_array($item[$field]['tag_id'], $tagsName)) {
                    $tagsName[] = $item[$field]['tag_id'];
                }
            }
        }
        return $tagsName;
    }

    /**
     * get tag in db follow tag submit import
     *
     * @param array $tagsName
     * @return array
     */
    public function getTagInDB(array $tagsName = [])
    {   
        if (!$tagsName) {
            return null;
        }
        $tblTag = Tag::getTableName();
        $tblField = Field::getTableName();
        $collection = Tag::select([$tblTag.'.id', $tblTag.'.value', 't_field.code'])
            ->join($tblField . ' AS t_field', 't_field.id', '=', $tblTag.'.field_id')
            ->whereNull('t_field.deleted_at')
            ->where($tblTag.'.status', TagConst::TAG_STATUS_APPROVE)
            ->whereIn($tblTag.'.value', $tagsName)
            ->whereIn('t_field.code', ['language', 'os', 'database', 'framework', 'ide'])
            ->get();
        $tagExists = [];
        foreach ($collection as $item) {
            switch ($item->code) {
                case 'language':
                    $code = 'lang';
                    break;
                case 'database':
                    $code = 'db';
                    break;
                case 'framework': case 'ide':
                    $code = 'other';
                    break;
                case 'os':
                    $code = 'os';
                    break;
                default:
                    $code = null;
                    break;
            }
            if (!$code) {
                continue;
            }
            $tagExists[$code][Str::lower($item->value)] = $item->id;
        }
        return $tagExists;
    }

    /**
     * get tag response for
     *
     * @return array
     */
    public function getTagRes()
    {
        $tags = EmployeeProjExper::getResponsiblesDefine();
        if (!isset($tags[$this->lang])) {
            return null;
        }
        $tags = $tags[$this->lang];
        $result = [];
        foreach ($tags as $key => $value) {
            $result[$value] = $key;
        }
        return $result;
    }

    /**
     * get tag trans
     *
     * @param string $type = {res, role}
     * @return null|array
     */
    public function getTagTrans($type)
    {
        $methods = [
            'res' => 'getResponsiblesDefine',
            'role' => 'listRoles',
        ];
        $tags = EmployeeProjExper::{$methods[$type]}();
        if (!isset($tags[$this->lang])) {
            return null;
        }
        $result = [];
        foreach ($tags[$this->lang] as $key => $value) {
            $result[Str::lower($value)] = $key;
        }
        return $result;
    }

    /**
     * convert tag data project
     *
     * @param array $dataProj
     * @param array $tagExists
     * @return array $dataProj
     */
    public function convertTagDataProj($dataProj, $tagExists)
    {
        if (!$tagExists) {
            return $dataProj;
        }
        foreach ($dataProj as $keyProj => $item) {
            foreach (['os', 'other', 'lang', 'res'] as $field) {
                if (!isset($item[$field])) {
                    continue;
                }
                foreach ($item[$field] as $indexField => $tagName) {
                    $tagNameLower = mb_strtolower($tagName, 'UTF-8');
                    if (isset($tagExists[$field][$tagNameLower])) {
                        $dataProj[$keyProj][$field][$indexField] = $tagExists[$field][$tagNameLower];
                        continue;
                    }
                    $dataProj[$keyProj][$field][$indexField] = 'n-' . $tagName;
                }
            }

            if (isset($item['role'])) {
                $tagNameLower = mb_strtolower($item['role'], 'UTF-8');
                if (isset($tagExists['role'][$tagNameLower])) {
                    $dataProj[$keyProj]['role'] = $tagExists['role'][$tagNameLower];
                }
            }
        }

        foreach ($dataProj as $keyProj => $item) {
            $field = 'db';
            if (!isset($item[$field])) {
                continue;
            }
            foreach ($item[$field] as $indexField => $tagName) {
                $tagNameLower = mb_strtolower($tagName, 'UTF-8');
                if (isset($tagExists[$field][$tagNameLower])) {
                    $dataProj[$keyProj]['lang'][] = $tagExists[$field][$tagNameLower];
                    continue;
                }
                $tagNameLower = mb_strtolower($tagName, 'UTF-8');
                if (isset($tagExists['other'][$tagNameLower])) {
                    $dataProj[$keyProj]['other'][] = $tagExists['other'][$tagNameLower];
                    continue;
                }
                $dataProj[$keyProj]['other'][] = 'n-' . $tagName;
            }
            unset($dataProj[$keyProj][$field]);
        }
        return $dataProj;
    }

    /**
     * convert tag data project
     *
     * @param type $dataSkill
     * @param type $tagExists
     * @return string
     */
    public function convertTagDataSkill($dataSkill, array $tagExists = [])
    {
        if (!$tagExists) {
            return [];
        }
        foreach ($dataSkill as $key => $item) {
            foreach (['os', 'other', 'lang'] as $field) {
                if (!isset($item[$field]) || !isset($item[$field]['tag_id'])) {
                    unset($dataSkill[$key][$field]);
                    continue;
                }
                $tagName = Str::lower($item[$field]['tag_id']);
                if ($field === 'other' && isset($tagExists['db'][$tagName])) {
                    $dataSkill[$key]['database'] = $dataSkill[$key]['other'];
                    $dataSkill[$key]['database']['tag_id'] = $tagExists['db'][$tagName];
                    unset($dataSkill[$key]['other']);
                    continue;
                } elseif ($field === 'other' && isset($tagExists['other'][$tagName])) {
                    $dataSkill[$key]['frame'] = $dataSkill[$key]['other'];
                    $dataSkill[$key]['frame']['tag_id'] = $tagExists['other'][$tagName];
                    unset($dataSkill[$key]['other']);
                    continue;
                } else {
                    if (isset($tagExists[$field][$tagName])) {
                        if ($field === 'lang') {
                            $dataSkill[$key]['language'] = $dataSkill[$key]['lang'];
                            $dataSkill[$key]['language']['tag_id'] = $tagExists['lang'][$tagName];
                            unset($dataSkill[$key]['lang']);
                            continue;
                        }
                        $dataSkill[$key][$field]['tag_id'] = $tagExists[$field][$tagName];
                        continue;
                    }
                }
                unset($dataSkill[$key][$field]);
            }
        }
        return $dataSkill;
    }

    /**
     * save item project experience of synthesis
     *
     * @param int $id
     * @param array $dataEmployee
     * @return array
     */
    public function saveCvProjItem($id, $dataEmployee, $import = true)
    {
        if (!$id || !is_numeric($id)) {
            $employeeItemRelative = new EmployeeProjExper();
        } else {
            $employeeItemRelative = EmployeeProjExper::find($id);
            if (!$employeeItemRelative) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelative->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        if ($import) {
            $validator = Validator::make((array) $dataEmployee, [
                'name' => 'required|max:255',
                //'start_at' => 'date',
                //'end_at' => 'date',
                //'env' => 'max:255',
                //'period_y' => 'digits_between:0,100',
                //'period_m' => 'digits_between:0,12',
            ]);
            if ($validator->fails()) {
                $response['status'] = 0;
                $response['message'] = $validator->errors()->all();
                return $response;
            }
        }
        if (isset($dataEmployee['start_at']) && preg_match('/^\d{4}-\d{2}$/', $dataEmployee['start_at'])) {
            $dataEmployee['start_at'] = "{$dataEmployee['start_at']}-01";
        }
        if (isset($dataEmployee['end_at']) && preg_match('/^\d{4}-\d{2}$/', $dataEmployee['end_at'])) {
            $dataEmployee['end_at'] = "{$dataEmployee['end_at']}-01";
        }
        Form::filterEmptyValue($dataEmployee, [
            'start_at',
            'end_at',
            'proj_number',
            'total_member',
            'total_mm',
        ]);
        $dataEmployee['lang_code'] = $this->lang;
        $employeeItemRelative->fill($dataEmployee);
        $employeeItemRelative->employee_id = $this->employee->id;
        $employeeItemRelative->save();
        EmplProjExperTag::saveProjExperTag($employeeItemRelative->id, $dataEmployee, $this->lang);
        $response['proj_id'] = $employeeItemRelative->id;
        $response['status'] = 1;
        return $response;
    }

    /**
     * save item project experience of synthesis
     *
     * @param int $id
     * @param array $dataEmployee
     * @return array
     */
    public function saveCvSkillItem($id, $dataEmployee)
    {
        if (!$id || !is_numeric($id)) {
            $employeeItemRelative = new EmployeeSkill();
        } else {
            $employeeItemRelative = EmployeeSkill::find($id);
            if (!$employeeItemRelative) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelative->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        reset($dataEmployee);
        $type = key($dataEmployee);
        $dataEmployee = reset($dataEmployee);
        $dataEmployee['type'] = $type;
        $rule = [
            'tag_id' => 'required|numeric',
            /*'type' => 'required',
            'level' => 'required',
            'exp_y' => 'digits_between:0,100',
            'exp_m' => 'digits_between:0,12',*/
        ];
        $validator = Validator::make($dataEmployee, $rule);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $employeeItemRelative->fill($dataEmployee);
        $employeeItemRelative->employee_id = $this->employee->id;
        $employeeItemRelative->save();
        $response['skill_id'] = $employeeItemRelative->id;
        $response['status'] = 1;
        return $response;
    }

    /**
     * remove all project of employee
     */
    public function removeAllProj()
    {
        $projIdsRemove = EmployeeProjExper::where('employee_id', $this->employee->id)
                ->where('lang_code', $this->lang)
                ->lists('id')
                ->toArray();
        EmployeeProjExper::removeBulk($projIdsRemove);
        return [];
    }

    public function removeAllProjOld($dataProj)
    {
        if ($dataProj) {
            $nameExists = $this->findProjExistName($dataProj);
        } else {
            $nameExists = [];
        }
        $collectionRemove = EmployeeProjExper::where('employee_id', $this->employee->id)
            ->select(['id'])
            ->whereNotIn('id', $nameExists)
            ->where('lang_code', $this->lang)
            ->get();
        if (!count($collectionRemove)) {
            return $nameExists;
        }
        $projIdsRemove = [];
        foreach ($collectionRemove as $item) {
            $projIdsRemove[] = $item->id;
        }
        EmployeeProjExper::removeBulk($projIdsRemove);
        return $nameExists;
    }

    /**
     * 
     *
     * @param type $dataProj
     * @return type
     */
    public function findProjExistName($dataProj)
    {
        $projsName = [];
        foreach ($dataProj as $projItem) {
            if (isset($projItem['name'])) {
                $projsName[] = $projItem['name'];
            }
        }
        $collection = EmplCvAttrValue::select(['code', 'value'])
            ->where('code', 'like', 'proj_%_name_' . $this->lang)
            ->where('employee_id', $this->employee->id)
            ->whereIn('value', $projsName)
            ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $matches = null;
            if (preg_match('/^proj\_([0-9]+)\_name\_'.$this->lang.'$/i', $item->code, $matches)) {
                if (isset($matches[1])) {
                    $result[Str::lower($item->value)] = $matches[1];
                }
            }
        }
        return $result;
    }

    /**
     * remove all project of employee
     */
    public function removeAllSkill()
    {
        EmployeeSkill::where('employee_id', $this->employee->id)
            ->delete();
    }

    /**
     * save csv attribute follow language
     */
    public function saveCvEav($dataEav, $isText = false, $isLang = true)
    {
        $response = [];
        $uncheckFields = ['role'];
        if (!$isText) {
            $rules = array_fill_keys(array_keys($dataEav), 'string|max:255');
            foreach ($uncheckFields as $field) {
                unset($rules[$field]);
            }
            $validator = Validator::make($dataEav, $rules);
            if ($validator->fails()) {
                $response['status'] = 0;
                $response['message'] = $validator->errors()->all();
                return $response;
            }
        }
        if (!$isLang) {
            $langPar = null;
        } else {
            $langPar = $this->lang;
        }
        if ($isText) {
            $class = EmplCvAttrValueText::class;
        } else {
            $class = EmplCvAttrValue::class;
        }
        $class::insertEav($this->employee->id, $dataEav, $langPar);
        $response['status'] = 1;
        return $response;
    }
    /*
    public function importFile($fileUpload)
    {
        $this->data = [];
        Excel::load($fileUpload->path(), function($reader) {
            $reader->noHeading();
            $reader->formatDates(false);
            $rows = $reader->get();
            
            // remove title row excel
            for ($i = 0; $i < 4; $i++) {
                $rows->forget($i);
            }
            // get basic info
            for ($i = 4; $i < 9; $i++) {
                $this->rowBasicInfo($rows->get($i));
                $rows->forget($i);
            }
            if (isset($this->option['classProfile']) && $this->option['classProfile'] &&
                isset($this->data['employees']) && $this->data['employees']
            ) {
                $response['employees'] = $this->option['classProfile']->saveBase(false, true, $this->data['employees']);
            }
            if (isset($this->data['eav']) && $this->data['eav']) {
                $response['eav'] = $this->saveCvEav($this->data['eav'], false, true);
            }
            if (isset($this->data['eav_s']) && $this->data['eav_s']) {
                $response['eav_s'] = $this->saveCvEav($this->data['eav_s'], false, false);
            }
            $titleIndexProjSkill = $this->rowTitleProj($rows);
        });
    }
    protected function rowBasicInfo($row)
    {
        $labels = $this->labelCol();
        foreach ($row as $indexCell => $cell) {
            foreach ($labels as $key => $dbData) {
                if (isset ($row[$indexCell+1]) && preg_match('/^('.$key.')$/i', $cell)) { // check label
                    if (isset($dbData[2])) {
                        $value = $this->{$dbData[2]}(trim($row[$indexCell+1]), isset($dbData[3]) ? $dbData[3] : null);
                    } else {
                        $value = trim($row[$indexCell+1]);
                    }
                    if ($value !== false) {
                        $this->data[$dbData[0]][$dbData[1]] = $value;
                    }
                }
            }
        }
    }
    protected function rowTitleProj($rows)
    {
        $labels = $this->labelColSkills();
        $result = [];
        foreach ($rows->get(9) as $indexCell => $valueCell) {
            $valueCell = trim($valueCell);
            foreach ($labels as $key => $skillType) {
                if (preg_match('/^('.$key.')$/i', $valueCell)) {
                    $result[$skillType[0]][$skillType[1]] = $indexCell;
                }
            }
        }
        $rows->forget(9);
        return $result;
    }

    protected function rowProj($rows)
    {
        foreach ($rows as $index => $row) {
            
        }
        print_r($rows);exit;
    }
    protected function labelCol()
    {
        // label in excel => [tableName, columnName, function convert, col2]
        return [
            '氏名|Name' => ['employees', 'name'],
            'カナ' => ['employees', 'japanese_name'],
            '性別|Gender' => ['employees', 'gender', 'convertGender'],
            '生年月日|Date of Birth' => ['employees', 'birthday', 'convertDate'],
            '本籍地|Place of Birth' => ['empl_cv_attr_values', 'code', 'convertAttr', 'address_home'],
            '住所|Address' => ['empl_cv_attr_values', 'code', 'convertAttr', 'address'],
            '出身校|Alma Mater' => ['empl_cv_attr_values', 'code', 'convertAttr', 'school_graduation'],
            '対応可能分野|Compatible Field' => ['empl_cv_attr_values', 'code', 'convertAttr', 'field_dev'],
            '日本語レベル.*|Others Languages|Japanese level' => ['empl_cv_attr_values', 'code', 'convertAttrSingle', 'lang_ja_level'],
            '業務経験.*|Work experience.*' => ['empl_cv_attr_values', 'code', 'convertAttrSingle', 'exper_year'],
            '日本常駐経験|Work Experience Oversea' => ['empl_cv_attr_values', 'code', 'convertAttr', 'exper_japan'],
            '英語レベル.*|English level' => ['empl_cv_attr_values', 'code', 'convertAttrSingle', 'lang_en_level'],
        ];
    }
    protected function labelColSkills()
    {
        // label in excel => [tableName, columnName, function convert, col2]
        return [
            'Previous Projects|業.*務.*内.*容' => ['proj', 'name'],
            'Description|説.*明' => ['proj', 'description'],
            'OS' => ['proj', 'os'],
            'Programming.*Language|言.*語' => ['proj', 'lang'],
            'Environment|環.*境' => ['proj', 'other'],
            'Responsible.*for|担.*当.*フ.*ェ.*ー.*ズ' => ['proj', 'res'],
            'Start\/End|開.*始.*終了' => ['proj', 'time'],
            'Start.*date|作.*業.*開.*始' => ['proj', 'start_date'],
            'End.*date|作.*業.*終.*了' => ['proj', 'end_date'],
            'Ranking|ランキング' => ['skill', 'name'],
            '1' => ['skill', '1'],
            '2' => ['skill', '2'],
            '3' => ['skill', '3'],
            '4' => ['skill', '4'],
            '5' => ['skill', '5'],
            'Years.*of.*experience|経験年数' => ['skill', 'exp'],
        ];
    }
    protected function convertGender($value)
    {
        $genders = [
            'Male|男' => 1,
            'Female|女' => 0
        ];
        foreach ($genders as $key => $gender) {
            if (preg_match('/^('.$key.')$/i', $value)) { // check label
                return $gender;
            }
        }
        return null;
    }
    protected function convertDate($value)
    {
        $matches = null;
        $formatStands = '/^([0-9]{4}).*([0-9]{2}).*([0-9]{2})/'; // Y-m-d
        if (preg_match($formatStands, $value, $matches)) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }
        $formats = [
            '/^([0-9]{2}).*([a-zA-Z]{3}).*([0-9]{2})/' =>  'd-M-y',
            '/^([0-9]{2}).*([a-zA-Z]{3}).*([0-9]{4})/' => 'd-M-Y',
        ];
        foreach ($formats as $reg => $format) {
            if (preg_match($reg, $value, $matches)) {
                return Carbon::createFromFormat($format, $matches[1] . '-' . $matches[2] . '-' . $matches[3])
                    ->format('Y-m-d');
            }
        }
        return null;
    }
    protected function convertAttr($value, $valueColumn = null)
    {
        $this->data['eav'][$valueColumn] = $value;
        return false;
    }
    protected function convertAttrSingle($value, $valueColumn = null)
    {
        $this->data['eav_s'][$valueColumn] = $value;
        return false;
    }*/
}
