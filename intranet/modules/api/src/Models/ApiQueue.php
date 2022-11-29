<?php

namespace Rikkei\Api\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Storage;

/**
 * cronjob call api
 *
 * @author lamnv
 */
class ApiQueue extends CoreModel
{
    protected $table = 'api_sync_queues';
    protected $fillable = ['api_url', 'method', 'data', 'is_auth', 'called_at', 'type', 'employee_id', 'called_by', 'schdule', 'error'];

    const KEY_RUNNING = 'api_calling';

    /*
     * set url, method, auth token
     */
    public function setUrl($url, $method = 'post', $isAuth = true)
    {
        $this->api_url = $url;
        $this->is_auth = $isAuth;
        $this->method = $method;
        return $this;
    }

    /*
     * set data request
     */
    public function setBodyData($data = [])
    {
        $this->data = json_encode($data);
        return $this;
    }

    /*
     * set type sync
     */
    public function setType($type, $employeeId = null)
    {
        $this->type = $type;
        $this->employee_id = $employeeId;
        return $this;
    }

    /*
     * set actor id
     */
    public function setActorId($employeeId = null)
    {
        $this->called_by = $employeeId;
        return $this;
    }

    /*
     * set schedule to run
     */
    public function setSchedule($dateTime = null)
    {
        $this->schedule = Carbon::parse($dateTime)->setTime(0, 0, 0)->toDateTimeString();
        return $this;
    }

    /*
     * set status calling request
     */
    public static function setRunning()
    {
        $fileRunning = 'app/process/' . self::KEY_RUNNING;
        if (!Storage::disk('base')->exists($fileRunning)) {
            Storage::disk('base')->put($fileRunning, 'public');
        }
    }

    /*
     * delete status calling
     */
    public static function deleteRunning()
    {
        $fileRunning = 'app/process/' . self::KEY_RUNNING;
        if (Storage::disk('base')->exists($fileRunning)) {
            Storage::disk('base')->delete($fileRunning);
        }
    }

    /*
     * check is calling
     */
    public static function checkRunning()
    {
        $fileRunning = 'app/process/' . self::KEY_RUNNING;
        return Storage::disk('base')->exists($fileRunning);
    }

    /*
     * call api
     */
    public static function callApi()
    {
        //run in production environment
        if (app()->environment() != 'production') {
            return;
        }
        if (self::checkRunning()) {
            return;
        }
        $collection = self::whereNull('called_at')
                ->whereNull('error')
                ->where(function ($query) {
                    $query->whereNull('schedule')
                            ->orWhere(DB::raw('DATE(schedule)'), '<=', Carbon::now()->toDateString());
                })
                ->get();
        if ($collection->isEmpty()) {
            return;
        }
        self::setRunning();
        try {
            $imAdminToken = CoreConfigData::getValueDb('im.admin.token');
            $imAdminId = CoreConfigData::getValueDb('im.admin.id');
            foreach ($collection as $item) {
                if ($item->type == 'employee_sync') {
                    \Rikkei\Api\Sync\EmployeeSync::callItem($item, 1, ['authToken' => $imAdminToken, 'userId' => $imAdminId]);
                }
            }
            self::deleteRunning();
        } catch (\Exception $ex) {
            self::deleteRunning();
            \Log::info($ex);
        }
    }
}
