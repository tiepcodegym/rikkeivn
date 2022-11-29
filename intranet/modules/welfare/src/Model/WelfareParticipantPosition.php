<?php
namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;

/**
 * Class WelfareFile
 *
 * @package Rikkei\Welfare\Models
 * @property int $id
 * @property int $wel_id
 * @property string $file
 * @property string $fileUrl
 * @property datetime created_at
 */
class WelfareParticipantPosition extends CoreModel
{
    /**
     * @var string
     */
    protected $table = 'wel_participant_positions';

    /**
     * @var array
     */
    protected $fillable = [
        'wel_id',
        'role_id',
    ];

    /**
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getParticipantPosition($id)
    {
        return static::Where('wel_id', $id)->get();
    }

    /**
     *
     * @param int $eventId
     * @param array $positionOption
     *
     */
    public static function saveEvent($eventId, array $positionOption = [])
    {
        $event = Event::find($eventId);

        if(!$event) {
            return;
        }

        $welParicipantPosition = self::getParticipantPosition($eventId);

        if ($welParicipantPosition && count($welParicipantPosition)) {
            $event->welfareParticipantPosition()->sync($positionOption);
        }
        else {
            $event->welfareParticipantPosition()->attach($positionOption);
        }
    }

    /**
     * List participant position of welfare
     *
     * @param int $welId
     * @return array
     */
    public static function getListParticipantPosition($welId)
    {
        return self::where('wel_id', $welId)
                ->lists('role_id')
                ->toArray();
    }
}
