<header class="main-header">
    <!-- Logo -->
    <a href="#" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>R</b>LE</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><img src="{{asset('public/admin/bower_components/images/logo.png')}}" class="logo-pic" height="45" alt="Releasur Logo"><b>Leman Pro</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                @if(Session::get('admin_profile')->image == '')
              <img src="{{asset('public/admin/dist/img/user2-160x160.jpg')}}" class="user-image" alt="User Image">
                @endif
                @if(Session::get('admin_profile')->image != '')
                        <img src="{{asset('public/images/profile_images/'.Session::get('admin_profile')->image)}}" class="user-image" alt="User Image">
                    @endif
              <span class="hidden-xs">{{  Session::get('admin_profile')->first_name.' '. Session::get('admin_profile')->last_name}}</span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                  @if(Session::get('admin_profile')->image == '')
                      <img src="{{asset('public/admin/dist/img/user2-160x160.jpg')}}" class="img-circle" alt="User Image">
                  @endif
                  @if(Session::get('admin_profile')->image != '')
                          <img src="{{asset('public/images/profile_images/'.Session::get('admin_profile')->image)}}" class="img-circle" alt="User Image">
                  @endif


                <p>
                    {{ Session::get('admin_profile')->first_name.' '. Session::get('admin_profile')->last_name}}
                  <small>{{ Auth::user()->email}}</small>
                </p>
              </li>

              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <a href="#" data-toggle="modal" data-target="#myModal_header" class="btn btn-default btn-flat">Profile</a>
                </div>
                <div class="pull-right">
                  <a href="{{route('logout')}}" class="btn btn-default btn-flat">Sign out</a>
                </div>
              </li>
            </ul>
          </li>

        </ul>
      </div>
    </nav>
  </header>
  <!-- Modal -->
  <div class="modal fade" id="myModal_header" role="dialog">
   <div class="modal-dialog">
   
     <!-- Modal content-->
     <div class="modal-content">
       <div class="modal-header">
         <button type="button" class="close" data-dismiss="modal">&times;</button>
         <h3 class="modal-title">Edit Profile</h3>
       </div>
       <div class="modal-body">
        <form action="{{route('update_admin')}}" method="POST" enctype="multipart/form-data">
            {{csrf_field()}}
           <div class="form-group">
              <label for="name">First Name:</label>
              <input type="text" class="form-control" name="first_name" value="{{Session::get('admin_profile')->first_name}}" id="name" readonly="readonly">
           </div>
           <div class="form-group">
              <label for="name">Last Name:</label>
              <input type="text" class="form-control" name="last_name" value="{{Session::get('admin_profile')->last_name}}" id="name" readonly="readonly">
           </div>
           <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" class="form-control" name="email" value="{{ Auth::user()->email}}" id="email" readonly="readonly">
           </div>
            <div class="form-group cpass">
                <label for="opass">Old Password:</label>
                <input type="password" class="form-control" name="old_password" id="opass" readonly="readonly">
            </div>
           <div class="form-group npass">
              <label for="npass">New Password:</label>
              <input type="password" class="form-control" name="new_password" id="npass" readonly="readonly">
           </div>
            <div class="form-group cpass">
              <label for="cpass">Confirm Password:</label>
              <input type="password" class="form-control" name="confirm_password" id="cpass" readonly="readonly">
           </div>
            <div class="form-group">
              <label>Upload Profile Picture</label>
              <div class="input-group">
                  <div class="form-group">
                      <label for="user_address">Image:</label>
                      <input type="file" class="form-control" id="user_profile" name="profile_picture" placeholder="Profile Picture">
                  </div>
              </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success add-attr">Update</button>
                <button type="button" class="btn btn-primary remove-attr">Edit</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
       </div>

     </div>
     
   </div>
  </div>