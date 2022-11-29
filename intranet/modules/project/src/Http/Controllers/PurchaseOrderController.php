<?php

namespace Rikkei\Project\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewLaravel;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\View\CurlHelper;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Project\View\TimesheetHelper;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamList;
use Rikkei\Team\View\Permission;

class PurchaseOrderController extends Controller
{
    const URI_GET_PURCHASE_ORDER_BY_DIVISION = '/Api/index.php/V8/custom/po/product-revenue/list';
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('project', 'project/dashboard');
    }

    /**
     * get purchase order by division
     *
     * @param array $teamId
     * @param array $params
     * @return array
     */
    public static function apiGetPoByDivisionId(array $teamId, array $listParams)
    {
        $tokenHelper = new TimesheetHelper();


        $token = $tokenHelper->getToken();
        $header = [
            "Authorization: Bearer {$token}",
            "Content-Type: application/x-www-form-urlencoded",
        ];


        $url = config('sales.api_base_url') . self::URI_GET_PURCHASE_ORDER_BY_DIVISION;
        if (!empty($teamId)) {
            $response = CurlHelper::httpGet($url, $listParams, $header);
            $response = json_decode($response, true);
            if (!isset($response['data'])) {

                // Nếu không có data trả về thì get lại token
                // Remove token cũ
                Storage::put('sale_token.json', '');
                \Log::info('Không có data');
                \Log::info(print_r($response, true));
                return false;
            }
            return $response['data'];
        }
        return false;
    }

    public function listView()
    {
        Breadcrumb::add(Lang::get('project::view.List'));
        $route = 'project::purchaseOrder.list';
        $currentDate = Carbon::now()->format('Y-m');
        $urlFilter = route($route) . '/';
        $pager = Config::getPagerData($urlFilter);
        $listNameTeam = [];
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $team = TeamList::toOption(null, true, false, null, false, false);
            foreach ($team as $key => $value) {
                $listNameTeam[$value['value']] =  str_replace('&nbsp;', '', $value['label']);
            }
        } else if ($inTeam = Permission::getInstance()->isScopeTeam(null, $route)) {
            $team = TeamList::toOption(null, true, false, null, false, false);
            foreach ($team as $key => $value) {
                if (in_array($value['value'], $inTeam)) {
                    $listNameTeam[$value['value']] = str_replace('&nbsp;', '', $value['label']);
                } else {
                    unset($team[$key]);
                }
            }
        } else {
            View::viewErrorPermission();
        }

        $filter = Form::getFilterData();
        // check if filter is not use
        $listTeamGet = isset($filter['team_id']) ? $filter['team_id'] : array_column($team, 'value');
        $params = [
            'team_id' => $listTeamGet,
            'month_from' => empty($filter['month_from']) ? '' : $filter['month_from'],
            'month_to' => empty($filter['month_to']) ? '' : $filter['month_to'],
            'limit' => $pager['limit'],
            'page' => $pager['page'],
        ];
        if (isset($filter['account_manager'])) {
            $params['account_manager'] = $filter['account_manager'];
        }
        if (isset($filter['account_name'])) {
            $params['account_name'] = $filter['account_name'];
        }
        if (isset($filter['po_title'])) {
            $params['po_title'] = $filter['po_title'];
        }
        if ($pager['order'] != 'id') {
            $params['sort_by'] = $pager['order'] . ' ' . $pager['dir'];
        }

        $getData = $this->apiGetPoByDivisionId($listTeamGet, $params);
        $list = $getData ? $getData['purchase_order'] : [];
        $totalRecord = $getData ? $getData['total_record'] : 0;
        $totalPage = floor($totalRecord / $pager['limit']) + (empty($totalRecord % $pager['limit']) ? 0 : 1);

        return View('project::purchase_order.list', [
            'collectionModel' => $list,
            'currentPage' => $pager['page'],
            'totalRecord' => $totalRecord,
            'totalPage' => $totalPage,
            'urlFilter' => $urlFilter,
            'teamsOptionAll' => $team,
            'limited' => $pager['limit'],
            'divisionNameList' => $listNameTeam,
        ]);
    }
}
