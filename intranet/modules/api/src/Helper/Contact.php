<?php

namespace Rikkei\Api\Helper;

use Rikkei\Team\Model\Employee;
use Rikkei\Api\Helper\ApiConst;
use Rikkei\Api\Helper\Base as BaseHelper;
use Illuminate\Support\Facades\DB;
use Rikkei\Sales\Model\Customer as CustomerModel;

/**
 * Description of Contact
 *
 * @author lamnv
 */
class Contact extends BaseHelper
{
    /**
     * get employees list
     * @param array $data
     * @return array
     */
    public function getEmployees($data = [])
    {
        $empTbl = Employee::getTableName();
        $specFields = ['team', 'userDivisions', 'address'];
        $opts = array_merge([
            'select' => [
                'emp.id',
                'emp.name',
                'emp.email',
                'emp.birthday',
                'emp.employee_code',
                'work.bank_account',
                'work.bank_name',
                'contact.mobile_phone',
                'contact.skype',
                'user.avatar_url',
                'team'
            ],
            's' => null,
            'page' => 1,
            'per_page' => ApiConst::PER_PAGE,
            'fields_search' => ['emp.name', 'emp.email'],
            'orderby' => 'emp.email',
            'order' => 'asc',
            'include_leaved_employee' => false
        ], array_filter($data));

        $selectFields = $this->unsetSpecCol($opts['select'], $specFields, 'emp');
        if (!$selectFields && !array_diff($opts['select'], $selectFields)) {
            throw new \Exception(trans('api::message.Select field can not be empty'), 422);
        }

        $collection = Employee::withoutGlobalScope(new \Illuminate\Database\Eloquent\SoftDeletingScope)
                ->select($selectFields)
                ->from($empTbl . ' as emp')
                ->leftJoin('employee_works as work', 'work.employee_id', '=', 'emp.id')
                ->leftJoin('employee_contact as contact', 'contact.employee_id', '=', 'emp.id')
                ->leftJoin('users as user', 'user.employee_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at');
        
        //if include_leaved_employee == false then do not need to show leaved Employees
        if (!$opts['include_leaved_employee']) {
            $collection->where(function($query) {
                $query->whereNull('emp.leave_date')
                    ->orWhereRaw('DATE(emp.leave_date) > CURDATE()');
            });
        }

        $collection->groupBy('emp.id')
                ->orderBy($opts['orderby'], $opts['order']);

        $selectTeam = in_array('team', $opts['select']) || in_array('userDivisions', $opts['select']);
        if ($selectTeam) {
            $collection->with(['teams' => function ($query) {
                $query->withPivot('role_id');
            }])
            ->addSelect('emp.id');
        }

        if (in_array('address', $opts['select'])) {
            $collection->addSelect(DB::raw('IFNULL(contact.tempo_addr, contact.native_addr) as address'));
        }

        if (preg_match('/teams\./', $opts['orderby'])) {
            $collection->leftJoin('team_members as tmb', 'tmb.employee_id', '=', 'emp.id')
                    ->leftJoin('teams', 'teams.id', '=', 'tmb.team_id');
        }

        if ($opts['s']) {
            $collection->where(function ($query) use ($opts) {
                foreach ($opts['fields_search'] as $field) {
                    $query->orWhere($field, 'like', '%'.$opts['s'].'%');
                }
            });
        }

        if ($opts['per_page'] < 0) {
            $items = $collection->get();
        } else {
            $collection = $collection->paginate($opts['per_page'], ['*'], 'page', $opts['page']);
            $items = collect($collection->items());
        }

        //set team
        if ($selectTeam) {
            $hasTeamId = in_array('team', $opts['select']);
            $hasTeamCode = in_array('userDivisions', $opts['select']);
            $roleNames = [];
            if ($hasTeamCode) {
                $roleNames = \Rikkei\Team\Model\Role::getAllPosition();
                $roleNames = $roleNames ? $roleNames->lists('role', 'id')->toArray() : [];
            }
            $items = $items->map(function ($item) use ($hasTeamId, $hasTeamCode, $roleNames) {
                if ($hasTeamId) {
                    $item->team = $item->teams->lists('pivot.role_id', 'id')->toArray();
                }
                if ($hasTeamCode) {
                    $userDivisions = [];
                    if (!$item->teams->isEmpty()) {
                        foreach ($item->teams as $udItem) {
                            $userDivisions[] = [
                                'division' => $udItem->code,
                                'position' => preg_replace('/\s+/', '', $roleNames[$udItem->pivot->role_id])
                            ];
                        }
                    }
                    $item->userDivisions = $userDivisions;
                }
                unset($item->teams);
                return $item;
            });
        }

        //if per_page < 0 then return all
        if ($opts['per_page'] < 0) {
            return [
                'employees' => $items
            ];
        }
        $collection->setCollection($items);
        return [
            'total' => $collection->total(),
            'current_page' => $collection->currentPage(),
            'last_page' => $collection->lastPage(),
            'per_page' => $collection->perPage(),
            'employees' => $collection->items()
        ];
    }

    public function getContact($params)
    {   
        $collection = CustomerModel::select(
            'cust_contacts.id', 
            'cust_contacts.name'
        )
        ->join('cust_companies', 'cust_contacts.company_id', '=' ,'cust_companies.id')
        ->where("cust_companies.id", $params['company_id'])
        ->get();
        return $collection;
    }
}
