<?php

namespace Rikkei\Sales\Http\Controllers;

use Illuminate\Support\Str;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Auth;
use DB;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssTeams;
use Rikkei\Sales\Model\CssQuestion;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssResultDetail;
use Rikkei\Sales\Model\CssProjectType;
use Rikkei\Sales\Model\CssView;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\View\CssPermission;
use Lang;
use Session;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Team\View\Permission;
use Route;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Project;
use Rikkei\Sales\Model\Customer;
use Rikkei\Sales\Model\CssMail;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Sales\View\View as CView;
use Rikkei\Core\Model\CoreConfigData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Sales\View\SendMailAnalysisCss;
use Rikkei\Sales\Model\CssComment;
use Rikkei\Core\View\CookieCore;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CssController extends Controller {
    
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Sales');
        Breadcrumb::add('Css' , route('sales::css.list'));
        Menu::setActive('sales', 'sales');
    }
    
    static $perPage = 10;
    static $perPageCss = 10;
    
    /**
     * Create Css page view
     */
    public function create() {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        Breadcrumb::add(Lang::get('sales::view.Create CSS'));
        $user = Auth::user(); 
        $employee = Employee::find($user->employee_id);
        $teams = Team::all();
        
        $css = new Css();
        // Set default value CSS
        $css->start_date = '';
        $css->end_date   = '';
        $css->projs_id = 0;
        $css->sale_name_jp = '';
        $css->project_code = '';
        $cssProjectType = Css::getCssProjectTypeValue();
        $projects = Project::getProjectByTypes($cssProjectType);
        return view(
                'sales::css.create', 
                [
                    'css'       => $css,
                    'employee'  => $employee,
                    'teams'     => $teams,
                    'save'      => 'create',
                    'projectType' => CssProjectType::all(),
                    'rikker_relate' => [],
                    'projects'  => $projects
                ]
        );
    }

    public function getPmAndSales(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        $idProj = $request->get('projectId');
        $data['pm'] = '';
        $data['sales'] = '';
        $project = Project::select(
                "projs.id",
                "projs.name",
                "projs.company_id",
                "employees.name as pm_name"
            )
            ->leftJoin("employees", "employees.id", "=", "projs.manager_id")
            ->where("projs.id", $idProj)
            ->first();
        if ($project) {
            $company = \Rikkei\Sales\Model\Company::select(
                "cust_companies.id",
                "cust_companies.company",
                "empSale.name as sale_name"
            )
            ->join("employees as empSale", "empSale.id", "=", "cust_companies.sale_support_id")
            ->where("cust_companies.id", $project->company_id)
            ->first();
        }

        $response = [
            'pm' => isset($project) ? ucwords(Str::slug($project->pm_name, ' ')) : '',
            'sale' => isset($company) ? ucwords(Str::slug($company->sale_name, ' ')) : ''
        ];
        return response()->json($response);
    }
    
    public function setTeam(Request $request) {
        $projsId = $request->input('projsId');
        $projs = Project::find($projsId);
        $pm = Employee::find($projs->manager_id);
        if (!$pm) {
            $pm = new Employee();
        }
        $projs->pm_name = $pm->name;
        $projs->japanese_name = $pm->japanese_name;
        $projs->pm_email = $pm->email;
        $projs->pm_account = CView::getAccName($pm->email);
        $cust = Customer::find($projs->cust_contact_id);
        if ($cust) {
            $projs->cus_name = $cust->name_ja ? $cust->name_ja : $cust->name;
            $projs->cus_email = $cust->email ? $cust->email : '';
            $company = \Rikkei\Sales\Model\Company::find($cust->company_id);
            $projs->company_name = $company ? ($company->name_ja ? $company->name_ja : $company->company) : null;
        }
        $projs->start = date('Y/m/d', strtotime($projs->start_at));
        $projs->end = date('Y/m/d', strtotime($projs->end_at));
        $teamIds = Project::getAllTeamOfProject($projsId);
        $model = new Team();
        $teams = $model->getTeamsByTeamIds($teamIds);
        $arrTeamNames = [];
        $arrTeamIds = [];
        if(count($teams)) {
            foreach($teams as $team) {
                $arrTeamNames[] = $team->name;
                $arrTeamIds[] = $team->id;
            }
        }
        $projs->teamNames = implode(', ', $arrTeamNames);
        $projs->teamIds = implode(', ', $arrTeamIds);
        $projs->type = CssProjectType::getTextById($projs->type);
        return response()->json($projs);
    }
    
    /**
     * Update Css page view
     * @param int $id
     * @param Request $request
     *
     * @return response view
     */
    public function update($id, Request $request) {

        $css = Css::find($id);
        if (!$css) {
            return view('core::errors.exception');
        }
        $type = $request->input('type');
        if ($type && $type == 'detail') {
            if (!Permission::getInstance()->isAllow('sales::css.cssDetail')) {
                return view('core::errors.permission_denied');
            }
        } else {
            $curEmp = Permission::getInstance()->getEmployee();
            $companyPermission = Permission::getInstance()->isScopeCompany(null, 'sales::css.update');
            if (!$companyPermission && $curEmp->id != $css->employee_id) {
                return view('core::errors.permission_denied');
            }
        }
        Breadcrumb::add(Lang::get('sales::view.Update CSS'));
        
        $projs = Project::find($css->projs_id);
        if ($projs) {
            $pm = Employee::find($projs->manager_id);
            if (!$pm) {
                $pm = new Employee();
            }
            $cust = Customer::find($projs->cust_contact_id);
            $css->pm = $css->pm_name . ' ('. CView::getAccName($pm->email) .')';
            if ($cust) {
                if ($css->lang_id == Css::ENG_LANG) {
                    $css->cus_name = $cust->name ? $cust->name : $cust->name_ja;
                } else {
                    $css->cus_name = $cust->name_ja ? $cust->name_ja : $cust->name;
                }
                $css->cus_email = $cust->email ? $cust->email : '';
            }
            $css->project_code = $projs->project_code;
        } else {
            $css->pm = $css->pm_name ? $css->pm_name : '';
            $css->cus_name = $css->customer_name ? $css->customer_name : '';
            $css->cus_email = '';
            $css->project_code = '';
        }
        
        //get CSS creator       
        $employee = Employee::find($css->employee_id);
        if (!$employee) {
            $employee = new Employee();
        }
        //get team_id list by css_id
        $teams = Team::all();
        $arrTeamId = array();
        $teamIds = CssTeams::getTeamIdsByCssId($id);

        foreach ($teamIds as $team) {
            $arrTeamId[] = $team->team_id;
        }

        //get Css's team list
        $teamModel = new Team();
        $teamsSet = $teamModel->getTeamsByTeamIds($arrTeamId);

        //Get Team's name is set
        $strTeamsNameSet = [];
        $teamIds = [];
        foreach ($teamsSet as $team) {
            $strTeamsNameSet[] = $team->name;
            $teamIds[] = $team->id;
        }
        $strTeamsNameSet = implode(', ', $strTeamsNameSet);
        $strTeamIds = implode(',', $teamIds);
        
        //format css date
        $css->start_date = date('Y/m/d',strtotime($css->start_date));
        $css->end_date   = date('Y/m/d',strtotime($css->end_date));
        
        $rikker_relate = [];
        if($css->rikker_relate && $css->rikker_relate != ''){
            $arrTemp = explode(',', $css->rikker_relate);
            foreach($arrTemp as $item){
                $emp = Employee::getEmpByEmail($item);
                if($emp && count($emp) > 0) {
                    $rikker_relate[] = [
                        'email'  => $item,
                        'name'  => $emp->name ? $emp->name : $item
                    ];
                } else {
                    $rikker_relate[] = [
                        'email'  => $item,
                        'name'  => $item
                    ];
                }
            }
        }
        $cssProjectType = Css::getCssProjectTypeValue();
        $projects = Project::getProjectByTypes($cssProjectType);
        
        // If view detail
        
        $previewUrl = null;
        $welcomeUrl = null;
        $totalViewCss = null;
        $totalMakeCss = null;
        $cssMail = null;
        $listCssWork = null;
        $workOrderUrl = route('project::project.edit', ['id' => $css->projs_id]);
        $projectReportUrl = route('project::point.edit', ['id' => $css->projs_id]);
        if ($type && $type == 'detail') {
            $previewUrl = route('sales::css.preview', ['id' => $css->id, 'token' => $css->token]);
            $welcomeUrl = route('sales::welcome', ['id' => $css->id, 'token' => $css->token]);
            $totalViewCss = Css::getTotalView($id);
            $totalMakeCss = Css::getTotalMake($id);
            $cssMail = CssMail::getCssMailByCssId($id);
            $listCssWork1 = new CssResult();
            $listCssWork = $listCssWork1->getCssResulByCssForDetail($css->id,"created_at","desc")->get();
        }
        return view(
            'sales::css.create', 
            [
                'css'               => $css, 
                'employee'          => $employee, 
                "teams"             => $teams, 
                "teamsSet"          => $teamsSet, 
                'strTeamIds'        => $strTeamIds,
                "strTeamsNameSet"   => $strTeamsNameSet, 
                'save'              => 'update',
                'projectType'       => CssProjectType::all(),
                "rikker_relate"     => $rikker_relate,
                'projects'          => $projects,
                'type'              => $type,
                'previewUrl'        => $previewUrl,
                'welcomeUrl'        => $welcomeUrl,
                'totalViewCss'      => $totalViewCss,
                'totalMakeCss'      => $totalMakeCss,
                'cssMail'           => $cssMail,
                'cust'              => $cust,
                'listCssWork'       => $listCssWork,
                'workOrderUrl'      => $workOrderUrl,
                'projectReportUrl'  => $projectReportUrl,
            ]
        );
    }
    
    public function getRikkerInfo(Request $request) {
        $value = $request->input('value');
        $employees = Employee::getEmpLikeEmail($value);
        return response()->json($employees);
    }

    /**
     * Preview page
     * @param string $token
     * @param int $id
     * @return objects
     */
    public function preview($token, $id, Request $request) {
        Breadcrumb::add(Lang::get('sales::view.Preview.Preview'));
        $data = self::getCssCategory($token, $id);
        if ($data) {
            $css = $data['css'];
            $cssOld = $css->created_at < Css::CSS_TIME;
            $permissionFlag = CssPermission::isCssPermission($css);
            //If hasn't permission
            if(!$permissionFlag){
                return view(
                    'core::errors.permission_denied'
                );
            }
            $time = explode('-', $css->time_reply);
            $overviewQuestion = $data['overviewQuestion'];
            // $cssMail = CssMail::getCssMailByCssId($id);
            $cssMail = CssMail::getLastSendCssMailByCssId($id);
            $codeResults = CssResult::getCodeResult($id);
            //data template mail
            $dataTemp = [
                'company_name' => $css->company_name,
                'hrefMake' => route('sales::welcome', ['id' => $css->id, 'token' => $css->token]),
                'customerName' => Lang::get('sales::view.CSS.Preview.Customer name'),
                'gender' => 1,
                'month' => $css->time_reply ? $time[1] : '',
                'date' => $css->time_reply ? $time[2] : '',
            ];
            $projectOfCss = Project::getProjectById($css->projs_id);
            $nameSession = 'css_import_'.$id.'_'.Auth::user()->employee_id;
            $session = $request->session()->get($nameSession);
            return view(
                'sales::css.preview', 
                [
                    'css' => $css,
                    "employee" => $data['employee'],
                    "cssCate" => $session ? $session['cssCate'] : $data['cssCate'],
                    "noOverView" => $session ? $session['noOverView'] : $data['noOverView'],
                    "overviewQuestionId" => $session ? $session['overviewQuestionId'] : $overviewQuestion->id,
                    "overviewQuestionContent" => $session ? $session['overviewQuestionContent'] : $overviewQuestion->content,
                    "overviewQuestionExplain" => isset($session['overviewQuestionExplain']) ? $session['overviewQuestionExplain'] : $overviewQuestion->explain,
                    'hrefMake' => route('sales::welcome', ['id' => $css->id, 'token' => $css->token]),
                    'hrefUpdateCss' => url("/css/update/$id"),
                    'cssMail' => $cssMail,
                    'codeResults' => $codeResults,
                    'data' => $dataTemp,
                    'hasPermission' => CssPermission::hasPermission($css),
                    'projectOfCss' => $projectOfCss,
                    'token' => $css->token,
                    'id' => $css->id,
                    'cssOld' => $cssOld,
                ]
            );
        } else {
            return redirect("/");
        }
    }

    public function importTemplate(Request $request){
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:csv,xlsx,xls',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('excel_file');
        try {
            $rowCount = 0;
            $dataReader = null;
            Excel::selectSheetsByIndex(0)->load($file->getRealPath(), function ($reader) use (&$rowCount, &$dataReader){
                $rowCount = $reader->get()->count();
                $dataReader = $reader;
            });

            $errors = [];
            $count = 0;
            $this->excuteFile($dataReader, $errors, $count, $request);
            if ($errors) {
                return redirect()->back()->with('messages', ['errors' => $errors]);
            }
            return redirect()->back()->with('messages', ['success' => ['Import template successful']]);
        } catch (\Exception $ex) {
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => 
                [$ex->getMessage()]
            ]);
        }
    }

    public function downloadTemplate($type) {
        if (!$type || !in_array($type, ['en', 'jp'])) {
            return redirect()->back();
        }
        $pathToFile = public_path('sales/files/Template-CSS-'.$type.'.xlsx');
        return response()->download($pathToFile);
    }

    private function excuteFile($reader, &$errors, &$count, Request $request){
        $dataRecord = $reader->get();        
        $data = [];
        $dataChild = [];
        $dataSubChild = [];
        $dataQs = [];
        $dataRoot = [];
        $overviewQuestionId = '';
        $overviewQuestionContent = '';
        $orderChild = 0;
        $isOverview = false;
        foreach ($dataRecord as $key => $itemRow) {
            $keyItem = $key + 2;
            if (strlen($itemRow->id) == 0 || strlen($itemRow->title) == 0 || strlen($itemRow->parent) == 0 || strlen($itemRow->question) == 0 || strlen($itemRow->overview) == 0) {
                $errors[] = 'Row '.$keyItem. ' missing ID, Title, Parent, Question or Overview';
                continue;
            }
            //ID
            if (is_float($itemRow['id'])) {
                $itemRow['id'] = (int)$itemRow['id'];
                if ($itemRow['id'] <= 0) {
                    $errors[] = 'Row '.$keyItem. ' - (id) format is invalid.';
                    continue;
                }
            } else {
                $errors[] = 'Row '.$keyItem. ' - (id) format is invalid.';
                continue;
            }
            //Parent
            if (is_float($itemRow['parent'])) {
                $itemRow['parent'] = (int)$itemRow['parent'];
                if ($itemRow['parent'] < 0) {
                    $errors[] = 'Row '.$keyItem. ' - (parent) format is invalid.';
                    continue;
                }
            } else {
                $errors[] = 'Row '.$keyItem. ' - (parent) format is invalid.';
                continue;
            }
            //Question
            if (is_float($itemRow['question'])) {
                $itemRow['question'] = (int)$itemRow['question'];
                if ($itemRow['question'] < 0) {
                    $errors[] = 'Row '.$keyItem. ' - (question) format is invalid.';
                    continue;
                }
            } else {
                $errors[] = 'Row '.$keyItem. ' - (question) format is invalid.';
                continue;
            }
            //Overview
            if (is_float($itemRow['overview'])) {
                $itemRow['overview'] = (int)$itemRow['overview'];
                if ($itemRow['overview'] < 0) {
                    $errors[] = 'Row '.$keyItem. ' - (overview) format is invalid.';
                    continue;
                } else {
                    $isOverview = true;
                }
            } else {
                $errors[] = 'Row '.$keyItem. ' - (overview) format is invalid.';
                continue;
            }

            // //Convert data
            if ($itemRow['question'] == 1 && $itemRow['overview'] == 1) {
                $overviewQuestionId = $itemRow['id'];
                $overviewQuestionContent = $itemRow['title'];
                $overviewQuestionExplain = null;
                if ($itemRow['question_explanation']) {
                    $qsExplains = preg_split('/\r\n|[\r\n]/', $itemRow['question_explanation']);
                    if ($qsExplains) {
                        $ovvQsExplain = [];
                        foreach ($qsExplains as $qsExplain) {
                            $qsExplainItem = explode('=>', $qsExplain);
                            if (count($qsExplainItem) > 1) {
                                $ovvQsExplain[$qsExplainItem[0]] = $qsExplainItem[1];
                            }
                        }
                        $overviewQuestionExplain = json_encode($ovvQsExplain);
                    }
                }
            }
            if ($itemRow['id'] == 1 && $itemRow['parent'] == 0 && $itemRow['question'] == 0 && $itemRow['overview'] == 0) {
                $rootTemp = [
                    'id' => $itemRow['id'],
                    'name' => $itemRow['title'],
                    'parent_id' => $itemRow['parent'],
                    'question_explanation' => $itemRow['question_explanation'],
                ];
                $dataRoot = $rootTemp;
            }
        }
        if (!$overviewQuestionId) {
            $errors[] = 'Data import must contain only one overview question (parent = Id of root category, question = 1, overview = 1)';
        }
        if (!$dataRoot) {
            $errors[] = 'Data import must contain only one root category (parent = 0, question = 0, overview = 0)';
        }

        if (empty($errors) && $dataRoot && $overviewQuestionId && $overviewQuestionContent) {
            foreach ($dataRecord as $key => $itemRow) {
                $keyItem = $key + 2;
                if ($itemRow['parent'] == $dataRoot['id'] && $itemRow['question'] != 1) {
                    $orderChild++;
                    $childTemp = [
                        'id' => $itemRow['id'],
                        'name' => $itemRow['title'],
                        'parent_id' => $itemRow['parent'],
                        'question_explanation' => $itemRow['question_explanation'],
                        'sort_order' => View::romanic_number($orderChild,true),
                        'noCate' => $orderChild,
                        'cssCateChild' => [],
                        'questions' => [],
                    ];
                    $dataChild[$itemRow['id']] = $childTemp;
                }
                if (!in_array($itemRow['parent'], [0,1]) && $itemRow['question'] != 1) {
                    $subChildTemp = [
                        'id' => $itemRow['id'],
                        'name' => $itemRow['title'],
                        'parent_id' => $itemRow['parent'],
                        'question_explanation' => $itemRow['question_explanation'],
                    ];
                    $dataSubChild[$itemRow['parent']][] = $subChildTemp;
                }
                if ($itemRow['question'] == 1 && $itemRow['overview'] != 1) {
                    $qsTemp = [
                        'id' => $itemRow['id'],
                        'content' => $itemRow['title'],
                        'category_id' => $itemRow['parent'],
                        'is_overview_question' => $itemRow['overview'],
                    ];
                    if ($itemRow['question_explanation']) {
                        $qsExplains = preg_split('/\r\n|[\r\n]/', $itemRow['question_explanation']);
                        if ($qsExplains) {
                            $dataQsExplain = [];
                            foreach ($qsExplains as $qsExplain) {
                                $qsExplainItem = explode('=>', $qsExplain);
                                if (count($qsExplainItem) > 1) {
                                    $dataQsExplain[$qsExplainItem[0]] = $qsExplainItem[1];
                                }
                            }
                            $qsTemp['question_explanation'] = json_encode($dataQsExplain);
                        }
                    }
                    $dataQs[$itemRow['parent']][] = $qsTemp;
                }
            }
    
            //Convert data
            foreach ($dataQs as $key => $qs) {
                $orderQs = 0;
                foreach ($qs as $value) {
                    $orderQs++;
                    $value['sort_order'] = $orderQs;
                    $dataQsNew[$key][] = $value;
                }
            }
            foreach ($dataSubChild as $key => $children) {
                $orderSubChild = 0;
                foreach ($children as $item) {
                    $orderSubChild++;
                    $item['sort_order'] = $orderSubChild;
                    $item['questionsChild'] = isset($dataQsNew[$item['id']]) ? $dataQsNew[$item['id']] : [];
                    $dataSubChildNew[$key][] = $item;
                }
            }
            $orderChild = 0;
            foreach ($dataChild as $key => $itemChild) {
                $orderChild++;
                $itemChild['noCate'] = $orderChild;
                if (isset($dataSubChildNew[$key])) {
                    $itemChild['cssCateChild'] = $dataSubChildNew[$key];
                } else {
                    if (isset($dataQsNew[$key])) {
                        $itemChild['questions'] = $dataQsNew[$key];
                    }
                }
                $dataChildNew[$key] = $itemChild;
            }   
    
            $dataImport = [
                'dataRoot' => $dataRoot,
                'cssCate' => $dataChildNew,
                "noOverView" => View::romanic_number(++$orderChild,true),
                'overviewQuestionId' => $overviewQuestionId,
                'overviewQuestionContent' => $overviewQuestionContent,
                'overviewQuestionExplain' => $overviewQuestionExplain,
            ];
            $nameSession = 'css_import_'.$request->get('css_id').'_'.Auth::user()->employee_id;
            $request->session()->put($nameSession, $dataImport);
            // $request->session()->put('css_import_cssId_userId', $dataImport);
        }
    }

    /**
     * Save Css (insert or update)
     */
    public function save(Request $request) { 
        if ($request->input("create_or_update") == 'create') {
            $css = new Css();
        } else {
            $cssId = $request->input("css_id");
            $css = Css::find($cssId);
            $curEmp = Permission::getInstance()->getEmployee();
            $companyPermission = Permission::getInstance()->isScopeCompany(null, 'sales::css.update');
            if (!$companyPermission && $curEmp->id != $css->employee_id) {
                return false;
            }
        }
        $projsId = $request->input('projId');
        $projs = Project::find($projsId);
        $pm = Employee::find($projs->manager_id);
        if (!$pm) {
            $pm = new Employee();
        }
        $cust = Customer::find($projs->cust_contact_id);
        // Get teamIds by project
        $teamIds = Project::getAllTeamOfProject($projsId);
        
        $employee = Permission::getInstance()->getEmployee();
        
        // Fill data to Css
        $css->employee_id = $employee->id;
        if ($cust) {
            $company = \Rikkei\Sales\Model\Company::find($cust->company_id);
            $lang = Input::get('lang');
            if ($lang == Css::ENG_LANG) {
                $css->company_name = $company ? ($company->company ? $company->company : $company->name_ja) : '';
                $css->customer_name = $cust->name ? $cust->name : $cust->name_ja;
            } else {
                $css->company_name = $company ? ($company->name_ja ? $company->name_ja : $company->company) : '';
                $css->customer_name = $cust->name_ja ? $cust->name_ja : $cust->name;
            }
        } else {
            $css->company_name = '';
            $css->customer_name = '';
        }
        $css->project_name = $projs->name;
        $css->rikker_relate = $request->input("rikker_relate");
        $css->start_date = $request->input("start_date");
        $css->end_date = $request->input("end_date");
        $css->pm_email = $pm->email;
        $css->pm_name_jp = $request->input("pm_name_jp");
        $css->project_type_id = $projs->type;
        $css->pm_name = $pm->name;
        $css->projs_id = $projsId;
        $css->sale_name_jp = $request->input("japanese_name");
        $css->lang_id = $request->input("lang");
        $css->status = Css::STATUS_NEW;
        $css->project_name_css = $request->input("project_name_css");
        $css->start_onsite_date = $request->input('start_onsite_date');
        $css->end_onsite_date = $request->input('end_onsite_date');
        if ($request->input('time_reply')) {
            $css->time_reply = $request->input('time_reply');
        }
        $css->css_creator_name = $request->input('css_creator_name');

        $employeePm = Employee::getEmpByEmail($css->pm_email);
        if($employeePm && count($employeePm) > 0) {
            $employeePm->name = $css->pm_name;
            $employeePm->japanese_name = $css->pm_name_jp;
            $employeePm->save();
            
        } else {
            $emp = new Employee();
            $emp->name = $css->pm_name;
            $emp->japanese_name = $request->input("pm_name_jp");
            $emp->email = $request->input("pm_email")[0];
            $emp->save();
        }
        
        // Generate token
        if ($request->input("create_or_update") == 'create') {
            $css->token = md5(rand());
        }

        $css->save();
        $employee->save();
        
        
        //insert into table css_team
        $cssTeamModel = new CssTeams();
        $cssTeamModel->insertCssTeam($css->id, $teamIds);
        
        // return url preview
        echo route('sales::css.preview', ['id' => $css->id, 'token' => $css->token]);
    }
    
    /**
     * Welcome page 
     * 
     * @param string $token
     * @param int $id
     * @param Request $request
     */
    public function welcome($token, $id, Request $request){
        $cssModel = new Css();
        $css = $cssModel->getCssByIdAndToken($id,$token);
        if (!$css) {
            return view('core::errors.layout_customer_not_found');
        }
        $code = ($request->input('c')) ? $request->input('c'): 0;
        $urlSubmit = route('sales::welcome', ['id' => $id, 'token' => $token]) . '?c=' . $code;
        if ($request->input('submit') !== null) {
            $makeName   = $request->input('make_name'); 
            $token      = $request->input('token'); 
            $id         = $request->input('id'); 
            if($makeName === '') { 
                return view(
                    'sales::css.welcome', [
                        'css' => $css,
                        'token' => $token,
                        'id'    => $id,
                        "nameRequired" => 1, //name is empty
                        'makeName'  => $makeName,
                        'urlSubmit' => $urlSubmit
                    ]);
            }elseif(strlen($makeName) > 100){
                return view(
                    'sales::css.welcome', [
                        'css' => $css,
                        'token' => $token,
                        'id'    => $id,
                        'makeName' => $makeName,
                        "nameRequired" => -1, //name with lenght > 100 char
                        'urlSubmit' => $urlSubmit
                    ]);
            }else { 
                //Set make name and 
                $request->session()->put('makeName'.$id, $makeName);
                
                //Insert css view
                $data = [
                    'css_id'        => $css->id,
                    'name'          => $makeName,
                    'ip_address'    => self::get_client_ip()
                ];
                
                $model = new CssView();
                if(!$model->isViewed($data)) {
                    $model->insert($data);
                }
                
                $urlMake = route('sales::make', ['id' => $id, 'token' => $token]) . '?c=' . $code;
                
                //Redirect to CSS make page
                return redirect($urlMake);
            }
        }else {
            $name = ($request->session()->get('makeName'.$id) !== null) ? $request->session()->get('makeName'.$id) : '';
            $listWork = CssResult::where('css_id',$css->id)
                ->where('code',$request->input('c'))->get();
            if ($listWork->count() > 0) {
                $status = true;
            } else {
                $status = false;
            }
            return view(
                'sales::css.welcome', [
                    'css' => $css,
                    'token' => $token,
                    'id'    => $id,
                    "nameRequired" => 0, //name valid
                    'makeName' => $name,
                    'urlSubmit' => $urlSubmit,
                    'code' => $code,
                    'status' => $status,
                ]
            );
        }
        
    }
    
    /**
     * Function to get the client IP address
     * 
     * @return string 
     */
    public function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
   
    /**
     * Make Css page
     * @param string $token
     * @param int $id
     * @return objects
     */
    public function make($token, $id, Request $request) {
        $code = ($request->input('c')) ? $request->input('c'): 0;
        $data = self::getCssCategory($token, $id, $code);
        $lang = SupportConfig::get('langs.'.$data['css']['lang_id']);
        //Get CSS information
        if ($data) {
            $overviewQuestion = $data['overviewQuestion'];
            $arrayValidate = array(
                "nameRequired" => Lang::get('sales::message.Name validate required',[],$lang),
                "emailRequired" => Lang::get('sales::message.Email validate required',[],$lang),
                "emailAddress" => Lang::get('sales::message.Email validate address',[],$lang),
                "totalMarkValidateRequired" => Lang::get('sales::message.Total mark validate required',[],$lang),
                "questionCommentRequired" => Lang::get('sales::message.Question comment required',[],$lang),
                "proposedRequired"  => Lang::get('sales::message.Proposed required',[],$lang),
                "generalCommentValidateRequired" => Lang::get('sales::message.General comment validate required',[],$lang),
            );
            $projectOfCss = Project::getProjectById($data['css']->projs_id);
            return view(
                'sales::css.make', [
                    'css' => $data['css'],
                    "employee" => $data['employee'],
                    "cssCate" => $data['cssCate'],
                    "arrayValidate" => json_encode($arrayValidate),
                    "noOverView" => $data['noOverView'],
                    "overviewQuestionId" => $overviewQuestion->id,
                    "overviewQuestionContent" => $overviewQuestion->content,
                    "overviewQuestionExplain" => $overviewQuestion->explain,
                    "makeName" => ($request->session()->get('makeName'.$id) !== null) ? $request->session()->get('makeName'.$id) : '',
                    'code' => $code,
                    'projectOfCss' => $projectOfCss,
                    'cssOld' => $data['cssOld'],
                ]
            );
        } else {
            return redirect(url('/'));
        }
    }
    
    public function getCssCategory($token, $id, $code = 1) {
        $cssQuestionModel = new CssQuestion();
        $cssCategoryModel = new CssCategory();
        $cssModel = new Css();
        $css = $cssModel->getCssByIdAndToken($id,$token);

        if ($css) {
            $employee = Employee::find($css->employee_id);
            if (!$employee) {
                $employee = new Employee();
            }
            $cssOld = $css->created_at < Css::CSS_TIME;
            $rootCategory = $cssCategoryModel->getRootCategoryV2($css->project_type_id, $css->id, $code, $css->created_at, $css->lang_id);
            $cssCategory = $cssCategoryModel->getCategoryByParent($rootCategory->id, $css->lang_id, $css->created_at, $css->project_type_id);
            $cssCate = array();
            if ($cssCategory) {
                $NoOverView = 0;
                foreach ($cssCategory as $item) {
                    $NoOverView++;
                    $cssCategoryChild = $cssCategoryModel->getCategoryByParent($item->id,$css->lang_id,$css->created_at, $css->project_type_id);
                    $cssCateChild = array();
                    if ($cssCategoryChild) {
                        foreach ($cssCategoryChild as $item_child) {
                            $cssQuestionChild = $cssQuestionModel->getQuestionByCategory($item_child->id);
                            $cssCateChild[] = array(
                                "id" => $item_child->id,
                                "name" => $item_child->name,
                                "parent_id" => $item->id,
                                "sort_order" => $item_child->sort_order,
                                "questionsChild" => $cssQuestionChild,
                                "show_brse_name" => $item_child->show_brse_name,
                                "show_pm_name" => $item_child->show_pm_name,
                                "question_explanation" => $item_child->question_explanation,
                            );
                        }
                    }
                    
                    $cssQuestion = $cssQuestionModel->getQuestionByCategory($item->id);
                    $cssCate[] = array(
                        "id" => $item->id,
                        "name" => $item->name,
                        "sort_order" => View::romanic_number($item->sort_order,true),
                        "cssCateChild" => $cssCateChild,
                        "questions" => $cssQuestion,
                        "noCate"    => $NoOverView, //No. of root cate
                        "show_brse_name" => $item->show_brse_name,
                        "show_pm_name" => $item->show_pm_name,
                    );
                }
            }
            
            //Get overview question
            // $questLangId = ($css->project_type_id == Css::TYPE_ONSITE && $cssOld) ? $css->lang_id : null;
            $questLangId = CView::checkLangOvvQuestion($css->project_type_id, $css->lang_id, $css->created_at);
            $overviewQuestion = $cssQuestionModel->getOverviewQuestionByCategory($rootCategory->id, 1, $questLangId);
        
            return [
                'css' => $css,
                'employee' => $employee,
                'cssCate' => $cssCate,
                'overviewQuestion' => $overviewQuestion,
                "noOverView" => View::romanic_number(++$NoOverView,true),
                'cssOld' => $cssOld,
            ];
        } else {
            return null;
        }    
    }
    
    /**
     * Insert Css result into database
     * @return void
     */
    public function saveResult(Request $request){ 
        $arrayQuestion  = $request->input('arrayQuestion');
        $name           = $request->input('make_name');
        $email          = $request->input('make_email');
        $avgPoint       = $request->input('totalMark');
        $proposed       = $request->input('proposed');
        $cssId          = $request->input('cssId');
        $code           = $request->input('code');

        $dataResult = [
            'css_id' => $cssId,
            'name' => $name,
            'email' => $email,
            'avg_point' => $avgPoint,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
            'proposed' => $proposed,
            'code' => $code
        ];
        
        $cssResultModel = new CssResult();
        $cssResultId = $cssResultModel->insertCssResult($dataResult);
        
        $cssResultDetailModel = new CssResultDetail();
        $cssResultDetailModel->insertCssResultDetail($cssResultId,$arrayQuestion);
        
        $css = Css::find($cssId); 
        $employee = Employee::getEmpById($css->employee_id); 
        if (!$employee) {
            $employee = new Employee();
        }
        $emailTo = $css->pm_email; 
        $relateEmails = explode(',', $css->rikker_relate); 
        $relateEmails[] = $employee->email; 
        
        $data = array(
            'href'      => route("sales::css.detail", ['id' => $cssResultId] ) ,
            'pm'       => $css->pm_name,
            'makeName' => $name,
            'point' => $avgPoint,
            'employeeName'  => $employee->name,
            'ccName' => implode(', ',$relateEmails),
            'companyName' => $css->company_name,
            'projectName' => $css->project_name,
            'projectDate' => date('d/m/Y', strtotime($css->start_date)) . ' - ' . date('d/m/Y', strtotime($css->end_date)),
            'projectType' => $css->project_type_id == 1 ? Lang::get('sales::view.Project OSDC name') : Lang::get('sales::view.Project base name')
        );
        
        //Save mail to queue
        $template = 'sales::css.sendMail';
        $subject = Lang::get('sales::view.Subject email notification make css',
                            [
                                "company" => $css['company_name'], 
                                "point" => $avgPoint
                            ]);
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($emailTo, $css->pm_name)
            ->setFrom('pqa@rikkeisoft.com', 'Rikkeisoft')
            ->setSubject($subject)
            ->setTemplate($template, $data);
        foreach ($relateEmails as $relate) {
            $emailQueue->addCc($relate);
        }
        
        try {
            $emailQueue->save();
            //set notify
            array_push($relateEmails, $emailTo);
            \RkNotify::put(
                Employee::whereIn('email', $relateEmails)->lists('id')->toArray(),
                $subject,
                $data['href'], [
                    'category_id' => RkNotify::CATEGORY_PROJECT,
                    'content_detail' => RkNotify::renderSections($template, $data)
                ]);
        } catch (Exception $ex) {

        }
       
    }
    
    /**
     * Clear all CSS
     */
    public function reset() {
        $per = new \Rikkei\Team\View\Permission;
        // Root only has permission
        if ($per->isRoot()) {
            $model = new Css();
            $model->clearAll();
        }
    }
    
    /**
     * View Css list 
     * @return void
     */
    public function grid(Request $request)
    {
        $curPage = $request->input('page');
        $pager = Config::getPagerData();
        $pagerFilter = (array) Form::getFilterPagerData();
        $pagerFilter = array_filter($pagerFilter);
        if ($pagerFilter) {
            $css = CssPermission::getCssListByPermission($pager['order'], $pager['dir']);
        } else {
            $css = CssPermission::getCssListByPermission('css.created_at', 'desc');
        }

        $css = CoreModel::filterGrid($css);
        $css = CoreModel::pagerCollection($css, $pager['limit'], $curPage);

        if (count($css) > 0) {
            $teamModel = new Team();
            foreach ($css as &$item) {
                //Get teams list
                $cssTeams = CssTeams::getCssTeamByCssId($item->id);
                $arr_team = array();
                foreach ($cssTeams as $cssTeamChild) {
                    $team = $teamModel->getTeamWithTrashedById($cssTeamChild->team_id);
                    $arr_team[] = $team->name;
                }
                //end get teams list
                //sort teams
                sort($arr_team);
                if($pager['order'] === 'team_join.name'){
                    if($pager['dir'] === 'asc'){
                        sort($arr_team); 
                    } else {
                        rsort($arr_team);
                    }
                }
                //end sort teams

                $item->teamsName = implode(", ", $arr_team);
                $item->created_date = date('d/m/Y', strtotime($item->created_at));
                if ($item->lastWork) {
                    $item->lastWork_date = date('d/m/Y', strtotime($item->lastWork));
                } else {
                    $item->lastWork_date = 'Chưa làm';
                }
                $item->url = route('sales::welcome', ['id' => $item->id, 'token' => $item->token]);
                $item->urlPreview = route('sales::css.preview', ['id' => $item->id, 'token' => $item->token]);
                $item->rikker_relate = str_replace(',', ', ', $item->rikker_relate);
                $cssResultModel = new CssResult();
                $cssViewModel = new CssView();
                //Check CSS count view to show link
                if ($item->countViewCss >= 1) {
                    $item->hrefView = url('/css/view/' . $item->id);
                }

                //Check CSS count make to show link
                if ($item->countMakeCss == 1) {
                    $cssResultDetail = $cssResultModel->getCssResultFirstByCss($item->id, true);
                    $item->hrefMake = url('/css/detail/' . $cssResultDetail->id);
                } else if ($item->countMakeCss > 1) {
                    $item->hrefMake = url('/css/list/make/' . $item->id);
                }
            }
        }
        $per = new Permission();
        return view(
                'sales::css.list', [
            'css' => $css,
            'isRoot' => $per->isRoot()
                ]
        );
    }

    /**
     * @param null $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function export($year = null)
    {
        if (empty($year)) {
            $year = Carbon::now()->year;
        }

        $result = Css::exportCss($year);
        $resultComment = Css::exportCssComment($year);

        return view('sales::css.export', compact('result', 'resultComment', 'year'));
    }

    public function listMake($cssId, Request $request) {
        Breadcrumb::add(Lang::get('sales::view.Css list result'));
        $css = Css::find($cssId);
        $permissionFlag = CssPermission::isCssPermission($css);
        
        //If hasn't permission
        if(!$permissionFlag){
            return view(
                'core::errors.permission_denied'
            );
        }
        
        //If has permission
        if(count($css)){
            $curPage = $request->input('page'); 
            $pager = Config::getPagerData();
            $cssResultModel = new CssResult();
            $pagerFilter = (array) Form::getFilterPagerData();
            $pagerFilter = array_filter($pagerFilter);
            $filterStatus = Form::getFilterData('except', 'css_result.status');
            if ($pagerFilter) {
                $cssResults = $cssResultModel->getAllCssResul($cssId,$pager['order'], $pager['dir']);
            } else {
                $cssResults = $cssResultModel->getAllCssResul($cssId,'id','desc');
            }
            $cssResults = CoreModel::filterGrid($cssResults);
            $cssResults = CoreModel::pagerCollection($cssResults, $pager['limit'], $curPage);
            if(count($cssResults)){
                foreach($cssResults as &$item){
                    $item->make_date = date('d/m/Y',strtotime($item->created_at));
                }
            }
        }

        

        return view(
            'sales::css.listMake', [
                'cssResults' => $cssResults,
                'css' => $css,
                'statusList' => CssResult::getLabelStatusCssResult(),
                'filterStatus' => !empty($filterStatus) ? $filterStatus : CssResult::getFilterStatusDefault(),
            ]
        );
    }
    
    /**
     * View Css result by Css
     * @param int $cssId
     */
    public function view($cssId, Request $request)
    {
        Breadcrumb::add(Lang::get('sales::view.Css list view'));
        $css = Css::find($cssId);
        $permissionFlag = CssPermission::isCssPermission($css);
        
        //If hasn't permission
        if(!$permissionFlag){
            return view(
                'core::errors.permission_denied'
            );
        }
        
        //If has permission
        if(count($css)){
            $curPage = $request->input('page'); 
            $pager = Config::getPagerData();
            $cssViewModel = new CssView();
            $pagerFilter = (array) Form::getFilterPagerData();
            $pagerFilter = array_filter($pagerFilter);
            if ($pagerFilter) {
                $cssViews = $cssViewModel->getCssViewsByCss($cssId,$pager['order'], $pager['dir']);
            } else {
                $cssViews = $cssViewModel->getCssViewsByCss($cssId,'id','desc');
            }
            
            $cssViews = CoreModel::filterGrid($cssViews);
            $cssViews = CoreModel::pagerCollection($cssViews, $pager['limit'], $curPage);
            if(count($cssViews)){
                foreach($cssViews as &$item){
                    $item->view_date = date('d/m/Y',strtotime($item->created_at));
                }
            }
        }
        
        return view(
            'sales::css.view', [
                'cssViews' => $cssViews,
                'css' => $css,
            ]
        );
    }

    public function exportCss(Request $request)
    {
        $pager = Config::getPagerData();
        $css = CssPermission::getCssListByPermission($pager['order'], $pager['dir']);
        $css = $css->get();
        if(count($css) > 0) {
            $teamModel = new Team();
            foreach ($css as &$item) {
                //Get teams list
                $cssTeams = CssTeams::getCssTeamByCssId($item->id);
                $arr_team = array();
                foreach ($cssTeams as $cssTeamChild) {
                    $team = $teamModel->getTeamWithTrashedById($cssTeamChild->team_id);
                    $arr_team[] = $team->name;
                }
                //end get teams list

                $item->teamsName = implode(", ", $arr_team);
                $item->created_date = date('d/m/Y', strtotime($item->created_at));
                if ($item->lastWork) {
                    $item->lastWork_date = date('d/m/Y', strtotime($item->lastWork));
                } else {
                    $item->lastWork_date = 'Chưa làm';
                }

                sort($arr_team);
                if($pager['order'] === 'team_join.name'){
                    if($pager['dir'] === 'asc'){
                        sort($arr_team);
                    } else {
                        rsort($arr_team);
                    }
                }
            }
        }

        if (!$css) {
            return back()->with('messages', [
                'errors' => [
                    trans('css::view.There are no css currently ongoing  to now'),
                ]
            ]);
        }
        Excel::create('Danh sách Css', function ($excel) use ($css) {
            $excel->sheet('sheet1', function ($sheet) use ($css) {
                $sheet->loadView('sales::css.include.export-css', [
                    'css' => $css
                ]);
            });
        })->export('xlsx');
    }
    
    /**
     * View Css result detail page
     * @param int $resultId
     * @return void
     */
    public function detail($resultId){ 
        Breadcrumb::add(Lang::get('sales::view.Detail.Title'));
        $cssResult = CssResult::find($resultId);
        $css = Css::find($cssResult->css_id);
        $employee = Employee::find($css->employee_id);
        if (!$employee) {
            $employee = new Employee();
        }
        $permissionFlag = CssPermission::isCssPermission($css);
        //If hasn't permission
        if(!$permissionFlag){
            return view(
                'core::errors.permission_denied'
            );
        }
        
        // If has permission, check permission PM, PQA and sales.
        $accessSubmit = false;
        $accessApprove = false;
        $accessReview = false;
        $accessCancel = false;
        
        $empCurrent = Permission::getInstance()->getEmployee();
//        if (!(Permission::getInstance()->isScopeCompany(null, 'sales::insertAnalysisResult')) && !(Project::checkPMAndPQAOfProject($empCurrent->id, $css->projs_id))) {
//            $accessSubmit = false;
//        }

        if (Permission::getInstance()->isScopeCompany(null, 'sales::insertAnalysisResult') && (Project::checkPMAndPQAOfProject($empCurrent->id, $css->projs_id) || Team::checkPqaLeader($empCurrent->id))) {
            $accessSubmit = true;
        }
        if (Permission::getInstance()->isAllow('sales::approveStatusCss')) {
            $accessApprove = true;
        }
        if (Permission::getInstance()->isAllow('sales::reviewStatusCss')) {
            $accessReview = true;
        }
        if (Permission::getInstance()->isAllow('sales::cancelCssResult')) {
            $accessCancel = true;
        }

        $cssCategoryModel = new CssCategory();
        $cssQuestionModel = new CssQuestion();
        $cssResultDetailModel = new CssResultDetail();
        
        $cssOld = $css->created_at < Css::CSS_TIME;
        $cssCate = self::getCssDetailPoint($css->project_type_id,$resultId,$cssResult->code);
        $rootCategory = $cssCategoryModel->getRootCategoryV2($css->project_type_id, $css->id, $cssResult->code, $css->created_at, $css->lang_id);
        $questLangId = CView::checkLangOvvQuestion($css->project_type_id, $css->lang_id, $css->created_at);
        $overviewQuestion = $cssQuestionModel->getOverviewQuestionByCategory($rootCategory->id, 1, $questLangId);
        $resultDetailRow = $cssResultDetailModel->getResultDetailRowOfOverview($resultId, $rootCategory->id);
        $resultDetailCss = $cssResultDetailModel->getResultDetailByCssResult($resultId);
        $projectOfCss = Project::getProjectById($css->projs_id);
        return view(
            'sales::css.detail', [
                'css' => $css,
                "employee" => $employee,
                "cssCate" => $cssCate,
                "cssResult" => $cssResult,
                "noOverView" => View::romanic_number(count($cssCate)+1,true),
                "overviewQuestionId" => $overviewQuestion->id,
                "overviewQuestionContent" => $overviewQuestion->content,
                "overviewQuestionExplain" => $overviewQuestion->explain,
                "resultDetailRowOfOverview" => $resultDetailRow,
                'projectOfCss' => $projectOfCss,
                'accessSubmit' => $accessSubmit,
                'accessApprove' => $accessApprove,
                'accessCancel' => $accessCancel,
                'accessReview' => $accessReview,
                'resultDetailCss' => $resultDetailCss,
                'cssOld' => $cssOld,
            ]
        );
    }
    
    public function exportExcel($resultId){
        $model = new Css();
        $cssCategoryModel = new CssCategory();
        $cssQuestionModel = new CssQuestion();
        $cssResultDetailModel = new CssResultDetail();
        
        $cssResult = CssResult::find($resultId);
        $projectInfo = $model->projectMakeInfo($resultId);
        $cssCate = self::getCssDetailPoint($projectInfo->project_type_id,$resultId,$cssResult->code);
        $lang = SupportConfig::get('langs.'.$projectInfo->lang_id);
        if ($lang == null) {
            $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
        }

        $cssOld = false;
        if ($projectInfo->created_at < Css::CSS_TIME) {
            $cssOld = true;
            if ($lang == 'vi' && $projectInfo->project_type_id != Css::TYPE_ONSITE) {
                $lang = SupportConfig::get('langs.'.Css::ENG_LANG);
            }
        }
        $rootCategory = $cssCategoryModel->getRootCategoryV2($projectInfo->project_type_id, $projectInfo->id, $cssResult->code, $projectInfo->created_at, $projectInfo->lang_id);
        $questLangId = CView::checkLangOvvQuestion($projectInfo->project_type_id, $projectInfo->lang_id, $projectInfo->created_at);
        $overviewQuestion = $cssQuestionModel->getOverviewQuestionByCategory($rootCategory->id, 1, $questLangId);
        $resultDetailRow = $cssResultDetailModel->getResultDetailRowOfOverview($resultId, $rootCategory->id);

        $dataInfo[] = array(Lang::get('sales::view.Welcome title',[],$lang),'','','',self::formatNumber($projectInfo->point) . ' ' . Lang::get('sales::view.Total point excel',[],$lang));
        //Project Info data
        if(trim($projectInfo->project_name_css) != null) { 
            $projectExport = $projectInfo->project_name_css; 
        }  else { 
            $projectExport = $projectInfo->project_name; 
        }
        $rowsInfoHead = count($dataInfo) + 1; //Get Row header of Project Info table 
        $rowsInfoHead = count($dataInfo) + 1; //Get Row header of Project Info table
        $dataInfo[] = array(Lang::get('sales::view.Project information',[],$lang));
        $dataInfo[] = array(Lang::get('sales::view.Project name jp',[],$lang), $projectExport , Lang::get('sales::view.Sale name jp',[],$lang),'', $projectInfo->sale_name_jp);
        $pmOrOnsiterName = $projectInfo->type == Project::TYPE_ONSITE ? Lang::get('sales::view.Onsiter',[],'',$lang) : Lang::get('sales::view.PM name jp',[],$lang);
        $dataInfo[] = array(Lang::get('sales::view.Customer company name jp',[],$lang), $projectInfo->company_name.Lang::get('sales::view.men jp',[],$lang), $pmOrOnsiterName,'', $projectInfo->pm_name_jp);
        $dataInfo[] = array(Lang::get('sales::view.Make name jp',[],$lang), $projectInfo->make_name.Lang::get('sales::view.men jp',[],$lang), Lang::get('sales::view.Project date jp',[],$lang),'', date("d/m/Y",strtotime($projectInfo->start_date)) . ' - ' . date("d/m/Y",strtotime($projectInfo->end_date)));
        
        
        //Point Detail data
        $rowsCateLv2 = [];
        if(count($cssCate)){
            $rowSpace = count($dataInfo) + 1; //Get Row space
            $dataInfo[] = array('','','',''); 
            $rowsDetailHead = count($dataInfo) + 1; //Get Row header of Point Detail table
            $dataInfo[] = array(Lang::get('sales::view.Question',[],$lang),'',Lang::get('sales::view.Rating',[],$lang),Lang::get('sales::view.Comment',[],$lang),'');
            
            foreach($cssCate as $item){
                $rowsCateLv1[] = count($dataInfo) + 1; //Get rows Categories lv 1
                $dataInfo[] = array($item["sort_order"] . ". " .$item['name'],''); //Category lv1 with I, II, ... 
                
                if($item['cssCateChild']){
                    foreach($item['cssCateChild'] as $itemChild){
                        $rowsCateLv2[] = count($dataInfo) + 1; //Get rows Categories lv 2
                        $dataInfo[] = array($itemChild["sort_order"] . ". " .$itemChild['name'],$itemChild["question_explanation"]); //Category lv2 with 1, 2, ... 
                        if($itemChild['questionsChild']){
                            foreach($itemChild['questionsChild'] as $questionChild){
                                $rowsQuestion[] = count($dataInfo) + 1; //Get rows questions
                                $dataInfo[] = array($questionChild->sort_order . ". " .$questionChild->content,'',$questionChild->point,$questionChild->comment,'');
                            }
                        }
                    }
                }elseif($item['questions']){
                    foreach($item['questions'] as $question){
                        $rowsQuestion[] = count($dataInfo) + 1; //Get rows questions
                        $dataInfo[] = array($question->sort_order . ". " .$question->content,'',$question->point,$question->comment,'');
                    }
                }
            }
        }
        $cateEnd = Lang::get('sales::view.General',[],$lang);
        if ($cssOld) {
            if ($lang == "ja" || $projectInfo->project_type_id == 5) {
                $questionEnd = $overviewQuestion->content;
            } else {
                $questionEnd = Lang::get('sales::view.OverviewQuestionContent OSDC',[],$lang);
            }
        } else {
            $questionEnd = $overviewQuestion->content;
        }
        
        $rowsCateLv1[] = count($dataInfo) + 1;
        $dataInfo[] = array(View::romanic_number(count($cssCate)+1) . ". " .$cateEnd,'','','','');
        $rowsQuestion[] = count($dataInfo) + 1;
        $dataInfo[] = array($questionEnd,'',$resultDetailRow->point,$resultDetailRow->comment,'');
        //Proposed
        // $rowProposed = count($dataInfo) + 1;
        // $dataInfo[] = array(Lang::get('sales::view.Proposed excel',[],$lang),'',$projectInfo->proposed);
        // dd($dataInfo);
        
        //Storage rows
        $rows = [
            'rowsInfoHead'      => $rowsInfoHead, 
            'rowsDetailHead'    => $rowsDetailHead,
            'rowsCateLv1'       => $rowsCateLv1,
            'rowsCateLv2'       => $rowsCateLv2,
            'rowsQuestion'      => $rowsQuestion,
            'rowSpace'          => $rowSpace,
            // 'rowProposed'       => $rowProposed,
        ];
        
        //Set color 
        if($projectInfo->project_type_id === 1 || $projectInfo->project_type_id === 5) {
            $headerColor = '#2b98d4';
            $rootCateColor = '#58c1ef';
        } else {
            $headerColor = '#43a047';
            $rootCateColor = '#82c785';
        }
        $color = [
            'headerColor'    => $headerColor,
            'rootCateColor'  => $rootCateColor,
        ];
        
        //Export excel
        Excel::create('Rikkeisoft_CSS_'.$projectInfo->customer_name.Lang::get('sales::view.men jp_',[],$lang).$projectInfo->project_name.'_'.date('Ymd',  strtotime($projectInfo->make_date)), function($excel)  use($dataInfo,$rows,$color){
            $excel->sheet('CSS information', function($sheet)  use($dataInfo,$rows,$color){
                $sheet->setVerticalCentered(true);
                $sheet->fromArray($dataInfo, null, 'A1', false, false);
                $sheet->setPageMargin(25);
                // $sheet->setBorder('A1:E'.$rows['rowProposed'], 'thin');
                
                // Set width for multiple cells
                $sheet->setWidth(array(
                    'A'     =>  50,
                    'B'     =>  50,
                    'C'     =>  10,
                    'D'     =>  30,
                    'E'     =>  40,
                ));
                
                //Set valign and height all rows
                $countData = count($dataInfo);
                for($i=1; $i<=$countData+1;$i++){
                    $sheet->row($i, function($row) {
                        $row->setValignment('center');
                    });
                    $sheet->setHeight($i,30);
                }
                
                $sheet->mergeCells('A1:D1');
                $sheet->setHeight(1,60);
                $sheet->row(1, function($row) {
                    $row->setAlignment('center');
                    $row->setFontSize(30);
                });
                
                /**
                 * Project Info table
                 */
                
                $sheet->mergeCells('A'.$rows['rowsInfoHead'].':E'.$rows['rowsInfoHead']);
                
                //Merge column C and D Project Info table
                for($i=$rows['rowsInfoHead']+1;$i<=$rows['rowsInfoHead']+4;$i++){
                    $sheet->mergeCells('C'.$i.':D'.$i);
                }
                
                //Set style row Project Information header
                $sheet->row($rows['rowsInfoHead'], function($row) use ($color) {
                    $row->setBackground($color['headerColor']);
                    $row->setFontColor('#ffffff');
                    $row->setFontWeight('bold');
                });
                
                /**
                 * End Project Info table
                 */
                
                //Merge A to E row space
                $sheet->mergeCells('A'.$rows['rowSpace'].':E'.$rows['rowSpace']);
                
                /**
                 * Point Detail table
                 */
                
                //Merge A and B, D and E Point Detail header
                $sheet->mergeCells('A'.$rows['rowsDetailHead'].':B'.$rows['rowsDetailHead']);
                $sheet->mergeCells('D'.$rows['rowsDetailHead'].':E'.$rows['rowsDetailHead']);
                
                //Set style row Point Detail header
                $sheet->row($rows['rowsDetailHead'], function($row) use ($color) {
                    $row->setBackground($color['headerColor']);
                    $row->setFontColor('#ffffff');
                    $row->setFontWeight('bold');
                    $row->setAlignment('center');
                });
                
                //Category lv1 style
                foreach($rows['rowsCateLv1'] as $k => $rowNum){
                    $sheet->mergeCells('A'.$rowNum.':E'.$rowNum);
                    $sheet->row($rowNum, function($row) use ($color) {
                        $row->setBackground($color['rootCateColor']);
                        $row->setFontColor('#ffffff');
                        $row->setFontWeight('bold');
                    });
                }
                
                //Category lv2 style
                foreach($rows['rowsCateLv2'] as $k => $rowNum){
                    $sheet->row($rowNum, function($row) {
                        $row->setFontWeight('bold');
                    });
                    
                    $sheet->mergeCells('B'.$rowNum.':E'.$rowNum);
                }
                
                //Merge column A and B, D and E of questions rows in Point Detail table
                foreach($rows['rowsQuestion'] as $k => $rowNum){
                    $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
                    $sheet->mergeCells('D'.$rowNum.':E'.$rowNum);
                    
                    //Set text align for rating column
                    $sheet->cells('C'.$rowNum, function($cells) {
                        $cells->setAlignment('center');
                        
                    });
                }
                
                //Merge column D and C, D and E proposed row
                // $sheet->mergeCells('A'.$rows['rowProposed'].':B'.$rows['rowProposed']);
                // $sheet->mergeCells('C'.$rows['rowProposed'].':E'.$rows['rowProposed']);
                
                //Set height proposed row
                // $sheet->setHeight($rows['rowProposed'],150);
            });
            
            //Set wrap text
            // $excel->getActiveSheet()->getStyle('A1:E'.$rows['rowProposed'])->getAlignment()->setWrapText(true); 
        })->export('xls');
    }
    
    public function getCssDetailPoint($projectTypeId,$resultId,$code){
        $cssCategoryModel = new CssCategory();
        $cssQuestionModel = new CssQuestion();
        $cssResultDetailModel = new CssResultDetail();
        $cssModel = new Css();

        $css = $cssModel->getCssByResultId($resultId);
        $cssLang = $css->lang_id;
        $rootCategory = $cssCategoryModel->getRootCategoryV2($css->project_type_id, $css->id, $code, $css->created_at, $css->lang_id);
        $cssCategory = $cssCategoryModel->getCategoryByParent($rootCategory->id,$cssLang,$css->created_at,$css->project_type_id);
        $cssCate = [];
        if($cssCategory){
            $NoOverView = 0;
            foreach($cssCategory as $item){
                $NoOverView++;
                $cssCategoryChild = $cssCategoryModel->getCategoryByParent($item->id,$cssLang,$css->created_at,$css->project_type_id); 
                $cssCateChild = array();
                if ($cssCategoryChild) {
                    foreach ($cssCategoryChild as $itemChild) {
                        $cssQuestionChild = $cssQuestionModel->getQuestionByCategory($itemChild->id);
                        $questionsChild = array();
                        foreach($cssQuestionChild as &$question){
                            $resultDetailRow = $cssResultDetailModel->getResultDetailRow($resultId, $question->id);
                            if($resultDetailRow){
                                $question->point = $resultDetailRow->point;
                                $question->comment = $resultDetailRow->comment;
                                $question->analysis = $resultDetailRow->analysis;
                                $questionsChild[] = $question;
                            }
                        }
                        $cssCateChild[] = array(
                            "id" => $itemChild->id,
                            "name" => $itemChild->name,
                            "sort_order" => $itemChild->sort_order,
                            "parent_id" => $item->id,
                            "questionsChild" => $questionsChild,
                            "show_brse_name" => $itemChild->show_brse_name,
                            "show_pm_name" => $itemChild->show_pm_name,
                            "question_explanation" => $itemChild->question_explanation,
                        );
                    }
                }

                $cssQuestion = $cssQuestionModel->getQuestionByCategory($item->id);
                $questions = array();
                foreach($cssQuestion as $question){
                    $resultDetailRow = $cssResultDetailModel->getResultDetailRow($resultId, $question->id);
                    if($resultDetailRow){
                        $question->point = $resultDetailRow->point;
                        $question->comment = $resultDetailRow->comment;
                        $question->analysis = $resultDetailRow->analysis;
                        $questions[] = $question;
                    }
                }

                $cssCate[] = array(
                    "id" => $item->id,
                    "name" => $item->name,
                    "sort_order" => View::romanic_number($item->sort_order,true),
                    "cssCateChild" => $cssCateChild,
                    "questions" => $questions,
                    "noCate" => $NoOverView,
                    "show_brse_name" => $item->show_brse_name,
                    "show_pm_name" => $item->show_pm_name,
                );
            }
        }
        
        return $cssCate;
    }
    
    /**
     * Make CSS success to this page
     * @param int $cssId
     * @return void
     */
    public function success(Request $request){
        if($request->input('lang'))
            $langCode = $request->input('lang');
        else $langCode = Css::JAP_LANG;
        return view('sales::css.success',compact('langCode')); 
    }
    
    /**
     * Cancel make CSS
     * @return void
     */
    public function cancelMake(){
        return view(
            'sales::css.cancel', []
        );
    }

    public function cancelCss(Request $request)
    {
        $css = Css::cancelCssById($request);
        return response()->json($css);
    }
    
    /**
     * CSS analyze page
     * @return void
     */
    public function analyze(){
        Breadcrumb::add(Lang::get('sales::view.Analyze.Title'));
        $htmlTeam = self::getTreeDataRecursive(null, 0, null);
        $projectType = CssProjectType::all();
        return view(
            'sales::css.analyze', [
                'htmlTeam'     => $htmlTeam,
                'projectType' => $projectType,
                'startDateDefault' => Carbon::now()->firstOfMonth()->format('Y-m-d'),
                'endDateDefault' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            ]
        );
    }
    
    /**
     * Apply event in CSS analyze page
     * @param string projectTypeIds
     * @param datetime $startDate
     * @param datetime $endDate
     * return json
     */
    public function applyAnalyze(Request $request){
        $projectTypeIds = $request->input("projectTypeIds"); 
        $startDate = $request->input("startDate");
        $endDate = $request->input("endDate");
        $teamIds = $request->input("teamIds"); 
        $criteriaType = $request->input("criteriaType"); 
        $criteriaIds = $request->input("criteriaIds"); 
        
        //lay thong tin hien ket qua danh sach du an
        $data = [];
        switch ($criteriaType){
            case 'tcProjectType':
                $data = self::applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,'projectType'); 
                break;
            case 'tcProjectName':
                $data = self::applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,'projectName');
                break;
            case 'tcTeam':
                $data = self::applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,'team');
                break;
            case 'tcPm':
                $data = self::applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,'pm');
                break;
            case 'tcCustomer':
                $data = self::applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,'customer');
                break;
            case 'tcSale':
                $data = self::applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,'sale');
                break;
            case 'tcQuestion':
                $data = self::applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,'question');
                break;
        }
        
        return response()->json($data);
    }
    
    /**
     * @param string $criteriaIds
     * @param string $projectTypeIds
     * @param string $startDate
     * @param string $endDate
     * @param string $teamIds
     * @return array
     */
    protected function applyByFilter($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,$criteria){
        $cssResultModel = new CssResult();
        if($criteria == 'projectType'){
            $cssResult = CssPermission::getAnalyzeByProjectType($criteriaIds, $startDate, $endDate,$teamIds);
        } else if ($criteria == 'projectName') {
            $cssResult = CssPermission::getAnalyzeByProjectName($criteriaIds, $projectTypeIds, $startDate, $endDate, $teamIds);
        } else if($criteria == 'team'){
            $cssResult = CssPermission::getAnalyzeByProjectType($projectTypeIds, $startDate, $endDate,$criteriaIds);
        }else if($criteria == 'pm'){
            $cssResult = CssPermission::getAnalyzeByPm($criteriaIds,$projectTypeIds, $startDate, $endDate,$teamIds);
        }else if($criteria == 'customer'){
            $cssResult = CssPermission::getAnalyzeByCustomer($criteriaIds,$projectTypeIds, $startDate, $endDate,$teamIds);
        }else if($criteria == 'sale'){
            $cssResult = CssPermission::getAnalyzeBySale($criteriaIds,$projectTypeIds, $startDate, $endDate,$teamIds);
        }
        
        if(count($cssResult)){
            //display chart all result
            $allResultChart = [];

            //cssResultIds list
            $cssResultIds = [];

            //piechart point
            $piechartPoint = array(
                '>=90' => 0,
                '<90' => 0,
            );
            $total = 0;
            //Get data chart all result
            foreach($cssResult as $itemResult){
                $cssResultIds[] = $itemResult->id;
                $point = (float)self::formatNumber($itemResult->avg_point);
                $allResultChart[] = [
                    'date'  => $itemResult->end_date,
                    'point' => $point,
                ];
                if ($point >= 90) {
                    $piechartPoint['>=90'] += 1;
                } else {
                    $piechartPoint['<90'] += 1;
                }
                $total++;
            }
            $piechartLabel = [
                '>=90' => '>=90: (' . ceil(($piechartPoint['>=90'] / $total) * 100) . '%)',
                '<90' => '<90: (' . (100 - (int)ceil(($piechartPoint['>=90'] / $total) * 100)) . '%)',
            ];
            $strResultIds = implode(",", $cssResultIds);
            //Get data fill to table project list 
            $cssResultPaginate = self::showAnalyzeListProject($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,$criteria,1,'css.end_date','desc');

            //Get data fill to compare charts in analyze page
            $compareChart = self::getCompareCharts($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,$criteria);

            //Get data fill to table criteria less 3 star
            $lessThreeStar = self::getListLessThreeStar($strResultIds,1,'result_make','desc');

            //Get data fill to table customer's proposes
            $proposes = self::getProposes($strResultIds,1,'result_make','desc');

            $htmlQuestionList = "<option value='0'>".Lang::get('sales::view.Please choose question')."</option>";

            $data = [
                "cssResult" => $cssResult,
                "cssResultPaginate" => $cssResultPaginate,
                "allResultChart" => $allResultChart,
                "piechartPoint" => $piechartPoint,
                "piechartLabel" => $piechartLabel,
                "compareChart" => $compareChart,
                "lessThreeStar" =>$lessThreeStar,
                "proposes" => $proposes,
                "htmlQuestionList" => $htmlQuestionList,
                "strResultIds" => $strResultIds,
            ];
        }else{
            $data = [];
        }
        
        
        return $data;
    }
    
    /**
     * Get data fill to table project list in analyze page
     * @param string $criteriaIds
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param string $startDate
     * @param string $endDate
     * @param string $criteria
     * @param int $curPage
     * @return object list
     */
    public function showAnalyzeListProject(
        $criteriaIds,
        $teamIds,
        $projectTypeIds,
        $startDate,
        $endDate,
        $criteria,
        $curPage,
        $orderBy,
        $ariaType
    ){
        $teamModel = new Team();
        Paginator::currentPageResolver(function () use ($curPage) {
            return $curPage;
        });
        $filter = Input::get('filter');
        $filterTeamName = null;
        if ($filter) {
            $filterConvertKey = [
                'project_name' => 'css.project_name',
                'content' => 'css_question.content',
                'point' => 'css_result_detail.point',
                'comment' => 'css_result_detail.comment',
                'created_at' => 'css_result.created_at',
                'avg_point' => 'css_result.avg_point',
                'name' => 'teams.name',
                'pm_name' => 'css.pm_name',
                'end_date' => 'css.end_date',
                'result_point' => 'css_result.avg_point'
            ];
            parse_str($filter,$filterOutput);
            $filterOutputClone = $filterOutput;
            foreach ($filterOutputClone as $filterKey => $filterValue) {
                if (isset($filterConvertKey[$filterKey])) {
                    $filterOutput[$filterConvertKey[$filterKey]] = $filterValue;
                    unset($filterOutput[$filterKey]);
                }
            }
            $filter = $filterOutput;
        }
        if($criteria == 'projectType'){
            //all result to show charts
            //$cssResult = CssPermission::getAnalyzeByProjectType($criteriaIds, $startDate, $endDate,$teamIds);
            //result by pagination
            $cssResultPaginate = CssPermission::getAnalyzePaginateByProjectType(
                $criteriaIds, 
                $startDate, 
                $endDate,
                $teamIds,
                self::$perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        }else if($criteria == 'team'){
            //$cssResult = CssPermission::getAnalyzeByProjectType($projectTypeIds, $startDate, $endDate,$criteriaIds);
            $cssResultPaginate = CssPermission::getAnalyzePaginateByProjectType(
                $projectTypeIds, 
                $startDate, 
                $endDate,
                $criteriaIds,
                self::$perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        }else if($criteria == 'projectName'){
            //$cssResult = CssPermission::getAnalyzeByPm($criteriaIds,$projectTypeIds, $startDate, $endDate,$teamIds);
            $cssResultPaginate = CssPermission::getAnalyzePaginateByProjectName(
                $criteriaIds,
                $projectTypeIds,
                $startDate,
                $endDate,
                $teamIds,
                self::$perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        }else if($criteria == 'pm'){
            //$cssResult = CssPermission::getAnalyzeByPm($criteriaIds, $projectTypeIds, $startDate, $endDate, $teamIds);
            $cssResultPaginate = CssPermission::getAnalyzePaginateByPm(
                $criteriaIds,
                $projectTypeIds, 
                $startDate, 
                $endDate,
                $teamIds,
                self::$perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        }else if($criteria == 'customer'){
            //$cssResult = CssPermission::getAnalyzeByCustomer($criteriaIds,$projectTypeIds, $startDate, $endDate,$teamIds);
            $cssResultPaginate = CssPermission::getAnalyzePaginateByCustomer(
                $criteriaIds,
                $projectTypeIds, 
                $startDate, 
                $endDate,
                $teamIds,
                self::$perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        }else if($criteria == 'sale'){
            //$cssResult = CssPermission::getAnalyzeBySale($criteriaIds,$projectTypeIds, $startDate, $endDate,$teamIds);
            $cssResultPaginate = CssPermission::getAnalyzePaginateBySale(
                $criteriaIds,
                $projectTypeIds, 
                $startDate, 
                $endDate,
                $teamIds,
                self::$perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        }else if($criteria == 'question'){
            //$cssResult = CssPermission::getAnalyzeByQuestion($criteriaIds,$projectTypeIds, $startDate, $endDate,$teamIds);
            $cssResultPaginate = CssPermission::getAnalyzePaginateByQuestion(
                $criteriaIds,
                $projectTypeIds, 
                $startDate, 
                $endDate,
                $teamIds,
                self::$perPage,
                $orderBy,
                $ariaType,
                $filter
            );
        }
        
        $offset = ($cssResultPaginate->currentPage()-1) * $cssResultPaginate->perPage() + 1;
        foreach($cssResultPaginate as &$itemResultPaginate){
            //get teams name of Css result
            $teamName = "";
            $team = DB::table("css_team")->where("css_id",$itemResultPaginate->css_id)->get();
            $arrTeam = [];
            foreach($team as $teamId){
                $team = $teamModel->getTeamWithTrashedById($teamId->team_id);
                $arrTeam[] = $team->name;
            }
            
            sort($arrTeam);
            if($orderBy === 'teams.name'){
                if($ariaType === 'desc'){
                    rsort($arrTeam);
                }
            }

            $teamName = implode(', ', $arrTeam);
            //end get teams name
            
            $itemResultPaginate->stt = $offset++;
            $itemResultPaginate->teamName = $teamName;
            $itemResultPaginate->css_end_date = date('d/m/Y',strtotime($itemResultPaginate->end_date));
            $itemResultPaginate->css_result_created_at = date('d/m/Y',strtotime($itemResultPaginate->created_at));
            $itemResultPaginate->point = self::formatNumber($itemResultPaginate->avg_point);
        }
        
        //Get html pagination render
        $totalPage = ceil($cssResultPaginate->total() / $cssResultPaginate->perPage());
        $html = "";
        if($totalPage > 1){
            if($curPage == 1){
                $html .= '<li class="disabled"><span>«</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="showAnalyzeListProject('.($curPage-1).',\''.Session::token().'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="back">«</a></li>';
            }
            for($i=1; $i<=$totalPage; $i++){
                if($i == $curPage){
                    $html .= '<li class="active"><span>'.$i.'</span></li>';
                }else{
                    $html .= '<li><a href="javascript:void(0)" onclick="showAnalyzeListProject('.$i.',\''.Session::token().'\',\''.$orderBy.'\',\''.$ariaType.'\');">'.$i.'</a></li>';
                }
            }
            if($curPage == $totalPage){
                $html .= '<li class="disabled"><span>»</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="showAnalyzeListProject('.($curPage+1).',\''.Session::token().'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="next">»</a></li>';
            }
        }
        
        return $data = [
            "cssResultdata" => $cssResultPaginate,
            "paginationRender"  => $html,
        ];
    }
    
    /**
     * Get list less three star by cssResultIds
     * @param array $cssResultIds
     */
    protected function getListLessThreeStar($cssResultIds,$curPage,$orderBy,$ariaType)
    {
        Paginator::currentPageResolver(function () use ($curPage) {
            return $curPage;
        });
        $filter = Input::get('filter');
        if ($filter) {
            $filterConvertKey = [
                'project_name' => 'css.project_name',
                'content' => 'css_question.content',
                'point' => 'css_result_detail.point',
                'comment' => 'css_result_detail.comment',
                'created_at' => 'css_result.created_at',
                'avg_point' => 'css_result.avg_point'
                
            ];
            parse_str($filter,$filterOutput);
            $filterOutputClone = $filterOutput;
            foreach ($filterOutputClone as $filterKey => $filterValue) {
                if (isset($filterConvertKey[$filterKey])) {
                    unset($filterOutput[$filterKey]);
                    $filterOutput[$filterConvertKey[$filterKey]] = $filterValue;
                }
            }
            $filter = $filterOutput;
        }
        $lessThreeStar = Css::getListLessThreeStar($cssResultIds,self::$perPage,$orderBy,$ariaType, $filter);
        $offset = ($lessThreeStar->currentPage()-1) * $lessThreeStar->perPage() + 1;
        $result = [];
        foreach($lessThreeStar as $item){
            $result[] = [
                "no"   => $offset++,
                "projectName"   => $item->project_name,
                "questionName" => $item->question_name,
                "stars" => $item->point,
                "comment"   => $item->comment,
                "makeDateCss" => date('d/m/Y',strtotime($item->result_make)),
                "cssPoint" => self::formatNumber($item->result_point),
            ];
        }
        //Get html pagination render
        $count = $lessThreeStar->total();
        $totalPage = ceil($count / $lessThreeStar->perPage());
        $html = "";
        if($totalPage > 1){
            if($curPage == 1){
                $html .= '<li class="disabled"><span>«</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getListLessThreeStar('.($curPage-1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="back">«</a></li>';
            }
            for($i=1; $i<=$totalPage; $i++){
                if($i == $curPage){
                    $html .= '<li class="active"><span>'.$i.'</span></li>';
                }else{
                    $html .= '<li><a href="javascript:void(0)" onclick="getListLessThreeStar('.$i.',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');">'.$i.'</a></li>';
                }
            }
            if($curPage == $totalPage){
                $html .= '<li class="disabled"><span>»</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getListLessThreeStar('.($curPage+1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="next">»</a></li>';
            }
        }
        
        $data = [
            "cssResultdata" => $result,
            "paginationRender" => $html,
        ];
        
        return $data;
    }
    
    /**
     * get customer's proposes
     * @param array $cssResultIds
     * @param int $curPage
     */
    protected function getProposes($cssResultIds,$curPage,$orderBy,$ariaType){
        Paginator::currentPageResolver(function () use ($curPage) {
            return $curPage;
        });
        $filter = Input::get('filter');
        if ($filter) {
            $filterConvertKey = [
                'project_name' => 'css.project_name',
                'proposed' => 'css_result.proposed',
                'result_make' => 'css_result.created_at',
                'result_point' => 'css_result.avg_point'
            ];
            parse_str($filter,$filterOutput);
            $filterOutputClone = $filterOutput;
            foreach ($filterOutputClone as $filterKey => $filterValue) {
                if (isset($filterConvertKey[$filterKey])) {
                    unset($filterOutput[$filterKey]);
                    $filterOutput[$filterConvertKey[$filterKey]] = $filterValue;
                }
            }
            $filter = $filterOutput;
        }
        $proposes = Css::getProposes($cssResultIds,self::$perPage,$orderBy,$ariaType, $filter);
        $offset = ($proposes->currentPage()-1) * $proposes->perPage() + 1;
        $result =[];
        foreach($proposes as $propose){
            $result[] = [
                "no"   => $offset++,
                "cssPoint"   => self::formatNumber($propose->avg_point),
                "projectName"   => $propose->project_name,
                "customerComment" => nl2br($propose->proposed),
                "makeDateCss" => date('d/m/Y',strtotime($propose->created_at)),
            ];
        }
        //Get html pagination render
        $count = $proposes->total();
        $totalPage = ceil($count / $proposes->perPage());
        $html = "";
        if($totalPage > 1){
            if($curPage == 1){
                $html .= '<li class="disabled"><span>«</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getProposes('.($curPage-1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="back">«</a></li>';
            }
            for($i=1; $i<=$totalPage; $i++){
                if($i == $curPage){
                    $html .= '<li class="active"><span>'.$i.'</span></li>';
                }else{
                    $html .= '<li><a href="javascript:void(0)" onclick="getProposes('.$i.',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');">'.$i.'</a></li>';
                }
            }
            if($curPage == $totalPage){
                $html .= '<li class="disabled"><span>»</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getProposes('.($curPage+1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="next">»</a></li>';
            }
        }
        
        $data = [
            "cssResultdata" => $result,
            "paginationRender" => $html,
        ];
        return $data;
    }
    
    /**
     * Get data fill to compare charts in analyze page
     * @param string $criteriaIds
     * @param string $teamIds
     * @param string $projectTypeIds
     * @param string $startDate
     * @param string $endDate
     * @param string $criteria
     * @return array list
     */
    public function getCompareCharts($criteriaIds,$teamIds,$projectTypeIds,$startDate,$endDate,$criteria){
        $cssResultModel = new CssResult();
        $cssResultDetailModel = new CssResultDetail();
        $criteriaIds = explode(",", $criteriaIds);
        $teamModel = new Team();
        $employeeModel = new Employee();
        
        $pointCompareChart = array();
        foreach($criteriaIds as $key => $criteriaId){
            if($criteria == 'projectType'){
                $name = self::getProjectTypeNameById($criteriaId);
                $cssResultByCriteria = CssPermission::getCompareChartByProjectType($criteriaId,$startDate,$endDate,$teamIds);
            }else if($criteria == 'projectName'){
                $name = $criteriaId;
                $cssResultByCriteria = CssPermission::getCompareChartByProjectName($criteriaId,$teamIds,$startDate,$endDate,$projectTypeIds);
            }else if($criteria == 'team'){
                $team = $teamModel->getTeamWithTrashedById($criteriaId);
                $name = $team->name;
                $cssResultByCriteria = CssPermission::getCompareChartByTeam($criteriaId,$startDate,$endDate,$projectTypeIds);
            }else if($criteria == 'pm'){
                $name = $criteriaId;
                $cssResultByCriteria = CssPermission::getCompareChartByPm($criteriaId,$teamIds,$startDate,$endDate,$projectTypeIds);
            }else if($criteria == 'customer'){
                $name = $criteriaId;
                $cssResultByCriteria = CssPermission::getCompareChartByCustomer($criteriaId,$teamIds,$startDate,$endDate,$projectTypeIds);
            }else if($criteria == 'sale'){
                $employee = $employeeModel::find($criteriaId);
                $name = $employee->name;
                $cssResultByCriteria = CssPermission::getCompareChartBySale($criteriaId,$teamIds,$startDate,$endDate,$projectTypeIds);
            }else if($criteria == 'question'){
                $question = CssQuestion::find($criteriaId);
                $name = $question->content;
                $cssResultByCriteria = CssPermission::getCompareChartByQuestion($criteriaId,$teamIds,$startDate,$endDate,$projectTypeIds);
            }
            
            $pointToHighchart = [];
            $pointToHighchart["data"] = [];
            
            if($criteria == 'question'){
                //get root category
                $model = new CssCategory();
                $cate = $model->getCateByQuestion($criteriaId);
                if($cate->parent_id == 0){
                    $rootCate = $cate;
                }else{
                    $rootCate = self::getRootCateByCate($cate);
                }
                //end get root category
                
                $question = CssQuestion::find($criteriaId);
                if($question->is_overview_question == 0){
                    $pointToHighchart["name"] = $rootCate->name . '.' . $question->sort_order;
                }else{
                    $pointToHighchart["name"] = $rootCate->name . '.' . Lang::get('sales::view.Overview question');
                }
                foreach($cssResultByCriteria as $itemCssResult){
                    $css_result_detail = $cssResultDetailModel->getResultDetailRow($itemCssResult->id,$criteriaId);
                    if($css_result_detail->point > 0){
                        $pointToHighchart["data"][] = [
                            'date'  => $itemCssResult->end_date,
                            'point' => (float)self::formatNumber($css_result_detail->point),
                        ];
                    }
                }
            }else{
                $pointToHighchart["name"] = $name;
                foreach($cssResultByCriteria as $item){
                    $pointToHighchart["data"][] = [
                        'date'  => $item->end_date,
                        'point' => (float)self::formatNumber($item->avg_point),
                    ];
                }
            }
            $pointCompareChart[] = [
                "name" => $pointToHighchart["name"],
                "data" => $pointToHighchart["data"]
            ];
        }
        
        return $pointCompareChart;
    }
    
    /**
     * trang phan tich css, thuc hien B1. Filter
     * @param string startDate
     * @param string endDate
     * @param string projectTypeIds
     */
    public function filterAnalyze(Request $request){
        $startDate = $request->input("startDate");
        $endDate = $request->input("endDate");
        $projectTypeIds = $request->input("projectTypeIds"); 
        $teamIds = $request->input("teamIds"); 

        $result["projectType"] = self::filterAnalyzeByProjectType($startDate, $endDate, $projectTypeIds,$teamIds);
        $result["projectName"] = self::filterAnalyzeByPmOrBrseOrCustomerOrSale($startDate, $endDate, $projectTypeIds,$teamIds, 'projectName');
        $result["team"] = self::filterAnalyzeByTeam($startDate, $endDate, $projectTypeIds,$teamIds);
        $result["pm"] = self::filterAnalyzeByPmOrBrseOrCustomerOrSale($startDate, $endDate, $projectTypeIds,$teamIds,'pm');
        
        $result["customer"] = self::filterAnalyzeByPmOrBrseOrCustomerOrSale($startDate, $endDate, $projectTypeIds,$teamIds,'customer');        
        $result["sale"] = self::filterAnalyzeByPmOrBrseOrCustomerOrSale($startDate, $endDate, $projectTypeIds,$teamIds,'sale');        
        //$result["question"] = self::filterAnalyzeByQuestion($startDate, $endDate, $projectTypeIds,$teamIds);        

        return response()->view('sales::css.include.table_criterias', $result);
    }
    
    /**
     * show data filter by project type
     * @param string $startDate
     * @param string $endDate
     * @param string $projectTypeIds
     * @param string $teamIds
     * return array
     */
    protected function filterAnalyzeByProjectType($startDate, $endDate, $projectTypeIds,$teamIds){
        $arrProjectTypeId = explode(",", $projectTypeIds);
        $css = array();
        $result = array();
        $no = 0;
        foreach($arrProjectTypeId as $k => $projectTypeId){
            $projectTypeName = self::getProjectTypeNameById($projectTypeId);
            $points = array();
            $css = CssPermission::getFilterAnalyzeByProjectType($projectTypeId,$teamIds);
            if(count($css) > 0){
                $countCss = 0;
                foreach($css as $itemCss){
                    $css_result = Css::getCssResultByCssId($itemCss->id,$startDate,$endDate);
                    if(count($css_result) > 0){
                        $countCss += count($css_result);
                        foreach($css_result as $itemCssResult){
                            $points[] = self::formatNumber($itemCssResult->avg_point);
                        }
                    }
                }

                if(count($points) > 0){
                    $avgPoint = array_sum($points) / count($points);
                    $no++;
                    $result[] = [
                        "no"                => $no,
                        "projectTypeId"     => $projectTypeId,
                        "projectTypeName"   => $projectTypeName,
                        "countCss"          => $countCss,
                        "maxPoint"          => self::formatNumber(max($points)),
                        "minPoint"          => self::formatNumber(min($points)),
                        "avgPoint"          => self::formatNumber($avgPoint),
                    ];
                }else{
                    $no++;
                    $result[] = [
                        "no"                => $no,
                        "projectTypeId"     => $projectTypeId,
                        "projectTypeName"   => $projectTypeName,
                        "countCss"          => 0,
                        "maxPoint"          => "-",
                        "minPoint"          => "-",
                        "avgPoint"          => "-",
                    ];
                }
            }else{
                $no++;
                $result[] = [
                    "no"                => $no,
                    "projectTypeId"     => $projectTypeId,
                    "projectTypeName"   => $projectTypeName,
                    "countCss"          => 0,
                    "maxPoint"          => "-",
                    "minPoint"          => "-",
                    "avgPoint"          => "-",
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Show data filter by team
     * @param string $startDate
     * @param string $endDate
     * @param string $projectTypeIds
     * @param string $teamIds
     * return array
     */
    protected function filterAnalyzeByTeam($startDate, $endDate, $projectTypeIds,$teamIds){
        $arrTeamId = explode(",", $teamIds);
        $css = array();
        $result = array();
        $no = 0;
        $teamModel = new Team();
        foreach($arrTeamId as $k => $teamId){
            $points = array();
            $css = CssPermission::getFilterAnalyzeByTeam($teamId,$projectTypeIds);
            $team = $teamModel->getTeamWithTrashedById($teamId);
            $teamId = $team->id;
            $teamName = $team->name;
            if(count($css) > 0){
                $countCss = 0;
                foreach($css as $itemCss){
                    $css_result = Css::getCssResultByCssId($itemCss->id,$startDate,$endDate);

                    if(count($css_result) > 0){
                        $countCss += count($css_result);
                        foreach($css_result as $itemCssResult){
                            $points[] = self::formatNumber($itemCssResult->avg_point);
                        }
                    }
                }

                if(count($points) > 0){
                    $avgPoint = array_sum($points) / count($points);
                    $no++;
                    $result[] = [
                        "no"                => $no,
                        "teamId"            => $teamId,
                        "teamName"          => $teamName,
                        "countCss"          => $countCss,
                        "maxPoint"          => self::formatNumber(max($points)),
                        "minPoint"          => self::formatNumber(min($points)),
                        "avgPoint"          => self::formatNumber($avgPoint),
                    ];
                }else{
                    $no++;
                    $result[] = [
                        "no"                => $no,
                        "teamId"            => $teamId,
                        "teamName"          => $teamName,
                        "countCss"          => 0,
                        "maxPoint"          => "-",
                        "minPoint"          => "-",
                        "avgPoint"          => "-",
                    ];
                }
            }else{
                $no++;
                $result[] = [
                    "no"                => $no,
                    "teamId"            => $teamId,
                    "teamName"          => $teamName,
                    "countCss"          => 0,
                    "maxPoint"          => "-",
                    "minPoint"          => "-",
                    "avgPoint"          => "-",
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Show data filter by PM or BrSE or Customer or Sale
     * @param string $startDate
     * @param string $endDate
     * @param string $projectTypeIds
     * @param string $teamIds
     * @param string $criteria
     * return array
     */
    protected function filterAnalyzeByPmOrBrseOrCustomerOrSale($startDate, $endDate, $projectTypeIds,$teamIds,$criteria){
        $css = array();
        $result = array();
        $no = 0;
        
        if($criteria == "pm"){
            $listResult = CSS::getListPm();
        }else if($criteria == "customer"){
            $listResult = CSS::getListCustomer();
        }else if($criteria == "sale"){
            $listResult = CSS::getListSale();
        }else if($criteria == "projectName"){
            $listResult = CSS::getListProjectName();
        }
        if(count($listResult) > 0){
            foreach($listResult as $itemList){
                $points = array();
                if($criteria == "pm"){
                    $css = CssPermission::getFilterAnalyzeByPm($itemList->pm_name, $teamIds,$projectTypeIds);
                }else if($criteria == "customer"){
                    $css = CssPermission::getFilterAnalyzeByCustomer($itemList->customer_name, $teamIds,$projectTypeIds);
                }else if($criteria == "sale"){
                    $css = CssPermission::getFilterAnalyzeBySale($itemList->employee_id, $teamIds,$projectTypeIds);
                }else if($criteria == "projectName"){
                    $css = CssPermission::getFilterAnalyzeByProjectName($itemList->project_name, $teamIds,$projectTypeIds);
                }

                $countCss = 0;
                if(count($css) > 0){
                    foreach($css as $itemCss){
                        $css_result = Css::getCssResultByCssId($itemCss->id,$startDate,$endDate);
                        
                        if(count($css_result) > 0){
                            $countCss += count($css_result);
                            foreach($css_result as $itemCssResult){
                                $points[] = self::formatNumber($itemCssResult->avg_point);
                            }
                        }
                    }

                    if($criteria == "pm"){
                        $id = $itemList->pm_name;
                        $name = $itemList->pm_name;
                    }  else if($criteria == "customer"){
                        $id = $itemList->customer_name; 
                        $name = $itemList->customer_name; 
                    } else if($criteria == "sale"){
                        $employee = Employee::find($itemList->employee_id);
                        if (!$employee) {
                            $employee = new Employee();
                        }
                        $id = $itemList->employee_id;
                        $name = $employee->name; 
                    } else if($criteria == "projectName"){
                        $id = $itemList->project_name;
                        $name = $itemList->project_name;
                    }

                    if(count($points) > 0){
                        $avgPoint = array_sum($points) / count($points);
                        $no++;
                        $result[] = [
                            "no"                => $no,
                            "id"                => $id,
                            "name"              => $name,
                            "countCss"          => $countCss,
                            "maxPoint"          => self::formatNumber(max($points)),
                            "minPoint"          => self::formatNumber(min($points)),
                            "avgPoint"          => self::formatNumber($avgPoint),
                        ];
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * ham format number voi 2 so thap phan
     * @param float $number
     * @return float
     */
    protected function formatNumber($number){
        return number_format($number, 2, ".",",");
    }

    /**
     * get team tree option recursive 
     * 
     * @param int $id
     * @param int $level
     */
    protected static function getTreeDataRecursive($parentId = 0, $level = 0, $idActive = null) 
    {
        $userAccount = Auth::user(); 
        $arrTeam = CssPermission::getArrTeamIdByEmployee($userAccount->employee_id);
        $permission = new Permission();
        $currentRoute = Route::getCurrentRoute()->getName();

        $teamList = Team::select('id', 'name', 'parent_id')
                ->where('parent_id', $parentId)
                ->orderBy('sort_order', 'asc')
                ->get();
        $countCollection = count($teamList);
        if (!$countCollection) {
            return;
        }
        $html = '';
        $i = 0;
        foreach ($teamList as $team) {
            $classLi = '';
            $classLabel = 'icheckbox-container label-normal';
            $optionA = " data-id=\"{$team->id}\"";
            $classA = '';
            if ($i == $countCollection - 1) {
                $classLi = 'last';
            }
            if ($team->id == $idActive) {
                $classA .= 'active';
            }
            $classLi = $classLi ? " class=\"{$classLi}\"" : '';
            $classLabel = $classLabel ? " class=\"{$classLabel}\"" : '';
            $classA = $classA ? " class=\"{$classA}\"" : '';
            
            $htmlChild = self::getTreeDataRecursive($team->id, $level + 1, $idActive);
            $hrefA = route('team::setting.team.view', ['id' => $team->id]);
            $html .= "<li{$classLi}>";
            $html .= "<label{$classLabel}>";
            $html .= "<div class=\"icheckbox\">";

                if($currentRoute == 'sales::css.analyze'){
                    if(!$permission->isScopeCompany() &&$permission->isScopeTeam()){
                        // If is scrope team -> checked only self team
                        if(in_array($team->id, $arrTeam)){ 
                            $html .= '<input type="checkbox" class="team-tree-checkbox" data-id="'.$team->id.'" parent-id="'.$parentId.'" name="team['.$team->id.']">&nbsp;&nbsp;<span>' .$team->name. '</span>';
                        }else{
                            $html .= '<input disabled="disabled" type="checkbox" class="team-tree-checkbox" data-id="'.$team->id.'" parent-id="'.$parentId.'" name="team['.$team->id.']">&nbsp;&nbsp;<span>' .$team->name. '</span>';
                        }
                    }else{
                        $html .= '<input type="checkbox" class="team-tree-checkbox" data-id="'.$team->id.'" parent-id="'.$parentId.'" name="team['.$team->id.']">&nbsp;&nbsp;<span>' .$team->name. '</span>';
                    }
                }else{
                    $html .= '<input type="checkbox" class="team-tree-checkbox" data-id="'.$team->id.'" parent-id="'.$parentId.'" name="team['.$team->id.']">&nbsp;&nbsp;<span>' .$team->name. '</span>';
                }

            
            $html .= '</div>';
            $html .= '</label>';
            
            if ($html) {
                $html .= '<ul>';
                $html .= $htmlChild;
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }
  
    /**
     * Get max, min, avg point of question
     * @param int $questionId
     * @param date $startDate
     * @param date $endDate
     * @param string $teamIds
     * @return type
     */
    protected function getQuestionInfoAnalyze($questionId,$startDate, $endDate,$teamIds){
        $cssResult = CssPermission::getFilterAnalyzeByQuestion($questionId,$startDate, $endDate,$teamIds);
        if(count($cssResult) > 0){
            $cssResultDetailModel = new CssResultDetail();
            $countCss = 0;
            $points = array();
            foreach($cssResult as $itemCssResult){
                $cssResultDetail = $cssResultDetailModel->getResultDetailRow($itemCssResult->id,$questionId);
                if(count($cssResultDetail)){
                    if($cssResultDetail->point > 0){
                        $points[] = $cssResultDetail->point;
                        $countCss++;
                    }
                }
            }
            $maxPoint = (count($points) > 0) ? self::formatNumber(max($points)) : "-";
            $minPoint = (count($points) > 0) ? self::formatNumber(min($points)) : "-";
            if(count($points) > 0){
                $avgPoint = array_sum($points) / count($points);
                $avgPoint = self::formatNumber($avgPoint);
            }else{
                $avgPoint = "-";
            }
        }else{
            $countCss = 0;
            $maxPoint = "-";
            $minPoint = "-";
            $avgPoint = "-";
        }
        
        return [
            "countCss"  => $countCss,
            "maxPoint"  => $maxPoint,
            "minPoint"  => $minPoint,
            "avgPoint"  => $avgPoint
        ];
    }

    /**
     * Get list less three star by cssResultIds and questionId
     * @param int $questionId
     * @param array $cssResultIds
     * @param int $curPage
     */
    protected function getListLessThreeStarByQuestion($questionId,$cssResultIds,$curPage,$orderBy,$ariaType){
        Paginator::currentPageResolver(function () use ($curPage) {
            return $curPage;
        });
        $filter = Input::get('filter');
        if ($filter) {
            $filterConvertKey = [
                'project_name' => 'css.project_name',
                'content' => 'css_question.content',
                'point' => 'css_result_detail.point',
                'comment' => 'css_result_detail.comment',
                'created_at' => 'css_result.created_at',
                'avg_point' => 'css_result.avg_point'
                
            ];
            parse_str($filter,$filterOutput);
            $filterOutputClone = $filterOutput;
            foreach ($filterOutputClone as $filterKey => $filterValue) {
                if (isset($filterConvertKey[$filterKey])) {
                    unset($filterOutput[$filterKey]);
                    $filterOutput[$filterConvertKey[$filterKey]] = $filterValue;
                }
            }
            $filter = $filterOutput;
        }
        $lessThreeStar = Css::getListLessThreeStarByQuestionId($questionId,$cssResultIds,self::$perPage,$orderBy,$ariaType, $filter);
        
        $offset = ($lessThreeStar->currentPage()-1) * $lessThreeStar->perPage() + 1;
        $result = [];
        foreach($lessThreeStar as $item){
            $result[] = [
                "no"            => $offset++,
                "projectName"   => $item->project_name,
                "questionName"  => $item->question_name,
                "stars"         => $item->point,
                "comment"       => $item->comment,
                "makeDateCss"   => date('d/m/Y',strtotime($item->result_make)),
                "cssPoint"      => self::formatNumber($item->result_point),
            ];
        }
        
        //Get html pagination render
        $count = $lessThreeStar->total();
        $totalPage = ceil($count / $lessThreeStar->perPage());
        $html = "";
        if($totalPage > 1){
            if($curPage == 1){
                $html .= '<li class="disabled"><span>«</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getListLessThreeStarByQuestion('.$questionId.','.($curPage-1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="back">«</a></li>';
            }
            for($i=1; $i<=$totalPage; $i++){
                if($i == $curPage){
                    $html .= '<li class="active"><span>'.$i.'</span></li>';
                }else{
                    $html .= '<li><a href="javascript:void(0)" onclick="getListLessThreeStarByQuestion('.$questionId.','.$i.',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');">'.$i.'</a></li>';
                }
            }
            if($curPage == $totalPage){
                $html .= '<li class="disabled"><span>»</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getListLessThreeStarByQuestion('.$questionId.','.($curPage+1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="next">»</a></li>';
            }
        }
        
        $data = [
            "cssResultdata" => $result,
            "paginationRender" => $html,
        ];
        
        return $data;
    }
    
    /**
     * get customer's proposes
     * @param int $questionId
     * @param array $cssResultIds
     * @param int $curPage
     */
    protected function getProposesByQuestion(
        $questionId,
        $cssResultIds,
        $curPage,
        $orderBy,
        $ariaType
    ){
        Paginator::currentPageResolver(function () use ($curPage) {
            return $curPage;
        });
        $filter = Input::get('filter');
        if ($filter) {
            $filterConvertKey = [
                'project_name' => 'css.project_name',
                'proposed' => 'css_result.proposed',
                'result_make' => 'css_result.created_at',
                'result_point' => 'css_result.avg_point'
            ];
            parse_str($filter,$filterOutput);
            $filterOutputClone = $filterOutput;
            foreach ($filterOutputClone as $filterKey => $filterValue) {
                if (isset($filterConvertKey[$filterKey])) {
                    unset($filterOutput[$filterKey]);
                    $filterOutput[$filterConvertKey[$filterKey]] = $filterValue;
                }
            }
            $filter = $filterOutput;
        }
        $proposes = Css::getProposesByQuestion($questionId,$cssResultIds,self::$perPage,$orderBy,$ariaType, $filter);
        
        $offset = ($proposes->currentPage()-1) * $proposes->perPage() + 1;
        $result =[];
        foreach($proposes as $propose){
            $result[] = [
                "no"   => $offset++,
                "cssPoint"   => self::formatNumber($propose->avg_point),
                "projectName"   => $propose->project_name,
                "customerComment" => $propose->proposed,
                "makeDateCss" => date('d/m/Y',strtotime($propose->created_at)),
            ];
        }
        //Get html pagination render
        $count = $proposes->total();
        $totalPage = ceil($count / $proposes->perPage());
        $html = "";
        if($totalPage > 1){
            if($curPage == 1){
                $html .= '<li class="disabled"><span>«</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getProposesQuestion('.$questionId.','.($curPage-1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="back">«</a></li>';
            }
            for($i=1; $i<=$totalPage; $i++){
                if($i == $curPage){
                    $html .= '<li class="active"><span>'.$i.'</span></li>';
                }else{
                    $html .= '<li><a href="javascript:void(0)" onclick="getProposesQuestion('.$questionId.','.$i.',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');">'.$i.'</a></li>';
                }
            }
            if($curPage == $totalPage){
                $html .= '<li class="disabled"><span>»</span></li>';
            }else{
                $html .= '<li><a href="javascript:void(0)" onclick="getProposesQuestion('.$questionId.','.($curPage+1).',\''.Session::token().'\',\''.$cssResultIds.'\',\''.$orderBy.'\',\''.$ariaType.'\');" rel="next">»</a></li>';
            }
        }
        
        $data = [
            "cssResultdata" => $result,
            "paginationRender" => $html,
        ];
        return $data;
    }
    
    
    
    /**
     * Get Project Type's name
     * @param int $projectTypeId
     * @return string
     */
    public function getProjectTypeNameById($projectTypeId){
        $projectTypeName = "";
        $projectType = CssProjectType::find($projectTypeId);
        
        return $projectType->name;
    }
    
    /**
     * Get root category by cate
     * @param CssCategory $cate
     */
    public function getRootCateByCate($cate){
        $cateParent = CssCategory::find($cate->parent_id);
        
        if(count($cateParent)){
            if($cateParent->parent_id == 0){
                return $cateParent;
            }else{
                return self::getRootCateByCate($cateParent);
            }
        }else{
            return null;
        }
    }
    
    public function sendMailCustomer(Request $request) {
        if (!$request->input('emails')) {
            return response()->json(['status' => CssMail::MAIL_ERROR]);
        }
        
        $customes = $request->input('emails');
        $cssId = $request->input('css_id');
        $css = Css::find($cssId);
        $lang = SupportConfig::get('langs.'.$css->lang_id);
        $time = explode('-', $css->time_reply);
        if ($lang == null) {
            $lang = SupportConfig::get('langs.'.Css::JAP_LANG);
        }
        $curUser = Permission::getInstance()->getEmployee()->id;
        $mailPqa = CoreConfigData::getCssMail(2);
        if (count($mailPqa)) {
            $fromMail = $mailPqa[1];
            if ($css->lang_id == Css::JAP_LANG) {
                $fromName = 'Rikkeisoft 品質管理部';
            } else {
                $fromName = 'Rikkeisoft Quality Control Department';
            }
        } else {
            $fromMail = 'pqa@rikkeisoft.com';
            $fromName = 'Rikkeisoft';
        }
        
        //Insert cssCate
        $nameSession = 'css_import_'.$cssId.'_'.Auth::user()->employee_id;
        $dataImport = $request->session()->get($nameSession);
        if ($dataImport) {
            $code = md5(rand().time());
            $dataRoot = [
                'name' => $dataImport['dataRoot']['name'],
                'parent_id' => 0,
                'project_type_id' => Css::TYPE_OSDC,
                'css_id' => $cssId,
                'code' => $code,
                'sort_order' => 0,
                'question_explanation' => $dataImport['dataRoot']['question_explanation'],
                'lang_id' => Css::ENG_LANG,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            $rootCate = CssCategory::create($dataRoot);
            $dataOvvQs = [
                'content' => $dataImport['overviewQuestionContent'],
                'category_id' => $rootCate->id,
                'sort_order' => 1,
                'is_overview_question' => 1,
                'explain' => $dataImport['overviewQuestionExplain'],
            ];
            CssQuestion::create($dataOvvQs);
            foreach ($dataImport['cssCate'] as $key => $itemChild) {
                $dataChild = [
                    'name' => $itemChild['name'],
                    'parent_id' => $rootCate->id,
                    'project_type_id' => Css::TYPE_OSDC,
                    'css_id' => $cssId,
                    'code' => $rootCate['code'],
                    'question_explanation' => $itemChild['question_explanation'],
                    'sort_order' => $itemChild['noCate'],
                    'lang_id' => Css::ENG_LANG,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $childCate = CssCategory::create($dataChild);
                if (isset($itemChild['questions']) && !empty($itemChild['questions'])) {
                    foreach ($itemChild['questions'] as $key2 => $itemQs) {
                        $dataQs = [
                            'content' => $itemQs['content'],
                            'category_id' => $childCate->id,
                            'sort_order' => $itemQs['sort_order'],
                            'explain' => $itemQs['question_explanation'],
                        ];
                        CssQuestion::create($dataQs);
                    }
                } elseif (isset($itemChild['cssCateChild']) && !empty($itemChild['cssCateChild'])) {
                    foreach ($itemChild['cssCateChild'] as $key3 => $itemSub) {
                        $dataSub = [
                            'name' => $itemSub['name'],
                            'parent_id' => $childCate->id,
                            'project_type_id' => Css::TYPE_OSDC,
                            'css_id' => $cssId,
                            'code' => $rootCate['code'],
                            'question_explanation' => $itemSub['question_explanation'],
                            'sort_order' => $itemSub['sort_order'],
                            'lang_id' => Css::ENG_LANG,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                        $subCate = CssCategory::create($dataSub);
                        if (isset($itemSub['questionsChild']) && !empty($itemSub['questionsChild'])) {
                            foreach ($itemSub['questionsChild'] as $key4 => $itemSubQs) {
                                $dataSubQs = [
                                    'content' => $itemSubQs['content'],
                                    'category_id' => $subCate->id,
                                    'sort_order' => $itemSubQs['sort_order'],
                                    'explain' => $itemSubQs['question_explanation'],
                                ];
                                CssQuestion::create($dataSubQs);
                            }
                        }
                    }
                }
            }
        }
        $arrProjsNotShowCompany = [
            'MSR-Intramart'
        ];
        foreach ($customes as $customer) {
            $name = $customer[0];
            $email = $customer[1];
            $gender = $customer[2];
            // $code = md5($email);
            $code = $dataImport ? $rootCate['code'] : 0;
            // $cssMail = CssMail::getCssMail($cssId, $email);
            $subject = Lang::get('sales::message.Mail customer title', ['name' => 'Rikkeisoft'],$lang);
            if ($lang == 'vi') {
                $subject = 'Khảo sát mức độ hài lòng của khách hàng dự án '.($css->project_name_css ? $css->project_name_css : $css->project_name);
            }
            //if sent mail 
            // if (count($cssMail)) {
            //     $json[] = [
            //         'css_id' => $cssId,
            //         'mail_to' => $email,
            //         'sender' => $curUser,
            //         'resend' => (int)$cssMail->resend + 1,
            //         'resend_date' => date('Y-m-d h:i:s'),
            //         'code' => $code,
            //         'name' => $name,
            //         'gender' => $gender
            //     ];
            //     $data = [
            //         'company_name' => $css->company_name,
            //         'hrefMake' => route('sales::welcome', ['id' => $css->id, 'token' => $css->token]) . '?c=' . $code,
            //         'resend' => true,
            //         'customerName' => $name,
            //         'lang' =>$lang,
            //         'gender' => $gender,
            //         'project_type_id' => $css->project_type_id
            //     ];
               
            // } else {
            //     $json[] = [
            //         'css_id' => $cssId,
            //         'mail_to' => $email,
            //         'sender' => $curUser,
            //         'name' => $name,
            //         'code' => $code,
            //         'gender' => $gender
            //     ];
            //     $data = [
            //         'company_name' => $css->company_name,
            //         'hrefMake' => route('sales::welcome', ['id' => $css->id, 'token' => $css->token]) . '?c=' . $code,
            //         'customerName' => $name,
            //         'lang' =>$lang,
            //         'gender' => $gender,
            //         'project_type_id' => $css->project_type_id
            //     ];
            // }
            $json[] = [
                'css_id' => $cssId,
                'mail_to' => $email,
                'sender' => $curUser,
                'name' => $name,
                'code' => $code,
                'gender' => $gender,
                'company_name' => $css->company_name,
                'project_name' => $css->project_name_css ? $css->project_name_css : $css->project_name,
                'employee' => $css->css_creator_name ? $css->css_creator_name : ucwords(Str::slug(Session::get('employee_'.$curUser)->name, ' ')),
                'month' => $css->time_reply ? $time[1] : '',
                'date' => $css->time_reply ? $time[2] : '',
                'time_reply' => $css->time_reply,
            ];
            $data = [
                'company_name' => $css->company_name,
                'time_reply' => $css->time_reply,
                'hrefMake' => route('sales::welcome', ['id' => $css->id, 'token' => $css->token]) . '?c=' . $code,
                'customerName' => $name,
                'project_name' => $css->project_name_css ? $css->project_name_css : $css->project_name,
                'lang' =>$lang,
                'gender' => $gender,
                'project_type_id' => $css->project_type_id,
                'employee' => $css->css_creator_name ? $css->css_creator_name : ucwords(Str::slug(Session::get('employee_'.$curUser)->name, ' ')),
                'month' => $css->time_reply ? $time[1] : '',
                'date' => $css->time_reply ? $time[2] : '',
                'isNotShowCompany' => in_array($css->project_name, $arrProjsNotShowCompany) ? 1 : 0
            ];
            //Save mail to queue
            $typeMail = $request->input('typeMail');
            if ($typeMail == 1) {
                $template = 'sales::css.customerMail';
            } elseif ($typeMail == 2) {
                $template = 'sales::css.email.customerMailPeriodic';
            } else {
                return;
            }
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($email)
                ->setFrom($mailPqa[1], $fromName)
                ->setSubject($subject)
                ->setTemplate($template, $data);
            
            //Bcc to relaters
            if (!empty($css->rikker_relate)) {
                $bcces = explode(',', $css->rikker_relate);
                if (count($bcces)) {
                    foreach ($bcces as $bcc) {
                        $emailQueue->addBcc($bcc);
                    }
                }
            }
            
            try {
                $emailQueue->save();
            } catch (Exception $ex) {
                
            }
        }

        //Save data send mail
        CssMail::saveData($json);
        foreach ($json as $key => $data) {
            // $json[$key]['cssMailId'] = CssMail::getCssMail($cssId, $data['mail_to'])->id;
            $json[$key]['cssMailId'] = CssMail::where('css_id', $cssId)->where('mail_to', $data['mail_to'])->orderBy('id', 'DESC')->first()->id;
        }

        return response()->json($json);
    }

    /**
     * Delete CSS item
     * 
     * @param Request $request
     */
    function deleteItem(Request $request) {
        if ($request->input('id') && Permission::getInstance()->isAllow('sales::css.deleteItem')) {
            $id = $request->input('id');
            $model = Css::find($id);
            if (! $model) {
                return redirect()->route('sales::css.list')->withErrors(Lang::get('resource::messages.Not found item.'));
            }
            
            $results = CssResult::where('css_id', $id)->select('id')->get();
            $idResults = [];
            if (count($results)) {
                foreach ($results as $result) {
                    $idResults[] = $result->id;
                }
            }
            
            //update deleted_at (soft delete item)
            DB::beginTransaction();
            try {
                CssResultDetail::whereIn('css_result_id', $idResults)->update(['deleted_at' => Carbon::now()]);
                CssTeams::where('css_id', $id)->select('css_id')->update(['deleted_at' => Carbon::now()]);
                CssView::where('css_id', $id)->select('css_id')->update(['deleted_at' => Carbon::now()]);
                CssResult::where('css_id', $id)->update(['deleted_at' => Carbon::now()]);
                $model->delete();
                DB::commit();
                $messages = [
                    'success'=> [
                        Lang::get('sales::message.Delete item success!'),
                    ]
                ];
                return redirect()->route('sales::css.list')->with('messages', $messages);
            } catch (Exception $ex) {
                DB::rollback();
                throw $ex;
            }
        }
    }
    
    /**
     * Get list CSS result by css_id, code
     * 
     * @param Request $request
     */
    function showAllMake(Request $request) {
        $data = $request->input();
        if ($data && count($data)) {
            $itemId = $data['itemId'];
            unset($data['itemId']); 
            $order = 'id';
            $dir = 'desc';
            $conditionsRaw = ["id NOT IN (select max(id) as last_id from css_result group by css_id, code)"];
            $resultList = CssResult::getList($data, $conditionsRaw, $order, $dir);
            return view('sales::css.include.css_result_list', ['result' => $resultList, 'itemId' => $itemId]);
        }
    }
    
     /**
     * history css work
     */
    public function historyAjax(Request $request) {
        $data = $request->all();
        $listWork = CssResult::where('css_id',$data['idCss'])
            ->where('code',$data['code'])
            ->orderBy('id','desc')
            ->get();
        $css = Css::find($data['idCss']);
        if ($listWork->count() > 0) {
            $json = [
                'html' => view('sales::css.include.table_history',['listWork' => $listWork,'css' => $css])->render(),
                'status' => true,
            ];
        } else {
            $json = [
                'html' => lang::get('ban chua lam danh gia'),
                'status' => false,
            ];
        }
        return response()->json($json);
    }

    /**
     * View Css result detail page
     * @param int $resultId
     * @return void
     */
    public function detailCssCus(Request $request,$resultId){
        Breadcrumb::add(Lang::get('sales::view.Detail.Title'));
        $code = $request->c; 
        $cssResult = CssResult::where('id',$resultId)
            ->where('code',$code)->first();
        if($cssResult) {
            $css = Css::find($cssResult->css_id);
            $employee = Employee::find($css->employee_id);
            if (!$employee) {
                $employee = new Employee();
            }
            $cssCategoryModel = new CssCategory();
            $cssQuestionModel = new CssQuestion();
            $cssResultDetailModel = new CssResultDetail();

            $cssCate = self::getCssDetailPoint($css->project_type_id,$resultId,$cssResult->code);
            $rootCategory = $cssCategoryModel->getRootCategoryV2($css->project_type_id, $css->id, $code, $css->created_at, $css->lang_id);
            $questLangId = CView::checkLangOvvQuestion($css->project_type_id, $css->lang_id, $css->created_at);
            $overviewQuestion = $cssQuestionModel->getOverviewQuestionByCategory($rootCategory->id, 1, $questLangId);
            $resultDetailRow = $cssResultDetailModel->getResultDetailRowOfOverview($resultId, $rootCategory->id);
            
            return view(
                'sales::customer.cssCustomer', [
                    'css' => $css,
                    "employee" => $employee,
                    "cssCate" => $cssCate,
                    "cssResult" => $cssResult,
                    "noOverView" => View::romanic_number(count($cssCate)+1,true),
                    "overviewQuestionId" => $overviewQuestion->id,
                    "overviewQuestionContent" => $overviewQuestion->content,
                    "resultDetailRowOfOverview" => $resultDetailRow,
                ]
            ); 
        } else {
            return redirect(url('/'));
        }
        
    }

    /**
     * Delete email css.
     */
    public function deleteMail(Request $request)
    {
        if ($request->input("idEmail")) {
            $idEmailCss = $request->input("idEmail");
            CssMail::where('id', $idEmailCss)->delete();
        }
    }

    /**
     * insert or update analysis or submit status css customer
     *
     * @param Request $request
     * @return data response
     */
    public function insertAnalysisCss(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        $questionId = $request->input('questionId');
        $cssResultId = $request->input('cssResultId');
        $analysisContent = $request->input('analysisContent');
        $cssId = $request->input('cssId');
        $status = $request->input('statusCss');
        $curEmp = Permission::getInstance()->getEmployee();
        $css = Css::find($cssId);
        $project = Project::find($css->projs_id);
        $cssResult = CssResult::find($cssResultId);

        if (!(Permission::getInstance()->isScopeCompany(null, 'sales::insertAnalysisResult')) && (!(Project::checkPMAndPQAOfProject($curEmp->id, $css->projs_id)) || Team::checkPqaLeader($curEmp->id))) {
            $response['popup'] = 1;
            $response['success'] = 0;
            $response['message'] = Lang::get('sales::message.You don\'t permission');

            return response()->json($response);
        }
        if ($cssResultId && $questionId) {
            $cssResultDetail = new CssResultDetail();
                $cssResultDetail->updateCssResultDetail($cssResultId, $questionId, $analysisContent);
                $response['popup'] = 1;
                $response['success'] = 1;
                $response['message'] = Lang::get('sales::message.Save data successful!');

                return response()->json($response);
        }

        if ($status && $cssResultId) {
            $cssResultDetail = new CssResultDetail();
            $detailResult = $cssResultDetail->getResultDetailByCssResult($cssResultId);

            foreach ($detailResult as $item) {
                if ($item->point > 0 && $item->point <= 3 && $item->analysis == '') {
                    $response['popup'] = 1;
                    $response['success'] = 0;
                    $response['message'] = Lang::get('sales::message.Submit data error!');

                    return response()->json($response);
                }
            }

            // save status css and save status resultCss .
            try {
                if ($cssResult->status == CssResult::STATUS_APPROVED) {
                    $response['success'] = 2;
                    $response['message'] = Lang::get("sales::message.The css file has been approved so it can not be submit !");

                    return response()->json($response);
                }
                if ($cssResult->status == CssResult::STATUS_SUBMITTED) {
                    $response['success'] = 2;
                    $response['message'] = Lang::get("sales::message.The css file has been submitted so it can not be submit !");

                    return response()->json($response);
                }
                $css->status = $status;
                $css->save();
                $cssResult->status = $status;
                $cssResult->save();
                CssResult::afterSaveCssResult($cssResult);
            } catch (Exception $e) {
                return $e;
            }

            // send mail to pm, pqa, sales, leader of project css.
            SendMailAnalysisCss::sendMailConfirmCss($css, $cssResultId, [
                'name_pm' => $project->getPMActive()->name,
                'name_projs' => $project->name,
                'projs_id' => $project->id,
                'status' => $status,
            ]);

            $response['popup'] = 1;
            $response['success'] = 1;
            $response['message'] = Lang::get('sales::message.Submit data successful!');

            return response()->json($response);
        }

    }

    public function cancelCssResult(Request $request){
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        $id = $request->get('cssResultId');
        $cssResult = CssResult::find($id);
        if (!$cssResult) {
            $response['success'] = 0;
            $response['message'] = Lang::get('sales::message.Cancel data error!');
            return response()->json($response);
        }

        if ($cssResult->status == Css::STATUS_CANCEL) {
            $cssResult->status = Css::STATUS_NEW;
        } else {
            $cssResult->status = Css::STATUS_CANCEL;
        }
        $cssResult->save();

        CssResult::afterSaveCssResult($cssResult);

        $response['success'] = 1;
        $response['message'] = Lang::get('sales::message.Cancel data successful!');
        return response()->json($response);
    }

    /**
     * Approve or feedback status css customer
     *
     * @param Request $request
     * @return data response
     */
    public function approveStatusCss(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        $status = $request->input('statusCss');
        $cssResultId = $request->input('cssResultId');
        $cssResult = CssResult::find($cssResultId);

        // approve customer css by sales.
        if (!(Permission::getInstance()->isAllow('sales::approveStatusCss'))) {
            $response['popup'] = 1;
            $response['success'] = 0;
            $response['message'] = Lang::get('sales::message.You don\'t permission');

            return response()->json($response);
        }
            // save status css and save status cssResult.
        if ($status == CssResult::STATUS_FEEDBACK) {
            $css = $this->feedbackResultCss($request);
            if ($css) {
                $response['popup'] = 1;
                $response['success'] = 1;
                $response['message'] = Lang::get('sales::message.Feedback data successful!');
                return response()->json($response);
            }
            return false;
        } else { // status is approve
            if ($cssResult->status == CssResult::STATUS_APPROVED) {
                $response['success'] = 0;
                $response['message'] = Lang::get('sales::message.The css file has been approved so it can not be approve !');
                return response()->json($response);
            }
            $css = $this->approveOrReviewResultCss($request);
            if ($css) {
                $response['popup'] = 1;
                $response['success'] = 1;
                $response['message'] = Lang::get('sales::message.Approve data successful!');

                return response()->json($response);
            }
            return false;
        }
    }

    public function reviewStatusCss(Request $request)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        $status = $request->input('statusCss');
        if (!(Permission::getInstance()->isAllow('sales::reviewStatusCss'))) {
            $response['popup'] = 1;
            $response['success'] = 0;
            $response['message'] = Lang::get('sales::message.You don\'t permission');

            return response()->json($response);
        }
        // save status css and save status cssResult.
        if ($status == CssResult::STATUS_FEEDBACK) {
            $css = $this->feedbackResultCss($request);
            if ($css) {
                $response['popup'] = 1;
                $response['success'] = 1;
                $response['message'] = Lang::get('sales::message.Feedback data successful!');
                return response()->json($response);
            }
            return false;
        } else { // status is review
            $css = $this->approveOrReviewResultCss($request);
            if ($css) {
                $response['popup'] = 1;
                $response['success'] = 1;
                $response['message'] = Lang::get('sales::message.Review data successful!');

                return response()->json($response);
            }
            return false;
        }
    }

    public function feedbackResultCss($request)
    {
        $cssId = $request->input('cssId');
        $status = $request->input('statusCss');
        $cssResultId = $request->input('cssResultId');
        $css = Css::find($cssId);
        $cssResult = CssResult::find($cssResultId);
        $curEmp = Permission::getInstance()->getEmployee();
        $project = Project::find($css->projs_id);
        $content = $request->input('content');

        if ($content) {
            try {
                // save status css
                $css->status = $status;
                $css->save();
                $cssResult->status = $status;
                $cssResult->save();
                CssResult::afterSaveCssResult($cssResult);

                // save content comment.
                $cssComment = new CssComment();
                $cssComment->content = $content;
                $cssComment->created_by = $curEmp->id;
                $cssComment->css_result_id = $cssResultId;
                $cssComment->save();

                // send mail to pm, pqa, sales, leader of project css.
                SendMailAnalysisCss::sendMailConfirmCss($css, $cssResultId, [
                    'status' => $status,
                    'content' => $content,
                    'name_pm' => $project->getPMActive()->name,
                    'name_projs' => $project->name,
                    'projs_id' => $project->id,
                ]);
                return $css;
            } catch (Exception $ex) {
                return $ex;
            }
        } else {
            return false;
        }
    }

    public function approveOrReviewResultCss($request)
    {
        $cssId = $request->input('cssId');
        $status = $request->input('statusCss');
        $cssResultId = $request->input('cssResultId');
        $css = Css::find($cssId);
        $cssResult = CssResult::find($cssResultId);
        $project = Project::find($css->projs_id);
        $content = $request->input('content');

        try {
            $css->status = $status;
            $css->save();
            $cssResult->status = $status;
            $cssResult->save();
            CssResult::afterSaveCssResult($cssResult);

            // send mail to pm, pqa, sales, leader of project css.
            SendMailAnalysisCss::sendMailConfirmCss($css, $cssResultId, [
                'status' => $status,
                'content' => $content,
                'name_pm' => $project->getPMActive()->name,
                'name_projs' => $project->name,
                'projs_id' => $project->id,
            ]);
            return $css;
        } catch (Exception $ex) {
            return $ex;
        }
    }
}
