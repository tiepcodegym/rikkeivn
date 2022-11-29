<?php

namespace Rikkei\ManageTime\Model;

use DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\Model\TimekeepingLockHistories;

class TimekeepingLock extends CoreModel
{
	protected $fillable = [
        'timekeeping_table_id',
        'time_close_lock',
        'time_open_lock',
    ];

    /**
     * Get all of the post's comments.
     */
    public function lockHistories()
    {
        return $this->hasMany(TimekeepingLockHistories::class, 'timekeeping_lock_id');
    }

    /**
     * update time lock open manage timekeeping table
     * @param  [int] $id
     * @param  [datetine] $time
     * @return [type]
     */
    public function updateLockOpen($id, $time)
    {
        return self::where('timekeeping_table_id', $id)
       		->whereNull('time_open_lock')
        	->update(['time_open_lock' => $time]);
    }

    /**
     * get information table timekeeping lock by timekeeping_table_id
     * @param  int $idTable
     * @return collections
     */
    public function getInforFirst($idTable, $idLock)
    {
    	return self::where('timekeeping_table_id', $idTable)
            ->where('id', $idLock)->first();
    }
}