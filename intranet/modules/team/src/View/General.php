<?php

namespace Rikkei\Team\View;

use Rikkei\Core\Model\CoreModel;

/**
 * View helper general
 */
Class General
{
    /**
     * convert string param to array collection skill model
     * 
     * @param type $employeeSkill
     * @param type $employeeSkillChange
     * @return type
     */
    public static function getEmployeeSkllObject($employeeSkill, $employeeSkillChange)
    {
        if (! $employeeSkill || ! $employeeSkillChange) {
            return;
        }
        $skillsArray = $skillsChageArray = null;
        parse_str($employeeSkill, $skillsArray);
        parse_str($employeeSkillChange, $skillsChageArray);
        $result = [];
        
        if (isset($skillsArray['schools'][0])) {
            unset($skillsArray['schools'][0]);
        }
        if (isset($skillsArray['schools']) && $skillsArray['schools'] &&
            isset($skillsChageArray['schools']) && $skillsChageArray['schools']) {
            $result['schools'] = self::setSkillObjectData($skillsArray['schools']);
        }
        
        // save language
        if (isset($skillsArray['languages'][0])) {
            unset($skillsArray['languages'][0]);
        }
        if (isset($skillsArray['languages']) && $skillsArray['languages'] &&
            isset($skillsChageArray['languages']) && $skillsChageArray['languages']) {
            $result['languages'] = self::setSkillObjectData($skillsArray['languages']);
        }
        
        // save cetificate
        if (isset($skillsArray['cetificates'][0])) {
            unset($skillsArray['cetificates'][0]);
        }
        if (isset($skillsArray['cetificates']) && $skillsArray['schools'] &&
            isset($skillsChageArray['cetificates']) && $skillsChageArray['cetificates']) {
            $result['cetificates'] = self::setSkillObjectData($skillsArray['cetificates']);
        }

        // save skill
        if (isset($skillsArray['programs'][0])) {
            unset($skillsArray['programs'][0]);
        }
        if (isset($skillsArray['programs']) && $skillsArray['programs'] &&
            isset($skillsChageArray['programs']) && $skillsChageArray['programs']) {
            $result['programs'] = self::setSkillObjectData($skillsArray['programs']);
        }

        if (isset($skillsArray['oss'][0])) {
            unset($skillsArray['oss'][0]);
        }
        if (isset($skillsArray['oss']) && $skillsArray['oss'] &&
            isset($skillsChageArray['oss']) && $skillsChageArray['oss']) {
            $result['oss'] = self::setSkillObjectData($skillsArray['oss']);
        }

        if (isset($skillsArray['databases'][0])) {
            unset($skillsArray['databases'][0]);
        }
        if (isset($skillsArray['databases']) && $skillsArray['databases'] &&
            isset($skillsChageArray['databases']) && $skillsChageArray['databases']) {
            $result['databases'] = self::setSkillObjectData($skillsArray['databases']);
        }
        
        if (isset($skillsArray['work_experiences'][0])) {
            unset($skillsArray['work_experiences'][0]);
        }
        if (isset($skillsArray['work_experiences']) && $skillsArray['work_experiences'] &&
            isset($skillsChageArray['work_experiences']) && $skillsChageArray['work_experiences']) {
            $result['work_experiences'] = self::setSkillObjectData($skillsArray['work_experiences']);
        }

        //save project experience
        if (isset($skillsArray['project_experiences'][0])) {
            unset($skillsArray['project_experiences'][0]);
        }
        if (isset($skillsArray['project_experiences']) && $skillsArray['project_experiences'] &&
            isset($skillsChageArray['project_experiences']) && $skillsChageArray['project_experiences']) {
            $result['project_experiences'] = self::setSkillObjectData($skillsArray['project_experiences']);
        }
        return $result;
    }
    
    /**
     * set data for object skill
     * 
     * @param object $object
     * @param array $data
     * @return object collection
     */
    protected static function setSkillObjectData($data)
    {
        $collection = collect();
        foreach ($data as $item) {
            if (!count($item)) {
                continue;
            }
            $objectModal = new CoreModel();
            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        $objectModal->{$key2} = $value2;
                    }
                } else {
                    $objectModal->{$key} = $value;
                }
            }
            $collection->push($objectModal);
        }
        if ($collection->count()) {
            return $collection;
        }
        return null;        
    }
}
