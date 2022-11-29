<?php

namespace Rikkei\Education\Http\Services;

use Illuminate\Http\Request;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Education\Model\SettingAddressMail;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;

class SettingAddressMailService
{
    protected $modelSetting;

    protected $modelTeam;

    const IS_BRANCH = '1';

    public function __construct(SettingAddressMail $modelSetting, Team $modelTeam)
    {
        $this->modelSetting = $modelSetting;
        $this->modelTeam = $modelTeam;
    }

    public function listItem()
    {
        $pager = Config::getPagerData();
        $collection = $this->modelTeam->select('id', 'name', 'branch_code')
            ->with('addressMail')
            ->where('is_branch', self::IS_BRANCH)
            ->orderBy('id', 'DESC')
            ->orderBy($pager['order'], $pager['dir']);

        return CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    public function find($id)
    {
        return $this->modelTeam->select('id','name', 'branch_code')
            ->with('addressMail')
            ->where('id', $id)->first();
    }

    public function findAddressMail($id)
    {
        return $this->modelSetting->where('team_id', $id)->first();
    }

    public function updateOrInsertAddressMail($attribute, $request)
    {
        if ($attribute) {
            $attribute->email = $request->email;

            return $attribute->save();
        }

        return $this->modelSetting->create($request->all());
    }
}
