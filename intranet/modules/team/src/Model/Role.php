<?php
namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use DB;
use Exception;
use Lang;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\CacheBase;
use Rikkei\Team\View\Permission as ViewPermission;

class Role extends CoreModel
{
    
    use SoftDeletes;
    
    /**
     * flag postion, role
     */
    const FLAG_ROLE = 0; //role another
    const FLAG_POSITION = 1; //role position of team
    
    const SPEC_TYPE_PQA = 3; // special type PQA team
    
    const KEY_CACHE_POSITION = 'role_position';
    const KEY_CACHE_ROLE = 'role_role';

    const KEY_CACHE_ROLE_ADMIN = 'role_administrator';
    const ROLE_ADMIN_NAME = 'Administrator';

    const ROLE_VIEW_PROFILE_2_NAME = 'Xem hồ sơ trừ nhân viên chưa vào làm việc';

    protected $table = 'roles';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role', 'special_flg', 'sort_order'
    ];
    
    /**
     * get all role is position
     * 
     * @param string $dir
     * @return collection model
     */
    public static function getAllPosition($dir = 'asc')
    {
        if ($positions = CacheHelper::get(self::KEY_CACHE_POSITION)) {
            return $positions;
        }
        $positions = self::select('id', 'role')
            ->where('special_flg', self::FLAG_POSITION)
            ->orderBy('sort_order', $dir)
            ->get();
        CacheHelper::put(self::KEY_CACHE_POSITION, $positions);
        return $positions;
    }
    
    /**
     * get all role is position
     * 
     * @return collection model
     */
    public static function getAllRole()
    {
        $currUserId = auth()->id();
        if ($roles = CacheHelper::get(self::KEY_CACHE_ROLE, $currUserId)) {
            return $roles;
        }
        $roles = self::select('id', 'role', 'description')
            ->where('special_flg', self::FLAG_ROLE)
            ->orderBy('role');
        if (!ViewPermission::getInstance()->isRootOrAdmin()) {
            $roles->where('role', '!=', self::ROLE_ADMIN_NAME);
        }
        $roles = $roles->get();
        CacheHelper::put(self::KEY_CACHE_ROLE, $roles, $currUserId);
        return $roles;
    }

    /**
     * get role Administrator id
     * @return integer
     */
    public static function roleAdminId() {
        if ($id = CacheHelper::get(self::KEY_CACHE_ROLE_ADMIN)) {
            return $id;
        }
        $roleAdmin = self::where('role', self::ROLE_ADMIN_NAME)
                ->first();
        $id = null;
        if ($roleAdmin) {
            $id = $roleAdmin->id;
        }
        CacheHelper::put(self::KEY_CACHE_ROLE_ADMIN, $id);
        return $id;
    }

    /**
     * get collection to show grid
     * 
     * @return type
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $collection = self::select('id','name')->orderBy($pager['order'], $pager['dir']);
        $collection = $collection->paginate($pager['limit']);
        return $collection;
    }
    
    /**
     * rewite delete role
     */
    public function delete()
    {
        if ($length = $this->getNumberMember()) {
            throw new Exception(Lang::get("team::messages.Position :name has :number members, can't delete!",[
                'name' => $this->role,
                'number' => $length
            ]), self::ERROR_CODE_EXCEPTION);
        }
        DB::beginTransaction();
        try {
            Permission::where('role_id', $this->id)->delete();
            if ($this->isRole()) {
                EmployeeRole::where('role_id', $this->id)->delete();
            }
            $result = parent::delete();
            CacheBase::flush();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
        return $result;
    }
    
    /**
     * move position roles
     * 
     * @param boolean $up
     */
    public function move($up = true)
    {
        $siblings = self::select('id', 'sort_order')
            ->where('special_flg', self::FLAG_POSITION)
            ->orderBy('sort_order')
            ->get();
        if (count($siblings) < 2) {
            return true;
        }
        $dataOrder = $siblings->toArray();
        $countDataOrder = count($dataOrder);
        if ($up) {
            if ($dataOrder[0]['id'] == $this->id) { //item move up is first
                return true;
            }
            for ($i = 1; $i < $countDataOrder; $i++) {
                $dataOrder[$i]['sort_order'] = $i;
                if ($dataOrder[$i]['id'] == $this->id) {
                    $dataOrder[$i]['sort_order'] = $i - 1;
                    $dataOrder[$i - 1]['sort_order'] = $i;
                    break;
                }
            }
        } else {
            if ($dataOrder[count($dataOrder) - 1]['id'] == $this->id) { //item move down is last
                return true;
            }
            for ($i = 0; $i < $countDataOrder - 1; $i++) {
                $dataOrder[$i]['sort_order'] = $i;
                if ($dataOrder[$i]['id'] == $this->id) {
                    $dataOrder[$i]['sort_order'] = $i + 1;
                    $dataOrder[$i + 1]['sort_order'] = $i;
                    $flagIndexToCurrent = true;
                    $i++;
                    break;
                }
            }
        }
        DB::beginTransaction();
        try {
            foreach ($dataOrder as $data) {
                $position = self::find($data['id']);
                $position->sort_order = $data['sort_order'];
                $position->save();
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
    
    /**
     * check role item is position team
     * 
     * @return boolean
     */
    public function isPosition()
    {
        if ($this->special_flg == self::FLAG_POSITION) {
            return true;
        }
        return false;
    }
    
    /**
     * check role item is role speical
     * 
     * @return boolean
     */
    public function isRole()
    {
        if ($this->special_flg == self::FLAG_ROLE) {
            return true;
        }
        return false;
    }
    
    /**
     * to option position array data
     * 
     * @return array
     */
    public static function toOptionPosition()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE_POSITION)) {
            return $result;
        }
        $data = self::select('id', 'role')
            ->where('special_flg', self::FLAG_POSITION)
            ->orderBy('sort_order', 'desc')
            ->get();
        $result = [];
        foreach ($data as $item) {
            $result[] = [
                'value' => $item->id,
                'label' => $item->role
            ];
        }
        CacheHelper::put(self::KEY_CACHE_POSITION, $result);
        return $result;
    }
    
    /**
     * get number member of a role
     * 
     * @return int
     */
    public function getNumberMember()
    {
        if ($this->isPosition()) {
            $children = TeamMember::select(DB::raw('count(*) as count'))
            ->where('role_id', $this->id)
            ->first();
            return $children->count;
        }
        if ($this->isRole()) {
            $children = EmployeeRole::select(DB::raw('count(*) as count'))
            ->where('role_id', $this->id)
            ->first();
            return $children->count;
        }
    }
    
    /**
     * check role position is leader - sort order min
     * 
     * @param int $id
     * @return boolean|null
     */
    public static function isPositionLeader($id)
    {
        $position = self::find($id);
        //not found position
        if (! $position || ! $position->isPosition()) {
            return null;
        }
        $position = self::select('id')
            ->where('id', $id)
            ->where('sort_order', function ($query) {
                $query->from(Role::getTableName())
                    ->select(DB::raw('MIN(sort_order)'))
                    ->where('special_flg', self::FLAG_POSITION);
            })
            ->first();
        if ($position) {
            return true;
        }
        return false;
    }

    /**
     * get position team leader id
     *
     * @return $id
     */
    public static function getPositionLeader()
    {
        $posLeader = self::select('id')
            ->where('sort_order', function ($query) {
                $query->from(Role::getTableName())
                    ->select(DB::raw('MIN(sort_order)'))
                    ->where('special_flg', self::FLAG_POSITION);
            })
            ->first();
        return $posLeader ? $posLeader->id : -1;
    }
    
    /**
     * check role position is leader
     * 
     * @return boolean
     */
    public function isLeader()
    {
        if (! $this->isPosition()) {
            return null;
        }
        //get sort order max
        $positionMax = self::select('sort_order')
            ->where('special_flg', self::FLAG_POSITION)
            ->orderBy('sort_order')
            ->take(1)
            ->first();
        if ($this->sort_order == $positionMax->sort_order) {
            return true;
        }
        return false;
    }
    
    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            $result = parent::save($options);
            CacheBase::flush();
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * to option roles
     * 
     * @return array
     */
    public static function toOptionRoles()
    {
        if ($roles = CacheHelper::get(self::KEY_CACHE_ROLE)) {
            return $roles;
        }
        $roles = self::select('id', 'role')
            ->where('special_flg', self::FLAG_ROLE)
            ->orderBy('role')
            ->get();
        $result = [];
        foreach ($roles as $item) {
            $result[$item->id] = $item->role;
        }
        CacheHelper::put(self::KEY_CACHE_ROLE, $result);
        return $result;
    }
}
