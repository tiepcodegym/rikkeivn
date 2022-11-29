<?php 
use Rikkei\Resource\View\View;

if (!function_exists('getHtmlProjectExperience')) {
    function getHtmlProjectExperience($pro) { ?>
        <!-- start row -->
        <div class="row">
            <div class="project_title">
                <h4 class="cvo-project-project-name default_min_width">{{ $pro->name }}</h4>
                <div class="time">
                    <span class="cvo-project-start start default_min_width" >( {{ View::getDate($pro->start_at, 'm/Y')}}</span>
                    <span>-</span>
                    <span class="cvo-project-end end default_min_width">{{ View::getDate($pro->end_at, 'm/Y') }} )</span>
                </div>
            </div>
            <div class="project_details">
                <table border="1">
                    <tbody>
                        <tr>
                            <th>{{ trans('team::view.Cutomer Name') }}</th>
                            <td class="cvo-project-customer default_min_width">{{ $pro->customer_name }}</td>
                        </tr>
                        <tr>
                            <th>{{trans('team::view.Description')}}</th>
                            <td class="cvo-project-description default_min_width">{{ $pro->description }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('team::view.No member') }}</th>
                            <td class="cvo-project-team-size default_min_width">{{ $pro->no_member }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('team::view.Positon work') }}</th>
                            <td class="cvo-project-my-position default_min_width">{{ $pro->poisition }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('team::view.Responsible') }}</th>
                            <td class="cvo-project-my-responsibility default_min_width">{{ $pro->responsible }}</td>
                        </tr>
                        <tr>
                            <th>{{trans('team::view.Technical')}}</th>
                            <td class="cvo-project-technologies-used default_min_width">
                                {!! $pro->getTechnical() !!}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div style="clear: both"></div>
        <!-- end row -->
    <?php 
    }
}
?>

<!--/ .Start Project info-->
<div class="cvo-block" id="cvo-project">
    <h3 class="cvo-block-title">
        <span id="cvo-project-blocktitle" class="default_min_width">{{ trans('team::view.Project experience') }}</span>
    </h3>
    <div id="project-table">
        @foreach($projects as $pro)
            {{ getHtmlProjectExperience($pro) }}
        @endforeach
    </div>
</div>
<!--/. End Project info-->