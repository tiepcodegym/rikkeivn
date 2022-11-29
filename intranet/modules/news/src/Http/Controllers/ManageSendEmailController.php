<?php

namespace Rikkei\News\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\News\Model\Post;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\Validator;

class ManageSendEmailController extends Controller
{
    /**
     * after construct
     */
    public function _construct() {
        Menu::setActive('admin', 'news');
    }

    /**
     * list post
     */
    public function index()
    {
        Breadcrumb::add('Send email post');
        return view('news::manage.email.send', [
            'titleHeadPage' => Lang::get('news::view.Send email post'),
        ]);
    }
    
    /**
     * list post ajax
     */
    public function listPostAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
                Post::searchAjax([
                    'q' => Input::get('q'),
                    'page' => Input::get('page')
                ])
            );
    }
    
    /**
     * send email to all employee
     */
    public function post()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $postIds = Input::get('post');
        $response = [];
        $message = Lang::get('news::message.Please choose post');
        if (!$postIds || !count($postIds)) {
            $response['error'] = 1;
            $response['message'] = $message;
            return response()->json($response);
        }
        $dataPost = [];
        $postTypes = ['feature', 'week', 'more'];
        $count = 0;
        $week = Input::get('week');
        if (!$week) {
            $week = Carbon::parse()->format('W');
        }
        $date = Carbon::now();
        $dateOfWeek = $date->setISODate($date->format('Y'), $week);
        $startWeek = $dateOfWeek->copy()->startOfWeek()->format('d');
        $endWeek = $dateOfWeek->copy()->endOfWeek()->format('d/m/Y');

        // get post to send email
        foreach ($postIds as $postType => $ids) {
            if (!in_array($postType, $postTypes)) {
                continue;
            }
            $dataPost[$postType] = Post::getPostFollowIds($ids);
            $count += count($dataPost[$postType]);
        }
        if (!$count) {
            $response['error'] = 1;
            $response['message'] = $message;
            return response()->json($response);
        }
        // render template to sendemail
        $postEmaliTemplate = view('news::manage.email.template.news_ver_3', [
//        $postEmaliTemplate = view('news::manage.email.template.news-ver-2', [
            'dataPost' => $dataPost,
            'week' => $week,
//            'week' => $week . ' (' . $startWeek . ' - ' . $endWeek . ')',
        ])->render();
        if (Input::get('preview')) {
            $response['success'] = 1;
            $response['html'] = $postEmaliTemplate;
            $response['popup'] = 1;
            return response()->json($response);
        }
        $validator = Validator::make(Input::all(), [
            'mail_to' => 'required|email',
        ]);
        if ($validator->fails()) {
            $response['error'] = 1;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }
        $mailTo = Input::get('mail_to');
        $emailSend = new EmailQueue();
        try {
            $emailSend->setTo($mailTo)
                ->setSubject(Lang::get('news::view.News internal week :week', [
                    'week' => $week
                ]))
                ->sentNow([
                    'html' => $postEmaliTemplate
                ]);
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Send mail success')
                . ': <b>' . $mailTo . '</b>';
            return response()->json($response);
        } catch (Exception $ex) {
            Log::error($ex);
            $response['error'] = 1;
            $response['message'] = $ex->getMessage();
            return response()->json($response);
        }
        /*$employees = Employee::getEmailNameEmployeeJoin();
        if (!$employees || !count($employees)) {
            $response['error'] = 1;
            $response['message'] = Lang::get('news::message.Not found employee to send email');
            return response()->json($response);
        }
        DB::beginTransaction();
        try {
            ViewNews::createFileTemplateEmail($postEmaliTemplate);
            foreach ($employees as $employee) {
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($employee->email, $employee->name)
                    ->setSubject(Lang::get('news::view.News internal week :week', [
                        'week' => $week
                    ]))
                    ->setTemplate('news::manage.email.template.queue')
                    ->save();
            }
            //set notify
            \RkNotify::put(
                $employees->lists('id')->toArray(),
                Lang::get('news::view.News internal week :week', ['week' => $week]),
                URL::to('/'),
                ['actor_id' => null]
            );
            DB::commit();
            $response['success'] = 1;
            $response['popup'] = 1;
            $response['refresh'] = URL::route('news::manage.email.send.index');
            Session::flash(
                'messages', [
                        'success'=> [
                            Lang::get('core::message.System will send email in 30 min'),
                        ]
                    ]
            );
            return response()->json($response);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            $response['error'] = 1;
            $response['message'] = Lang::get('core::message.Error system');
            return response()->json($response);
        }*/
    }
}