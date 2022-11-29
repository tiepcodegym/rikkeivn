<?php

namespace Rikkei\Team\Http\Controllers;

use Chumper\Zipper\Zipper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Lang;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Certificate;
use Rikkei\Team\Model\EmployeeCertificate;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;

class CertificateController extends Controller
{
    public function index()
    {
        Breadcrumb::add('Admin');
        Breadcrumb::add('Report');
        Menu::setActive('admin', 'admin');
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = Certificate::ROUTER_REPORT;
        $id = 0;
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            if (Permission::getInstance()->isScopeTeam(null, $route)) {
                $teamIdsAvailable = (array) Permission::getInstance()->getTeams();
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResonsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if ($teamIdsResonsibleByPqa) {
                foreach ($teamIdsResonsibleByPqa as $teamId) {
                    $teamIdsAvailable[] = $teamId->team_id;
                }
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            if (!$id) {
                $id = end($teamIdsAvailable);
            }
            //get team and all child avaliable
            $teamIdsChildAvailable = [];
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamPathTree = Team::getTeamPath();
                foreach ($teamIdsAvailable as $teamId) {
                    if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                        if (isset($teamPathTree[$teamId]['child'])) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]['child']);
                            $teamIdsChildAvailable = array_merge($teamIdsChildAvailable, $teamPathTree[$teamId]['child']);
                            unset($teamPathTree[$teamId]['child']);
                        }
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }

