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
                  Artist Management
                  <!-- <small></small> -->
               </h1>
               <ol class="breadcrumb">
                  <li><a href=""><i class="fa fa-dashboard"></i> Artist Management</a></li>
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
                                          <th>Name</th>
                                          <th>City</th>
                                          <th>Profession</th>
                                          <th>Payment ID</th>
                                           <th>Featured</th>
                                          <th>Access</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                    
                                       <tr>
                                          <td>1</td>
                                          <td>2</td>
                                          <td>3</td>
                                          <td>4</td>
                                           <td>
                                             <input type="checkbox" data-id="user_id" class="feature_artist" >
                                          </td>
                                          <td>
                                             <input type="checkbox" data-id="user_id" class="block_artist"  >
                                          </td>
                                       </tr>
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

      <!-- Modal -->
      <div class="modal fade" id="myModal" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h3 class="modal-title">Advertisment Details</h3>
               </div>
               <div class="modal-body">
                  <form action="/" method="post">
                     <div class="form-group">
                        <label for="category">Category:</label>
                        <input type="text" class="form-control" value="Category here" id="category" readonly>
                     </div>
                     <div class="form-group">
                        <label for="title">Title:</label>
                        <input type="text" class="form-control" value="Title here" id="title" readonly>
                     </div>
                     <div class="form-group">
                        <label for="price">Price:</label>
                        <input type="text" class="form-control" id="price" value="Price here" readonly>
                     </div>
                     <div class="form-group">
                        <label for="desc">Description:</label>
                        <textarea class="form-control textarea" rows="4" cols="85" id="desc" readonly="readonly">Brief description here</textarea>
                     </div>
                     <div class="form-group">
                        <label for="location">Seller Location:</label>
                        <input type="text" class="form-control" value="Seller Location" id="location" readonly>
                     </div>
                     <div class="form-group">
                        <label for="usr">Contact By:</label>
                        <i><img src="{{asset('public/admin/bower_components/images/call.png')}}"></i>
                        <i><img src="{{asset('public/admin/bower_components/images/chat.png')}}"></i>
                        <i><img src="{{asset('public/admin/bower_components/images/mail.png')}}"></i>
                        <i><img src="{{asset('public/admin/bower_components/images/sms.png')}}"></i>
                     </div>
                      <div class="form-group">
                        <label for="image_gallery">Image Gallery:</label>
                        <div class="col-lg-12 col-md-12 col-xs-12 padding0">
                          <div class="col-lg-2 col-md-4 col-xs-6 thumb"> 
                            <a class="thumbnail" href="#" data-image-id="" data-toggle="modal" data-image="{{asset('public/admin/bower_components/images/Large_imageGallery1.jpg')}}" data-target="#image-gallery">
                              <img class="img-thumbnail" src="{{asset('public/admin/bower_components/images/imageGallery1.jpg')}}" alt="Another alt text">
                            </a>
                          </div>

                          <div class="col-lg-2 col-md-4 col-xs-6 thumb"> 
                            <a class="thumbnail" href="#" data-image-id="" data-toggle="modal" data-image="{{asset('public/admin/bower_components/images/Large_imageGallery2.jpg')}}" data-target="#image-gallery">
                            
                            <img class="img-thumbnail" src="{{asset('public/admin/bower_components/images/imageGallery2.jpg')}}" alt="Another alt text">
                            </a>
                          </div>

                          <div class="col-lg-2 col-md-4 col-xs-6 thumb"> 
                            <a class="thumbnail" href="#" data-image-id="" data-toggle="modal" data-image="{{asset('public/admin/bower_components/images/Large_imageGallery3.jpg')}}" data-target="#image-gallery">
                            
                            <img class="img-thumbnail" src="{{asset('public/admin/bower_components/images/imageGallery3.jpg')}}" alt="Another alt text">
                            </a>
                          </div>

                          <div class="col-lg-2 col-md-4 col-xs-6 thumb"> 
                            <a class="thumbnail" href="#" data-image-id="" data-toggle="modal" data-image="{{asset('public/admin/bower_components/images/Large_imageGallery4.jpg')}}" data-target="#image-gallery">
                            
                            <img class="img-thumbnail" src="{{asset('public/admin/bower_components/images/imageGallery4.jpg')}}" alt="Another alt text">
                            </a>
                          </div>

                          <div class="col-lg-2 col-md-4 col-xs-6 thumb"> 
                            <a class="thumbnail" href="#" data-image-id="" data-toggle="modal" data-image="{{asset('public/admin/bower_components/images/Large_imageGallery5.jpg')}}" data-target="#image-gallery">
                            
                            <img class="img-thumbnail" src="{{asset('public/admin/bower_components/images/imageGallery5.jpg')}}" alt="Another alt text">
                            </a>
                          </div>

                        </div>
                      </div>
                  </form>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
               </div>
            </div>
         </div>
      </div>
      
      <div class="modal fade" id="image-gallery" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="image-gallery-title"></h4>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">×</span>
              <span class="sr-only">Close</span>
            </button>
          </div>
          <div class="modal-body">
            <img id="image-gallery-image" class="img-responsive col-md-12" src="">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary float-left" id="show-previous-image">
              <i class="fa fa-arrow-left"></i>
            </button>
            <button type="button" id="show-next-image" class="btn btn-secondary float-right">
              <i class="fa fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
   </body>
</html>

<script>
    $('.feature_artist').on('change', function() {

        var $this = $(this);
        // $this will contain a reference to the checkbox
        if ($this.is(':checked')) {
            // alert('ello');
            var $a = 1;
            var $user_id = $(this).data("id");
            $.ajax({
                type: "get",
                url: "feature",
                data: {
                    check: $a, user_id: $user_id
                },
                beforeSend: function() {
                    $("#btn-submit").html('sending');
                },
                success: function(data) {
                    console.log(data);
                    // do something with the response
                }
            });
            // the checkbox was checked
        } else {
            var $a = 0;
            var $user_id = $(this).data("id");
            // alert('tttt');
            $.ajax({
                type: "get",
                url: "feature",
                data: {
                    check: $a, user_id: $user_id
                },
                beforeSend: function() {
                    $("#btn-submit").html('sending');
                },
                success: function(data) {
                    console.log(data);
                    // do something with the response
                }
            });
            // the checkbox was unchecked
        }
    })
</script>
<script>
    $('.block_artist').on('change', function() {

        var $this = $(this);
        // $this will contain a reference to the checkbox
        if ($this.is(':checked')) {
            // alert('ello');
            var $a = 0;
            var $user_id = $(this).data("id");
            $.ajax({
                type: "get",
                url: "block_artist",
                data: {
                    check: $a, user_id: $user_id
                },
                beforeSend: function() {
                    $("#btn-submit").html('sending');
                },
                success: function(data) {
                    console.log(data);
                    // do something with the response
                }
            });
            // the checkbox was checked
        } else {
            var $a = 1;
            var $user_id = $(this).data("id");
            // alert('tttt');
            $.ajax({
                type: "get",
                url: "block_artist",
                data: {
                    check: $a, user_id: $user_id
                },
                beforeSend: function() {
                    $("#btn-submit").html('sending');
                },
                success: function(data) {
                    console.log(data);
                    // do something with the response
                }
            });
            // the checkbox was unchecked
        }
    })

</script>