<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\View\CacheHelper;
use DB;
use Lang;
use Rikkei\Project\View\View;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;

class CommonRisk extends CoreModel
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'common_risk';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['suggest_action', 'risk_description', 'process', 'risk_source'];

    /**
     * SOURCE OF PROCESS
     */
    const SOURCE_PROCESS_RELATED = 1;
    const SOURCE_REQUIREMENT = 2;
    const SOURCE_DESIGN = 3;
    const SOURCE_CODING = 4;
    const SOURCE_UNIT_TESTING = 5;
    const SOURCE_INTEGRATION_TESTING = 6;
    const SOURCE_SYSTEM_TESTING = 7;
    const SOURCE_TEST_ENVIRONMENT = 8;
    const SOURCE_CUSTOMER_SUPPORT = 9;
    const SOURCE_DEPLOYMENT = 10;
    const SOURCE_CONFIGURATION_MANAGER = 11;
    const SOURCE_TRAINING = 12;
    const SOURCE_PROJECT_MANAGEMENT = 13;
    const SOURCE_QUALITY_CONTROL = 14;
    const SOURCE_PREVENTION = 15;
    const SOURCE_CONTRACT_MANAGEMENT = 16;
    const SOURCE_ESTIMATION = 17;
    const SOURCE_CHANGE_REQUEST_MANAGEMENT = 18;

    public static function getSourceListProcess()
    {
        return [
            self::SOURCE_PROCESS_RELATED => Lang::get('project::view.LBL_PROCESS_PROCESS_RELATED'),
            self::SOURCE_REQUIREMENT => Lang::get('project::view.LBL_PROCESS_REQUIREMENT'),
            self::SOURCE_DESIGN => Lang::get('project::view.LBL_PROCESS_DESIGN'),
            self::SOURCE_CODING => Lang::get('project::view.LBL_PROCESS_CODING'),
            self::SOURCE_UNIT_TESTING => Lang::get('project::view.LBL_PROCESS_UNIT_TESTING'),
            self::SOURCE_INTEGRATION_TESTING => Lang::get('project::view.LBL_PROCESS_INTEGRATION_TESTING'),
            self::SOURCE_SYSTEM_TESTING => Lang::get('project::view.LBL_PROCESS_SYSTEM_TESTING'),
            self::SOURCE_TEST_ENVIRONMENT => Lang::get('project::view.LBL_PROCESS_TEST_ENVIRONMENT'),
            self::SOURCE_CUSTOMER_SUPPORT => Lang::get('project::view.LBL_PROCESS_CUSTOMER_SUPPORT'),
            self::SOURCE_DEPLOYMENT => Lang::get('project::view.LBL_PROCESS_DEPLOYMENT'),
            self::SOURCE_CONFIGURATION_MANAGER => Lang::get('project::view.LBL_PROCESS_CONFIGURATION_MANAGER'),
            self::SOURCE_TRAINING => Lang::get('project::view.LBL_PROCESS_TRAINING'),
            self::SOURCE_PROJECT_MANAGEMENT => Lang::get('project::view.LBL_PROCESS_PROJECT_MANAGEMENT'),
            self::SOURCE_QUALITY_CONTROL => Lang::get('project::view.LBL_PROCESS_QUALITY_CONTROL'),
            self::SOURCE_PREVENTION => Lang::get('project::view.LBL_PROCESS_PREVENTION'),
            self::SOURCE_CONTRACT_MANAGEMENT => Lang::get('project::view.LBL_PROCESS_CONTRACT_MANAGEMENT'),
            self::SOURCE_ESTIMATION => Lang::get('project::view.LBL_PROCESS_ESTIMATION'),
            self::SOURCE_CHANGE_REQUEST_MANAGEMENT => Lang::get('project::view.LBL_PROCESS_CHANGE_REQUEST_MANAGEMENT'),
        ];
    }

    public static function getAllCommonRiskExport($columns = ['*'], $conditions)
    {
        $urlFilter = trim(URL::route('project::report.common-risk'), '/') . '/';
        $collection = self::select($columns);
        $collection->orderBy('id', 'desc');
        return $collection;
    }
    
    /**
     * Save common risk
     * 
     * @param array $data
     * @return boolean
     */
    public static function store($data) {
        if (isset($data['id'])) {
            $commonRiskId = $data['id'];
            $commonRisk = CommonRisk::find($commonRiskId);
        } else {
            $commonRisk = new CommonRisk();
        }
        DB::beginTransaction();
        try {
            $commonRisk->fill($data);
            $commonRisk->save();
            DB::commit();
            return $commonRisk;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
        
    }

    /*
     * delete common risk
     * @param array
     * @return boolean
     */
    public static function deleteCommonRisk($input)
    {
        $risk = self::find($input['id']);
        if ($risk) {
            if ($risk->delete()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Risk by id
     * 
     * @param int $riskId
     * @return Risk 
     */
    public static function getById($riskId)
    {
        $item = self::where('id', $riskId)
            ->select('*')
            ->first();
        return $item;
    }

    /**
     * Get common risks
     * 
     * @param array $conditions
     * @param array $columns
     * @return Risk collection
     */
    public static function getCommonRisks($columns = ['*'], $conditions = [], $order = 'id', $dir = 'desc') {
        $risks = self::select($columns);
        $risks->orderBy($order, $dir);
        return $risks;
    }
}