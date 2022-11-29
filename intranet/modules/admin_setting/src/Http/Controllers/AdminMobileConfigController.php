<?php

namespace Rikkei\AdminSetting\Http\Controllers;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Rikkei\AdminSetting\Model\MobileConfig;
use Rikkei\AdminSetting\Model\MobileConfigUser;
use Rikkei\AdminSetting\View\ConfigPermission;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Exception;
use Rikkei\Core\View\View;
use Rikkei\HomeMessage\View\FileUploader;
use Storage;
use Intervention\Image\ImageManagerStatic as Image;


class AdminMobileConfigController extends Controller
{
    protected $mobileConfig;

    public function __construct(MobileConfig $mobileConfig, ConfigPermission $permission)
    {
        if (!$permission->isAllow()) {
            View::viewErrorPermission();
        }
        $this->mobileConfig = $mobileConfig;
    }

    /**
     * Show admin list of division
     * @return [view]
     */
    public function index()
    {
        Breadcrumb::add(trans('admin_setting::view.mobile'));
        Breadcrumb::add(trans('admin_setting::view.config'));
        $confessionEmployees = MobileConfigUser::join('employees', 'employees.id', '=', 'mobile_config_users.employee_id')
            ->where('mobile_config_id', MobileConfig::CONFESSION_ID)->select('employees.id', 'employees.name', 'employees.email'
                , DB::raw("CONCAT(employees.name,'(',employees.email,')') as full_name"))->get();
        $marketEmployees = MobileConfigUser::join('employees', 'employees.id', '=', 'mobile_config_users.employee_id')
            ->where('mobile_config_id', MobileConfig::MARKET_ID)->select('employees.id', 'employees.name', 'employees.email', DB::raw("CONCAT(employees.name,'(',employees.email,')') as full_name"))->get();
        $giftEmployees = MobileConfigUser::join('employees', 'employees.id', '=', 'mobile_config_users.employee_id')
            ->where('mobile_config_id', MobileConfig::GIFT_ID)->select('employees.id', 'employees.name', 'employees.email', DB::raw("CONCAT(employees.name,'(',employees.email,')') as full_name"))->get();;
        $proposedEmployees = MobileConfigUser::join('employees', 'employees.id', '=', 'mobile_config_users.employee_id')
            ->where('mobile_config_id', MobileConfig::PROPOSED_ID)->select('employees.id', 'employees.name', 'employees.email', DB::raw("CONCAT(employees.name,'(',employees.email,')') as full_name"))->get();;
        $collectionModel = $this->mobileConfig->first();
        return view('admin_setting::config', [
            'confessionEmployees' => $confessionEmployees,
            'marketEmployees' => $marketEmployees,
            'giftEmployees' => $giftEmployees,
            'proposedEmployees' => $proposedEmployees,
            'collectionModel' => $collectionModel
        ]);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse [view]
     * @throws Exception
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        if ($request->hasFile('avatar_url')) {
            $image      = $request->file('avatar_url');
            $fileName   = time() . '.' . $image->getClientOriginalExtension();

            $img = Image::make($image->getRealPath());
            $img->resize(120, 120, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->stream();
            Storage::disk('public_asset_path')->put($fileName, $img);
            $this->mobileConfig->where('id', '>=', 1)->delete();
            $this->mobileConfig->create(['avatar_url' => $fileName]);
        }
        $mobileConfigUserData = [];
        if ($request->confessionEmployees) {
            foreach ($request->confessionEmployees as $key => $confessionEmployee) {
                $mobileConfigUserData[$key] = [
                    'mobile_config_id' => MobileConfig::CONFESSION_ID,
                    'employee_id' => $confessionEmployee
                ];
            }
            MobileConfigUser::where('mobile_config_id', MobileConfig::CONFESSION_ID)->delete();
            MobileConfigUser::insert($mobileConfigUserData);
        }
        if ($request->marketEmployees) {
            $mobileConfigMarketUserData = [];
            foreach ($request->marketEmployees as $key => $confessionEmployee) {
                $mobileConfigMarketUserData[$key] = [
                    'mobile_config_id' => MobileConfig::MARKET_ID,
                    'employee_id' => $confessionEmployee
                ];
            }
            MobileConfigUser::where('mobile_config_id', MobileConfig::MARKET_ID)->delete();
            MobileConfigUser::insert($mobileConfigMarketUserData);
        }

        if ($request->giftEmployees) {
            $mobileConfigGiftUserData = [];
            foreach ($request->giftEmployees as $key => $giftEmployee) {
                $mobileConfigGiftUserData[$key] = [
                    'mobile_config_id' => MobileConfig::GIFT_ID,
                    'employee_id' => $giftEmployee
                ];
            }
            MobileConfigUser::where('mobile_config_id', MobileConfig::GIFT_ID)->delete();
            MobileConfigUser::insert($mobileConfigGiftUserData);
        }

        if ($request->proposedEmployees) {
            $mobileConfigProposedUserData = [];
            foreach ($request->proposedEmployees as $key => $giftEmployee) {
                $mobileConfigProposedUserData[$key] = [
                    'mobile_config_id' => MobileConfig::PROPOSED_ID,
                    'employee_id' => $giftEmployee
                ];
            }
            MobileConfigUser::where('mobile_config_id', MobileConfig::PROPOSED_ID)->delete();
            MobileConfigUser::insert($mobileConfigProposedUserData);
        }
        \DB::commit();
        return redirect()->route('admin::mobile.config.index')->with('messages', ['success' => ['Thêm mới thành công']]);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse [view]
     * @throws Exception
     */
    public function update($id, Request $request)
    {
        DB::beginTransaction();
        if ($request->hasFile('avatar_url')) {
            $image = new FileUploader('avatar_url', '/admin/');
            $image = $image->upload();
            $data = [
                'avatar_url' => $image['files'][0]['file'],
            ];
            $this->mobileConfig->find($id)->update($data);
        }
        MobileConfigUser::where('mobile_config_id', $id)->delete();
        $mobileConfigUserData = [];
        foreach ($request->employees as $key => $employee) {
            $mobileConfigUserData[$key] = [
                'mobile_config_id' => $id,
                'employee_id' => $employee
            ];
        }
        MobileConfigUser::insert($mobileConfigUserData);
        \DB::commit();
        return redirect()->route('admin::mobile.config.index')->with('messages', ['success' => ['Cập nhật thành công']]);
    }

}
