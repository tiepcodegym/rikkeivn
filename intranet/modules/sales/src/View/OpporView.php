<?php

namespace Rikkei\Sales\View;

class OpporView
{
    const PRIORITY_HIGH = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_LOW = 3;

    const STT_OPEN = 1;
    const STT_SUBMIT = 2;
    const STT_PROCESSING = 3;
    const STT_CANCEL = 4;
    const STT_FAIL = 5;
    const STT_PASS = 6;
    const STT_CLOSED = 7;

    public static function priorityLabels()
    {
        return [
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_LOW => 'Low'
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STT_OPEN => 'Open',
            self::STT_PROCESSING => 'Processing',
            self::STT_CANCEL => 'Canceled',
            self::STT_CLOSED => 'Closed'
        ];
    }

    

    public static function listLanguages()
    {
        return [
            'jp' => trans('sales::view.Japanese'),
            'en' => trans('sales::view.English'),
            'vi' => trans('sales::view.Vietnamese')
        ];
    }

    public static function listLocations()
    {
        return [
            'tokyo' => 'Tokyo',
            'hn' => trans('sales::view.Hanoi'),
            'dn' => trans('sales::view.Danang')
        ];
    }

    public static function renderStatusHtml($status, $statuses, $class = 'callout', $progress = null)
    {
        if (!isset($statuses[$status])) {
            return null;
        }
        $html = '<div class="'. $class .' text-center white-space-nowrap ' . $class;
        $progressHtml = '<div class="progress text-center"><div style="width: '. $progress .'%" class="progress-bar';
        switch ($status) {
            case self::STT_OPEN:
                $html .=  '-info">' . $statuses[$status];
                $progressHtml .= '-info';
                break;
            case self::STT_PROCESSING:
                $html .= '-warning">' . $statuses[$status];
                $progressHtml .= '-warning';
                break;
            case self::STT_SUBMIT:
                $html .= '-info">' . $statuses[$status];
                $progressHtml .= '-info';
                break;
            case self::STT_PASS:
                $html .= '-success">' . $statuses[$status];
                $progressHtml .= '-success';
                break;
            case self::STT_CANCEL:
            case self::STT_FAIL:
            case self::STT_CLOSED:
                $html .= '-danger">' . $statuses[$status];
                $progressHtml .= '-danger';
                break;
            default:
                return null;
        }
        $progressHtml .= '">' . $progress . '%' . '</div></div>';
        if ($progress === null) {
            return $html .= '</div>';
        }
        return $html . '</div>' . $progressHtml;
    }
}