<?php

namespace Rikkei\Recruitment\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Recruitment\Model\CddMailSent;
use Rikkei\Resource\Model\Programs;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\CoreFile;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\ResourceRequest;
use GuzzleHttp\Client;
use Rikkei\Api\Helper\ApiConst;
use Validator;
use File;

class MailMarketingController extends Controller
{
    public function index()
    {
        $templatePath = RIKKEI_RECRUITMENT_PATH . 'resources/views/email-marketing/mail-template-content';
        $templateFiles = File::files($templatePath);
        $templates = [];
        foreach ($templateFiles as $file){
            $name = explode('.', basename($file))[0];
            $templates[$name] = $name;
        }
        return view('recruitment::email-marketing.index', [
            'programingLanguages' => Programs::getListOption(),
            'emailSubject' => CoreConfigData::getValueDb('email-marketing.subject'),
            'emailContent' => CoreConfigData::getValueDb('email-marketing.content'),
            'templates' => $templates,
        ]);
    }

    public function getRequestDetailFilter(Request $request)
    {
        $id = $request->get('id');
        if (!$id) {
            return response()->json(trans('core::view.Not found item'), 404);
        }
        $rsRequest = ResourceRequest::find($id);
        if (!$rsRequest) {
            return response()->json(trans('core::view.Not found item'), 404);
        }
        return [
            'request' => [
                'id' => $id,
                'title' => $rsRequest->title,
            ],
            'type' => $rsRequest->requestTypes
                ->pluck('type')
                ->toArray(),
            'position' => $rsRequest->requestTeam()
                ->withPivot(['position_apply'])
                ->pluck('position_apply')
                ->toArray(),
            'progIds' => $rsRequest->requestProgramming
                ->pluck('id')
                ->toArray(),
        ];
    }

    /*
     * get candidate collection
     */
    public function getCandidates(Request $request)
    {
        try {
            $dataNotSend = $request->get('not_send');
            $dataSent = $request->get('sent');
            $dataNotSend['is_sent'] = false;
            $dataSent['is_sent'] = true;
            $this->preFilterStatus($dataNotSend);
            $requestId = isset($dataNotSend['filter']['except']['request_id']) ? $dataNotSend['filter']['except']['request_id'] : null;
            if (!isset($dataSent['orderby']) || !$dataSent['orderby']) {
                $dataSent['orderby'] = [
                    'cdd_mail.sent_date' => 'desc',
                    'cdd.created_at' => 'desc',
                ];
            }
            if (!isset($dataNotSend['orderby']) || !$dataNotSend['orderby']) {
                $dataNotSend['orderby'] = [
                    'cdd.interested' => 'desc',
                    'updated_date' => 'asc',
                    'cdd.created_at' => 'asc'
                ];
            }
            return [
                'sent' => CddMailSent::getCandidates($dataSent),
                'not_send' => CddMailSent::getCandidates($dataNotSend),
                'request' => $requestId ? ResourceRequest::find($requestId, ['id', 'title']) : null,
            ];
        } catch (\Excpetion $ex) {
            return response()->json(trans('core::message.Error system, please try later!'), 500);
        }
    }

    /*
     * render preview email
     */
    public function previewEmail(Request $request)
    {
        $template = 'default';
        return view('recruitment::email-marketing.mail-templates.' . $template, [
            'data' => [
                'name' => 'Candidate name',
                'email' => 'candidate@mail.com',
                'content' => CoreConfigData::getValueDb('email-marketing.content'),
            ]
        ]);
    }

