<?php

namespace Rikkei\ManageTime\Composers;

use Illuminate\View\View;
use Rikkei\ManageTime\Model\WorkingTime;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
class SidebarComposer
{
    public function compose(View $view)
    {
        $statistic = with(new WorkingTimeRegister())->myStatistic();
        $view->with('statistic', $statistic);
        $view->with('isPermissApprove', WorkingTime::isPermissApprove());
    }
}

