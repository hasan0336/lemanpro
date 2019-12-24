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
                  How to use?
                  <!-- <small>How to use?</small> -->
               </h1>
               <ol class="breadcrumb">
                  <li><a href="#"><i class="fa fa-dashboard"></i> Content Management</a></li>
                  <li class="active">How to use?</li>
               </ol>
            </section>
            <div class="page-wrapper padding25">
               <!-- Main content -->
               <div class="container-fluid">
                  <form action="{{route('how')}}" method="post" enctype="multipart/form-data">
                     {{csrf_field()}}
                     <textarea class="ckeditor" name="editor">{{$results->content}}</textarea>
                     <div class="text-center padding20">
                        <button type="submit" class="btn btn-success">Save</button>
                     </div>
                  </form>

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