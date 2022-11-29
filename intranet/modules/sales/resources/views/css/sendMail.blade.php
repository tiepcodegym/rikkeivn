
<p>{{trans('sales::view.Email hello',["pm" => $data['pm']])}} </p>
<p>{!!trans('sales::view.Email cc',["relate" => $data['ccName']]) !!} </p>
<p>{{trans('sales::view.Email notifical') }} </p>
<p>{{trans('sales::view.Email company name',["company" => $data['companyName']])}} </p>
<p>{{trans('sales::view.Email make name', ['make_name' => $data['makeName']]) }} </p>
<p>{{trans('sales::view.Email make date',["make_date" => date('d/m/Y')])}} </p>

<p>{{trans('sales::view.Email project name', ['project_type' => $data['projectType'], 'project_name' => $data['projectName']]) }} </p>
<p>{{trans('sales::view.Email project date',["project_date" => $data['projectDate']])}} </p>
<p>{{trans('sales::view.Email pm name', ['pm_name' => $data['pm']]) }} </p>

<p>{!! trans('sales::view.Email point', ['point' => $data['point']]) !!} </p>
<p></p>
<p>{{trans('sales::view.Email text view detail CSS') }} </p>
<p><a href="{{$data['href']}}" target="_blank">{{$data['href']}}</a></p>

<p>{{trans('sales::view.Email respect')}}</p>
<p>{{trans('sales::view.Product team')}}</p>