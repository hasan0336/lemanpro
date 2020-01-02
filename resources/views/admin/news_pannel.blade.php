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
                  News Pannel
                  <!-- <small></small> -->
               </h1>
               <ol class="breadcrumb">
                  <li><a href=""><i class="fa fa-dashboard"></i> News Pannel</a></li>
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
                                <form method="post" action="{{route('create_news')}}">
                                    {{csrf_field()}}
                                    <input type="hidden" name="web" value="web">
                                    <label>Title</label>
                                    <input type="text" name="title" value=""><br>
                                    <label>Descritpion</label>
                                    <input type="textarea" name="description" value=""><br>
                                    <label>Image</label>
                                    <input type="file" name="news_image" value="">
                                    <input type="submit" name="submit" value="submit">
                                </form>
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

