<!DOCTYPE html>
<html>
   <head>
       @include('admin.layouts.head')
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <!-- Header includes Logo, Header Navbar, toggle button, Notifications, User Account, image, Menu Footer-->
          @include('admin.layouts.header');
         <!-- Left side column. contains the logo and sidebar -->
          @include('admin.layouts.side_navbar')
         <!-- Content Wrapper. Contains page content -->
         <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
               <h1 class="title">
                  Tryout's Management
                  <!-- <small></small> -->
               </h1>
               <ol class="breadcrumb">
                  <li><a href=""><i class="fa fa-dashboard"></i> Tryout's Management</a></li>
                  <!-- <li class="active"></li> -->
               </ol>
            </section>
            
            <div class="page-wrapper padding25">
               <!-- Main content -->
               <div class="container-fluid">
                   <div class="row">
                     <div class="col-12">
                        <div class="card">
                           <div class="card-body">
                              <div class="table-responsive m-t-40">
                                 <table id="example23" class="text-center display nowrap table table-hover table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>

                                       <tr>
                                          <th>ID</th>
                                          <th>Participants</th>
                                          <th>Price</th>
                                          <th>Collections</th>
                                          <th>Time</th>
                                          <th>Starting Date</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($get_tryouts as $get_tryout)
                                       <tr>
                                          <td>{{$get_tryout->id}}</td>
                                          <td>{{$get_tryout->participants}}</td>
                                          <td>{{$get_tryout->costoftryout}}</td>
                                          <td>{{$get_tryout->collections}}</td>
                                          <td>{{$get_tryout->timeoftryout}}</td>
                                          <td>{{$get_tryout->dateoftryout}}</td>
                                       </tr>
                                    @endforeach
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- /.content -->
            </div>
         </div>
         <!-- /.content-wrapper -->
          @include('admin.layouts.footer')
      </div>
      <!-- ./wrapper -->
      @include('admin.layouts.javascripts')
   </body>
</html>