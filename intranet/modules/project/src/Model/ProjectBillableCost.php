<?php

namespace Rikkei\Project\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;

class ProjectBillableCost extends CoreModel
{
    use SoftDeletes;

    protected $table = 'project_billable_costs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['project_id', 'price', 'month'];

    public static function insertProjectBillableCostDetail($dataBillableCostDetails, $projectId)
    {
        DB::beginTransaction();
        $input = [];
        foreach ($dataBillableCostDetails as $month => $value) {
            $input[] = [
                'project_id' => $projectId,
                'month' => Carbon::parse($month),
                'price' => $value
            ];
        }
        $result = self::insert($input);
        $result ? DB::commit() : DB::rollback();
    }

    public static function updateProjectBillableCostDetail($dataBillableCostDetails, $projectId)
    {
        DB::beginTransaction();
        self::where('project_id', $projectId)->forceDelete();
        $input = [];
        $dataBillableCostDetails = json_decode($dataBillableCostDetails, true);
        foreach ($dataBillableCostDetails as $month => $value) {
            $input[] = [
                'project_id' => $projectId,
                'month' => Carbon::parse($month),
                'price' => $value
            ];
        }
        $result = self::insert($input);
        $result ? DB::commit() : DB::rollback();
    }

    public static function getByProjectId($projectId)
    {
        return self::select(
            'id',
            'project_id',
            'price',
            DB::raw("date_format(month, '%Y-%m') as month")
        )->where('project_id', $projectId)->orderBy('month', 'ASC')->get();
    }

    public static function calculatePercentFromApproveCost($total, $amount)
    {
        if ($total == 0) return 0;
        return ($amount / $total) * 100;
    }

    public static function calculateBillableFromPercentage($total, $percent)
    {
        return ($percent * $total) / 100;
    }

    public static function checkExistBillableCosts($projectId, $billableCosts, $billableCostTotal, $approveCostTotal)
    {
        $approveCostByMonths = ProjectApprovedProductionCost::getTotalApproveCostByMonth($projectId);
        $inputs = [];
        DB::beginTransaction();
        $arrayMonthOfBillableCost = $billableCosts->pluck('month')->all();
        foreach ($approveCostByMonths as $approveCostByMonth) {
            if (in_array($approveCostByMonth->time, $arrayMonthOfBillableCost)) continue;
            $percent = self::calculatePercentFromApproveCost($approveCostTotal, $approveCostByMonth->total);
            $inputs[] = [
                'project_id' => $projectId,
                'month' => Carbon::createFromFormat('Y-m-d', $approveCostByMonth->time . '-01'),
                'price' => self::calculateBillableFromPercentage($billableCostTotal, $percent)
            ];
        }
        if ($inputs) {
            self::insert($inputs);
            DB::commit();
            return self::getByProjectId($projectId);
        }

        return $billableCosts;
    }
}
