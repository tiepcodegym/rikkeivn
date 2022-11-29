<?php

namespace Rikkei\Event\Http\Controllers;

use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\View\Permission;
use Exception;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\File;

class SendController extends Controller
{
    /**
     * show upload file view
     */
    public function compose()
    {
        if (app('request')->isMethod('post')) {
            return $this->composePost();
        }
        Breadcrumb::add('Email compose');
        return view('event::send_email.compose');
    }

    /**
     * send email for employees
     */
    public function composePost()
    {
        $validator = Validator::make(Input::all(), [
            'to' => 'required',
            'subject' => 'required',
            'content' => 'required',
            'file.*' => 'file|max:' . (10 * 1000)
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response);
        }
        $mailTo = explode(',', Input::get('to'));
        $emailSend = new EmailQueue();
        $i = 0;
        $mailSuccess = '';
        foreach ($mailTo as $mail) {
            $mail = trim($mail);
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if ($i === 0) {
                $emailSend->setTo($mail);
            } else {
                $emailSend->addCc($mail);
            }
            $mailSuccess .= $mail . '; ';
            ++$i;
        }
        if ($i === 0) {
            $response['status'] = 0;
            $response['message'] = Lang::get('event::message.miss to');
            return response()->json($response);
        }
        $mailSuccess = substr($mailSuccess, 0, -2);
        $emailSend->setSubject(Input::get('subject'));
        if (Input::file('file')) {
            $i = 0;
            foreach (Input::file('file') as $file) {
                if (!file_exists($file->getRealPath()) || !is_file($file->getRealPath())) {
                    continue;
                }
                $pathFile = dirname($file->getRealPath()) . '/' . $file->getClientOriginalName();
                try {
                    File::move($file->getRealPath(), $pathFile);
                } catch (Exception $ex) {
                    Log::error($ex);
                    continue;
                }
                $emailSend->addAttachment($pathFile);
                ++$i;
            }
            if ($i === 0) {
                $response['status'] = 0;
                $response['message'] = Lang::get('event::message.miss file');
                return response()->json($response);
            }
        }
        // reply
        $replies = [];
        if (Input::get('reply')) {
            $replies = explode(',', Input::get('reply'));
            foreach ($replies as $reply) {
                $emailSend->addReply($reply);
            }
        }
        try {
            $emailSend->sentNow([
                'html' => Input::get('content'),
            ]);
            $response['status'] = 1;
            $response['message'] = Lang::get('core::message.Send mail success')
                . ': <b>' . $mailSuccess . '</b>';
            $user = Permission::getInstance()->getEmployee();
            Log::info(sprintf('Email compose. Creator: %s - %s - %s. To: %s',
                $user->id, $user->email, $user->name, $mailSuccess), $replies);
            return response()->json($response);
        } catch (Exception $ex) {
            Log::error($ex);
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            return response()->json($response);
        }
    }
}