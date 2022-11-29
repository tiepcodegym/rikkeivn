<?php

namespace Rikkei\Welfare\Model;

use Rikkei\Core\Model\CoreModel;
use Carbon\Carbon;
use Rikkei\Team\View\Config as TeamConfig;
use Illuminate\Support\Facades\Lang;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends CoreModel
{
    use SoftDeletes;

    const IS_REGISTER_ONLINE = 1;
    const NOT_REISTER_ONLINE = 0;
    const SEND_MAIL_AUTO = 1;

    const STATUS_PLAN = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_COMPLETE = 3;
    const STATUS_CANCEL = 4;

    const IS_ATTACHED = 1;
    const NOT_ATTACHED = 0;

    const MAX_FILE_SIZE_UPLOAD = 26214400;

    /**
     * @var string
     */
    protected $table = 'welfares';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'welfare_group_id',
        'start_at_exec',
        'end_at_exec',
        'start_at_register',
        'end_at_register',
        'description',
        'wel_purpose_id',
        'wel_form_imp_id',
        'address',
        'join_number_plan',
        'join_number_exec',
        'status',
        'participant_desc',
        'is_register_online',
        'is_send_mail_auto',
        'is_allow_attachments',
        'is_same_fee',
    ];

    protected $attributes = [
        'is_register_online' => self::IS_REGISTER_ONLINE,
        'is_allow_attachments' => self::NOT_ATTACHED,
    ];

    /**
     * @return array option
     */
    public static function getOptionStatus()
    {
        return [
            self::STATUS_PLAN => Lang::get('welfare::view.Plan'),
            self::STATUS_PROCESSING => Lang::get('welfare::view.Processing'),
            self::STATUS_COMPLETE => Lang::get('welfare::view.Complete'),
            self::STATUS_CANCEL => Lang::get('welfare::view.Cancel')
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function welfareFile()
    {
        return $this->hasMany(WelfareFile::class, 'wel_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function welfareParticipantTeam()
    {
        return $this->belongsToMany(
            Team::class,
            'wel_participant_teams',
            'wel_id',
            'team_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function welfareParticipantPosition()
    {
        return $this->belongsToMany(
            Role::class,
            'wel_participant_positions',
            'wel_id',
            'role_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function welfareEmployee()
    {
        return $this->belongsToMany(Employee::class, 'wel_employee', 'wel_id', 'employee_id')->withTimestamps();
    }

    public function setWelfareGroupIdAttribute($value)
    {
        $this->attributes['welfare_group_id'] = $value ? : null;
    }

    public function setWelPurposeIdAttribute($value)
    {
        $this->attributes['wel_purpose_id'] = $value ? : null;
    }

    public function setStartAtRegisterAttribute($value)
    {
        $this->attributes['start_at_register'] = $value ? : null;
    }

    public function setEndAtRegisterAttribute($value)
    {
        $this->attributes['end_at_register'] = $value ? : null;
    }

    /**
     * get grid data
     *
     * @param [int] || null : $employeeId.
     * @return object
     */
    public static function getGridData($employeeId = null)
    {
        $pager = TeamConfig::getPagerData();
        $collection = self::select('welfares.id', 'welfares.name', 'welfare_groups.name as groupName', 'welfares.address',
            'welfares.start_at_exec', 'welfares.end_at_exec', 'welfares.end_at_register', 'wel_fee.fee_total_actual',
            'welfares.status', 'welfares.participant_desc', 'wel_fee.fee_total',
            'wel_fee.empl_trial_fee', 'wel_fee.empl_trial_company_fee', 'wel_fee.empl_offical_company_fee','welfares.is_register_online')
            ->leftjoin('welfare_groups', 'welfares.welfare_group_id', '=', 'welfare_groups.id')
            ->leftjoin('wel_fee', 'welfares.id', '=', 'wel_fee.wel_id');
            if ($employeeId) {
                $collection->leftjoin('wel_employee', 'welfares.id', '=', 'wel_employee.wel_id')
                            ->where('wel_employee.employee_id', '=', $employeeId);
            }
            $collection->orderBy($pager['order'], $pager['dir']);
        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get dataByID
     * @return item
     */
    public static function getItem($id)
    {
        $welPartner = DB::table('wel_partners')
            ->leftjoin('partners', 'partners.id', '=', 'wel_partners.partner_id')
            ->select('wel_partners.partner_id', 'partners.name as part_name', 'wel_partners.wel_id');

        $item = self::select('welfares.id', 'welfares.name', 'welfare_groups.name as groupName', 'welfares.address',
            'welfares.start_at_exec', 'welfares.end_at_exec', 'welfares.end_at_register', 'wel_fee.fee_total_actual',
            'welfares.status', 'welfares.participant_desc', 'wel_fee.fee_total', 'welfares.description as description',
            'wel_fee.empl_trial_fee', 'wel_fee.empl_trial_company_fee', 'wel_fee.empl_offical_company_fee',
            'wel_organizers.name as nameOrg', 'wel_purposes.name as namePur', 'i.part_name as namePart')
            ->leftjoin('welfare_groups', 'welfares.welfare_group_id', '=', 'welfare_groups.id')
            ->leftjoin(DB::raw('(' . $welPartner->toSql() . ') as i'), 'i.wel_id', '=', 'welfares.id')
            ->leftjoin('wel_fee', 'welfares.id', '=', 'wel_fee.wel_id')
            ->leftjoin('wel_organizers', 'wel_organizers.wel_id', '=', 'welfares.id')
            ->leftjoin('wel_purposes', 'wel_purposes.id', '=', 'welfares.wel_purpose_id')
            ->where('welfares.id', '=', $id)
            ->first();
        return $item;
    }

    /**
     * get public date of post
     *
     * @return string
     */
    public function getPublicDate()
    {
        if (!$this->public_at) {
            return null;
        }
        $date = Carbon::parse($this->public_at);
        return $date->format('d') . ' ' . Lang::get('news::view.month') . ' ' .
            $date->format('m, Y');
    }

    /**
     * get list post ajax
     *
     * @param array $option
     */
    public static function searchAjax(array $option = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 10,
            'q' => ''
        ];
        $option = array_merge($arrayDefault, $option);
        $collection = self::select('id', 'title', 'image')
            ->where('status', self::STATUS_PUBLIC)
            ->where('title', 'LIKE', '%' . $option['q'] . '%')
            ->orderBy('public_at', 'desc');
        self::pagerCollection($collection, $option['limit'], $option['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => e($item->title),
                'image' => $item->getImage(true),
            ];
        }
        return $result;
    }

    /**
     * Get Inforamtion Confirm of Event
     *
     * @param int $id
     * @return Event $event
     */
    public static function getBasicInformation($id)
    {
        return self::select('welfares.id as id', 'welfares.name as name', 'welfares.address', 'welfares.is_same_fee',
            DB::raw("DATE_FORMAT(welfares.start_at_exec, '%d/%m/%Y') as start_at_exec"),
            DB::raw("DATE_FORMAT(welfares.end_at_exec, '%d/%m/%Y') as end_at_exec"),
            DB::raw("DATE_FORMAT(welfares.end_at_register, '%d/%m/%Y') as end_at_register"),
            'wel_fee.empl_offical_fee', 'wel_fee.empl_offical_company_fee', 'wel_fee.empl_trial_fee', 'welfares.is_allow_attachments',
            'wel_fee.empl_trial_company_fee', 'welfares.description', 'welfares.is_register_online',
            'wel_organizers.name as organizers_name', 'welfares.is_allow_attachments', 'teams.name as name_team',
            'wel_fee.intership_company_fee', 'wel_fee.intership_fee', 'wel_fee.attachments_first_fee',
            'wel_fee.attachments_first_company_fee', 'wel_employee.is_joined', 'wel_employee.is_confirm')
            ->leftjoin('wel_fee', 'welfares.id', '=',  'wel_fee.wel_id')
            ->leftjoin('wel_organizers', 'welfares.id', '=', 'wel_organizers.wel_id')
            ->leftjoin('wel_participant_teams', 'welfares.id', '=', 'wel_participant_teams.wel_id')
            ->leftjoin('teams', 'teams.id', '=', 'wel_participant_teams.team_id')
            ->leftjoin('wel_employee', 'wel_employee.wel_id', '=', 'welfares.id')
            ->where('welfares.id', '=', $id)
            ->first();
    }
}