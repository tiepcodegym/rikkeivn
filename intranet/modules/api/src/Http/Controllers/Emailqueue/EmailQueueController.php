<?php
namespace Rikkei\Api\Http\Controllers\Emailqueue;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Log;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\EmailQueue;
use DB;
use Illuminate\Support\Facades\Config as SupportConfig;
use Illuminate\Support\Facades\Storage;

class EmailQueueController extends Controller
{
    /**
     * API store data to table email_queue
     *
     * @param Request $request
     * @return json     success 1: ok, success 0: fail
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
                        
            $dataEmails = $request->all();

            $dataInsert = [];
            foreach ($dataEmails as $data) {
                // Validate data
                if (!$this->isValid($data)) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Subject, template, to email are required!',
                    ]);
                }

                $template = $data['template'];
                if (!empty($template['attach']['data']) && empty($template['attach']['ext'])) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'attach_ext is required!',
                    ]);
                }
                $dataTemplate = []; 
                if (isset($template['data'])) {
                    foreach ($template['data'] as $key => $value) {
                        $dataTemplate[$key] = $value;
                    }
                }

                $queue = new EmailQueue();
                // Store into email_queue table
                $queue->setTo($data['email_to'])
                        ->setSubject($data['subject'])
                        ->setTemplate($template['template_path'], $dataTemplate);

                if (!empty($data['cc'])) {
                    foreach ($data['cc'] as $emailCc) {
                        $queue->addCc($emailCc);
                    }
                }

                if (!empty($data['bcc'])) {
                    foreach ($data['bcc'] as $emailBcc) {
                        $queue->addBcc($emailBcc);
                    }
                }

                if (!empty($data['email_from'])) {
                    $queue->setFrom($data['email_from']);
                }

                if (!empty($data['sent_plan'])) {
                    $queue->sent_plan($data['sent_plan']);
                }

                //File attach
                if (isset($template['attach']) && count($template['attach'])) {
                    $checkFile = $this->isValidFile($template['attach']);
                    if (!empty($checkFile)) {
                        return response()->json([
                            'success' => 0,
                            'message' => $checkFile,
                        ]);
                    }

                    $fileAttachs = $template['attach'];
                    foreach ($fileAttachs as $key => $item) {
                        try {
                            $decodedFile = base64_decode($item['data']);
                        } catch (Exception $ex) {
                            $decodedFile = null;
                        }
                        $pathFolder = SupportConfig::get('general.upload_storage_public_folder') . '/'. EmailQueue::UPLOAD_FILE_FOLDER;
                        if ($decodedFile) {
                            $fileName = $item['name'].'_'.str_random(5) .'_'. time() .'.'. $item['ext'];
                            $filePath = trim($pathFolder, '/') . '/'. $fileName;
                            Storage::put(
                                $filePath,
                                $decodedFile
                            );
    
                            $myFile = storage_path("app/".SupportConfig::get('general.upload_storage_public_folder')."/".EmailQueue::UPLOAD_FILE_FOLDER.$fileName);
                            $queue->addAttachment($myFile);
                        }
                    }
                }

                $dataInsert[] = $queue->getValue();
            }

            if (!empty($dataInsert)) {
                EmailQueue::insert($dataInsert);
            }
            DB::commit();
            $result = [
                'success' => 1,
                'message' => 'Email queue store succesfully!',
            ];

            return response()->json($result);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollBack();
            return response()->json([
                'success' => 0,
                'message' => 'Email queue store error!',
            ]);
        }
    }

    /**
     * Validate data
     *
     * @param Illuminate\Http\Request $valid
     * @return boolean  true is valid
     */
    public function isValid($valid)
    {
        return !empty($valid['subject']) && !empty($valid['template']) && !empty($valid['email_to']);
    }

    public function isValidFile($dataFiles)
    {
        foreach ($dataFiles as $key => $file) {
            if (empty($file['data']) || empty($file['ext']) || empty($file['name'])) {
                return 'Data, ext, name ['.$key.'] is required.';
            }
        }
        return '';
    }
}