    /*
     * send email
     */
    public function sendMail(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'subject' => 'required|max:255',
            'request_id' => 'required'
        ]);
        $validate->setAttributeNames([
            'subject' => trans('recruitment::view.Email subject'),
            'request_id' => trans('resource::view.Request'),
        ]);
        if ($validate->fails()) {
            return response()->json(trans('core::message.Error input data!'), 422);
        }

        $files = $request->file('files');
        $isSendAll = (int) $request->get('is_send_all');
        if ($isSendAll) {
            $filterData = $request->get('filterData');
            $filterData = $filterData ? json_decode($filterData, true) : [];
            $dataNotSend = isset($filterData['not_send']) ? $filterData['not_send'] : [];
            $this->preFilterStatus($dataNotSend);
            $candidates = CddMailSent::getCandidates($dataNotSend);
        } else {
            $candidateIds = $request->get('candidate_ids');
            $candidates = CddMailSent::getCandidateByIds($candidateIds);
        }

        //save config mail
        $subject = $request->get('subject');
        $content = $request->get('content');
        $this->saveConfigMail($request);

        if ($candidates->isEmpty()) {
            return response()->json(trans('core::view.Not found item'), 404);
        }

        $attachmentFiles = [];
        if ($files) {
            $folderFile = 'email-marketing';
            CoreFile::getInstance()->createDir($folderFile);
            foreach ($files as $file) {
                $fileName = CoreFile::getInstance()->toSlugName($file->getClientOriginalName());
                $fullFolderPath = storage_path('app/' . $folderFile);
                $file->move($fullFolderPath, $fileName);
                @chmod($fullFolderPath . '/' . $fileName, CoreFile::ACCESS_PUBLIC);
                $attachmentFiles[] = $fullFolderPath . '/' . $fileName;
            }
        }

        $template = 'default';
        $dateNow = \Carbon\Carbon::now()->toDateTimeString();
        $requestId = $request->get('request_id');
        DB::beginTransaction();
        try {
            $queueData = [];
            $listCddIds = [];
            $dataSentInsert = [];
            foreach ($candidates as $cdd) {
                $listCddIds[] = $cdd->id;
                $dataSentInsert[] = [
                    'candidate_id' => $cdd->id,
                    'request_id' => $requestId,
                    'sent_date' => $dateNow
                ];

                $subject = preg_replace(
                    ['/\{\{\sname\s\}\}/'],
                    [$cdd->fullname],
                    $subject
                );
                $content = preg_replace(
                    ['/\{\{\sname\s\}\}/'],
                    [$cdd->fullname],
                    $content
                );
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($cdd->email)
                    ->setSubject($subject)
                    ->setTemplate('recruitment::email-marketing.mail-templates.' . $template, [
                        'name' => $cdd->fullname,
                        'email' => $cdd->email,
                        'content' => $content,
                    ]);
                if ($attachmentFiles) {
                    foreach ($attachmentFiles as $filePath) {
                        $emailQueue->addAttachment($filePath);
                    }
                }
                $queueData[] = $emailQueue->getValue();
            }
            if ($queueData) {
                EmailQueue::insert($queueData);
            }
            //remove available data
            CddMailSent::where('request_id', $requestId)
                    ->whereIn('candidate_id', $listCddIds)
                    ->delete();
            //insert new data
            if ($dataSentInsert) {
                CddMailSent::insert($dataSentInsert);
            }

            DB::commit();
            return response()->json(['message' => trans('recruitment::message.Send email success')]);
        } catch (\Exception $ex) {
            DB::rollback();
            if ($attachmentFiles) {
                foreach ($attachmentFiles as $filePath) {
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }
            \Log::info($ex);
            return response()->json(trans('core::message.Error system, please try later!'), 500);
        }
    }

    public function preFilterStatus(&$dataNotSend)
    {
        if (!isset($dataNotSend['filter'])) {
            $dataNotSend['filter'] = [];
        }
        if (!isset($dataNotSend['filter']['except'])) {
            $dataNotSend['filter']['except'] = [];
        }
        if (!isset($dataNotSend['filter']['except']['status']) || !$dataNotSend['filter']['except']['status']) {
            $dataNotSend['filter']['except']['status'] = array_keys(getOptions::getInstance()->cddFailStepStatuses());
        }
    }

    /**
     * save email subject, content
     */
    public function saveConfigMail(Request $request)
    {
        $configSubject = CoreConfigData::getItem('email-marketing.subject');
        $configSubject->value = $request->get('subject');
        $configSubject->save();
        $configContent = CoreConfigData::getItem('email-marketing.content');
        $configContent->value = $request->get('content');
        $configContent->save();
        return response()->json(true);
    }

    /**
     * get template mail content
     * @param Request $request
     * @return json
     */
    public function getTemplateContent(Request $request)
    {
        $template = $request->get('template');
        $requestId = $request->get('request_id');
        if (!$template) {
            return response()->json(['content' => '']);
        }
        $viewFile = 'recruitment::email-marketing.mail-template-content.' . $template;
        if (!view()->exists($viewFile)) {
            return response()->json(['content' => '']);
        }
        $webvnArticleLink = null;
        //get article recruitment link from webvn
        if ($requestId && $template != 'blank') {
            $webvnArticleLink = $this->getArticleRecruitmentLink($requestId);
        }
        return response()->json([
            'content' => view($viewFile, [
                'articleLink' => $webvnArticleLink
            ])->render()
        ]);
    }

    /*
     * get article link from webvn by request id
     */
    public function getArticleRecruitmentLink($requestId)
    {
        $client = new Client(['verify' => false]);
        $configWebvnApi = config('services.webvn_api');
        $urlGetArticleByRequest = trim($configWebvnApi['base_url'], '/') . '/api/' . trim($configWebvnApi['url_get_article_request'], '/'); 
        try {
            $responseArticle = $client->request('GET', $urlGetArticleByRequest, [
                'headers' => [
                    'Content-type' => 'application/json'
                ],
                'query' => [
                    'request_id' => $requestId,
                    'get_view_url' => 1,
                ]
            ]);
            if ($responseArticle->getStatusCode() == ApiConst::CODE_SUCCESS) {
                $responseData = json_decode($responseArticle->getBody()->getContents(), true);
                if (!isset($responseData['view_url'])) {
                    return null;
                }
                return $responseData['view_url'];
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function viewCddWillSend(Request $request)
    {
        $candidateIds = $request->get('candidate_ids');
        if (!$candidateIds) {
            return response()->json(trans('core::view.Not found item'), 404);
        }
        return \Rikkei\Resource\Model\Candidate::whereIn('id', $candidateIds)
                ->select(DB::raw('CONCAT(fullname, " - ", email) as name_email'))
                ->get();
    }
}
