<?php

namespace Rikkei\Notify\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\Builder;
use Rikkei\Core\Model\User;
use Carbon\Carbon;

class NotifyReciever extends CoreModel
{
    protected $table = 'notify_reciever';
    protected $fillable = ['notify_id', 'reciever_id', 'actor_id', 'read_at'];
    protected $primaryKey = ['notify_id', 'reciever_id'];
    public $timestamps = false;
    public $incrementing = false;

    /**
     * set custom primary key save
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->primaryKey;
        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }
        return $query;
    }

    /*
     * get custom primary key save
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }
        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }
        return $this->getAttribute($keyName);
    }

    /*
     * set read notification
     */
    public static function setRead($notifyId, $recieverIds)
    {
        if (!is_array($recieverIds)) {
            $recieverIds = [$recieverIds];
        }
        if (!is_array($notifyId)) {
            $notifyId = [$notifyId];
        }
        return self::whereIn('notify_id', $notifyId)
                ->whereIn('reciever_id', $recieverIds)
                ->whereNull('read_at')
                ->update(['read_at' => Carbon::now()->toDateTimeString()]);
    }

    /*
     * set read notification
     */
    public static function setReadUrl($url, $recieverIds)
    {
        if (!is_array($recieverIds)) {
            $recieverIds = [$recieverIds];
        }
        $url = trim($url, '/');
        $domainHost = request()->getSchemeAndHttpHost();
        $notifyIds = self::from(self::getTableName() . ' as receiver')
                ->join(Notification::getTableName() . ' as noti', 'receiver.notify_id', '=', 'noti.id');
        if ($url == $domainHost) {
            $notifyIds->where('noti.link', $url);
        } else {
            $notifyIds->where('noti.link', 'like', $url . '%');
        }
        $notifyIds = $notifyIds->groupBy('noti.id')
                ->lists('noti.id')
                ->toArray();
        if (!$notifyIds) {
            return 0;
        }
        $update = self::whereIn('notify_id', $notifyIds)
                ->whereIn('reciever_id', $recieverIds)
                ->whereNull('read_at')
                ->update(['read_at' => Carbon::now()->toDateTimeString()]);
        self::decreaseNotiNum($recieverIds);
        return $update;
    }

    /*
     * set read all notification
     */
    public static function setReadAll($recieverId = null)
    {
        if (!$recieverId) {
            $recieverId = auth()->id();
        }
        return self::where('reciever_id', $recieverId)
                ->whereNull('read_at')
                ->update(['read_at' => Carbon::now()->toDateTimeString()]);
    }

    /*
     * set not read or create notification
     */
    public static function setNotReadOrCreate($notifyId, $recieverIds)
    {
        if (!is_array($recieverIds)) {
            $recieverIds = [$recieverIds];
        }
        foreach ($recieverIds as $recieverId) {
            $item = self::where('notify_id', $notifyId)
                    ->where('reciever_id', $recieverId)
                    ->first();
            if ($item) {
                $item->update(['read_at' => null]);
            } else {
                $item = self::create([
                    'notify_id' => $notifyId,
                    'reciever_id' => $recieverId,
                ]);
            }
        }
        self::increaseNotiNum($recieverIds);
    }

    /**
     * increment notify number
     * @return type
     */
    public static function increaseNotiNum($recieverIds)
    {
        return User::whereIn('employee_id', $recieverIds)
                ->increment('notify_num');
    }

    /*
     * decrement notify number
     */
    public static function decreaseNotiNum($recieverIds)
    {
        return User::whereIn('employee_id', $recieverIds)
                ->where('notify_num', '>', 0)
                ->decrement('notify_num');
    }

    /**
     * reset notify number
     * @param type $userId
     * @return type
     */
    public static function resetNotiNum($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }
        $user->notify_num = 0;
        return $user->save();
    }
}
