<?php

namespace Rikkei\FinesMoney\Model;

use Rikkei\Core\Model\CoreModel;

class FinesActionHistory extends CoreModel
{
    protected $table = 'fines_action_history';
    public $timestamps = true;

    /**
     * Get content history change
     *
     * @param $oldData
     * @param $newData
     * @return string
     */
    public function getContentHistory($oldData, $newData)
    {
        $dataChange = array_diff_assoc($newData, $oldData);
        if ($oldData['type'] == FinesMoney::TYPE_TURN_OFF) {
            unset($dataChange['amount']);
        }
        unset($dataChange['_token']);
        $obFineMoney = new FinesMoney();
        $status = $obFineMoney->getStatus();
        $content = "";

        if (!empty($dataChange)) {
            foreach ($dataChange as $key => $item) {
                if ($key == 'status_amount') {
                    $content .= trans("fines_money::view.content_log", [
                            'key' => trans("fines_money::view.{$key}"),
                            'old' => $status[$oldData[$key]],
                            'new' => $status[$item]
                        ]) . '<br>';
                } else {
                    $content .= trans("fines_money::view.content_log", [
                            'key' => trans("fines_money::view.{$key}"),
                            'old' => ($key == 'amount' && $oldData[$key]) ? $obFineMoney->formatMoney($oldData[$key]) : ($oldData[$key] ? $oldData[$key] : 'NULL'),
                            'new' => ($key == 'amount' && $item) ? $obFineMoney->formatMoney($item) : ($item ? $item : 'NULL'),
                        ]) . "<br>";
                }
            }
            return $content;
        }
    }

    /**
     * Save history
     * @param object $fineMoney
     * @param string $contentHis
     */
    public function saveFineHis($fineMoney, $contentHis)
    {
        FinesActionHistory::insert([
            'fines_money_id' => $fineMoney->id,
            'action' => $fineMoney->status_amount,
            'amount' => $fineMoney->amount,
            'type' => $fineMoney->type,
            'content' => $contentHis,
            'month' => $fineMoney->month,
            'year' => $fineMoney->year,
            'object_fines' => $fineMoney->employee_id,
            'checked_date' => date("Y-m-d H:i:s"),
            'checker_id' => auth()->user()->employee_id,
        ]);
    }
}
