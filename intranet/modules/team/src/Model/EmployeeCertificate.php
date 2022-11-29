<?php
namespace Rikkei\Team\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use function redirect;

class EmployeeCertificate extends CoreModel
{
    const KEY_CACHE = 'employee_certies';

    protected $table = 'employee_certies';
    public $timestamps = false;

    public function certImages()
    {
        return self::hasMany(EmployeeCertificateImage::class, 'employee_certies_id', 'id');
    }

    /**
     * save employee cetificate
     * 
     * @param int $employeeId
     * @param array $cetificatesTypeIds
     * @param array $cetificatesType
     * @param int $type
     */
    public static function saveItems($employeeId, $cetificatesTypeIds = [], $cetificatesType = [], $type = null)
    {
        if (! $type) {
            $type = Certificate::TYPE_LANGUAGE;
        }
        $cetificateTable = Certificate::getTableName();
        self::where('employee_id', $employeeId)
            ->whereIn('certificate_id', function ($query) use ($cetificateTable, $type) {
                $query->from($cetificateTable)
                    ->select('id')
                    ->where('type', $type);
            })
            ->delete();

        if (! $cetificatesTypeIds || ! $cetificatesType || ! $employeeId) {
            return;
        }
        $cetificateAdded = [];
        
        $typeCetificates = Certificate::getAllType();
        $tblName = $typeCetificates[$type];
        foreach ($cetificatesTypeIds as $key => $cetificatesTypeId) {
            if (! isset($cetificatesType[$key]) || 
                ! isset($cetificatesType[$key]["employee_{$tblName}"]) || 
                ! $cetificatesType[$key]["employee_{$tblName}"] || 
                in_array($cetificatesTypeId, $cetificateAdded)) {
                continue;
            }
            $employeeCetificateData = $cetificatesType[$key]["employee_{$tblName}"];
            if ($type == Certificate::TYPE_LANGUAGE) {
                $arrayRule = [
                    'level' => 'required|max:255',
                    'start_at' => 'required|max:255',
                ];
            } else {
                $arrayRule = [
                    'start_at' => 'required|max:255',
                ];
            }
            $validator = Validator::make($employeeCetificateData, $arrayRule);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->send();
            }
            if ( isset($employeeCetificateData['end_at']) && ! $employeeCetificateData['end_at']) {
                unset($employeeCetificateData['end_at']);
            }
            $employeeCetificateItem = new self();
            $employeeCetificateItem->setData($employeeCetificateData);
            $employeeCetificateItem->certificate_id = $cetificatesTypeId;
            $employeeCetificateItem->employee_id = $employeeId;
            $employeeCetificateItem->updated_at = date('Y-m-d H:i:s');
            $employeeCetificateItem->save();
            $cetificateAdded[] = $cetificatesTypeId;
        }
    }
    
    /**
     * get language follow employee
     * 
     * @param type $employeeId
     * @return object model
     */
    public static function getItemsFollowEmployee($employeeId, $type = null)
    {
        if ($type == null ) {
            $type = Certificate::TYPE_LANGUAGE;
        }
        $thisTable = self::getTableName();
        $cetificateTable = Certificate::getTableName();
        
        $return = self::select('level', 'start_at', 'end_at', 'type', 
                'name', 'image', 'id', 'place', 'note', 'listen', 'speak', 'read', 'write', 'sum', 'note')
            ->join($cetificateTable, "{$cetificateTable}.id", '=', "{$thisTable}.certificate_id")
            ->where('employee_id', $employeeId);
        if ($type != Certificate::TYPE_ALL) {
            $return->where('type', $type);
        }
        return $return->get();
    }

    /**
     * list allCeritificate for gridView all type
     * @param int $employeeIds
     * @return Collection Description
     */
    public static function listItemByEmployee($employeeId)
    {
        $thisTable = self::getTableName();
        $cetificateTable = Certificate::getTableName();
        
        $collection = self::select('level', 'start_at', 'end_at', 'type', 
                'name', 'image', 'id')
            ->join($cetificateTable, "{$cetificateTable}.id", '=', "{$thisTable}.certificate_id")
            ->where('employee_id', $employeeId);
        
        $pager = Config::getPagerData(null, ['order' => "{$thisTable}.updated_at", 'dir' => 'DESC']);
        $collection = $collection->orderBy($pager['order'], $pager['dir'])->orderBy("{$thisTable}.updated_at", 'ASC');
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get all certificate of employee
     *
     * @param int $employeeId
     * @return collection
     */
    public static function getAllCertificate($employeeId)
    {
        $pager = Config::getPagerData(null, [
            'order' => 'updated_at',
            'dir' => 'DESC'
        ]);
        $collection = self::select(['id', 'name', 'type', 'start_at', 'end_at','level', 'status'])
            ->where('employee_id', $employeeId)
            ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection, [], null, 'LIKE');
        return self::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     * overwrite save method
     *
     * @param array $options
     */
    public function save(array $options = array())
    {
        if (!isset($this->original['name']) ||
            (isset($this->original['name']) &&
            isset($this->attributes['name']) &&
            $this->name &&
            $this->attributes['name'] !== $this->original['name'])
        ) {
            Certificate::checkAndSaveFromCerfiticate($this->name);
        }
        return parent::save($options);
    }

    /**
     * Builder sql to filter list employee certificate
     * @param null collection $filter
     * @return mixed
     */
    public static function listEmployeeCertificates($filter = null)
    {
        $listEmployeeCertificates = EmployeeCertificate::select(
            'employee_certies.id as employee_certies_id',
            'employee_certies.level',
            'employee_certies.end_at',
            'employee_certies.employee_id',
            'employee_certies.start_at',
            'employee_certies.name',
            'employee_certies.confirm_date',
            'employee_certies.status',
            'employees.name as employees_name',
            'employees.employee_code',
            'teams.name as teams_name',
            'teams.id as teams_id',
            'employee_certies.type')
            ->join('employees', 'employees.id', '=', 'employee_certies.employee_id')
            ->join('team_members', 'team_members.employee_id', '=', 'employees.id')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->whereNull('employees.deleted_at')->where(function ($q) {
                $q->orWhereDate('employees.leave_date', '>=', Carbon::toDay()->endOfDay()->format('Y-m-d'))
                    ->orWhereNull('employees.leave_date');
            });
        if (isset($filter->certificateId) && !empty($filter->certificateId)) {
            $listEmployeeCertificates = $listEmployeeCertificates->join('certificates', 'certificates.id', '=', 'employee_certies.certificate_id')->where('employee_certies.certificate_id', $filter->certificateId);
        }
        if (isset($filter->type) && !empty($filter->type)) {
            $listEmployeeCertificates = $listEmployeeCertificates->where('employee_certies.type', (int)$filter->type);
        }
        if (isset($filter->startDate) && !empty($filter->startDate)) {
            $listEmployeeCertificates = $listEmployeeCertificates->whereDate('employee_certies.start_at', '>=', Carbon::parse($filter->startDate)->format('Y-m-d'));
        }
        if (isset($filter->endDate) && !empty($filter->endDate)) {
            $listEmployeeCertificates = $listEmployeeCertificates->whereDate('employee_certies.end_at', '<=', Carbon::parse($filter->endDate)->format('Y-m-d'));
        }
        if (isset($filter->dateConfirm) && !empty($filter->dateConfirm)) {
            $listEmployeeCertificates = $listEmployeeCertificates->whereDate('employee_certies.confirm_date', '=', Carbon::parse($filter->dateConfirm)->format('Y-m-d'));
        }
        if (isset($filter->status) && !empty($filter->status)) {
            $listEmployeeCertificates = $listEmployeeCertificates->where('employee_certies.status', '=', (int)$filter->status);
        }

        return $listEmployeeCertificates;
    }
}
