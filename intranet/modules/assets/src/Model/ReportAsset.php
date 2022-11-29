<?php

namespace Rikkei\Assets\Model;

use Rikkei\Assets\View\AssetView;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Form as CoreForm;
use Illuminate\Support\Facades\DB;
use Rikkei\Team\View\Config;
use Rikkei\Assets\View\AssetConst;

class ReportAsset extends CoreModel
{
    protected $table = 'report_assets';
    protected $fillable = ['creator_id', 'type', 'status'];

    /**
     * get items that belongs to
     * @return type
     */
    public function items()
    {
        return $this->belongsToMany('\Rikkei\Assets\Model\AssetItem', 'report_asset_items', 'report_id', 'asset_id');
    }

    /**
     * get attribute label
     * @param type $field
     * @param type $list
     * @return type
     */
    public function getAttrLabel($field, $list = [])
    {
        if (isset($list[$this->{$field}])) {
            return $list[$this->{$field}];
        }
        return null;
    }

    /**
     * get creator
     * @return type
     */
    public function creator()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'creator_id', 'id');
    }

    /**
     * get creator nick name
     * @return type
     */
    public function getCreatorAccount()
    {
        $employee = $this->creator;
        if ($employee) {
            return $employee->getNickName();
        }
        return null;
    }

    /**
     * insert or update item
     * @param type $data
     * @return boolean
     */
    public static function insertOrUpdate($data = [])
    {
        $assetIds = $data['asset_ids'];
        $type = $data['type'];
        if (!$assetIds) {
            return false;
        }
        $report = self::create([
            'creator_id' => Permission::getInstance()->getEmployee()->id,
            'type' => $type
        ]);
        $report->items()->attach($assetIds);
        return $report;
    }

    /**
     * get list items
     * @return type
     */
    public static function getGridData()
    {
        $tblTeam = Team::getTableName();
        $tblTmb = TeamMember::getTableName();
        $tblEmp = Employee::getTableName();
        $collection = self::select(
            'report.id',
            'report.creator_id',
            'creator.email as creator_email',
            'report.type',
            'report.status',
            'report.created_at',
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names')
        )
                ->from(self::getTableName() . ' as report')
                ->leftJoin($tblTmb . ' as tmb', 'report.creator_id', '=', 'tmb.employee_id')
                ->leftJoin($tblTeam . ' as team', 'tmb.team_id', '=', 'team.id')
                ->leftJoin($tblEmp . ' as creator', 'report.creator_id', '=', 'creator.id')
                ->groupBy('report.id');
        //check permisison
        $scope = Permission::getInstance();
        $employeeId = $scope->getEmployee()->id;
        if ($scope->isScopeCompany()) {
            //get all
        } elseif ($teamIds = $scope->isScopeTeam()) {
            !is_array($teamIds) ? array($teamIds) : $teamIds;
            $collection->where(function ($query) use ($teamIds, $employeeId) {
                $query->whereIn("tmb.team_id", $teamIds)
                    ->orwhere('report.creator_id', $employeeId);
            });
        } else {
            $collection->where('report.creator_id', $employeeId);
        }
        //filter grid
        self::filterGrid($collection);
        if ($filterTeamId = CoreForm::getFilterData('excerpt', 'team_id')) {
            $collection->leftJoin($tblTmb . ' as ft_tmb', 'report.creator_id', '=', 'ft_tmb.employee_id')
                    ->where('ft_tmb.team_id', $filterTeamId);
        }
        $pager = Config::getPagerData();
        //sort order
        if (CoreForm::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('report.status')
                ->orderBy(DB::raw("DATE_FORMAT(report.created_at,'%Y-%m-%d')"), 'desc');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get list asset items
     * @param type $reportId
     * @return type
     */
    public static function getAssetItems($reportId)
    {
        return self::select(
            'emp.name as user_name',
            'asset.name',
            'asset.code',
            'cat.name as category_name',
            'asset.change_date',
            'asset.id',
            'asset.reason',
            'asset.state as state_using',
            'rai.status as state'
        )
        ->from('report_asset_items as rai')
        ->join(AssetItem::getTableName() . ' as asset', 'rai.asset_id', '=', 'asset.id')
        ->leftJoin(Employee::getTableName() . ' as emp', 'asset.employee_id', '=', 'emp.id')
        ->join(AssetCategory::getTableName() . ' as cat', 'asset.category_id', '=', 'cat.id')
        ->where('rai.report_id', $reportId)
        ->get();
    }

    /**
     * confirm item
     * @param type $reportItem
     * @param type $status
     * @param type $data
     */
    public static function confirmItem($reportItem, $status, $data = [])
    {
        $curUserId = \Illuminate\Support\Facades\Auth::id();
        $assetItems = AssetItem::whereIn('id', $data['item']);
        $type = $data['type'];
        if ($status == AssetConst::STT_RP_REJECTED) {
            $assetItems->update([
                'state' => AssetItem::STATE_USING
            ]);
            $note = trans('asset::view.Confirm reject') . ': ' . AssetItem::getLabelByAction($data['type']);
            $isApprove = false;
            AssetHistory::saveMultiHistory($assetItems->get(), $curUserId, $note, AssetItem::STATE_CONFRIM_REJECT, true);
        } else {
            $isApprove = true;
            switch ($type) {
                case AssetConst::TYPE_HANDING_OVER:
                    $note = trans('asset::view.Approve asset handover');
                    AssetHistory::saveMultiHistory($assetItems->get(), $curUserId, $note, AssetItem::STATE_NOT_USED, true);
                    //send mail to employee use asset
                    $assetList = AssetView::getDataSendMail($data['item']);
                    $dataSend = ['state' => AssetConst::assetActionsList()[$type], 'isApproved' => $isApprove];
                    AssetView::sendMailToEmpUseAsset($assetList, $dataSend,'asset::item.mail.mail_noti_approve_asset');

                    $assetItems->update([
                        'state' => AssetItem::STATE_NOT_USED,
                        'employee_id' => null,
                        'warehouse_id' => $data['warehouse_id'],
                        'allocation_confirm' => null,
                        'received_date' => null,
                        'note_of_emp' => '',
                    ]);
                    break;
                case AssetConst::TYPE_BROKEN_NOTIFY:
                    $note = trans('asset::view.Approval of broken notification asset');
                    AssetHistory::saveMultiHistory($assetItems->get(), $curUserId, $note, AssetItem::STATE_BROKEN, true);
                    $assetItems->update([
                        'state' => AssetItem::STATE_BROKEN,
                        'employee_id' => null,
                        'note_of_emp' => '',
                    ]);
                    break;
                case AssetConst::TYPE_LOST_NOTIFY:
                    $note = trans('asset::view.Approval of lost notification asset');
                    AssetHistory::saveMultiHistory($assetItems->get(), $curUserId, $note, AssetItem::STATE_LOST, true);
                    $assetItems->update([
                        'state' => AssetItem::STATE_LOST,
                        'employee_id' => null,
                        'note_of_emp' => '',
                    ]);
                    break;
                default:
                    break;
            }
        }

        $assetList = AssetView::getDataSendMail($data['item']);
        $dataSend = ['state' => AssetConst::assetActionsList()[$type], 'isApproved' => $isApprove];
        AssetView::sendMailToEmpUseAsset($assetList, $dataSend,'asset::item.mail.mail_noti_approve_asset');

        with(new ReportAsset())->saveMultiReportAsset($reportItem, $status, $data, $assetItems);
    }

    /**
     * save status report_asset_items and type, status report_assets
     * 
     * @param  [collection] $reportItem [Model]
     * @param  [int] $status
     * @param  [array] $data       [iteam: list id asset]
     * @param  [collection] $assetItems
     */
    public function saveMultiReportAsset($reportItem, $status, $data, $assetItems)
    {
        $dataSync = [];
        foreach ($data['item'] as $assetId) {
            $dataSync[$assetId] = ['status' => $assetItems->first()->state];
        }

        $reportItem->items()->syncWithoutDetaching($dataSync);
        if ($reportItem->items->count() == count($data['item'])) {
            $reportItem->status = $status;
            $reportItem->save();
        } else {
            $listAssetId = $reportItem->items()->get()->pluck("id")->toArray();
            $listAsset = AssetItem::whereIn('id', $listAssetId)
                ->whereIn("state", [
                    AssetItem::STATE_BROKEN_NOTIFICATION,
                    AssetItem::STATE_LOST_NOTIFICATION,
                    AssetItem::STATE_SUGGEST_HANDOVER,
                ])->get();
            if ($listAsset && count($listAsset) == 0) {
                    $reportAssetIteam = DB::table('report_asset_items')
                        ->where('report_id', $reportItem->id)
                        ->whereIn('asset_id', $listAssetId)
                        ->groupBy('status')
                        ->get();
                if (count($reportAssetIteam) == 2) {
                    $reportItem->status = AssetConst::TYPE_APPROVALS;
                    $reportItem->save();
                } else {
                    $reportItem->status = $status;
                    $reportItem->save();
                }
            }
        }
    }

    /**
     * render item status html
     * @param type $statuses
     * @param type $class
     * @return type
     */
    public function renderStatusHtml($statuses, $class = 'callout')
    {
        $status = $this->status;
        $html = '<div class="'. $class .' text-center white-space-nowrap ' . $class;
        switch ($status) {
            case AssetConst::STT_RP_PROCESSING:
                $html .=  '-warning">' . $statuses[$status];
                break;
            case AssetConst::STT_RP_REJECTED:
                $html .= '-danger">' . $statuses[$status];
                break;
            case AssetConst::STT_RP_CONFIRMED:
                $html .= '-success">' . $statuses[$status];
                break;
            case AssetConst::STT_RP_APPROVALS:
                $html .= '-info">' . $statuses[$status];
                break;
            default:
                return null;
        }
        return $html .= '</div>';
    }

    /**
     * get creator name
     * @return type
     */
    public function getCreatorName()
    {
        $employee = $this->creator;
        if ($employee) {
            return $employee->name;
        }
        return null;
    }

    public function getReportBy($assetIds, $starts = [])
    {
        $tblReport = self::getTableName();
        $tblReportItems = 'report_asset_items';
        $tblAssetItems = 'manage_asset_items';
        $colection = self::select(
            "{$tblReport}.id",
            "{$tblReport}.creator_id",
            "{$tblReport}.type",
            "{$tblReport}.status",
            "{$tblReport}.created_at",
            "{$tblReport}.updated_at",
            DB::raw("group_concat(" . "{$tblReportItems}.asset_id" . ") as asstet_ids")
        )
        ->leftJoin("{$tblReportItems}", "{$tblReportItems}.report_id", '=', "{$tblReport}.id")
        ->join("{$tblAssetItems} as ast", function($query) use ($tblReportItems, $tblReport, $assetIds) {
            $query->on('ast.id', '=', "{$tblReportItems}.asset_id")
                ->on('ast.employee_id', '=', "{$tblReport}.creator_id");
        })
        ->whereIn("{$tblReportItems}.asset_id", $assetIds)
        ->where("{$tblReport}.status", AssetConst::STT_RP_PROCESSING);
        if (count($starts)) {
            $colection = $colection->whereIn("ast.state", $starts);
        }
        return $colection->groupBy("{$tblReport}.id")->get();
    }
}
