<?php

namespace Rikkei\Assets\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Log;
use Lang;
use Rikkei\Assets\Model\AssetWarehouse;
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Team\Model\Team;
use Session;
use Exception;
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\Assets\View\AssetView;
use Rikkei\Assets\View\AssetConst;
use Rikkei\Assets\View\AssetPermission;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Assets\Model\AssetCategory;
use Rikkei\Assets\Model\AssetAttribute;
use Rikkei\Assets\Model\AssetHistory;
use Rikkei\Assets\Model\AssetItemAttribute;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Datatables;
use Rikkei\Assets\Model\Inventory;
use Rikkei\Assets\Model\InventoryItem;
use Rikkei\Assets\Model\InventoryItemHistory;
use Rikkei\Assets\Model\RequestAssetHistory;
use Rikkei\Assets\Model\RequestAssetItem;
use Rikkei\Assets\Model\ReportAsset;
use Rikkei\Assets\View\ExportAsset;
use Rikkei\Assets\Model\AssetsHistoryRequest;
use Rikkei\Assets\View\RequestView;
use Rikkei\Team\View\TeamList;
use Rikkei\Assets\Model\RequestAssetItemsWarehouse;

class AssetController extends Controller
{
    /**
     * Asset list
     *
     * @return [view]
     */
    public function index()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Menu::setActive('HR');
        if (!AssetPermission::viewListPermision()) {
            View::viewErrorPermission();
        }
        $params = [
            'assetCategoriesList' => AssetCategory::getAssetCategoriesList(),
            'warehouseList' => AssetWarehouse::listWarehouse(),
        ];

