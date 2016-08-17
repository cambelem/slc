<!-- BEGIN brand -->
<div class="container-fluid">
    <div class="navbar-header">
        <a class="navbar-brand" href="{BRAND_LINK}">{BRAND}</a>
    </div>
    <!-- END brand -->
    
    <ul class='nav navbar-nav'>
        <!-- BEGIN repeat_nav_links -->
        <li><a class='faxmaster-nav-link' href='{LINK}' title='{LINK_TITLE}' style="padding:0 0 0 8px;"><button type="button" class='btn btn-default navbar-btn'>{TEXT}</button></a></li>
        <!-- END repeat_nav_links -->

    </ul>

    <ul class='nav navbar-nav navbar-right'>
      <li><a class="navbar-report" href="{REPORT_LINK}"><i class="fa fa-bar-chart"></i> {REPORT}</a></li>
      <li><a class="navbar-report" href="{LANDLORD_LINK}"><i class="fa fa-pencil-square-o"></i> {LANDLORDS}</a></li>
        <!-- BEGIN admin_links -->
          {ADMIN_OPTIONS}
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cog"></i> Settings &nbsp;<b class="caret"></b></a>
            <ul class="dropdown-menu">
              <!-- BEGIN ctrl_panel -->
              <li>{CONTROL_PANEL}</li>
              <!-- END ctrl_panel -->
            </ul>
          </li>
          <!-- END admin_links -->
          <li>
            <a href="#">{USER_FULL_NAME}</a>
          </li>
        <li>
            <a href="{LOGOUT_URI}"><i class="fa fa-sign-out"></i> Sign out</a>
        </li>
    </ul>
</div>
