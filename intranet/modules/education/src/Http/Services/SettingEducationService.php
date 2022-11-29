<?php

namespace Rikkei\Education\Http\Services;

use Illuminate\Http\Request;
use Rikkei\Education\Model\SettingEducation;
use Rikkei\Team\View\Config;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Model\EducationRequest;

class SettingEducationService
{
    protected $modelSetting;

    protected $modelType;

    protected $modelRequest;

    public function __construct(SettingEducation $modelSetting, EducationCourse $modelType, EducationRequest $modelRequest)
    {
        $this->modelSetting = $modelSetting;
        $this->modelType = $modelType;
        $this->modelRequest = $modelRequest;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function create($request)
    {
        return $this->modelSetting->create($request->all());
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function find($id)
    {
        return $this->modelSetting->find($id);
    }

    /**
     *
     * @return string
     */
    public function listItem()
    {
        $pager = Config::getPagerData();
        $collection = $this->modelSetting->withCount(['educationCoursesTypes', 'educationRequestTypes'])
            ->orderBy('id', 'DESC')
            ->orderBy($pager['order'], $pager['dir']);

        return CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    /**
     *
     * @return string
     */
    public function update($item ,$request)
    {
        $item->fill($request->except('id'));

        return $item->save();
    }

    /**
     *
     * @return string
     */
    public function delete($item)
    {
        return $item->delete();
    }

    /**
     *
     * @return boolean
     */
    public function checkCodeEducation($id)
    {
        $isType = $this->modelType->where('type', $id)->exists();
        $isRequest = $this->modelRequest->where('type_id', $id)->exists();
        if (!$isType && !$isRequest) {
            return false;
        }

        return true;
    }
}
