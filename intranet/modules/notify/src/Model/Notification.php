<?php

namespace Rikkei\Notify\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Notify\Model\NotifyReciever;
use Rikkei\Core\Model\User;
use Rikkei\Notify\View\NotifyView;

class Notification extends CoreModel
{
    protected $table = 'notifications';
    protected $fillable = ['actor_id', 'link', 'content', 'schedule_code', 'icon', 'category_id', 'type', 'content_detail'];

    /*
     * get image url of this object
     */
    public function getImage($item = [])
    {
        if (!$item) {
            $item = $this->toArray();
        }
        return NotifyView::getImage($item);
    }

    /*
     * get notify link
     */
    public function getLink()
    {
        return NotifyView::fixLink($this->id, $this->link);
    }

    /*
     * get display custom time
     */
    public function getDiffTime()
    {
        return NotifyView::diffTime($this->updated_at);
    }

    /*
     * get user that belongs to
     */
    public function actor()
    {
        return $this->belongsTo('\Rikkei\Core\Model\User', 'actor_id', 'employee_id');
    }

    /**
     * get notifications of current user
     * @param type $filters
     * @param type $isJson
     * @return type
     */
    public static function getByUser($filters = [], $isJson = false)
    {
        $userId = auth()->id();
        $defaultFitler = [
            'orderby' => 'noti.updated_at',
            'order' => 'desc',
            'per_page' => NotifyView::PER_PAGE,
            'page' => 1,
            'last_id' => null
        ];
        $filters = array_merge($defaultFitler, $filters);

        $collection = self::select(
            'noti.id',
            'noti.actor_id',
            'noti.link',
            'noti.content',
            'noti.icon',
            'noti.updated_at',
            'reci.read_at',
            'user.avatar_url as image'
        )
            ->from(self::getTableName() . ' as noti')
            ->join(NotifyReciever::getTableName() . ' as reci', function ($join) use ($userId) {
                $join->on('noti.id', '=', 'reci.notify_id')
                        ->where('reciever_id', '=', $userId);
            })
            ->leftJoin(User::getTableName() . ' as user', 'noti.actor_id', '=', 'user.employee_id')
            ->where('noti.type', NotifyView::TYPE_MENU)
            ->groupBy('noti.id')
            ->orderBy($filters['orderby'], $filters['order']);
        if (is_numeric($filters['last_id'])) {
            $collection->where('noti.id', '>', $filters['last_id']);
            $collection = $collection->get();
        } else {
            $collection = $collection->paginate($filters['per_page']);
        }

        if ($isJson) {
            $collection = self::filterJsonData($collection);
        }
        return $collection;
    }

    /**
     * filter json data
     * @param type $collection
     * @return type
     */
    public static function filterJsonData($collection)
    {
        //filter data
        $collection->map(function ($item) {
            $item->image = $item->getImage();
            $item->timestamp = $item->updated_at->timestamp;
            $item->link = NotifyView::fixLink($item->id, $item->link);
            return $item;
        });
        return $collection;
    }

    /**
     * get total notify not read
     * @return integer
     */
    public static function getTotalNotRead()
    {
        return NotifyReciever::where('reciever_id', auth()->id())
                ->whereNull('read_at')
                ->count();
    }

    /**
     * cron job run to delete old notify 2 month before
     */
    public static function cronDeleteNotify()
    {
        //2 month before
        $dateBeforeMonth = \Carbon\Carbon::now()->subMonthsNoOverflow(2)->toDateString();
        self::where(\DB::raw('DATE(updated_at)'), '<=', $dateBeforeMonth)
                ->delete();
    }
}

