<?php
$disabledLink = $employeeModelItem->id ? '' : ' disabled';
if ($employeeModelItem->isIntanceSkillsheet()) {
    $hiddenLink = '';
} else {
    $hiddenLink = 'hidden';
}
if ($employeeModelItem->isIntanceViewProfile()) {
    $disableLinkProfile = '';
} else {
    $disableLinkProfile = 'disabled';
}
if ($isSelfProfile) {
    $hiddenLink = '';
    $disableLinkProfile = '';
}
$menuInfo =  [
    'base' => [
        [
            'trans' => trans('team::profile.General Info'),
            'tag' => 'base',
            'icon' => 'user',
            'disabled' => '',
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans' => trans('team::profile.Work Info'),
            'tag' => 'work',
            'icon' => 'briefcase',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans' => trans('team::profile.Contact Info'),
            'tag' => 'contact',
            'icon' => 'phone-square',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
    ],
    'skill' => [
        [
            'trans'  => trans('team::profile.Relationship Info'),
            'tag'    => 'relationship',
            'icon' => 'child',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Education Process'),
            'tag'    => 'education',
            'icon' => 'university',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans' => trans('team::profile.Business Trips'),
            'tag' => 'business',
            'icon' => 'briefcase',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Certificate'),
            'tag'    => 'certificate',
            'icon' => 'certificate',
            'disabled' => $disabledLink,
            'disableLinkProfile' => !empty($isScopeTeam) ? '' : $disableLinkProfile,
        ],
        /*[
            'trans'  => trans('team::profile.Skill Info'),
            'tag'    => 'skill',
            'icon' => 'asterisk',
            'disabled' => $disabledLink,
        ],
        [
            'trans'  => trans('team::profile.Experiences'),
            'tag'    => 'experience',
            'icon' => 'tripadvisor',
            'disabled' => $disabledLink,
            'activeMore' => ['comexper']
        ],
        [
            'trans'  => trans('team::profile.Scan doc'),
            'tag'    => 'doc',
            'icon' => 'paperclip',
            'disabled' => $disabledLink,
        ],*/
        [
            'trans'  => trans('team::profile.Scan doc'),
            'tag'    => 'attach',
            'icon' => 'paperclip',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        
        [
            'trans'  => trans('team::profile.Want onsite'),
            'tag'    => 'wonsite',
            'icon' => 'plane',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Skill sheet'),
            'tag'    => 'cv',
            'icon' => 'sticky-note-o',
            'disabled' => $disabledLink,
            'viewSkillsheet' => $hiddenLink,
            'disableLinkProfile' => '',
        ],
    ],
    'another' => [
        [
            'trans'  => trans('team::profile.Health Info'),
            'tag'    => 'health',
            'icon' => 'stethoscope',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Hobby Info'),
            'tag'    => 'hobby',
            'icon' => 'yelp',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Prize Info'),
            'tag'    => 'prize',
            'icon' => 'trophy',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Costume Info'),
            'tag'    => 'costume',
            'icon' => 'odnoklassniki',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Politic Info'),
            'tag'    => 'politic',
            'icon' => 'user',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
        [
            'trans'  => trans('team::profile.Military menu'),
            'tag'    => 'military',
            'icon' => 'user',
            'disabled' => $disabledLink,
            'disableLinkProfile' => $disableLinkProfile,
        ],
    ]
];
if ($isSelfProfile) {
    $menuInfo['another'][] = [
        'trans'  => trans('team::profile.Setting'),
        'tag'    => 'api',
        'icon' => 'code-fork',
        'disabled' => $disabledLink,
    ];
}
?>
<div class="profile-menu-group">
@foreach($menuInfo as $menuGroup)
<!-- MENU_CREATED -->   
<div class="box box-solid">
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-employee nav nav-bars"
            data-flag-dom="employee-left-menu"> 
            @foreach($menuGroup as $menu)
            <li class="<?php echo $active == $menu['tag'] || (isset($menu['activeMore']) && in_array($active, $menu['activeMore']))? 'active' : ''?>{!!$menu['disabled']!!} {!! isset($menu['viewSkillsheet']) ? $menu['viewSkillsheet'] : '' !!} {!! isset($menu['disableLinkProfile']) ? $menu['disableLinkProfile'] : '' !!}" >
                <a href="{{ route('team::member.profile.index', ['employeeId' => $employeeModelItem->id, 'tag' => $menu['tag']]) }}" style="text-transform: capitalize;">
                    <i class="fa fa-{{ $menu['icon'] }}"></i>
                    {{ $menu['trans'] }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->
@endforeach
</div>