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
class WelfareParticipantTeam extends CoreModel
{
    /**
     * @var string
     */
    protected $table = 'wel_participant_teams';

    /**
     * @var array
     */
    protected $fillable = [
        'wel_id',
        'team_id'
    ];

    /**
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getParticipantTeam($id)
    {
        return static::Where('wel_id', $id)->get();
    }

    /**
     *
     * @param int $eventId
     * @param array $teamOption
     *
     */
    public static function saveEvent($eventId, array $teamOption = [])
    {
        $event = Event::find($eventId);

        if(!$event) {
            return;
        }

        $welParicipantTeam = self::getParticipantTeam($eventId);

        if ($welParicipantTeam && count($welParicipantTeam)) {
            $event->welfareParticipantTeam()->sync($teamOption);
        }
        else {
            $event->welfareParticipantTeam()->attach($teamOption);
        }
    }

    /**
     * List participant team of welfare
     *
     * @param int $welId
     * @return array
     */
    public static function getListParticipantTeam($welId)
    {
        return self::where('wel_id', $welId)
                ->lists('team_id')
                ->toArray();
    }
}
