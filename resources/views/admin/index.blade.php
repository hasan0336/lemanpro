<!DOCTYPE html>
<html>
   <head>
         @include('admin.layouts.head')
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <!-- Header includes Logo, Header Navbar, toggle button, Notifications, User Account, image, Menu Footer-->
         @include('admin.layouts.header')
         @if(Session::has('error_message'))
            <p class="alert {{ Session::get('alert-class', 'alert-danger') }}">{{ Session::get('error_message') }}</p>
         @endif
         @if(Session::has('update_message'))
            <p class="alert {{ Session::get('alert-class', 'alert-success') }}">{{ Session::get('update_message') }}</p>
         @endif
         @if(Session::has('error_match_password'))
            <p class="alert {{ Session::get('alert-class', 'alert-danger') }}">{{ Session::get('error_match_password') }}</p>
         @endif
         <!-- Left side column. contains the logo and sidebar -->
         @include('admin.layouts.side_navbar')
         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
               <h1 class="title">
                  Dashboard
                  <small>Users</small>
               </h1>
               <ol class="breadcrumb">
                  <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>
                  <li class="active">Users</li>
               </ol>
            </section>
            <!-- Main content -->
            <div class="box-body">
               <div class="row">
                  <div class="col-md-6">
                     <div class="chart-responsive">
                        <canvas id="myChart1"></canvas>
                     </div>
                     <!-- ./chart-responsive -->
                  </div>
                  <div class="col-md-6">
                     <div class="chart-responsive">
                        <canvas id="myChart2"></canvas>
                     </div>
                     <!-- ./chart-responsive -->
                  </div>
                  <!-- /.col -->
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <div class="chart-responsive">
                        <canvas id="myChart3"></canvas>
                     </div>
                     <!-- ./chart-responsive -->
                  </div>
                  <div class="col-md-6">
                     <div class="chart-responsive">
                        <canvas id="myChart4"></canvas>
                     </div>
                     <!-- ./chart-responsive -->
                  </div>
                  <!-- /.col -->
               </div>
               <!-- /.row -->
            </div>
            <!-- /.content -->
         </div>
         <!-- /.content-wrapper -->
         @include('admin.layouts.footer')
      </div>
      <!-- ./wrapper -->
      @include('admin.layouts.javascripts')
   </body>
</html>