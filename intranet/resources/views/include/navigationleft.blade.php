<ul class="sidebar-menu">
    <li class="header">MAIN NAVIGATION</li>
    <li class="active treeview">
        <a href="{{ url(ADMIN_URI) }}">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
        </a>
    </li>
    
    <li class="treeview">
        <a href="#">
            <i class="fa fa-files-o"></i>
            <span>Menu parent</span>
            <i class="fa fa-angle-left pull-right"></i>
        </a>
        <ul class="treeview-menu">
            <li><a href="{{ url(ADMIN_URI) }}"><i class="fa fa-circle-o"></i> Sub</a></li>
            <li><a href="{{ url(ADMIN_URI) }}"><i class="fa fa-circle-o"></i> Sub</a></li>
        </ul>
    </li>
</ul>