<?php
/**
 * Created by PhpStorm.
 * User: quanhv
 * Date: 03/01/20
 * Time: 08:44
 */

namespace Rikkei\Education\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\Model\Employee;
use URL;
use Lang;
use Rikkei\Education\Http\Services\CertificateService;

class CertificateController extends Controller
{
    protected $url;
    protected function _construct()
    {
        $this->url = URL::route('education::education.certificates.index');
        Breadcrumb::add('HR');
        Breadcrumb::add(trans('education::view.Certificate List'), $this->url);
        Menu::setActive('HR');
    }

    public function index()
    {
        $dataSearch = CoreForm::getFilterData('search', null);
        $id = $dataSearch['team_id'];

        $arrTeams = CertificateService::getTeamTree($id);

        $users = CertificateService::getEmployees($arrTeams['teamIdActive']);

        $data = [
            'collectionModel'  => $users,
            'listStatus' => Employee::getStatusListValidity(),
            'teamIdCurrent' => $arrTeams['teamIdActive'],
            'dataSearch' =>$dataSearch,
            'teamIdsAvailable' => $arrTeams['teamIdsAvailable'],
            'teamTreeAvailable' => $arrTeams['teamTreeAvailable'],
        ];
        return view('education::certificate.index', $data);
    }

    public function export()
    {
        $urlFilter = $this->url . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        CertificateService::exportCertificate($urlFilter, $dataSearch);
    }
}