            if (!in_array($id, $teamIdsAvailable)) {
                View::viewErrorPermission();
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::find($id);
            }
        }
        $certificates = Certificate::whereNull('deleted_at')->orderBy('name', 'asc')->get(['id', 'name', 'type'])->toArray();
        $cookieFilter = json_decode(CookieCore::get('dataFilterCer'));
        $statusCertificate = Certificate::getOptionStatus();
        $data = [
                'teamIdsAvailable'=>$teamIdsAvailable,
                'teamTreeAvailable'=>$teamTreeAvailable,
                'teamIdsAvailable'=>$teamIdsAvailable,
                'teamIdCurrent'=>$id,
                'listCertificate'=>Certificate::labelAllType(),
                'certificates' => $certificates,
                'cookieFilter' => $cookieFilter,
                'statusCertificate' => $statusCertificate,
            ];
    	return view('team::certificate.index', $data);
    }

    public function createCookeiFilter(Request $request)
    {
        $data = $request->all();
        CookieCore::set('dataFilterCer', json_encode($data), 7);
    }
    public function forgetCookieFilter(Request $request)
    {
        CookieCore::forget('dataFilterCer');
    }

    /**
     * export file excel or zip img
     * @param array $ids list id of certificate
     */
    public function export(Request $request)
    {
        $isIMG = $request->get('isImg');
        if ($request->team_ids === 'null') {
            $listId = ['0'];
        } else {
           $listId = explode(",", $request->team_ids);
        }
        $listEmployeeCertificates = EmployeeCertificate::listEmployeeCertificates($request);
        $teamIdsAvailable = $listId;
            $checkIsAll = false;
            foreach ($teamIdsAvailable as $id) {
               if ($id === '0') {
                $checkIsAll = true;
                break;
               }
            }
            if (!$checkIsAll) {
                $teamIds = [];
                $temIdResult = [];
                foreach ($teamIdsAvailable as $teamId) {
                    Team::getTeamChildRecursivePublic($teamIds, $teamId);
                    $temIdResult = array_merge($temIdResult, [$teamId]);
                    if (isset($teamIds['child'])) {
                        $temIdResult = array_merge($temIdResult, $teamIds['child']);
                    }
                }
                $temIdResult = array_unique($temIdResult);
                $listEmployeeCertificates = $listEmployeeCertificates->whereIn('teams.id', $temIdResult);
            }
            if ($isIMG && $isIMG == true) {
                $listEmployeeCertificates->join('employee_certies_image', 'employee_certies_image.employee_certies_id', '=', 'employee_certies.id')
                    ->addSelect('image');
            }
            $listEmployeeCertificates = $listEmployeeCertificates->get()->toArray();
            $listTeam = [];
            foreach ($listEmployeeCertificates as $item) {
                if (isset($item['status'])) {
                    $item['status'] = Certificate::getOptionStatus()[$item['status']];
                }
                if (!isset($listTeam[$item['teams_id']])) {
                    $listTeam[$item['teams_id']] = [];
                }
                if (isset($listTeam[$item['teams_id']])) {
                    if (!isset($listTeam[$item['teams_id']])) {
                        $listTeam[$item['teams_id']] = [];
                    }
                    if (isset($listTeam[$item['teams_id']])) {
                        array_push($listTeam[$item['teams_id']], $item);
                    }
                }
            }

            // export image
            if ($isIMG) {
                $date = date('ymh');
                if (!file_exists('storage/certificate/export_image/' . $date)) {
                    mkdir('storage/certificate/export_image/' . $date, 0777, true);
                }
                $zipper = new Zipper();
                foreach ($listEmployeeCertificates as $key => $employee) {
                    if (storage::disk('public')->exists(trim($employee['image'], '/'))) {
                        $fileName = trim(str_slug($employee['employee_code']) . '_' . str_slug($employee['employees_name']) . '_' . str_slug($employee['name']) . '_' . md5($employee['image']), '_');
                        $ext = File::extension($employee['image']);
                        $img = Storage::disk('public')->get(trim($employee['image'], '/'));
                        if ($ext == 'pdf') {
                            File::put(
                                'storage/certificate/export_image/' . $date . '/' . $fileName . '.' . $ext,
                                file_get_contents(storage_path("app/public{$employee['image']}"))
                            );
                        } else {
                            @Image::make($img)->save('storage/certificate/export_image/' . $date . '/' . $fileName . '.' . $ext);
                        }
                    }
                }
                if (Storage::disk('public')->exists('/resource/certificate_image.zip')) {
                    //Nếu có thì xóa file cũ đi để tạo file zip mới
                    File::delete(storage_path("app/public/resource/certificate_image.zip"));
                }
                if (file_exists('storage/certificate/export_image/' . $date)) {
                    $zipper->make(storage_path('app/public/resource/certificate_image.zip'))
                        ->add(('storage/certificate/export_image/' . $date))
                        ->close();
                    File::deleteDirectory("storage/certificate/export_image/" . $date);

                    if (Storage::disk('public')->exists('/resource/certificate_image.zip')) {
                        return response()->download(storage_path("app/public/resource/certificate_image.zip"));
                    }
                }

                return redirect()->back()->withErrors(trans('team::view.no img to download'))->withInput();
            }

             Excel::create('DanhSachChungChi', function ($excel) use ($listTeam) {
                 $excel->sheet('Danh sách chứng chỉ', function ($sheet) use ($listTeam) {
                     $sheet->setFontFamily('Arial');
                     $sheet->setFontSize(10);
                     $sheet->loadView('team::certificate.export', ['data' => $listTeam]);
                     $sheet->row(1, function ($row) {
                         $row->setAlignment('center');
                         $row->setBackground('#EBEBE0');
                         $row->setBorder('thin', 'thin', 'thin', 'thin');
                         $row->setFont([
                             'size' => '11',
                             'bold' => true
                         ]);
                     });
                     $sheet->setBorder('A1:G1', 'thin');
                     $sheet->setAutoSize(true);
                 });
             })->export('xlsx');
    }

    /**
     * Load data when search certificate
     * @param array $type type filter of certificate
     * @param string $startDate && $endDate start_at and end_at filter
     * @param array $team_ids team select filter
     */
    public function report(Request $request)
    {
        $route = Certificate::ROUTER_REPORT;
        if (Permission::getInstance()->isScopeCompany(null, $route) || Permission::getInstance()->isScopeTeam(null, $route)) {
            $listEmployeeCertificates = EmployeeCertificate::listEmployeeCertificates($request);
            $teamIdsAvailable = $request->team_ids;
            if (empty($teamIdsAvailable)) {
                $teamIdsAvailable = '0';
                $teamIdsAvailable = explode(",", $teamIdsAvailable);
            }
            $checkIsAll = false;
            foreach ($teamIdsAvailable as $id) {
               if ($id === '0') {
                $checkIsAll = true;
                break;
               }
            }
            if (!$checkIsAll) {
                $teamIds = [];
                $temIdResult = [];
                foreach ($teamIdsAvailable as $teamId) {
                    Team::getTeamChildRecursivePublic($teamIds, $teamId);
                    $temIdResult = array_merge($temIdResult, [$teamId]);
                    if (isset($teamIds['child'])) {
                        $temIdResult = array_merge($temIdResult, $teamIds['child']);
                    }
                }
                $temIdResult = array_unique($temIdResult);
                $listEmployeeCertificates = $listEmployeeCertificates->whereIn('teams.id', $temIdResult);
            }
            $listEmployeeCertificates = $listEmployeeCertificates->get()->toArray();
            $listTeam = [];
            foreach ($listEmployeeCertificates as $item) {
                if (isset($item['status'])) {
                    $item['status'] = Certificate::getOptionStatus()[$item['status']];
                }
                if (!isset($listTeam[$item['teams_id']])) {
                    $listTeam[$item['teams_id']] = [];
                }
                if (isset($listTeam[$item['teams_id']])) {
                    if (!isset($listTeam[$item['teams_id']][$item['employee_id']])) {
                        $listTeam[$item['teams_id']][$item['employee_id']] = [];
                    }
                    if (isset($listTeam[$item['teams_id']][$item['employee_id']])) {
                        array_push($listTeam[$item['teams_id']][$item['employee_id']], $item);
                    }
                }
            }
            return ['listTeam' => $listTeam, 'success' => true];
        } else {
            return [];
        }
    }

    public function zipIMGCertificate(Request $request)
    {
        $route = Certificate::ROUTER_REPORT;
        if (Permission::getInstance()->isScopeCompany(null, $route) || Permission::getInstance()->isScopeTeam(null, $route)) {
            $listEmployeeCertificates = EmployeeCertificate::listEmployeeCertificates($request);

        }
    }
}
