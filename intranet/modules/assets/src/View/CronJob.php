<?php

namespace Rikkei\Assets\View;

use Lang;
use Rikkei\Assets\Model\AssetItem;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Notify\Classes\RkNotify;
use Illuminate\Support\Facades\DB;

class CronJob extends Controller
{
    public static function sendEmailEmpolyeeNotConfirmAsset()
    {
        $employeeList = AssetItem::getEmployeeNotConfirmAssets();
        $selected = [];
        foreach ($employeeList as $key => $item) {
            if (!in_array($item->employee_email, $selected)) {
                $selected[] = $item->employee_email;
            } else {
                $employeeList->forget($key);
            }
        }
        $dataEmail = array();
        foreach ($employeeList as $key => $value) {
            $data['id']           = $value->request_id;
            $data['request_name'] = $value->request_name;
            $data['subject']      = Lang::get('asset::view.[Rikkeisoft] Confirm allocation new asset').' '.ucfirst($value->request_name);
            $data['content']      = $value->request_reason;
            $data['sent_to']      = $value->employee_email;
            $data['name_sent_to'] = $value->employee_name;
            $data['reviewer_name'] = $value->reviewer_name;
            $data['petitioner_name'] = $value->employee_name;
            $data['creator_name'] = $value->creator_name;
            $data['request_date'] = $value->request_date;
            $data['link']       = route('asset::profile.view-personal-asset');
            $data['template']   = 'asset::request.mail.send_email_employee_not_confirm';
            $tmp = self::pushEmailToArray($data);
            if (!isset($tmp['option'])) {
                $tmp['option'] = null;
            }
            $contentDetail = RkNotify::renderSections($data['template'], $data);
            $dataEmail[] = $tmp;
            //set notify
            \RkNotify::put(
                $value->employee_id,
                Lang::get('asset::view.IT department requires you to confirm your assets.'),
                $data['link'],
                ['actor_id' => null, 'icon' => 'fill.png', 'category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => $contentDetail]
            );
        }
        EmailQueue::insert($dataEmail);
    }
    /**
     * [pushEmailToArray lấy giá trị của các trường để insert vào bảng email_queues
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public static function pushEmailToArray($data)
    {
        $template   = $data['template'];
        $subject    = $data['subject'];
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($data['sent_to'])
                   ->setSubject($subject)
                   ->setTemplate($template, $data);
        if (isset($data['created_by'])) {
            $emailQueue->addCc($data['created_by']);
        }
        return $emailQueue->getValue();
    }

    /**
     * alert notification asset out of date
     */
    public static function AlertAssetOutOfDate()
    {
        $dateNow = \Carbon\Carbon::now()->toDateString();
        $assetTbl = AssetItem::getTableName();
        $assets = AssetItem::select(
                $assetTbl . '.id',
                $assetTbl . '.code',
                $assetTbl . '.name',
                $assetTbl . '.out_of_date',
                $assetTbl . '.prefix',
                'emp.id as emp_id',
                'emp.name as emp_name',
                'emp.email as emp_email'
            )
            ->join(\Rikkei\Team\Model\Employee::getTableName() . ' as emp', 'emp.id', '=', $assetTbl . '.employee_id')
            ->whereNotNull($assetTbl . '.out_of_date')
            ->whereNotNull($assetTbl . '.employee_id')
            ->where(DB::raw('SUBDATE('. $assetTbl .'.out_of_date, '. $assetTbl .'.days_before_alert_ood)'), $dateNow)
            ->get();

        if ($assets->isEmpty()) {
            return;
        }

        $arrConvertPrefix = AssetConst::convertAssetPrefixToBranch();
        $arrAssetsPrefix = $assets->pluck('prefix')->toArray();
        $arrPrefix = [];
        foreach ($arrAssetsPrefix as $code) {
            $arrPrefix[$code] = isset($arrConvertPrefix[$code]) ? $arrConvertPrefix[$code] : $code;
        }
        $branchAdmins = [];
        $branchAssets = [];
        foreach ($arrPrefix as $code => $branchCode) {
            $branchAdmins[$code] = \Rikkei\Team\Model\Permission::builderEmployeesAllowAction('request.asset.approve', true, $branchCode)
                    ->get();
            $branchAssets[$code] = $assets->where('prefix', $code);
        }

        DB::beginTransaction();
        try {
            $mailSubject = trans('asset::view.mail_subject_out_of_date');
            //send mail to admin
            foreach ($branchAdmins as $code => $assetAdmins) {
                $lastAdmin = $assetAdmins->pop();
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($lastAdmin->email)
                    ->setSubject($mailSubject)
                    ->setTemplate('asset::item.mail.alert-out-of-date', [
                        'listAssets' => $branchAssets[$code]->toArray(),
                        'isAdmin' => 1,
                    ])
                    ->setNotify(
                        $lastAdmin->id,
                        $mailSubject . ' ('. trans('notify::view.Detail in mail') .')',
                        RkNotify::GMAIL_LINK,
                        [
                            'actor_id' => null,
                            'icon' => 'asset.png',
                            'category_id' => RkNotify::CATEGORY_ADMIN,
                        ]
                    );
                foreach ($assetAdmins as $admin) {
                    $emailQueue->addCc($admin->email)
                        ->addCcNotify($admin->id);
                }
                $emailQueue->save();
            }

            //send mail to employee
            $empAssets = $assets->groupBy('emp_email');
            foreach ($empAssets as $mail => $listAssets) {
                $firstAsset = $listAssets->first();
                $mailEmp = new EmailQueue();
                $mailEmp->setTo($mail)
                    ->setSubject($mailSubject)
                    ->setTemplate('asset::item.mail.alert-out-of-date', [
                        'empName' => $firstAsset->emp_name,
                        'listAssets' => $listAssets->toArray(),
                        'isAdmin' => 0
                    ])
                    ->setNotify(
                        $firstAsset->emp_id,
                        $mailSubject . ' ('. trans('notify::view.Detail in mail') .')',
                        RkNotify::GMAIL_LINK,
                        [
                            'actor_id' => null,
                            'icon' => 'asset.png',
                            'category_id' => RkNotify::CATEGORY_ADMIN,
                        ]
                    )
                    ->save();
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
