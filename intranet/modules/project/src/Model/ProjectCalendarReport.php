<?php

namespace Rikkei\Project\Model;

use Illuminate\Http\Request;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;

class ProjectCalendarReport extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'project_calendar_report';

    const SIGNAL_FINE = 1;
    const SIGNAL_USUALLY = 2;
    const SIGNAL_BAD = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'title', 'description', 'date', 'signal', 'employee_id'];

    /**
     * Get project report by project ID
     *
     * @param $projectId
     * @return array : Format for event of fullcalendar
     */
    public static function getReportByProjectId($projectId)
    {
        if(empty($projectId)){
            return [];
        }

        $reports = self::query()->where('project_id', $projectId)->get();
        $event = [];
        if (!empty($reports)) {
            foreach ($reports as $report) {
                $event[] = [
                    'id' => $report->id,
                    'title' => $report->title,
                    'description' => $report->description,
                    'start' => $report->date,
                    'color' => self::getColorSignal($report->signal)
                ];
            }
        }

        return $event;
    }

    /**
     * Get calendar report by date
     *
     * @param $projectId
     * @param $date
     * @return array
     */
    public static function getReportByProjectDate($reportId)
    {
        if(empty($reportId)){
            return [];
        }

        $report = self::find($reportId);

        if(!empty($report)) {
            return $report->toArray();
        }

        return [];
    }

    /**
     *  Create project calendar report
     *
     * @param Request $request
     * @param $projectId
     * @param $date
     * @return bool
     */
    public static function publishCalendarReport(Request $request, $projectId)
    {
        try {
            $currentUser = Permission::getInstance()->getEmployee();
            $data = $request->except('_token');
            $data['project_id'] = $projectId;
            $data['employee_id'] = $currentUser->id;
            $report = self::create($data);

            return $report->id;
        } catch (\Exception $e) {
            \Log::error($e);
            return false;
        }
    }

    public static function updateCalendarReport(Request $request, $reportId)
    {
        try {
            $data = $request->except('_token');
            $report = self::find($reportId);
            $report->update($data);
            return true;
        } catch (\Exception $e) {
            \Log::error($e);
            return false;
        }
    }

    public static function deleteCalendarReport($reportId)
    {
        try {
            \Log::info('repot_id: ' .$reportId);
            self::where('id', $reportId)->delete();

            return true;
        } catch (\Exception $e) {
            \Log::error($e);
            return false;
        }

    }

    /**
     * Get color signal of project report
     * @param $signal
     * @return mixed
     */
    private static function getColorSignal($signal)
    {
        $colorSignal = [
            self::SIGNAL_FINE => 'blue',
            self::SIGNAL_USUALLY => 'orange',
            self::SIGNAL_BAD => 'red',
        ];

        return data_get($colorSignal, $signal, '#cecece');
    }
}
