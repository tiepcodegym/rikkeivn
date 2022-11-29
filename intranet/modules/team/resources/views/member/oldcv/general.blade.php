<?php 
    use Rikkei\Core\Model\User;
    use Rikkei\Core\View\View;
    use Rikkei\Team\Model\Employee;
    $labelGender = Employee::labelGender();
    $avatar = $employee->avatar_url;
    if (!$avatar) {
        $user = User::where(['employee_id' => $employee->id])->first();
        if ($user) {
            //remove sz params avatar from google
            $avatar = preg_replace('/\?(sz=)(\d+)/i', '', $user->avatar_url);
        }
        $avatar = $avatar ? $avatar : View::getLinkImage();
    }
?>
<!--Header-->
<div id="cvo-main">
    <!-- #group-header -->
    <div id="group-header" style="display: block;">
        <div class="cvo-block" id="cvo-profile">
            <table id="profile-table">
                <tbody>
                    <tr>
                        @if(isset($pdf) && $pdf)
                        <td class="avatar-wraper" rowspan="9" id="avatar">
                            <img id="cvo-profile-avatar" src="{{ $avatar }}"  alt="avatar">
                        </td>
                        <td>
                            <span id="cvo-profile-fullname" class="default_min_width">{{ isset($employee->name) ? $employee->name : '' }}</span>
                        </td>
                        @else
                        <td>
                            <span id="cvo-profile-fullname" class="default_min_width">{{ isset($employee->name) ? $employee->name : '' }}</span>
                        </td>
                        <td class="avatar-wraper" rowspan="9" id="avatar">
                            <img id="cvo-profile-avatar" src="{{ $avatar }}"  alt="avatar">
                        </td>
                        @endif
                    </tr>
                    <tr>
                        <td>
                            <span class="profile-label">{{ trans('team::view.Birthday') }}</span>
                            <span class="profile-field default_min_width" id="cvo-profile-dob" cvo-placeholder="Ngày sinh (không bắt buộc nhập)">{{ View::getDate($employee->birthday) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="profile-label">{{ trans('team::view.Gender') }}</span>
                            <span class="profile-field default_min_width" id="cvo-profile-gender">{{ isset($labelGender[$employee->gender]) ? $labelGender[$employee->gender] : "" }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="profile-label">{{ trans('team::view.Phone') }}</span>
                            <span class="profile-field default_min_width cvoInvalidate" id="cvo-profile-phone">{{ isset($contact->mobile_phone) ? $contact->mobile_phone : '' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="profile-label">{{ trans('team::profile.Personal email') }}</span>
                            <span class="profile-field default_min_width cvoInvalidate" id="cvo-profile-email">{{ isset($contact->email) ? $contact->email : $employee->email }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="profile-label">{{ trans('team::view.Address') }}</span>
                            <span class="profile-field default_min_width" id="cvo-profile-address">{!! $contact ? $contact->getContactAddress() : '' !!}</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="profile-label">Website</span>
                            <span class="profile-field default_min_width" id="cvo-profile-website">{!! isset($contact) ? $contact->getSocial() : '' !!}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- FIXED GROUP -->
</div>
<!-- END #group-header -->