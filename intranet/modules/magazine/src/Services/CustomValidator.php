<?php 
namespace Rikkei\Magazine\Services;

use Illuminate\Validation\Validator;
use Rikkei\Magazine\Model\Magazine;

class CustomValidator extends Validator
{
    /**
     * validate if value less than value of other field
     *
     * @param  $attribute  
     * @param  $value      
     * @param  $parameters 
     * @return Boolean          
     */
    public function validateMagazine($attribute, $value, $parameters)
    {
        $projectMember = ProjectMember::find($parameters[1]);
        $projectMemberParent = ProjectMember::find($projectMember->parent_id);
        if ($projectMember->employee_id == $projectMemberParent->employee_id) {
            return true;
        } else {
            $countApproved  = ProjectMember::where('project_id', $parameters[0])
                                      ->where('employee_id', $value)
                                      ->whereNotIn('id', [$parameters[1]])
                                      ->where('status', '!=', ProjectMember::STATUS_APPROVED)
                                      ->count();
            $countNotApproved = ProjectMember::where('project_id', $parameters[0])
                                      ->where('employee_id', $value)
                                      ->where('status', ProjectMember::STATUS_APPROVED)
                                      ->count();
            if (!$countApproved || $countNotApproved == 1) {
                return true;
            }
            return false;
        }
    }
}
