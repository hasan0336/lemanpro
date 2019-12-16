<aside class="main-sidebar">
<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
   
   <!-- sidebar menu: : style can be found in sidebar.less -->
   <ul class="sidebar-menu" data-widget="tree">

      <li class="Dashboard">
         <a href="{{route('dashboard')}}">
            <i class="fa fa-dashboard"></i> 
            <span>Dashboard</span>
         </a>
      </li>
<!--       <li class="user_management">
         <a href="user_management.php">
            <i class="fa fa-user-o"></i> <span>Users` Management</span>
         </a>
      </li>
      <li class="categories_management">
         <a href="categories_management.php">
            <i class="fa fa-list"></i> <span>Categories` Management</span>
         </a>
      </li> -->
      <li class="treeview">
         <a href="#">
            <i class="fa fa-user-o"></i>
            <span>Users' Management</span>
            <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
            </span>
         </a>
         <ul class="treeview-menu">
            <li class="artist_management">
               <a href="{{route('palyer_management')}}">
                  <i class="fa fa-circle-o"></i> <span>Player's Management</span>
               </a>
            </li>
            <li class="artLover_management">
               <a href="">
                  <i class="fa fa-circle-o"></i> <span>Team's Management</span>
               </a>
            </li>
         </ul>
      </li>
        <li class="artLover_management">
            <a href="">
                  <i class="fa fa-search"></i> <span>Search Management</span>
            </a>
        </li>
        <li class="artLover_management">
           <a href="">
              <i class="fa fa-circle-o"></i> <span>Points Management</span>
           </a>
        </li>
      <li class="treeview">
         <a href="#">
         <i class="fa fa-file"></i>
         <span>Content Management</span>
         <span class="pull-right-container">
         <i class="fa fa-angle-left pull-right"></i>
         </span>
         </a>
         <ul class="treeview-menu">
            <li class="cm_term"><a href=""><i class="fa fa-circle-o"></i> Terms and Conditions</a></li>
            <li class="cm_privacy"><a href=""><i class="fa fa-circle-o"></i> Privacy Policy</a></li>
            <li class="cm_how"><a href=""><i class="fa fa-circle-o"></i> How to use?</a></li>
         </ul>
      </li>
   </ul>
</section>
<!-- /.sidebar -->
</aside>