        return view('asset::item.index', $params);
    }

    public function getAsset(Datatables $datatables)
    {
        $filter = ['asset_code', 'asset_name', 'category_name', 'warehouse_name', 'manager_name', 'user_name',
            'received_date', 'state', 'allocation_confirm', '_configure', '_serial'
        ];
        foreach ($filter as $field) {
            $options[$field] = Input::get($field);
        }
        $idHandover = Input::get('ids');
        if (isset($idHandover) && $idHandover) {
            $options['ids'] = explode(',', $idHandover);
        }

        $data = AssetItem::getGridData($options);
        $approver = AssetItem::getApprovedAsset();
        $roleName = AssetItem::getRoleName();
        $managerName = AssetItem::getManagerName($options);
        $usersProperty = AssetItem::getUsersProperty($options);
        return $datatables->of($data)
            ->addColumn('', function () {
                return '';
            })
            ->editColumn('state', function ($model) {
                return AssetItem::labelStates()[$model->state];
            })
            ->editColumn('manager_name', function ($model) use ($managerName) {
                if (isset($managerName[$model->manager_id])) {
                    return $managerName[$model->manager_id];
                }
                return null;
            })
            ->editColumn('email', function ($model) use ($usersProperty) {
                if (isset($usersProperty[$model->employee_id])) {
                    return $usersProperty[$model->employee_id]->email;
                }
                return null;
            })
            ->editColumn('approver', function ($model) use ($approver) {
                if (isset($approver[$model->id]) && $model->state == AssetItem::STATE_USING) {
                    return $approver[$model->id]->approver;
                }
                return null;
            })
            ->editColumn('role_name', function ($model) use ($roleName) {
                if ($model->employee_id && isset($roleName[$model->employee_id])) {
                    return $roleName[$model->employee_id]->role_name;
                }
                return null;
            })
            ->editColumn('allocation_confirm', function ($model) {
                if ($model->allocation_confirm !== null) {
                    return AssetItem::labelAllocationConfirm()[$model->allocation_confirm];
                }
                return null;
            })
            ->editColumn('', function ($model) {
                return '<a class="btn btn-success" href="' . route('asset::asset.edit', ['id' => $model->id]) . '" ><i class="fa fa-pencil-square-o"></i></a>
                <form action="' . route('asset::asset.delete') . '" method="post" class="form-inline">
                    <input type=hidden name="_token" value="' . csrf_token() . '">
                    <input type="hidden" name="_method" value="delete">
                    <input type="hidden" name="id" value="' . $model->id . '" />
                    <button href="" class="btn-delete delete-confirm" title="' . trans("asset::view.Delete") . '">
                            <span><i class="fa fa-trash"></i></span>
                    </button>
                </form>';
            })
            ->make(true);
    }

    /**
     * Add asset item
     * @return [view]
     */
    public function add()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Menu::setActive('HR');

        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $params = [
            'allowEdit' => AssetPermission::createAndEditPermision(),
            'warehouseList' => AssetWarehouse::getGridData(),
        ];
        return view('asset::item.edit')->with($params);
    }

    /**
     * Edit asset item
     * @param int $assetItemId
     * @return view
     */
    public function edit($assetItemId)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Menu::setActive('HR');

        $curEmp = Permission::getInstance()->getEmployee();
        $regionEmp = AssetView::getRegionByEmp($curEmp->id);

        if (!AssetPermission::viewDetailPermision() && !AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $assetItem = AssetItem::getAssetItemById($assetItemId);
        if (!$assetItem) {
            return redirect()->route('asset::asset.index')->withErrors(Lang::get('asset::message.Not found item'));
        }
        if (!Permission::getInstance()->isScopeCompany() && $regionEmp !== $assetItem->prefix) {
            View::viewErrorPermission();
        }
        $assetItem->formatDateFields();
        Form::setData($assetItem, 'asset');
        $assetItemAttributes = AssetItemAttribute::getAssetItemAttributes($assetItemId);
        $assetHistories = AssetHistory::getHistoriesByAssetId($assetItemId);
        $allowEdit = false;
        if ($assetItem->state == AssetItem::STATE_NOT_USED && AssetPermission::createAndEditPermision()) {
            $allowEdit = true;
        }
        $configEdit = $assetItem->state == AssetItem::STATE_USING;
        $params = [
            'assetItemAttributes' => $assetItemAttributes,
            'assetHistories' => $assetHistories,
            'allowEdit' => $allowEdit,
            'configEdit' => $configEdit,
            'warehouseList' => AssetWarehouse::getGridData(),
        ];
        return view('asset::item.edit')->with($params);
    }

    /**
     * View asset item
     *
     * @param int $assetItemId
     * @return view
     */
    public function view($assetItemId)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Asset');
        Menu::setActive('HR');

        if (!AssetPermission::viewDetailPermision()) {
            View::viewErrorPermission();
        }
        $assetItem = AssetItem::getAssetItemById($assetItemId);
        if (!$assetItem) {
            return redirect()->route('asset::asset.index')->withErrors(Lang::get('asset::message.Not found item'));
        }
        $assetItem->formatDateFields();
        $assetHistories = AssetHistory::getHistoriesByAssetId($assetItemId);
        $processUsingAsset = AssetHistory::getProcessUsingAsset($assetItemId);
        $params = [
            'assetItem' => $assetItem,
            'assetHistories' => $assetHistories,
            'processUsingAsset' => $processUsingAsset,
        ];
        return view('asset::item.view')->with($params);
    }

    /**
     * Save asset item
     */
    public function save()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::createAndEditPermision()) {
            View::viewErrorPermission();
        }
        $isCreate = false;
        $dataItem = Input::get('item');
        $assetItemId = Input::get('id');
        $assetItemId = !empty($assetItemId) ? $assetItemId : null;
        $rules = [
            'code' => 'required|max:100|unique:manage_asset_items,code,' . $assetItemId,
            'name' => 'required|max:100',
            'category_id' => 'required',
            'warehouse_id' => 'required',
            'warranty_priod' => 'numeric|min:0',
            'serial' => 'max:100|unique:manage_asset_items,serial,' . $assetItemId,

        ];
        $messages = [
            'code.required' => Lang::get('asset::message.Asset code is field required'),
            'code.max' => Lang::get('asset::message.Asset code not be greater than :number characters', ['number' => 100]),
            'code.unique' => Lang::get('asset::message.Asset code has exist'),
            'name.required' => Lang::get('asset::message.Asset name is field required'),
            'name.max' => Lang::get('asset::message.Asset name not be greater than :number characters', ['number' => 100]),
            'category_id.required' => Lang::get('asset::message.Asset category is field required'),
            'warehouse_id.required' => Lang::get('asset::message.Asset warehouse is field required'),
            'serial.max' => Lang::get('asset::message.Serial not be greater than :number characters', ['number' => 100]),
            'warranty_priod.numeric' => Lang::get('asset::message.Warranty priod be type number'),
            'warranty_priod.min' => Lang::get('asset::message.Warranty priod be greater than :number', ['number' => 0]),

        ];

        if ($assetItemId) {
            $assetItem = AssetItem::find($assetItemId);
            if (!$assetItem) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            if (!in_array($assetItem->state, [AssetItem::STATE_NOT_USED, AssetItem::STATE_USING])) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Can not perform this function'));
            }
        } else {
            $assetItem = new AssetItem();
            $isCreate = true;
        }

        if (!empty($dataItem['warranty_exp_date'])) {
            if (empty($dataItem['purchase_date'])) {
                $rules['purchase_date'] = 'required';
                $messages['purchase_date.required'] = Lang::get('asset::message.Please enter purchase date');
            }
            if (!empty($dataItem['purchase_date'])) {
                $purchaseDate = Carbon::createFromFormat('d-m-Y', $dataItem['purchase_date']);
                $warrantyPriodDate = Carbon::createFromFormat('d-m-Y', $dataItem['warranty_exp_date']);
                if ($warrantyPriodDate->lt($purchaseDate)) {
                    $rules['warranty_exp_date'] = 'after:purchase_date';
                    $messages['warranty_exp_date.after'] = Lang::get('asset::message.Warranty date not be less than purchase date');
                }
            }
        }
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        if ($assetItem->state == AssetItem::STATE_USING) {
            //only update configure when status using
            $configData = $dataItem['configure'];
            unset($dataItem);
            $dataItem['configure'] = $configData;
        }
        $curEmp = Permission::getInstance()->getEmployee();
        $prefixRegion = AssetView::getRegionByEmp($curEmp->id);
        DB::beginTransaction();
        try {
            $dataItem = (new AssetItem())->toDateStringFields($dataItem);
            $historyNote = trans('asset::view.Update asset information') . '<br>';
            $checkEdit = false;
            if ($isCreate) {
                $dataItem['state'] = AssetItem::STATE_NOT_USED;
                $dataItem['created_by'] = $curEmp->id;
                $dataItem['prefix'] = (isset($prefixRegion) && $prefixRegion) ? $prefixRegion : 'HN';
                $checkEdit = true;
            } else {
                foreach ($dataItem as $field => $new) {
                    if (isset($assetItem->{$field}) || $assetItem->{$field} == null) {
                        $old = $assetItem->{$field};
                        if (in_array($field, ['supplier_id', 'manager_id', 'warranty_priod', 'origin_id', 'days_before_alert_ood', 'warehouse_id'])) {
                            if ($old == 0 && ($new == '' || $new == null)) continue;
                        }
                        $listField = array('category_id', 'warehouse_id', 'manager_id', 'origin_id', 'supplier_id');
                        if ($old != $new) {
                            if (in_array($field, $listField)) {
                                AssetItem::getItemName($field, $old, $new);
                            }
                            if (isset(AssetItem::fieldNames()[$field])) $field = AssetItem::fieldNames()[$field];
                            $historyNote .= trans('asset::view.- :field : :old => :new', [
                                'field' => $field,
                                'old' => $old,
                                'new' => $new ? $new : "' '",
                            ]);
                            $checkEdit = true;
                        }
                    }
                }
                $oldId = array();
                $newAttr = Input::get('attribute');
                if (is_array($newAttr)) array_values($newAttr);
                $field = 'attribute';
                $old = '';
                $new = '';
                if (isset($assetItem->attributes)) {
                    foreach ($assetItem->attributes as $attribute) {
                        $oldId[] = $attribute->pivot->attribute_id;
                    }
                }
                if ($oldId != $newAttr) {
                    $oldAttr = AssetAttribute::getNameAttributeById($oldId);
                    $newAttr = AssetAttribute::getNameAttributeById($newAttr);
                    foreach ($oldAttr as $oldVal) {
                        $old .= $oldVal . ', ';
                    }
                    foreach ($newAttr as $newVal) {
                        $new .= $newVal . ', ';
                    }
                    if (isset(AssetItem::fieldNames()[$field])) AssetItem::fieldNames()[$field];
                    $historyNote .= trans('asset::view.- :field : :old => :new', [
                        'field' => $field,
                        'old' => trim($old, ', '),
                        'new' => trim($new, ', ')
                    ]);
                    $checkEdit = true;
                }
            }
            $assetItem->setData($dataItem);
            if (!isset($dataItem['reason'])) {
                $assetItem->reason = null;
            }
            $assetItem->save();
            $assetItemId = $assetItem->id;
            $assetAttributes = Input::get('attribute');
            $dataInsertItemAttributes = [];
            if ($assetAttributes) {
                $now = Carbon::now();
                foreach ($assetAttributes as $value) {
                    $dataInsertItemAttributes[] = [
                        'asset_id' => $assetItemId,
                        'attribute_id' => $value,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
            $checkCatAttr = AssetAttribute::getAssetAttributesList($assetItem->category_id)->count();
            if (!empty($dataInsertItemAttributes) || !$checkCatAttr) {
                AssetItemAttribute::deleteAndInsert($assetItemId, $dataInsertItemAttributes);
            }
            $state = AssetHistory::STATE_UPDATE;
            if ($isCreate) {
                $historyNote = Lang::get('asset::view.Add new asset');
                $state = AssetHistory::STATE_CREATE;
            }
            if (!$checkEdit) $historyNote = trans('asset::view.No items have changed');
            AssetHistory::insertHistory($assetItem, $userCurrent->id, $historyNote, $state);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.edit', ['id' => $assetItem->id])->with('messages', $messages);
    }

    /**
     * Delete asset item
     */
    public function delete()
    {
        if (!AssetPermission::deletePermision()) {
            View::viewErrorPermission();
        }
        $assetItemId = Input::get('id');
        DB::beginTransaction();
        try {
            $assetItem = AssetItem::find($assetItemId);
            if (!$assetItem) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            AssetItemAttribute::where('asset_id', $assetItemId)->delete();
            $assetItem->state = AssetItem::STATE_CANCELLED;
            $assetItem->change_date = null;
            $assetItem->reason = null;
            $assetItem->save();
            $assetItem->delete();
            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('asset::message.Delete data success'),
                ]
            ];
            return redirect()->route('asset::asset.index')->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
    }

    /**
     * View asset by employee
     * @return [view]
     */
    public function viewPersonalAsset()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Asset');
        Menu::setActive('Profile');
        $userCurrent = Permission::getInstance()->getEmployee();
        $params = [
            'assetCategoriesList' => AssetCategory::getAssetCategoriesList(),
            'warehouseList' => AssetWarehouse::listWarehouse(),
            'employee_id' => Employee::getNickNameById($userCurrent->id),
            'inventory' => Inventory::getInventoryOpenByEmp($userCurrent->id)
        ];

        return view('asset::index')->with($params);
    }

    public function getAssetProfile(Datatables $datatables)
    {
        $filter = ['asset_code', 'asset_name', 'category_name', 'warehouse_name', 'received_date', 'state', 'allocation_confirm', 'employee_id', 'note_of_emp'];
        foreach ($filter as $field) {
            $options[$field] = Input::get($field);
        }
        $options['employee_id'] = Permission::getInstance()->getEmployee()->id;
        $options['check_profile'] = true;
        $data = AssetItem::getGridData($options);
        return $datatables->of($data)
            ->addColumn('', function () {
                return '';
            })
            ->editColumn('note_of_emp', function ($model) {
                return '<div class="comment"><textarea rows="2" cols="60" name="note_of_emp" class="form-control note_asset_profile" style="">' . $model->note_of_emp . '</textarea>
                <div class="dropdown-content make-conten-dropdown">
                <textarea rows="5" cols="60" readonly="true" type="text" maxlength="1000" style="width: 100%;">' . $model->note_of_emp . '</textarea>
                </div><div>';
            })
            ->editColumn('state', function ($model) {
                return AssetItem::labelStates()[$model->state];
            })
            ->editColumn('note', function ($model) {
                return $model->note;
            })
            ->editColumn('allocation_confirm', function ($model) {
                $confirm = AssetItem::labelAllocationConfirm();
                return ($model->allocation_confirm !== null) && isset($confirm[$model->allocation_confirm]) ? $confirm[$model->allocation_confirm] : '';
            })
            ->editColumn('', function ($model) {
                $labelAllocationConfirm = AssetItem::labelAllocationConfirm();
                return (!array_key_exists($model->allocation_confirm, $labelAllocationConfirm) || $model->allocation_confirm == AssetItem::ALLOCATION_CONFIRM_NONE) ?
                    '<button value="' . $model->id . '" class="btn btn-success btn-allocation-confirm">' . trans('asset::view.Confirm') . '</button>' : '';
            })
            ->make(true);
    }

    /*
     * get persional asset item ajax
     */
    public function getPersonalAssetAjax(Request $request)
    {
        $employeeId = $request->get('employeeId');
        $userCurrent = Permission::getInstance()->getEmployee();
        $employeeId = $employeeId ? $employeeId : $userCurrent->id;
        //check has asset unconfirmed
        $unConfirmItems = AssetItem::hasUnconfirmedAsset($employeeId);
        if ($unConfirmItems) {
            return response()->json(trans('asset::message.You must confirm asset before inventory') . ': ' . $unConfirmItems, 422);
        }
        $dataFilter = [];
        $page = $request->get('page');
        $dataFilter['page'] = $page && $page > 0 ? $page : 1;
        return AssetItem::getGridDataAjax($dataFilter, $employeeId);
    }

    /*
     * confirm asset allocation
     */
    public function confirmAllocation()
    {
        $assetItemId = Input::get('id');
        $assetIteam = AssetItem::find($assetItemId);
        if (!$assetIteam) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        DB::beginTransaction();
        try {
            $assetItem = AssetItem::find($assetItemId);
            if (!$assetItem) {
                return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
            }
            $assetItem->allocation_confirm = Input::get('confirm');
            $assetItem->save();
            $tblRequestAssetHistory = RequestAssetHistory::getTableName();
            $tblAssetItem = AssetItem::getTableName();
            $requestAssetHistoryId = AssetItem::select("{$tblRequestAssetHistory}.id")
                ->leftJoin("{$tblRequestAssetHistory}", "{$tblRequestAssetHistory}.request_id", '=', "{$tblAssetItem}.request_id")
                ->where("{$tblRequestAssetHistory}.action", '=', RequestAssetHistory::ACTION_ALLOCATE)
                ->where("{$tblAssetItem}.id", '=', $assetItemId)
                ->first();
            if ($requestAssetHistoryId) {
                $assetsHistoryRequest = AssetsHistoryRequest::where('asset_id', '=', $assetItemId)->where('request_asset_history_id', '=', $requestAssetHistoryId->id)->first();
            }
            if (isset($assetsHistoryRequest) && $assetsHistoryRequest) {
                $assetsHistoryRequest->allocation_confirm = Input::get('confirm');
                $assetsHistoryRequest->save();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->withErrors($ex->getMessage());
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Confirm data success'),
            ]
        ];
        return redirect()->back()->with('messages', $messages);
    }

    /*
     * confirm asset inventory
     */
    public function confirmAssetInventory(Request $request)
    {
        $assetIds = $request->get('asset_ids');
        $assetIds = $assetIds ? $assetIds : [];
        $inventoryId = $request->get('inventory_id');
        $employeeId = Permission::getInstance()->getEmployee()->id;
        $currentAssetIds = AssetItem::listIdsByEmployee($employeeId);
        $inventoryItem = InventoryItem::findByEmployeeId($inventoryId, $employeeId);
        if (!$inventoryId || !$inventoryItem) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $extraAsset = trim($request->get('extra_asset'));
        if ($extraAsset) {
            $itemStatus = AssetConst::INV_RS_EXCESS;
        } else {
            $itemStatus = AssetConst::INV_RS_ENOUGH;
            if (array_diff($currentAssetIds, $assetIds)) {
                $itemStatus = AssetConst::INV_RS_NOT_ENOUGH;
            }
        }
        $employeeNotes = $request->get('employee_notes');
        if (isset($employeeNotes[0])) {
            unset($employeeNotes[0]);
        }
        DB::beginTransaction();
        try {
            AssetItem::updateEmployeeConfirmed($employeeId, $assetIds, $employeeNotes);
            $inventoryItem->status = $itemStatus;
            $inventoryItem->note = $extraAsset;
            $inventoryItem->save();
            $inventoryItem->closeTask();

            // update table inventoryItemHisory
            InventoryItemHistory::updateHistoryConfirm($inventoryId, $employeeId, $assetIds, $employeeNotes);
            DB::commit();

            return redirect()->back()->with('messages', ['success' => [trans('asset::message.Confirm data success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->with('messages', ['errors' => [trans('core::message.Error system, please try later!')]]);
        }
    }

    public function assetRequestToWh(Request $request) {
        $data = $request->all();
        $userCurrent = Permission::getInstance()->getEmployee();

        $empId = $userCurrent->id;
        $requestId = $data['item']['request_id'];
        $cateIds = $data['cate_id'];
        $qty = $data['qty'];
        $branch = $data['branch'];
        
        //Check employee
        $reqEmp = RequestAssetItemsWarehouse::where('request_id', $requestId)->first();
        if ($reqEmp && $reqEmp->employee_id != $empId) {
            return response()->json([
                'success' => 0,
                'className' => 'modal-danger',
                'message' => 'Bạn không có quyền sửa yêu cầu tài sản này!',
            ]);
        }

        //get origin request
        $reqOrigins = RequestAssetItemsWarehouse::where('employee_id', $empId)->where('request_id', $requestId)
            ->where('status', RequestAssetItemsWarehouse::STATUS_UNALLOCATE)->get()->pluck('id')->toArray();
        $idsUpdate = [];
        $idsDelete = [];
        if (count($cateIds)) {
            DB::beginTransaction();
            $error = [];
            try {
                foreach ($cateIds as $key => $item) {
                    $req = RequestAssetItemsWarehouse::where('employee_id', $empId)->where('request_id', $requestId)
                    ->where('asset_category_id', $item)
                    ->orderBy('id', 'DESC')->first();
                    if (!$req) {
                        $dataInsert = [
                            'employee_id' => $empId,
                            'request_id' => $requestId,
                            'asset_category_id' => $item,
                            'branch' => $branch,
                            'quantity' => $qty[$key],
                            'allocate' => 0,
                            'unallocate' => $qty[$key],
                            'status' => RequestAssetItemsWarehouse::STATUS_UNALLOCATE,
                        ];
                        RequestAssetItemsWarehouse::create($dataInsert);
                    } else {
                        $idsUpdate[] = $req->id;
                        $newQty = $qty[$key];
                        $dataUpdate = [];
                        if ($req->status == RequestAssetItemsWarehouse::STATUS_UNALLOCATE && $req->quantity == $req->unallocate) {
                            $dataUpdate = [
                                'quantity' => $newQty,
                                'unallocate' => $newQty,
                                'branch' => $branch,
                            ];
                        } else {
                            if ($req->status == RequestAssetItemsWarehouse::STATUS_UNALLOCATE) {
                                if ($newQty < $req->allocate) {
                                    // lỗi
                                    $error[$item]['mess'] = 'Số lượng yêu cầu phải >= số lượng đã cấp: '.$req->allocate;
                                } elseif ($newQty == $req->allocate) {
                                    $dataUpdate = [
                                        'branch' => $branch,
                                    ];
                                } else {
                                    $dataUpdate = [
                                        'quantity' => $newQty,
                                        'unallocate' => $newQty - $req->allocate,
                                        'branch' => $branch,
                                    ];
                                }
                            } else {
                                if ($newQty < $req->allocate) {
                                    // lỗi
                                    $error[$item]['mess'] = 'Số lượng yêu cầu phải >= số lượng đã cấp: '.$req->allocate;
                                } elseif ($newQty == $req->allocate) {
                                    $dataUpdate = [
                                        'branch' => $branch,
                                    ];
                                } else {
                                    $dataInsert = [
                                        'employee_id' => $empId,
                                        'request_id' => $requestId,
                                        'asset_category_id' => $item,
                                        'branch' => $branch,
                                        'quantity' => $newQty,
                                        'allocate' => $req->allocate,
                                        'unallocate' => $newQty - $req->allocate,
                                        'status' => RequestAssetItemsWarehouse::STATUS_UNALLOCATE,
                                    ];
                                    RequestAssetItemsWarehouse::create($dataInsert);
                                }
                            }
                        }
                        if ($dataUpdate) {
                            $req->update($dataUpdate);
                        }
                    }
                }

                //delete record
                foreach ($reqOrigins as $key => $valId) {                    
                    if (!in_array($valId, $idsUpdate)) {
                        $idsDelete[] = $valId;
                    }
                }
                if (count($idsDelete)) {
                    foreach ($idsDelete as $val) {
                        $req = RequestAssetItemsWarehouse::find($val);
                        if (!$req) {
                            continue;
                        }
                        if ($req->allocate > 0) {
                            $reqCheck = RequestAssetItemsWarehouse::where('employee_id', $req->employee_id)->where('request_id', $req->request_id)
                            ->where('asset_category_id', $req->asset_category_id)
                            ->where('status', RequestAssetItemsWarehouse::STATUS_ALLOCATE)
                            ->orderBy('id', 'DESC')->first();
                            if ($reqCheck) {
                                $dataUpdate = [
                                    'quantity' => $req->allocate,
                                    'allocate' => $req->allocate
                                ];
                                $reqCheck->update($dataUpdate);
                            } else {
                                $dataCreate = [
                                    'employee_id' => $req->employee_id,
                                    'request_id' => $req->request_id,
                                    'asset_category_id' => $req->asset_category_id,
                                    'branch' => $req->branch,
                                    'quantity' => $req->allocate,
                                    'allocate' => $req->allocate,
                                    'unallocate' => 0,
                                    'status' => RequestAssetItemsWarehouse::STATUS_ALLOCATE
                                ];
                                RequestAssetItemsWarehouse::create($dataCreate);
                            }
                        }
                        $req->delete();
                    }
                }

                //Update state request_asset
                $reqEnoughs = RequestAssetItemsWarehouse::where('request_id', $requestId)->get();
                $reqAsset = RequestAsset::find($requestId);
                $isNotEnough = false;
                if (count($reqEnoughs)) {
                    foreach ($reqEnoughs as $key => $value) {
                        if ($value->status == 1) {
                            $isNotEnough = true;
                            break;
                        }
                    }
                    $dataUpdateReqAsset = [
                        'state' => RequestAsset::STATE_ENOUGH
                    ];
                    if ($isNotEnough) {
                        $dataUpdateReqAsset = [
                            'state' => RequestAsset::STATE_NOT_ENOUGH
                        ];
                    }
                } else {
                    $dataUpdateReqAsset = [
                        'state' => RequestAsset::STATE_NOT_YET
                    ];
                }
                if ($reqAsset) {
                    $reqAsset->update($dataUpdateReqAsset);
                }

                if (empty($error)) {
                    DB::commit();
                    return response()->json([
                        'success' => 1,
                        'className' => 'modal-success',
                        'message' => 'Gửi yêu cầu tới kho thành công!',
                    ]);
                }
                return response()->json([
                    'success' => 0,
                    'className' => 'modal-danger',
                    'message' => 'Gửi yêu cầu tới kho không thành công!',
                    'errors' => $error,
                ]);
            } catch (Exception $e) {
                \Log::info($e);
                DB::rollback();
            } 
        }
    }

    /**
     * Asset allocation to employee
     */
    public function assetAllocation()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::allocationAndRetrievalPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetItemId = Input::get('asset_id');
        $assetItem = AssetItem::whereIn('id', $assetItemId);
        $listAssetItem = $assetItem->get();
        $categoryId = Input::get('cate_id');
        if (($listAssetItem->isEmpty())) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $listAsset = [];
        foreach ($listAssetItem as $item) {
            array_push($listAsset, $item);
        }

        $rules = [
            'employee_id' => 'required',
            'received_date' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'employee_id.required' => Lang::get('asset::message.Asset user is field required'),
            'received_date.required' => Lang::get('asset::message.Allocation date is field required'),
            'reason.required' => Lang::get('asset::message.Allocation reason is field required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        //check number quantity allocate
        $requestAsset = RequestAsset::find($dataItem['request_id']);
        if ($requestAsset) {
            $rqCatsQty = RequestAssetItem::getCatsQty($requestAsset->id);
            $listAssetByCats = $listAssetItem->groupBy('category_id')->toArray();
            foreach ($listAssetByCats as $catId => $arrayItem) {
                if (isset($rqCatsQty[$catId]) && $rqCatsQty[$catId] < count($arrayItem)) {
                    return redirect()->back()->withErrors(trans('asset::message.Number allocation may not be greater than total quantity in request'));
                }
            }
        }
        DB::beginTransaction();
        try {
            $assetItem->update([
                'received_date' => Carbon::createFromFormat('d-m-Y', $dataItem['received_date'])->format('Y-m-d'),
                'change_date' => Carbon::createFromFormat('d-m-Y', $dataItem['received_date'])->format('Y-m-d'),
                'state' => AssetItem::STATE_USING,
                'allocation_confirm' => AssetItem::ALLOCATION_CONFIRM_NONE,
                'employee_id' => $dataItem['employee_id'],
                'request_id' => $dataItem['request_id'],
                'reason' => $dataItem['reason'],
                'warehouse_id' => null
            ]);
            //update status request asset to close
            $isCloseRequest = false;
            if ($requestAsset) {
                foreach ($listAssetByCats as $catId => $arrayItems) {
                    RequestAssetItem::updateQtyAllocated($requestAsset->id, $catId, count($arrayItems));
                }
                if (RequestAssetItem::checkRequestAllocated($requestAsset->id)) {
                    $requestAsset->update(['status' => RequestAsset::STATUS_CLOSE]);
                    $isCloseRequest = true;
                }
            }

            $employeeAllocated = AssetItem::getEmployeeAllocated($dataItem['employee_id']);
            $note = 'asset::view.Allocation asset to: :name';
            AssetHistory::saveMultiHistory($assetItem->get(), $userCurrent->id, $note, AssetHistory::STATE_ALLOCATION);

            if ($employeeAllocated) {
                $dataSendMail = [];
                $dataSendMail['mail_to'] = $employeeAllocated->email;
                $dataSendMail['receiver_name'] = $employeeAllocated->name;
                $dataSendMail['mail_title'] = Lang::get('asset::view.[Rikkeisoft] Confirm allocation new asset');
                $dataSendMail['href'] = route('asset::profile.view-personal-asset');
                $dataSendMail['list_asset'] = $listAsset;
                $template = 'asset::item.mail.mail_confirm_allocation';
                AssetView::pushEmailToQueue($dataSendMail, $template);
            }
            //check save history
            if (Input::get('save_history') || $isCloseRequest) {
                $requsetAssetsHistoryId = RequestAssetHistory::create([
                    'request_id' => $dataItem['request_id'],
                    'employee_id' => $userCurrent->id,
                    'action' => RequestAssetHistory::ACTION_ALLOCATE
                ])->id;
                $data = [];
                $now = Carbon::now();
                foreach ($listAssetItem as $key => $value) {
                    $data[] = [
                        'request_asset_history_id' => $requsetAssetsHistoryId,
                        'asset_id' => $value->id,
                        'code' => $value->code,
                        'name' => $value->name,
                        'allocation_confirm' => AssetItem::ALLOCATION_CONFIRM_NONE,
                        'warehouse_id' => $value->warehouse_id,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                };
                AssetsHistoryRequest::insert($data);
            }

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        if ($redirectUrl = Input::get('redirect_url')) {
            return redirect()->to($redirectUrl)->with('messages', ['success' => [trans('asset::message.Allocation success')]]);
        }
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /**
     * Asset retrieval by employee
     */
    public function assetRetrieval()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::allocationAndRetrievalPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetItemId = Input::get('asset_id');
        $assetItem = AssetItem::whereIn('id', $assetItemId);
        $reports = with(new ReportAsset())->getReportBy($assetItemId, [AssetItem::STATE_SUGGEST_HANDOVER]);
        $warehouseId = Input::get('warehouse_id');
        if ($assetItem->get()->isEmpty()) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }

        $rules = [
            'change_date' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'change_date.required' => Lang::get('asset::message.Retrieval date is field required'),
            'reason.required' => Lang::get('asset::message.Retrieval reason is field required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $changeDate = $dataItem['change_date'];
            $changeReason = $dataItem['reason'];
            $note = 'asset::view.Retrieval asset from: :name';
            AssetHistory::saveDataHistory($assetItem->get(), $userCurrent->id, $note, AssetHistory::STATE_CREATE, $changeDate, $changeReason);
            //send mail to employee using
            $assetList = AssetView::getDataSendMail($assetItemId);
            $data = ['state' => trans('asset::view.Asset retrieval'), 'isApproved' => true];
            AssetView::sendMailToEmpUseAsset($assetList, $data, 'asset::item.mail.mail_noti_approve_asset');

            $assetItem->update([
                'state' => AssetItem::STATE_NOT_USED,
                'employee_id' => null,
                'request_id' => null,
                'change_date' => Carbon::createFromFormat('d-m-Y', $dataItem['change_date'])->format('Y-m-d'),
                'allocation_confirm' => null,
                'received_date' => null,
                'reason' => $dataItem['reason'],
                'employee_note'=> null,
            ]);
            if ($dataItem['warehouse']) {
                $warehouseId = $dataItem['warehouse'];
            }
            if (isset($warehouseId) && $warehouseId) {
                $assetItem->update([
                    'warehouse_id' => $warehouseId
                ]);
            }
            // === đồng bộ bên báo cáo ===
            if (count($reports)) {
                $objReportAsset = new ReportAsset();
                foreach ($reports as $report) {
                    if ($report->asstet_ids != '') {
                        $data['item'] = explode(',', $report->asstet_ids);
                        $objReportAsset->saveMultiReportAsset($report, AssetConst::STT_RP_CONFIRMED, $data, AssetItem::whereIn('id', $data['item']));
                    }
                }
            }

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /**
     * Asset lost notification
     */
    public function assetLostNotification()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::allocationAndRetrievalPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetItemIds = Input::get('asset_id');
        $assetItems = AssetItem::whereIn('id', $assetItemIds);
        if ($assetItems->get()->isEmpty()) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $rules = [
            'change_date' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'change_date.required' => Lang::get('asset::message.Lost notification date is field required'),
            'reason.required' => Lang::get('asset::message.Lost notification reason is field required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetItemsTmp = clone $assetItems;
            AssetItem::updateAllocationConfirm($assetItemsTmp);
            $assetItems->update([
                'change_date' => Carbon::createFromFormat('d-m-Y', $dataItem['change_date'])->format('Y-m-d'),
                'state' => AssetItem::STATE_LOST_NOTIFICATION,
                'reason' => $dataItem['reason'],
            ]);
            //save asset histories
            $note = 'asset::view.Asset lost notification';
            AssetHistory::saveMultiHistory($assetItems->get(), $userCurrent->id, $note, AssetHistory::STATE_LOST_NOTIFICATION);

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /**
     * Asset broken notification
     */
    public function assetBrokenNotification()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::allocationAndRetrievalPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetItemIds = Input::get('asset_id');
        $assetItems = AssetItem::whereIn('id', $assetItemIds);
        if ($assetItems->get()->isEmpty()) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $rules = [
            'change_date' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'change_date.required' => Lang::get('asset::message.Broken notification date is field required'),
            'reason.required' => Lang::get('asset::message.Broken notification reason is field required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetItemsTmp = clone $assetItems;
            AssetItem::updateAllocationConfirm($assetItemsTmp);
            $assetItems->update([
                'change_date' => Carbon::createFromFormat('d-m-Y', $dataItem['change_date'])->format('Y-m-d'),
                'state' => AssetItem::STATE_BROKEN_NOTIFICATION,
                'reason' => $dataItem['reason'],
            ]);

            //save asset histories
            $note = 'asset::view.Asset broken notification';
            AssetHistory::saveMultiHistory($assetItems->get(), $userCurrent->id, $note, AssetHistory::STATE_BROKEN_NOTIFICATION);

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /**
     * Asset sugget liquidate
     */
    public function assetSuggestLiquidate()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::allocationAndRetrievalPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetItemIds = Input::get('asset_id');
        $assetItems = AssetItem::whereIn('id', $assetItemIds);
        if ($assetItems->get()->isEmpty()) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $rules = [
            'change_date' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'change_date.required' => Lang::get('asset::message.Suggest liquidate date is field required'),
            'reason.required' => Lang::get('asset::message.Suggest liquidate reason is field required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetItemsTmp = clone $assetItems;
            AssetItem::updateAllocationConfirm($assetItemsTmp);
            $assetItems->update([
                'change_date' => Carbon::createFromFormat('d-m-Y', $dataItem['change_date'])->format('Y-m-d'),
                'state' => AssetItem::STATE_SUGGEST_LIQUIDATE,
                'reason' => $dataItem['reason'],
            ]);
            $note = 'asset::view.Asset suggest liquidate';
            AssetHistory::saveMultiHistory($assetItems->get(), $userCurrent->id, $note, AssetHistory::STATE_SUGGEST_LIQUIDATE);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /**
     * Asset sugget repair and maintenance
     */
    public function assetSuggestRepairMaintenance()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::allocationAndRetrievalPermision()) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetItemIds = Input::get('asset_id');
        $assetItems = AssetItem::whereIn('id', $assetItemIds);
        if ($assetItems->get()->isEmpty()) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $rules = [
            'change_date' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'change_date.required' => Lang::get('asset::message.Suggest repair, maintenance date is field required'),
            'reason.required' => Lang::get('asset::message.Suggest repair, maintenance reason is field required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetItemsTmp = clone $assetItems;
            AssetItem::updateAllocationConfirm($assetItemsTmp);
            $assetItems->update([
                'change_date' => Carbon::createFromFormat('d-m-Y', $dataItem['change_date'])->format('Y-m-d'),
                'state' => AssetItem::STATE_SUGGEST_REPAIR_MAINTENANCE,
                'reason' => $dataItem['reason'],
            ]);
            $note = 'asset::view.Asset suggest repair, maintenance';
            AssetHistory::saveMultiHistory($assetItems->get(), $userCurrent->id, $note, AssetHistory::STATE_SUGGEST_REPAIR_MAINTENANCE);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /**
     * Approve asset lost notification, broken notification, suggest liquidate and suggest repair, maintenance
     */
    public function approve()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::approvePermision()) {
            View::viewErrorPermission();
        }
        $assetItemIds = Input::get('item');
        $state = Input::get('state');
        if (!$assetItemIds) {
            return redirect()->back()->withErrors(Lang::get('asset::message.No data'));
        }
        $isApprove = false;
        if (!empty(Input::get('approve')) && Input::get('approve')) {
            $isApprove = true;
        }
        $reports = with(new ReportAsset())->getReportBy($assetItemIds, [AssetItem::STATE_BROKEN_NOTIFICATION, AssetItem::STATE_LOST_NOTIFICATION]);
        DB::beginTransaction();
        try {
            $dataUpdate = [];
            $historyNote = null;
            $stateHistory = null;
            switch ($state) {
                case AssetItem::STATE_BROKEN_NOTIFICATION:
                    $dataUpdate = [
                        'state' => AssetItem::STATE_BROKEN,
                        'note_of_emp' => '',
                        'employee_id' => null,
                    ];
                    $historyNote = Lang::get('asset::view.Approval of broken notification asset');
                    $stateHistory = AssetHistory::STATE_BROKEN;
                    if (!$isApprove) {
                        $historyNote = Lang::get('asset::view.Unapproval of broken notification asset');
                    }
                    break;
                case AssetItem::STATE_SUGGEST_REPAIR_MAINTENANCE:
                    $dataUpdate = [
                        'state' => AssetItem::STATE_REPAIRED_MAINTAINED,
                        'note_of_emp' => '',
                        'employee_id' => null,
                    ];
                    $historyNote = Lang::get('asset::view.Approval of suggest repair, maintenance asset');
                    $stateHistory = AssetHistory::STATE_REPAIRED_MAINTAINED;
                    if (!$isApprove) {
                        $historyNote = Lang::get('asset::view.Unapproval of suggest repair, maintenance asset');
                    }
                    break;
                case AssetItem::STATE_LOST_NOTIFICATION:
                    $dataUpdate = [
                        'state' => AssetItem::STATE_LOST,
                        'note_of_emp' => '',
                        'employee_id' => null,
                    ];
                    $historyNote = Lang::get('asset::view.Approval of lost notification asset');
                    $stateHistory = AssetHistory::STATE_LOST;
                    if (!$isApprove) {
                        $historyNote = Lang::get('asset::view.Unapproval of lost notification asset');
                    }
                    break;
                case AssetItem::STATE_SUGGEST_LIQUIDATE:
                    $dataUpdate = [
                        'state' => AssetItem::STATE_LIQUIDATE,
                        'note_of_emp' => '',
                        'employee_id' => null,
                    ];
                    $historyNote = Lang::get('asset::view.Approval of suggest liquidate asset');
                    $stateHistory = AssetHistory::STATE_LIQUIDATE;
                    if (!$isApprove) {
                        $historyNote = Lang::get('asset::view.Unapproval of suggest liquidate asset');
                    }
                    break;
                default:
                    break;
            }
            if (!empty($dataUpdate['state'])) {
                foreach ($assetItemIds as $key => $value) {
                    $assetItem = AssetItem::find($value);
                    if (!$assetItem) {
                        continue;
                    }
                    if ($isApprove) {
                        $assetItem->change_date = Carbon::now()->format('Y-m-d');
                        $assetItem->state = $dataUpdate['state'];
                        $assetItem->note_of_emp = $dataUpdate['note_of_emp'];
                        $assetItem->employee_id = $dataUpdate['employee_id'];
                    } else {
                        $assetItem->state = $assetItem->employee_id ? AssetItem::STATE_USING : AssetItem::STATE_NOT_USED;
                        $assetItem->change_date = null;
                        $assetItem->reason = null;
                        $stateHistory = AssetHistory::STATE_UNAPPROVE;
                    }
                    $assetItem->save();
                    AssetHistory::insertHistory($assetItem, $userCurrent->id, $historyNote, $stateHistory);
                }
                $assetList = AssetView::getDataSendMail($assetItemIds);
                $data = ['state' => AssetItem::labelStates()[$dataUpdate['state']], 'isApproved' => $isApprove];
                if (in_array($state, [AssetItem::STATE_BROKEN_NOTIFICATION, AssetItem::STATE_LOST_NOTIFICATION])) {
                    AssetView::sendMailToEmpUseAsset($assetList, $data, 'asset::item.mail.mail_noti_approve_asset');
                }
            }
            // đồng bộ bên báo cáo
            if (count($reports)) {
                if ($isApprove) {
                    $status = AssetConst::STT_RP_CONFIRMED;
                } else {
                    $status = AssetConst::STT_RP_REJECTED;
                }

                $objReportAsset = new ReportAsset();
                foreach ($reports as $report) {
                    if ($report->asstet_ids != '') {
                        $data['item'] = explode(',', $report->asstet_ids);
                        $objReportAsset->saveMultiReportAsset($report, $status, $data, AssetItem::whereIn('id', $data['item']));
                    }
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->route('asset::asset.index')->withErrors($messages);
        }

        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /*
     * Confirm repaired and maintained asset
     */
    public function confirmRepairedAndMaintained()
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!AssetPermission::approvePermision()) {
            View::viewErrorPermission();
        }
        $assetItemIds = Input::get('item');
        $state = Input::get('state');
        if (!$assetItemIds) {
            return redirect()->back()->withErrors(Lang::get('asset::message.No data'));
        }
        DB::beginTransaction();
        try {
            foreach ($assetItemIds as $key => $value) {
                $assetItem = AssetItem::find($value);
                if (!$assetItem) {
                    continue;
                }
                $assetItem->change_date = Carbon::now()->format('Y-m-d');
                $assetItem->state = $assetItem->employee_id ? AssetItem::STATE_USING : AssetItem::STATE_NOT_USED;
                $assetItem->save();
                $dataHistory = [];
                $dataHistory['asset_id'] = $assetItem->id;
                $dataHistory['employee_id'] = $assetItem->employee_id;
                $dataHistory['state'] = AssetHistory::STATE_HAD_REPAIRED_MAINTAINED;
                $dataHistory['change_date'] = $assetItem->change_date;
                $dataHistory['created_by'] = $userCurrent->id;
                $dataHistory['created_at'] = $assetItem->updated_at;
                $dataHistory['updated_at'] = $assetItem->updated_at;
                $dataHistory['note'] = Lang::get('asset::view.Approval of asset repaired, maintained');
                AssetHistory::insert($dataHistory);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->route('asset::asset.index')->withErrors($messages);
        }

        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    /**
     * Get asset attribute and asset code by asset category
     * @return [json]
     */
    public function ajaxGetAssetAttributesAndCode()
    {
        $assetCategoryId = Input::get('assetCategoryId');
        $assetItemId = Input::get('assetItemId');
        $params = [
            'assetAttributesList' => AssetAttribute::getAssetAttributesList($assetCategoryId),
        ];
        $html = view('asset::item.include.asset_attributes_list')->with($params)->render();
        $assetCode = AssetItem::getAssetCodeByCategory($assetCategoryId, $assetItemId);

        return response()->json(['html' => $html, 'code' => $assetCode]);
    }

    /**
     * Get asset information
     * @return [json]
     */
    public function ajaxGetAssetInformation()
    {
        $assetItemId = Input::get('listItem');
        $assetItem = AssetItem::getAssetItemById($assetItemId);
        $stateModal = Input::get('stateModal');
        $listState = AssetItem::whereIn('id', $assetItemId)->pluck('state', 'id')->toArray();
        if (!isset($assetItemId)) {
            $response['success'] = 0;
            $response['messages'] = Lang::get('asset::message.Please choose asset');
            return response()->json($response);
        }
        if (empty($assetItem)) {
            $response['success'] = 0;
            Session::flash('messages', [
                    'errors' => [
                        Lang::get('asset::message.Not found item'),
                    ]
                ]
            );
            return response()->json($response);
        }
        $params = [
            'assetItem' => $assetItem,
        ];
        $getFucByState = AssetItem::getFuncByState();
        switch ($stateModal) {
            case AssetConst::MODAL_ALLOCATION:
                $stateNotTrue = array_diff($listState, $getFucByState['allocation']);
                if (empty($stateNotTrue)) {
                    $response['success'] = 1;
                    $response['html'] = view('asset::item.include.form_asset_allocation')->with($params)->render();
                    return $response;
                }
                $response['success'] = 0;
                $response['messages'] = Lang::get('asset::message.All asset choose state must be not used. Below are asset state not valid');
                $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById(array_keys($stateNotTrue))])->render();
                break;
            case AssetConst::MODAL_RETRIEVAL:
                $stateNotTrue = array_diff($listState, $getFucByState['retrieval']);
                if (empty($stateNotTrue)) {
                    $response['success'] = 1;
                    $response['html'] = view('asset::item.include.form_asset_retrieval')->with(['assetItem' => $assetItem])->render();
                    return $response;
                }
                $response['success'] = 0;
                $response['messages'] = Lang::get('asset::message.Asset retrieval must state using, handover, repaired, maintained. Below are asset state not valid');
                $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById(array_keys($stateNotTrue))])->render();
                break;
            case AssetConst::MODAL_LOST_NOTIFICATION:
                $stateNotTrue = array_diff($listState, $getFucByState['lostNotify']);
                if (empty($stateNotTrue)) {
                    $response['success'] = 1;
                    $response['html'] = view('asset::item.include.form_asset_lost_notification')->with($params)->render();
                    return $response;
                }
                $response['success'] = 0;
                $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById(array_keys($stateNotTrue))])->render();
                $response['messages'] = Lang::get('asset::message.Asset lost notification must state using or not using. Below are asset state not valid');
                break;
            case AssetConst::MODAL_BROKEN_NOTIFICATION:
                $stateNotTrue = array_diff($listState, $getFucByState['brokenNotify']);
                if (empty($stateNotTrue)) {
                    $response['success'] = 1;
                    $response['html'] = view('asset::item.include.form_asset_broken_notification')->with($params)->render();
                    return $response;
                }
                $response['success'] = 0;
                $response['messages'] = Lang::get('asset::message.Asset broken notification must state using or not using. Below are asset state not valid');
                $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById(array_keys($stateNotTrue))])->render();
                break;
            case AssetConst::MODAL_SUGGEST_LIQUIDATE:
                $stateNotTrue = array_diff($listState, $getFucByState['sugLiquidate']);
                if (empty($stateNotTrue)) {
                    $response['success'] = 1;
                    $response['html'] = view('asset::item.include.form_asset_suggest_liquidate')->with($params)->render();
                    return $response;
                }
                $response['success'] = 0;
                $response['messages'] = Lang::get('asset::message.Asset suggest liquidate must state using, not using, suggest repair, repair, broken notification, broken. Below are asset state not valid');
                $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById(array_keys($stateNotTrue))])->render();
                break;
            case AssetConst::MODAL_SUGGEST_REPAIR_MAINTENACE:
                $stateNotTrue = array_diff($listState, $getFucByState['repair']);
                if (empty($stateNotTrue)) {
                    $response['success'] = 1;
                    $response['html'] = view('asset::item.include.form_asset_suggest_repair_maintenance')->with($params)->render();
                    return $response;
                }
                $response['success'] = 0;
                $response['messages'] = Lang::get('asset::message.Asset suggest repair must state using, not using, broken notification, broken. Below are asset state not valid');
                $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById(array_keys($stateNotTrue))])->render();
                break;
            case AssetConst::MODAL_RETURN_CUSTOMER:
                $stateNotTrue = array_diff($listState, $getFucByState['returnCustomer']);
                if (empty($stateNotTrue)) {
                    $response['success'] = 1;
                    $response['html'] = view('asset::item.include.form_asset_return_customer')->with($params)->render();
                    return $response;
                }
                $response['success'] = 0;
                $response['messages'] = Lang::get('asset::message.Asset must state not using. Below are asset state not valid');
                $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById(array_keys($stateNotTrue))])->render();
                break;

            default:
                $response['html'] = '';
                break;
        }

        return response()->json($response);
    }

    /**
     * Get asset lost, repair, liquidate notification to approve
     * @return [json]
     */
    public function ajaxGetAssetToApprove()
    {
        $assetState = Input::get('assetState');
        $params = [
            'assetItems' => AssetItem::getAssetItemsByState([intval($assetState)]),
        ];
        $view = 'asset::item.include.form_approve_asset_lost_notification';
        switch ($assetState) {
            case AssetItem::STATE_BROKEN_NOTIFICATION:
                $view = 'asset::item.include.form_approve_asset_broken_notification';
                break;
            case AssetItem::STATE_SUGGEST_REPAIR_MAINTENANCE:
                $view = 'asset::item.include.form_approve_asset_suggest_repair_maintenance';
                break;
            case AssetItem::STATE_SUGGEST_LIQUIDATE:
                $view = 'asset::item.include.form_approve_asset_suggest_liquidate';
                break;
            case AssetItem::STATE_REPAIRED_MAINTAINED:
                $view = 'asset::item.include.form_approve_asset_repaired_maintained';
                break;
            default:
                $view = 'asset::item.include.form_approve_asset_lost_notification';
                break;
        }
        $html = view($view)->with($params)->render();

        return response()->json(['html' => $html]);
    }

    /*
     * View report before download
     */
    public function viewReport(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 600);
        if (!AssetPermission::reportPermision()) {
            View::viewErrorPermission();
        }
        $data = $request->get('data');
        parse_str($data, $dataSubmit);
        $optionsReport = [];
        switch ($dataSubmit['report_type']) {
            case AssetConst::REPORT_TYPE_LOST_AND_BROKEN:
                $optionsReport['start_date'] = Carbon::createFromFormat('d-m-Y', $dataSubmit['date']);
                $optionsReport['end_date'] = Carbon::now();
                $optionsReport['team_id'] = $dataSubmit['team_id'];
                $states = [AssetItem::STATE_BROKEN, AssetItem::STATE_LOST];
                return [
                    'data' => AssetItem::getAssetItemsReport($states, $dataSubmit['report_type'], $optionsReport),
                    'startDate' => Carbon::parse($dataSubmit['date'])->format('Y-m-d'),
                    'endDate' => Carbon::now()->format('Y-m-d'),
                    'team' => Team::getTeamById($dataSubmit['team_id']),
                    'labelAsset' => AssetItem::labelStates(),
                ];
            case AssetConst::REPORT_TYPE_DETAIL_BY_EMPLOYEE:
                $employeeIds = isset($dataSubmit['employees']) ? explode(',', $dataSubmit['employees']) : null;
                if (count($employeeIds) < 0) {
                    return redirect()->back()->with(['messages' => Lang::get('asset::message.You have not selected any employees')]);
                }
                return [
                    'data' => AssetItem::getAssetByEmployee($employeeIds),
                    'dateTo' => Carbon::now()->format('Y-m-d'),
                ];
            case AssetConst::REPORT_TYPE_DETAIL_ON_ASSET_USE_PROCESS:
                $assetIds = isset($dataSubmit['assets']) ? explode(',', $dataSubmit['assets']) : null;
                if (count($assetIds) < 0) {
                    return redirect()->back()->with(['messages' => Lang::get('asset::message.You have not selected any employees')]);
                }
                return [
                    'data' => AssetHistory::reportAssetByUseProcess($assetIds),
                    'startDate' => $dataSubmit['date_from'] ? Carbon::parse($dataSubmit['date_from'])->format('Y-m-d') : '',
                    'endDate' => $dataSubmit['date_to'] ? Carbon::parse($dataSubmit['date_to'])->format('Y-m-d') : '',
                ];
            default:
                break;
        }
    }

    /**
     * Ajax get employees to report
     * @return [json]
     */
    public function ajaxGetEmployeesToReport()
    {
        $teamId = Input::get('teamId');
        $params = [
            'employees' => AssetView::getEmployeesByTeam($teamId),
        ];
        $html = view('asset::item.report.include.employees_list')->with($params)->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Ajax get assets to report
     * @return [json]
     */
    public function ajaxGetAssetsToReport()
    {
        $data = Input::all();
        $objAI = new AssetItem();
        $params = [
            'assetItems' => $objAI->getReportFluctuation($data),
        ];
        $html = view('asset::item.report.include.assets_list')->with($params)->render();
        return response()->json(['html' => $html]);
    }

    /** method confirm handover
     *
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function confirmHandover()
    {
        $response = [];
        $listIds = Input::get('listIds');
        $listState = Input::get('listState');
        $assetItems = AssetItem::whereIn('id', $listIds);

        if (!isset($listIds)) {
            $response['success'] = 0;
            $response['message'] = Lang::get('asset::message.Please choose item');
            return response()->json($response);
        }
        if ($assetItems->get()->isEmpty()) {
            $response['success'] = 0;
            $response['message'] = Lang::get('asset::message.Not found item');
            return response()->json($response);
        }
        $subject = null;
        $template = null;
        //send mail for IT
        $data = [
            'listIds' => $listIds,
            'employeeId' => Input::get('employeeId'),
        ];
        $assetItems = AssetItem::whereIn('id', $data['listIds']);
        $getFucByState = AssetItem::getFuncByState();
        DB::beginTransaction();
        try {
            // save report
            $reportAsest = ReportAsset::insertOrUpdate([
                'asset_ids' => $assetItems->lists('id')->toArray(),
                'type' => Input::get('type')
            ]);
            $data['href'] = route('asset::report.detail', ['id' => $reportAsest->id]);
            $listItem = AssetItem::whereIn('id', $listIds)->pluck('state', 'id')->toArray();
            switch (Input::get('type')) {
                case AssetConst::TYPE_HANDING_OVER:
                    if (!empty($stateNotTrue = array_diff($listState, $getFucByState['handover']))) {
                        foreach ($listItem as $key => $item) {
                            foreach ($stateNotTrue as $state) {
                                if ($state == $item) {
                                    $listIdFail[] = $key;
                                }
                            }
                        }
                        $response['success'] = 0;
                        $response['message'] = trans('asset::message.Asset already handover');
                        $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById($listIdFail)])->render();
                        return $response;
                    }
                    $assetItems->update([
                        'state' => AssetItem::STATE_SUGGEST_HANDOVER,
                        'change_date' => Carbon::now()->format('Y-m-d'),
                    ]);
                    $note = Lang::get('asset::view.Asset handover');
                    AssetHistory::saveMultiHistory($assetItems->get(), $data['employeeId'], $note, AssetItem::STATE_SUGGEST_HANDOVER, true);
                    break;
                case AssetConst::TYPE_BROKEN_NOTIFY:
                    if (!empty($stateNotTrue = array_diff($listState, $getFucByState['brokenNotify']))) {
                        foreach ($listItem as $key => $item) {
                            foreach ($stateNotTrue as $state) {
                                if ($state == $item) {
                                    $listIdFail[] = $key;
                                }
                            }
                        }
                        $response['success'] = 0;
                        $response['message'] = trans('asset::message.Asset already broken');
                        $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById($listIdFail)])->render();
                        return $response;
                    }
                    $assetItems->update([
                        'state' => AssetItem::STATE_BROKEN_NOTIFICATION,
                        'change_date' => Carbon::now()->format('Y-m-d'),
                    ]);
                    $note = Lang::get('asset::view.Asset broken notification');
                    AssetHistory::saveMultiHistory($assetItems->get(), $data['employeeId'], $note, AssetItem::STATE_BROKEN_NOTIFICATION, true);
                    break;
                case AssetConst::TYPE_LOST_NOTIFY:
                    if (!empty($stateNotTrue = array_diff($listState, $getFucByState['lostNotify']))) {
                        foreach ($listItem as $key => $item) {
                            foreach ($stateNotTrue as $state) {
                                if ($state == $item) {
                                    $listIdFail[] = $key;
                                }
                            }
                        }
                        $response['success'] = 0;
                        $response['message'] = trans('asset::message.Asset already lost');
                        $response['html'] = view('asset::item.include.asset_information')->with(['assetItem' => AssetItem::getAssetItemById($listIdFail)])->render();
                        return $response;
                    }
                    $assetItems->update([
                        'state' => AssetItem::STATE_LOST_NOTIFICATION,
                        'change_date' => Carbon::now()->format('Y-m-d'),
                    ]);
                    $note = Lang::get('asset::view.Asset lost notification');
                    AssetHistory::saveMultiHistory($assetItems->get(), $data['employeeId'], $note, AssetItem::STATE_LOST_NOTIFICATION, true);

                    break;
                default:
                    break;
            }
            $currEmp = Permission::getInstance()->getEmployee();
            $subject = Lang::get('asset::view.[Rikkeisoft] Has employee :employeeName :type send mail to IT department', [
                'employeeName' => View::getNickName($currEmp->email),
                'type' => AssetConst::getLabelByType(Input::get('type'))
            ]);
            $template = 'asset::item.mail.mail_confirm_handover';
            AssetConst::sendEmailToIT($subject, $template, $data, AssetConst::TYPE_HANDING_OVER);
            $response['success'] = 1;
            $response['message'] = Lang::get('asset::message.:type success. Please wait IT confirm', ['type' => AssetConst::getLabelByType(Input::get('type'))]);
            DB::commit();
            return response()->json($response);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($messages, 500);
            }
            return redirect()->back()->withErrors($messages);
        }
    }

    /**
     * Import excel
     * @return $this
     */
    public function importFile()
    {
        \PHPExcel_Settings::setZipClass(\PHPExcel_Settings::ZIPARCHIVE);
        $file = Input::file('file_upload');
        $exFile = $file->getClientOriginalExtension();

        if (!in_array($exFile, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
        }
        $excel = Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) {
        })->get();
        //check format file
        if (!AssetView::checkHeading(AssetItem::defineHeadingCompel(), $excel->getHeading())) {
            return redirect()
                ->back()
                ->withErrors(Lang::get('asset::message.Format not invalid'));
        }
        DB::beginTransaction();
        try {
            $check = AssetView::importFile($excel->toArray());
            if (!empty($check)) {
                return redirect()
                    ->back()
                    ->withErrors($check);
            }
            DB::commit();
            return redirect()
                ->back()
                ->with('messages', ['success' => [Lang::get('asset::message.Import success')]]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
    }

    public function assetITProfile()
    {
        $ids = explode('-', Input::get('ids'));
        $typeAction = Input::get('type');
        $assetItems = DB::table('manage_asset_items as assets')
            ->select('employees.name as user_name', 'assets.name', 'assets.code', 'category.name as category_name', 'assets.state', 'assets.change_date', 'assets.id', 'assets.reason')
            ->leftJoin('employees', 'employees.id', '=', 'assets.employee_id')
            ->join('manage_asset_categories as category', 'category.id', '=', 'assets.category_id')
            ->whereIn('assets.id', $ids)
            ->whereNull('assets.deleted_at')
            ->where('assets.state', '!=', AssetItem::getStateByAction($typeAction))
            ->get();

        return view('asset::item.profile.asset', ['assetItems' => $assetItems, 'type' => $typeAction, 'employeeId' => Input::get('employeeId')]);
    }

    public function searchAjax(Request $request)
    {
        if (!$request->ajax() || !$request->wantsJson()) {
            return redirect('/');
        }
        return AssetItem::searchAjax($request->get('q'), $request->except('q'));
    }

    public function searchAjaxByEmpId(Request $request)
    {
        if (!$request->ajax() || !$request->wantsJson()) {
            return redirect('/');
        }
        $id = Permission::getInstance()->getEmployee()->id;
        return AssetItem::searchAjax($request->get('q'), $request->except('q'), $id, AssetItem::STATE_USING);
    }

    public function searchAjaxByWarehouse(Request $request)
    {
        if (!$request->ajax() || !$request->wantsJson()) {
            return redirect('/');
        }
        $id = Permission::getInstance()->getEmployee()->id;
        return AssetItem::searchAjaxByWarehouse($request->get('q'), $request->except('q'), $id, $request->get('branch'));
    }

    /*
     * export asset
     */
    public function exportAsset(Request $request)
    {
        $columns = $request->get('columns');
        if (!$columns) {
            return redirect()
                ->back()
                ->withInput()
                ->with('messages', ['errors' => [trans('asset::export.chose_column_to_export')]]);
        }
        try {
            return [
                'colsHead' => ExportAsset::getColsHeading($request->get('columns')),
                'sheetsData' => [
                    'Members' => ExportAsset::getDataExport($request->all())
                ],
                'fileName' => 'List_Asset_' . Carbon::now()->now()->format('Y_m_d')
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return response()->json(trans('asset::export.An error occurred, please try again later!'), 500);
        }
    }

    /**
     * save note asset of employee
     */
    public function saveNote(Request $request)
    {
        $response = array();
        $response['status'] = true;
        $response['message'] = trans('asset::message.Save data success');

        if (!request()->ajax()) {
            return redirect('/');
        }
        $objAsset = AssetItem::find($request->assetId);
        if (!$objAsset) {
            $response['status'] = false;
            $response['message'] = trans('asset::message.Not found item');
            return response()->json($response);
        }
        $objAsset->update([
            'note_of_emp' => $request->note,
        ]);
        return response()->json($response);
    }

    /**
     * View detail request asset
     * @param [int] $requestId
     * @return [view]
     */
    public function viewRequest($requestId)
    {
        Breadcrumb::add('Asset', route('asset::profile.view-personal-asset'));
        Breadcrumb::add('Asset request list', route('asset::profile.my_request_asset'));
        Breadcrumb::add('Asset request detail');
        return RequestView::viewRequest($requestId);
    }


    /*
     * list my requests
     */
    public function myRequests(Request $request)
    {
        Breadcrumb::add('Asset', route('asset::profile.view-personal-asset'));
        Breadcrumb::add('Asset request list');
        Menu::setActive('Profile');

        $collectionModel = RequestAsset::getMyRequests($request->all());
        $listStatuses = RequestAsset::labelStates();
        $tblRq = RequestAsset::getTableName();
        $teamList = TeamList::toOption(null, false, false);
        $type = $request->get('type');
        $status = $request->get('status');
        if (isset($listStatuses[$status])) {
            $txtStatus = $listStatuses[$status];
        } else {
            $txtStatus = trans('asset::view.All');
        }
        switch ($type) {
            case 'approver':
                $pageTitle = trans('asset::view.Approve request asset');
                break;
            case 'reviewer':
                $pageTitle = trans('asset::view.Review request asset');
                break;
            case 'creator':
            default:
                $pageTitle = trans('asset::view.My request asset');
                break;
        }
        $pageTitle .= ': ' . $txtStatus;
        return view('asset::profile.request', compact('collectionModel', 'listStatuses', 'tblRq', 'teamList', 'type', 'pageTitle', 'status'));
    }

    public function assetToWarehouse()
    {
        if (!Permission::getInstance()->isAllow('asset::asset.asset_to_warehouse')) {
            View::viewErrorPermission();
        }
        $collectionModel = RequestAssetItemsWarehouse::getAll();
        $viewData = [
            'collectionModel' => $collectionModel
        ];

        return view('asset::asset_warehouse.index')->with($viewData);
    }

    public function showAssetToWarehouse(Request $request){
        if ($id = $request->get('id')) {
            $assets = RequestAssetItemsWarehouse::findByEmpId($id);
            $userCurrent = Permission::getInstance()->getEmployee();
            $empBranch = AssetWarehouse::where('manager_id', $userCurrent->id)->get()->pluck('branch')->toArray();
            $empBranch = array_unique($empBranch);
            if (!$assets) {
                return response()->json([
                    'success' => 0
                ]); 
            }
            
            $viewData = [
                'assets' => $assets,
                'empId' => $id,
                'empBranch' => $empBranch,
            ];    
            return response()->json([
                'assets' => $assets,
                'html' => view('asset::asset_warehouse.include.asset_detail')->with($viewData)->render(),
            ]);    
        } else {
            return response()->json([
                'success' => 0
            ]); 
        }
    }

    public function saveAssetToWarehouse(Request $request)
    {
        if (!request()->ajax()) {
            return redirect('/');
        }
        DB::beginTransaction();
        try {
            $data = $request->all();
            $arrIds = $request->get('arr_checkbox');
            $empId = $request->get('empId');
            $idsCheck = [];
            foreach ($arrIds as $id) {
                $req = RequestAssetItemsWarehouse::find($id);
                if ($req) {
                    $idsCheck[] = $req->request_id;
                    $qty = $data['qty'.$id];
                    $assetIds = $data['asset_id'.$id];
                    if ($qty >= $req->unallocate) {
                        $dataUpdate = [
                            'quantity' => $req->quantity + ($qty - $req->unallocate),
                            'allocate' => $req->allocate + $qty,
                            'unallocate' => 0,
                            'status' => RequestAssetItemsWarehouse::STATUS_ALLOCATE,
                        ];
                    } else {
                        $dataUpdate = [
                            'allocate' => $req->allocate + $qty,
                            'unallocate' => $req->unallocate - $qty
                        ];
                    }
                    $req->update($dataUpdate);
                    foreach ($assetIds as $asset) {
                        $assItem = AssetItem::find($asset);
                        if ($assItem) {
                            $assItem->employee_id = $req->employee_id;
                            $assItem->state = AssetItem::STATE_USING;
                            $assItem->save();
                        }
                    }
                }
            }
            if (count($idsCheck)) {
                $idsCheck = array_unique($idsCheck);
                foreach ($idsCheck as $valueId) {
                    $reqEnough = RequestAssetItemsWarehouse::where('request_id', $valueId)->where('status', RequestAssetItemsWarehouse::STATUS_UNALLOCATE)->first();
                    if (!$reqEnough) {
                        RequestAsset::where('id', $valueId)->update([
                            'state' => RequestAsset::STATE_ENOUGH
                        ]);
                    }
                }
            }
            DB::commit();
            $requests = RequestAssetItemsWarehouse::whereIn('id', $arrIds)->get();
            return response()->json([
                'success' => 1,
                'className' => 'modal-success',
                'message' => trans('project::me.Submited successful'),
                'requests' => $requests,
                'empId' => $empId,
            ]);
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
            return response()->json([
                'success' => 0,
                'className' => 'modal-danger',
                'message' => trans('project::me.Save error, please try again laster')
            ]);
        }
    }

    public function assetReturn(Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        if (!Permission::getInstance()->isAllow('asset::asset.asset-return')) {
            View::viewErrorPermission();
        }
        $dataItem = Input::get('item');
        $assetItemIds = Input::get('asset_id');
        $assetItems = AssetItem::whereIn('id', $assetItemIds);
        if ($assetItems->get()->isEmpty()) {
            return redirect()->back()->withErrors(Lang::get('asset::message.Not found item'));
        }
        $rules = [
            'change_date' => 'required',
            'reason' => 'required',
        ];
        $messages = [
            'change_date.required' => Lang::get('asset::message.The field is required'),
            'reason.required' => Lang::get('asset::message.The field is required'),
        ];
        $validator = Validator::make($dataItem, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        DB::beginTransaction();
        try {
            $assetItems->update([
                'change_date' => Carbon::createFromFormat('d-m-Y', $dataItem['change_date'])->format('Y-m-d'),
                'state' => AssetItem::STATE_RETURN_CUSTOMER,
                'reason' => $dataItem['reason'],
            ]);
            $note = 'asset::view.Has returned';
            AssetHistory::saveMultiHistory($assetItems->get(), $userCurrent->id, $note, AssetHistory::STATE_RETURN_CUSTOMER);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
        $messages = [
            'success' => [
                Lang::get('asset::message.Save data success'),
            ]
        ];
        return redirect()->route('asset::asset.index')->with('messages', $messages);
    }

    // ========== start import file configure case ==========
    /**
     * Import excel
     * @return $this
     */
    public function importFileConfigure()
    {
        $file = Input::file('file_upload');
        $type = Input::get('type');
        $exFile = $file->getClientOriginalExtension();

        if (!in_array($exFile, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
        }
        $codeAssetsUpdate = ['HNCA'];

        $dataUpdate = [];
        $dataError = [];
        $dataKeyOrigin = [];
        $sheetNull = [];
        if ($type == 1) {
            $excel = Excel::selectSheetsByIndex(0, 1, 2)->load($file->getRealPath(), function ($reader) {
            })->get()->toArray();

            if (!empty($excel[0])) {
                $dataSheet1 = $this->processingSheet1($excel[0]);
                if (!$dataSheet1['data']) {
                    if (isset($dataSheet1['error'])) {
                        $dataError[] = 'Sheet1 ' . $dataSheet1['error'];
                    } else {
                        $sheetNull[] = 'Sheet 1 không có tài sản hoặc tài sản không tồn tại';
                    }
                } else {
                    $dataUpdate = array_merge($dataUpdate, $dataSheet1['data']);
                    $dataKeyOrigin = array_merge($dataKeyOrigin, $dataSheet1['keyOrigin']);
                }
            }

            


        } else {
            $excel = Excel::selectSheetsByIndex(0, 1)->load($file->getRealPath(), function ($reader) {
            })->get()->toArray();
            if (count($excel) > 2) {
                return redirect()->back()->withErrors(['Thiếu sheet2']);
            }
            if (!empty($excel[0])) {
                $dataSheet1 = $this->processingOnsiteSheet1($excel[0], $codeAssetsUpdate);
                if (!$dataSheet1['data']) {
                    if (isset($dataSheet1['error'])) {
                        $dataError[] = 'Sheet1 ' . $dataSheet1['error'];
                    } else {
                        $sheetNull[] = 'Sheet1 trống hoặc tài sản không tồn tại';
                    }
                } else {
                    $updateDataSheet = $dataSheet1['data'];
                }
                if (isset($dataSheet1['emailTwoAsset'])) {
                    $emailTwoAsset = $dataSheet1['emailTwoAsset'];
                }
                if (isset($dataSheet1['emailNotImport'])) {
                    $emailNotImport = $dataSheet1['emailNotImport'];
                }
            }

        }
        if ($dataError) {
            return redirect()->back()->withErrors($dataError);
        }
        DB::beginTransaction();
        try {
            $objAssets = new AssetItem();
            $codeAssets = $objAssets->getAssetByCode($codeAssetsUpdate)->lists('code')->toArray();
            if ($dataUpdate) {
                $arrKeyUpdate = array_keys($dataUpdate);
                $codeNotExits = [];
                $updateData = [];
                foreach ($arrKeyUpdate as $key) {
                    if (!in_array($key, $codeAssets)) {
                        $codeNotExits[] = $dataKeyOrigin[$key];
                    } else {
                        $updateData[$key] = $dataUpdate[$key];
                    }
                }
            }
            if (isset($updateDataSheet)) {
                if (isset($updateData)) {
                    $updateData = array_merge($updateData, $updateDataSheet);
                } else {
                    $updateData = $updateDataSheet;
                }
            }
            $messagesSuccess = [Lang::get('asset::message.Import success')];
            if (!empty($codeNotExits)) {
                $messagesSuccess[] = 'Các mã tài sản sau chưa phù hợp: ' . implode(', ', $codeNotExits);
            }
            if (!empty($emailTwoAsset)) {
                $messagesSuccess[] ='Tài sản không import vì email có nhiều hơn một case: ' . implode(', ', $emailTwoAsset);
            }
            if (!empty($emailNotImport)) {
                $messagesSuccess[] = 'Các email không có tài sản hoặc email chưa đúng: ' . implode(', ', $emailNotImport);
            }
            if ($sheetNull) {
                $messagesSuccess = array_merge($sheetNull, $messagesSuccess);
            }
            if (isset($updateData) && $updateData) {
                $objAssets->updateConfigure($updateData, $codeAssetsUpdate);
            }
            DB::commit();
            return redirect()->back()->with('messages', ['success' => $messagesSuccess]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
    }

    /**
     * xử lý sheet1 of file thống kê case
     * @param $dataExcel
     * @return array
     */
    public function processingSheet1($dataExcel)
    {
        $data = [];
        $keyOrigin = [];
        $arrkey = array_keys($dataExcel[0]);
        $dataKey = [];
        foreach ($arrkey as $item) {
            if (strpos($item, '_01') !== false) {
                $dataKey['01'][] = $item;
            }
            if (strpos($item, '_02') !== false) {
                $dataKey['02'][] = $item;
            }
            if (strpos($item, '_03') !== false) {
                $dataKey['03'][] = $item;
            }
            if (strpos($item, '_04') !== false) {
                $dataKey['04'][] = $item;
            }
        }
        if (!$dataKey) {
            return [
                'data' => $data,
                'error' => 'không đúng định dạng',
            ];
        }
        foreach ($dataExcel as $item) {
            if (!empty($item[$dataKey['01'][0]])) {
                $result = $this->getTextConfigure($item, $dataKey['01'][0], $dataKey['01']);
                $data[$result[0]] = $result[1];
                $keyOrigin[$result[0]] = $result[2];
            }
            if (!empty($item[$dataKey['02'][0]])) {
                $result = $this->getTextConfigure($item, $dataKey['02'][0], $dataKey['02']);
                $data[$result[0]] = $result[1];
                $keyOrigin[$result[0]] = $result[2];
            }
            if (!empty($item[$dataKey['03'][0]])) {
                $result = $this->getTextConfigure($item, $dataKey['03'][0], $dataKey['03']);
                $data[$result[0]] = $result[1];
                $keyOrigin[$result[0]] = $result[2];
            }
            if (!empty($item[$dataKey['04'][0]])) {
                $result = $this->getTextConfigure($item, $dataKey['04'][0], $dataKey['04']);
                $data[$result[0]] = $result[1];
                $keyOrigin[$result[0]] = $result[2];
            }
        }
        return [
            'data' => $data,
            'keyOrigin' => $keyOrigin,
        ];
    }

    /**
     * xử lý sheet2 of file thống kê case
     * @param $dataExcel
     * @return array
     */
    public function processingSheet2($dataExcel)
    {
        $data = [];
        $keyOrigin = [];
        $dataKey = [
            'ma_case',
            'mainboard',
            'ram',
            'o_cung',
            'card_man_hinh',
        ];
        $arrkey = array_keys($dataExcel[0]);
        foreach ($dataKey as $item) {
            if (!in_array($item, $arrkey)) {
                return [
                    'data' => $data,
                    'error' => 'không đúng định dạng',
                ];
            }
        }
        foreach ($dataExcel as $item) {
            if (!empty($item['ma_case'])) {
                $result = $this->getTextConfigure($item, 'ma_case', $dataKey);
                $data[$result[0]] = $result[1];
                $keyOrigin[$result[0]] = $result[2];
            }
        }

        return [
            'data' => $data,
            'keyOrigin' => $keyOrigin,
        ];
    }

    /**
     * xử lý sheet3 of file thống kê case
     * @param $dataExcel
     * @return array
     */
    public function processingSheet3($dataExcel)
    {
        return $this->processingSheet2($dataExcel);
    }

    /**
     * get text update configure for sheet1,sheet2,sheet3 of file thống kê case
     * @param $item
     * @param $case
     * @param $data
     * @return array
     */
    public function getTextConfigure($item, $case, $data)
    {
        $item[$case] = trim(trim($item[$case]), ';');
        $key = $this->getCodeCase($item[$case]);

        $text = '';
        unset($data[0]);
        foreach ($data as $keyData) {
            if (!isset($item[$keyData])) {
                continue;
            }
            $item[$keyData] = str_replace("\n", ', ', $item[$keyData]);

            if ((strpos($keyData, 'mainboard') !== false)) {
                $text = $text . '|Mainboard: ' . $item[$keyData];
            }
            if ((strpos($keyData, 'cpu') !== false)) {
                $text = $text . '|CPU: ' . $item[$keyData];
            }
            if ((strpos($keyData, 'ram') !== false)) {
                $text = $text . '|RAM: ' . $item[$keyData];
            }
            if ((strpos($keyData, 'o_cung') !== false)) {
                $text = $text . '|Hard Drive: ' . $item[$keyData];
            }
            if ((strpos($keyData, 'card_man_hinh') !== false)) {
                $text = $text . '|Card: ' . $item[$keyData];
            }
        }
        return [
            $key,
            trim($text, '|'),
            $item[$case]
        ];
    }

    /**
     * xử lý mã code ở file import cho phù hơp
     * @param $code
     * @return string
     */
    public function getCodeCase($code)
    {
        if ((strpos($code, 'HNCA') !== false)) {
            $key = $code;
        } elseif ((strpos($code, 'CA') !== false)) {
            $key = 'HN' . $code;
        } elseif (is_numeric($code)) {
            $key = 'HNCA ' . (int)$code;
        } else {
            $key = $code;
        }
        return $key;
    }

    /**
     * xử lý sheet1 of file khai báo onsite
     * @param $dataExcel
     * @return array
     */
    public function processingOnsiteSheet1($dataExcel, $codeAsset)
    {
        $data = [];
        $dataKey = [
            'email_rikkeisoft',
            'mainboard',
            'cpu',
            'ram',
            'o_cung',
            'card_man_hinh_neu_co',
        ];
        $arrkey = array_keys($dataExcel[0]);
        foreach ($dataKey as $key) {
            if(!in_array($key, $arrkey, true)) {
                return [
                    'data' => [],
                    'error' => 'Không đúng định dạng',
                ];
            }
        }
        $arrExcel = $this->getTextOnsite($dataExcel, 'email_rikkeisoft');
        $arrEmail = array_keys($arrExcel);
        $objAsset = new AssetItem();
        $assets = $objAsset->getCaseHNAssetByEmailEmployee($arrEmail, $codeAsset);

        $emailTwoAsset = [];
        $emailAsset = [];
        foreach ($assets as $item) {
            if (!in_array($item->employee_email, $arrEmail)) {
                $emailTwoAsset[] = $item->employee_email;
                if (isset($emailAsset[$item->employee_email])) {
                    foreach ($emailAsset[$item->employee_email] as $value) {
                       if (isset($data[$value])) {
                            unset($data[$value]);
                        }
                    }
                }
            } else {
                $data[$item->asset_code] = $arrExcel[$item->employee_email];
                $emailAsset[$item->employee_email][] = $item->asset_code;
                unset($arrEmail[array_search($item->employee_email, $arrEmail)]);
            }
        }
        return [
            'emailTwoAsset' => array_unique($emailTwoAsset),
            'emailNotImport' => $arrEmail,
            'data' => $data,
        ];
    }


    /**
     * get text update configure of file khai báo onsite for sheet1, sheet2
     * @param $dataExcel
     * @param $key
     * @param bool $isKeyCode
     * @return array
     */
    public function getTextOnsite($dataExcel, $key, $isKeyCode = false)
    {
        $arrExcel = [];

        foreach ($dataExcel as $item) {
            $text = '';
            if (empty($item[$key])) {
                $item[$key] = 'Dòng có ' . $key . ' để trống';
            }
            $keyResult = $item[$key];
            if (!empty($item['mainboard'])) {
                $text = $text . '|Mainboard: ' . $item['mainboard'];
            }
            if (!empty($item['cpu'])) {
                $text = $text . '|CPU: ' . $item['cpu'];
            }
            if (!empty($item['ram'])) {
                $text = $text . '|RAM: ' . str_replace("\n", ', ', $item['ram']);
            }
            if (!empty($item['o_cung'])) {
                $text = $text . '|Hard Drive: ' . str_replace("\n", ', ', $item['o_cung']);
            }
            if (!empty($item['card_man_hinh_neu_co'])) {
                $text = $text . '|Card: ' . str_replace("\n", ', ', $item['card_man_hinh_neu_co']);
            }
            if ($isKeyCode) {
                $keyResult = $this->getCodeCase($keyResult);
            }
            $arrExcel[$keyResult] = trim($text, '|');
        }

        return $arrExcel;
    }

    /**
     * xử lý sheet2 of file khai báo onsite
     *
     * @param $dataExcel
     * @return array
     */
    public function processingOnsiteSheet2($dataExcel)
    {
        $dataKey = [
            'ma_case',
            'mainboard',
            'cpu',
            'ram',
            'o_cung',
            'card_man_hinh_neu_co',
        ];
        $arrkey = array_keys($dataExcel[0]);
        foreach ($dataKey as $key) {
            if(!in_array($key, $arrkey, true)) {
                return [
                    'data' => [],
                    'error' => 'Không đúng định dạng',
                ];
            }
        }

        $data = $this->getTextOnsite($dataExcel, 'ma_case', true);
        return [
            'data' => $data,
        ];
    }
    // ========== end import file configure case ==========

    /**
     * Import excel
     * @return $this
     */
    public function importSerialNumber()
    {
        $file = Input::file('file_upload_serial');
        if (!$file) {
            return redirect()->back()->with('messages', ['errors' => ['Chưa chọn file']]);
        }

        $exFile = $file->getClientOriginalExtension();

        if (!in_array($exFile, ['xlsx', 'xls'])) {
            return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
        }
        
        $excel = Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) {
        })->get();
        $heading = $excel->getHeading();
        $data = $excel->toArray();
        if (!in_array('ma_tai_san', $heading) || !in_array('serial', $heading)) {
            return redirect()->back()->withErrors(Lang::get('asset::message.File not invalid'));
        }
        DB::beginTransaction();
        try {
            $arrayCode = [];
            $dataCodeAssetUpdate = [];
            $dataSerialAsset = [];
            $dataErrorNotSerial = [];
            $dataErrorSerialDuplicateFile = [];
            foreach ($data as $key => $item) {
                $codeAsset = trim($item['ma_tai_san']);
                $serialAsset = trim($item['serial']);
                if ($codeAsset) {
                    if ($serialAsset) {
                        $dataCodeAssetUpdate[$codeAsset] = $serialAsset;
                        //check duplicate serial file import
                        if (isset($dataSerialAsset[$serialAsset])) {
                            $dataErrorSerialDuplicateFile[] = $codeAsset;
                            $dataErrorSerialDuplicateFile[] = $dataSerialAsset[$serialAsset];
                            unset($dataCodeAssetUpdate[$codeAsset]);
                        } else {
                            $dataSerialAsset[$serialAsset] = $codeAsset;
                        }
                    } else {
                        $dataErrorNotSerial[] = $codeAsset;
                    }
                }
            }
            $message  = [Lang::get('asset::message.Import success')];
            if ($dataCodeAssetUpdate) {
                $serialDuplicateDB = [];
                $arrayCode = array_keys($dataCodeAssetUpdate);
                $arraySrial =  array_values($dataCodeAssetUpdate);
                $serialIsset = 0;
                $codesAssetItem = AssetItem::whereIn('code', $arrayCode)->lists('code')->toArray();
                if (array_diff($arrayCode, $codesAssetItem)) {
                    $message[] = 'Các mã tài sản sau không tồn tại: ' . implode(',', array_diff($arrayCode, $codesAssetItem));
                }
                
                $assetitemsBySrial = AssetItem::whereIn('serial', $arraySrial)->select('code', 'serial')->get();
                if (count($assetitemsBySrial)) {
                    foreach($assetitemsBySrial as $item) {
                        foreach($dataCodeAssetUpdate as $codeAsset => $serial) {
                            if ($item->serial == $serial && $item->code == $codeAsset) {
                                $serialIsset = $serialIsset + 1;
                                continue;
                            }
                            if ($item->serial == $serial && $item->code != $codeAsset) {
                                $serialDuplicateDB[] = $codeAsset;
                                unset($dataCodeAssetUpdate[$codeAsset]);
                            }
                        }
                    }
                    if ($serialIsset && $serialIsset == count($dataCodeAssetUpdate)) {
                        $message[] = 'Tất cả tài sản đã tồn tại trong hệ thống';
                    }

                    if ($serialDuplicateDB) {
                        $message[] = 'Các mã tài sản sau không import do trùng serial trong database: ' . implode(',', $serialDuplicateDB);
                    }
                }
                if ($serialIsset == 0 || ($serialIsset && $serialIsset != count($dataCodeAssetUpdate))) {
                    $userCurrent = Permission::getInstance()->getEmployee();
                    $updateAssetHistory = new AssetHistory();
                    $updateAssetHistory->updateHistorySerial($dataCodeAssetUpdate, $userCurrent);
                }
            }
            if ($dataErrorSerialDuplicateFile) {
                $message[] = 'Các mã tài sản sau không import do trùng serial trong file: ' . implode(',', array_unique($dataErrorSerialDuplicateFile));
            }
            if ($dataErrorNotSerial) {
                $message[] = 'Các mã tài sản sau không có serial:' . implode(',', array_unique($dataErrorNotSerial));
            }
            $objAssets = new AssetItem();
            $objAssets->updateSerialNumber($dataCodeAssetUpdate);
            DB::commit();
            return redirect()->back()->with('messages', ['success' => $message]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('asset::message.System error')
                ]
            ];
            return redirect()->back()->withErrors($messages);
        }
    }
}
