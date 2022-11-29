<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\ProjectMember;
use Exception;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Rikkei\Team\View\Config as TeamConfig;
use Carbon\Carbon;
use DB;

class EmployeeProjExper extends CoreModel
{   
    const KEY_CACHE = 'project_exper';

    protected $table = 'employee_proj_expers';
    protected $fillable = [
        'start_at', 'end_at', 'lang_code', 'en_id', 'employee_id', 'proj_number', 'total_member', 'total_mm',
    ];
    /**
     * save model school
     *
     * @param array $employeeId
     * @param array $experiences
     * @return array
     * @throws Exception
     */
    public static function saveItems($employeeId, $experiences = []) 
    {
        if (! $employeeId) {
            return;
        }
        try {
            $idExperienceIdsAdded = [];
            foreach ($experiences as $experienceData) {
                if (! isset($experienceData['project_experience']) || ! $experienceData['project_experience']) {
                    continue;
                }
                $experienceData = $experienceData['project_experience'];
                
                if (isset($experienceData['id']) && $experienceData['id']) {
                    if ( ! $experience = self::find($experienceData['id'])) {
                        $experience = new self();
                    }
                    unset($experienceData['id']);
                } else {
                    $experience = new self();
                }
                $validator = Validator::make($experienceData, [
                    'name' => 'required|max:255',
                    'enviroment_language' => 'required|max:255',
                    'enviroment_enviroment' => 'required|max:255',
                    'enviroment_os' => 'required|max:255',
                    'responsible' => 'required',
                    'start_at' => 'required|max:255',
                    'end_at' => 'required|max:255',
                    
                ]);
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator)->send();
                }
                if (isset($experienceData['image_path']) && $experienceData['image_path']) {
                        $experience->image = $experienceData['image_path'];
                } else if (isset($experienceData['image']) && $experienceData['image']) {
                    $urlEncode = preg_replace('/\//', '\/', URL::to('/'));
                    $image = preg_replace('/^' . $urlEncode . '/', '', $experienceData['image']) ;
                    $image = trim($image, '/');
                    if (preg_match('/^' . Config::get('general.upload_folder') . '/', $image)) {
                        $experience->image = $image;
                    }
                }
                unset($experienceData['image_path']);
                unset($experienceData['image']);
                
                //get environment group
                $experienceEnvironment = self::getEnvironmentGroupData($experienceData);
                $experienceEnvironment = serialize($experienceEnvironment);
                $experience->setData($experienceData);
                $experience->employee_id = $employeeId;
                $experience->enviroment = $experienceEnvironment;
                $experience->save();
                $idExperienceIdsAdded[] = $experience->id;
            }
            //delete experience 
            $delete = self::where('employee_id', $employeeId)
                ->whereNotIn('id', $idExperienceIdsAdded);
            $delete->delete();
            CacheHelper::forget(self::KEY_CACHE);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * get work experience follow employee
     * 
     * @param type $employeeId
     * @return object model
     */
    public static function getItemsFollowEmployee($employeeId)
    {
        $thisTbl = self::getTableName();
        
        return self::select("{$thisTbl}.id", "{$thisTbl}.name", "{$thisTbl}.start_at", "{$thisTbl}.end_at", "{$thisTbl}.enviroment",
                "{$thisTbl}.image", "{$thisTbl}.responsible", "{$thisTbl}.customer_name", "{$thisTbl}.description", 
                "{$thisTbl}.poisition", "{$thisTbl}.no_member", "{$thisTbl}.other_tech", "{$thisTbl}.work_experience_id")
            ->where('employee_id', $employeeId)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * get environment group data
     * 
     * @param type $experienceData
     * @return array
     */
    protected static function getEnvironmentGroupData(&$experienceData = [])
    {
        $result = [];
        if (! $experienceData) {
            return $result;
        }
        $flag = 'enviroment';
        foreach ($experienceData as $key => $value) {
            if (preg_match('/^' . $flag . '_/', $key)) {
                $result[preg_replace('/^' . $flag . '_/', '', $key)] = $value;
                unset($experienceData[$key]);
            }
        }
        return $result;
    }
    
    /**
     * return environment data
     * 
     * @param string|null $key
     * @return array|string
     */
    public function getEnvironment($key = null)
    {
        if (! ($enviroment = $this->enviroment)) {
            return;
        }
        $enviroment = unserialize($enviroment);
        if (! $key) {
            return $enviroment;
        }
        if (isset($enviroment[$key])) {
            return $enviroment[$key];
        }
        return ;
    }
    
    /**
     * 
     * @param int $employeeId
     * @param int $experienceId
     * @return Collection
     */
    public static function getItemsFollowEmployeeAndExperience($employeeId, $workId)
    {
        $thisTbl = self::getTableName();
        
        return self::select("{$thisTbl}.id", "{$thisTbl}.name", "{$thisTbl}.start_at", "{$thisTbl}.end_at", "{$thisTbl}.enviroment",
                "{$thisTbl}.image", "{$thisTbl}.responsible", "{$thisTbl}.customer_name", "{$thisTbl}.description", 
                "{$thisTbl}.poisition", "{$thisTbl}.no_member", "{$thisTbl}.other_tech", "{$thisTbl}.work_experience_id")
                ->where(["{$thisTbl}.employee_id" => $employeeId, "{$thisTbl}.work_experience_id" => $workId])
                ->orderby("{$thisTbl}.name")
                ->get();
    }
    
    /**
     * get list list item follow employeeId and array workExperienceIds
     * @param int $employeeId
     * @param array $workIds
     * @return Collection
     */
    public static function getItemsFollowExperience($employeeId, $workIds = array())
    {
        $thisTbl = self::getTableName();
        
        return self::select("{$thisTbl}.id", "{$thisTbl}.name", "{$thisTbl}.start_at", "{$thisTbl}.end_at", "{$thisTbl}.enviroment",
                "{$thisTbl}.image", "{$thisTbl}.responsible", "{$thisTbl}.customer_name", "{$thisTbl}.description", 
                "{$thisTbl}.poisition", "{$thisTbl}.no_member", "{$thisTbl}.other_tech", "{$thisTbl}.work_experience_id")
                ->where(["{$thisTbl}.employee_id" => $employeeId])
                ->whereIn("{$thisTbl}.work_experience_id", $workIds)
                ->orderBy("{$thisTbl}.work_experience_id")
                ->orderby("{$thisTbl}.name")
                ->get();
    }
    
    /**
     * get nice string technicals
     * @return string
     */
    public function getTechnical()
    {
        $key = [
            'language' =>  trans('team::view.Language'),
            'enviroment' => trans('team::view.Environment'),
            'os'    => trans('team::view.OS'),
        ];
        $str = '';
        foreach($key as $i => $v) {
            $str .= "- " . $v . " : ";
            $str .= $this->getEnvironment($i) . "<br>";
        }
        $str .= "- " . $this->other_tech;
        return $str;
    }

    /**
     * get all proj exper of employee
     *
     * @param int $employeeId
     * @return collection
     */
    public static function getAllProjExper($employeeId)
    {
        $pager = TeamConfig::getPagerData(null, [
            'order' => 'start_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select(['id', 'name', 'position', 'no_member',
            'start_at', 'end_at'])
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * load period and analyze
     *
     * @return $this
     */
    public function loadPeriod()
    {
        if (!$this->start_at || !$this->end_at) {
            $this->period_y = 0;
            $this->period_m = 0;
            return $this;
        }
        $start = Carbon::parse($this->start_at);
        $end = Carbon::parse($this->end_at)->modify('+1 days');
        $diff = $end->diff($start);
        if (!$diff) {
            return $this;
        }
        $this->period_y = $diff->y;
        $this->period_m = $diff->m;
        return $this;
    }

    /**
     * get all proj exper of employee
     *
     * @param int $employeeId
     * @return collection
     */
    public static function getProjExpersInCv($employeeId, $lang = null)
    {
        $pager = TeamConfig::getPagerData(null, [
            'dir' => ''
        ]);

        $result = self::select([
                'id',
                'total_member',
                'total_mm',
                'lang_code',
                'proj_number as number',
                DB::raw('DATE_FORMAT(start_at, "%Y-%m") AS start_at'),
                DB::raw('DATE_FORMAT(end_at, "%Y-%m") AS end_at'),
            ])
            ->where('employee_id', $employeeId);
        if (!empty($pager['dir'])) {
            $result->orderBy('start_at', $pager['dir']);
            $result->orderBy('proj_number', $pager['dir']);
        }
        elseif (!empty(\request()->all()['order'])) {
            $order = \request()->all()['order'];
            $result->orderBy('start_at', substr($order, 8));
            $result->orderBy('proj_number', substr($order, 8));
        }
        else {
            $result->orderBy('proj_number', 'ASC');
            $result->orderBy('start_at', 'DESC');
        }

        if ($lang) {
            $result->where('lang_code', $lang);
        }
        return $result->get();
    }

    /**
     * remove project expert
     *
     * @param array $ids
     */
    public static function removeBulk(array $ids)
    {
        // remove tag of project
        EmplProjExperTag::removeProj($ids);
        // remove attr of project
        EmplCvAttrValue::removeProj($ids);
        EmplCvAttrValueText::removeProj($ids);
        // remove project
        self::whereIn('id', $ids)
            ->delete();
    }

    /**
     * get position or responsibles
     *
     * @return array
     */
    public static function getResponsiblesDefine()
    {
        return [
            'en' => [
                0 => 'Requirement Definition',
                1 => 'Basic Design',
                2 => 'Detail Design',
                3 => 'Coding',
                4 => 'Unit Test',
                5 => 'Integration Test',
                6 => 'Operation / maintenance',
                7 => 'External design',
                8 => 'Internal design',
                9 => 'Programming (manufacturing)',
                10 => 'System test',
                11 => 'Operational test',
                12 => 'System migration',
            ],
            'ja' => [
                0 => '要件定義',
                1 => '基本設計',
                2 => '詳細設計',
                3 => 'コーディング',
                4 => '単体テスト',
                5 => '結合テスト',
                6 => '運用・保守',
                7 => '外部設計',
                8 => '内部設計',
                9 => 'プログラミング(製造)',
                10 => 'システムテスト',
                11 => '運用テスト',
                12 => 'システム移行',
            ]
        ];
    }

    /**
     * list all roles of member in project
     *
     * @return array
     */
    public static function listRoles()
    {
        return [
            'en' => [
                ProjectMember::TYPE_DEV => 'Developer',
                ProjectMember::TYPE_TEAM_LEADER => 'Technical Leader',
                ProjectMember::TYPE_SQA => 'SQA',
                ProjectMember::TYPE_PQA => 'PQA',
                ProjectMember::TYPE_PM => 'PM',
                ProjectMember::TYPE_BRSE => 'BrSE',
                ProjectMember::TYPE_SUB_BRSE => 'Sub-BrSE',
                ProjectMember::TYPE_COMTOR => 'Comtor',
                ProjectMember::TYPE_SUBPM => 'Sub-PM',
                ProjectMember::TYPE_BA => 'BA',
                ProjectMember::TYPE_DESIGNER => 'Designer',
            ],
            'ja' => [
                ProjectMember::TYPE_DEV => 'デベロッパー',
                ProjectMember::TYPE_TEAM_LEADER => 'チームリーダー',
                ProjectMember::TYPE_SQA => 'SQA',
                ProjectMember::TYPE_PQA => 'PQA',
                ProjectMember::TYPE_PM => 'PM',
                ProjectMember::TYPE_BRSE => 'BrSE',
                ProjectMember::TYPE_SUB_BRSE => 'Sub-BrSE',
                ProjectMember::TYPE_COMTOR => 'Comtor',
                ProjectMember::TYPE_SUBPM => 'Sub-PM',
                ProjectMember::TYPE_BA => 'BA',
                ProjectMember::TYPE_DESIGNER => 'Designer',
            ],
        ];
    }

    public function experTags()
    {
        return $this->hasMany('\Rikkei\Team\Model\EmplProjExperTag', 'proj_exper_id', 'id');
    }

    /**
     * search project expre of employee by number project
     * @param  [int] $empId
     * @param  [int] $number
     * @param  [string] $lang_code
     * @return [collection]
     */
    public static function getProjExperByNumber($empId, $number, $lang_code)
    {
        return self::select('id', 'employee_id', 'lang_code', 'proj_number')
        ->where('employee_id', $empId)
        ->where('proj_number', $number)
        ->where('lang_code', $lang_code)
        ->first();
    }
}
