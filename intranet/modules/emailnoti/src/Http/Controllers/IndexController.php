<?php
namespace Rikkei\Emailnoti\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Model\User;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Notify\Classes\RkNotify;
use URL;
use Rikkei\Core\View\Menu;
use Illuminate\Http\Request;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\CoreConfigData as CCD;
use Illuminate\Support\Facades\Lang;
use Rikkei\Team\View\Permission;
use DB;

class IndexController extends \Rikkei\Core\Http\Controllers\Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('HR');
        Menu::setActive('hr');
    }
    
    /**
     * [index description]
     * @return [type] [description]
     */
    public function index( Request $request ) { 
        //check exist subject,content and return value subject adn content
        $core = $this->checkExist();
        $coreSubject = $core['coreSubject'];
        $coreContent = $core['coreContent'];

        $response = "";

        if($request->input('response')){
            $response = $request->input('response');
        }

        $listEmployees = Employee::all();

        $params = [
            'title' => Lang::get('emailnoti::view.Send notification'),
            'list_employee' => $listEmployees,
            'response' => $response,
            'subject' => $coreSubject,
            'content' => $coreContent
        ];

        Breadcrumb::add('Send notification');
        return view('emailnoti::send-email.index', ['authId' => Auth::id()])->with($params);
    }

    /**
     * [sendEmail xử lý email]
     * @param Request $request [description]
     * @return [$resonpe]       [description]
     */
    public function sendEmail(Request $request)
    {
        $listEmailByExcel = array();
        if ($request->hasFile('csv_file')) {
            $file = $request->csv_file;
            $fileFinesExtension = $file->getClientOriginalExtension();
            $limit = isset($request->limit) ? $request->limit : 1000;
            if (!in_array($fileFinesExtension, [
                'xlsx',
                'xls',
                'csv'
            ])
            ) {
                return redirect()->back()
                    ->withErrors(Lang::get('core::message.Only allow file csv or excel'));
            }
            try {
                Excel::selectSheetsByIndex(0)->load($file, function ($reader) use (&$listEmailByExcel) {
                    $results = $reader->all()->toArray();
                    foreach ($results as $result) {
                        $email = str_replace(' ', '', $result['email']);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $listEmailByExcel[] = $email;
                        }
                    }
                });
            } catch (\Exception $exception) {
                return redirect()->back()->withInput()->with('messages',
                    ['errors' => [trans('emailnoti::view.Wrong format excel file')]]);
            }

            $listEmailByExcel = array_values(array_filter(array_unique($listEmailByExcel)));
            if (count($listEmailByExcel) > $limit) {
                return redirect()->back()->withInput()->with('messages',
                    ['errors' => [trans('emailnoti::view.max 1000')]]);
            }
        }

        $listIdTeam = explode(',', $request->input('team_list'));
        $listEmailOther = [];
        if ($request->input('to_other') != '') {
            $listEmailOther = explode(',', $request->input('to_other'));
        }
        $listEmailOtherTmp = array();
        foreach ($listEmailOther as $emailTmp) {
            $emailTmp = str_replace(' ', '', $emailTmp);
            if (!filter_var($emailTmp, FILTER_VALIDATE_EMAIL)) {
                $messages = [
                    'errors' => [
                        Lang::get('emailnoti::view.The email format is invalid')
                    ]
                ];
                return redirect()->route('emailnoti::email.notification.index')->with('messages', $messages);
            }
            $listEmailOtherTmp[] = $emailTmp;
        }

        $listEmailbyTeam = [];

        $listEmailbyTeamTmp = DB::table('team_members AS a')
            ->select('c.email')
            ->leftJoin('teams AS b', 'a.team_id', '=', 'b.id')
            ->leftJoin('employees AS c', 'a.employee_id', '=', 'c.id')
            ->whereIn('a.team_id', $listIdTeam)
            ->whereNull('c.leave_date')
            ->whereNull('c.deleted_at')
            ->groupBy('c.email')
            ->get();

        foreach ($listEmailbyTeamTmp as $key) {
            $listEmailbyTeam[] = $key->email;
        }

        if (count($listEmailOtherTmp) > 0) {
            $listEmail = array_unique(array_merge($listEmailbyTeam, $listEmailOtherTmp));
        } else {
            $listEmail = array_unique($listEmailbyTeam);
        }
        if (count(array_filter($listEmailByExcel)) > 0) {
            $listEmail = array_unique(array_merge($listEmailByExcel, $listEmail));
        }

        $data = array();
        $data['subject'] = $request->input('subject');
        $data['content'] = $request->input('content');

        $email = $listEmail;
        $fromEmail = null;
        $name = null;
        if (Input::get('tab') == 1) {
            $user = User::getEmployeeLogged()->toArray();
            if (!isset($user['app_password'])) {
                return redirect()->back()->withInput()->with('messages',
                    ['errors' => [trans('emailnoti::view.Not registered for mailing service')]]);
            }
            $fromEmail = $user['email'];
            $name = $user['name'];
        }
        if (count($email) > 0) {
            $array = array();

            foreach ($email as $key => $value) {
                $data['email'] = $value;
                $array[] = $this->pushEmailToArray($data, $fromEmail, $name);
            }
            //bulk insert
            if (EmailQueue::insert($array)) {
                CCD::where('key', 'email.notification.subject')->update(['value' => $data['subject']]);
                CCD::where('key', 'email.notification.content')->update(['value' => $data['content']]);
                $messages = [
                    'success' => [
                        Lang::get('emailnoti::view.Your Mail has been sent successfully')
                    ]
                ];
                //set notify
                \RkNotify::put(
                    Employee::whereIn('email', $email)->lists('id')->toArray(),
                    $data['subject'],
                    'https://mail.google.com',
                    ['category_id' => RkNotify::CATEGORY_ADMIN]
                );
                return redirect()->route('emailnoti::email.notification.index')->with('messages', $messages);
            } else {
                $messages = [
                    'errors' => [
                        Lang::get('emailnoti::view.Message sending failed')
                    ]
                ];
                return redirect()->route('emailnoti::email.notification.index')->with('messages', $messages);
            }
        } else {
            $messages = [
                'errors' => [
                    Lang::get('emailnoti::view.Your Mail is not Sent')
                ]
            ];
            return redirect()->route('emailnoti::email.notification.index')->with('messages', $messages);
        }

    }

    /**
     * [pushEmailToArray lấy giá trị của các trường để insert vào bảng email_queues]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function pushEmailToArray( $data, $email = null, $name = null ) {
        $template = 'emailnoti::template.mail';
        $subject = $data['subject'];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($data['email'])
            ->setSubject($subject)
            ->setTemplate($template, $data);
        if ($email){
            $emailQueue->from_email = $email;
        }
        if ($name){
            $emailQueue->from_name = $name;
        }
        if (isset($data['email_bcc'])) {
            foreach ($data['email_bcc'] as $key) {
                $emailQueue->addBcc($key);
            } 
        }
        
        return $emailQueue->getValue();
    }
    
    /**
     * [checkExist check exist subject content and return subject and content]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function checkExist() {
        $data = array();

        if (CCD::where('key', 'email.notification.subject')->count() == 0) {
            $flight = new CCD;
            $flight->key = 'email.notification.subject';
            $flight->save();
            $data['coreSubject'] = '';
        }else{
            $data['coreSubject'] = CCD::where('key', 'email.notification.subject')->first()->value;
        }

        if (CCD::where('key', 'email.notification.content')->count() == 0) {
            $flight = new CCD;
            $flight->key = 'email.notification.content';
            $flight->save();
            $data['coreContent'] = '';
        }else{
            $data['coreContent'] = CCD::where('key', 'email.notification.content')->first()->value;
        }

        return $data;
    }

    /**
     * @param Request $request
     * @return view
     */
    public function searchEmail(Request $request)
    {
        $keySearch = $request->key_search;

        if (empty($keySearch)) {
            return \Response::json([]);
        }

        $tags = Employee::where('email','like', $keySearch.'%')->limit(5)->get();

        $formatted_tags = [];

        foreach ($tags as $tag) {
            $formatted_tags[] = ['id' => $tag->email, 'text' => $tag->email];
        }

        return \Response::json($formatted_tags);
    }
    
}
