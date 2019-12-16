<!-- jQuery UI 1.11.4 -->
<script src="{{asset('public/admin/bower_components/jquery-ui/jquery-ui.min.js')}}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap 3.3.7 -->
<script src="{{asset('public/admin/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>

<script src="{{asset('public/admin/dist/js/adminlte.min.js')}}"></script>
<script src="{{asset('public/admin/bower_components/js/jquery.dataTables.min.js')}}"></script>

<script>  $('title').text("Releasur | " + $('.title').text()); </script>
<script>
	$(document).ready( function () {
	 $('#example23').DataTable({
	    'paging':true,
	    'info':true,
	    'ordering':true,
	    'lengthMenu': [[5, 10, 15, -1], [5, 10, 15, "All"]]
	 });
	});

	$(document).ready(function() {
		$('.add-attr').hide();
		$('.cpass').hide();
		$('.npass').hide();
		$('.remove-attr').click(function(){            
		   $('input,.textarea').removeAttr('readonly','readonly');
		   $('#FileToUpload').removeAttr('disabled','disabled');
		   $('#FileForUpload').removeAttr('disabled','disabled');
		   $('.FileUploadText').attr('readonly','readonly');
		   $('.remove-attr').hide();
		   $('.add-attr').show();
		   $('.cpass').show();
		   $('.npass').show();
		});

		$('.add-attr').click(function(){
		  $('input,.FileUploadText,.textarea').attr('readonly','readonly');
		  $('#FileToUpload').attr('disabled','disabled');
		  $('#FileForUpload').attr('disabled','disabled');
		  $('.add-attr').hide();
		  $('.remove-attr').show();
		  $('.cpass').hide();
		  $('.npass').hide();
		});
	});
</script>

<?php

//$result = basename($_SERVER['PHP_SELF']);
$result = Request::segment(2);
//dd($result);
//Including JavaScript files for Text Editor
if ($result == "cm_term" || $result == "cm_privacy" || $result == "cm_how"){ ?>
	<script src="{{asset('public/admin/ckeditor/ckeditor.js')}}"></script>

<?php }else{} ?>

<?php if($result == "signin_admin" || $result == 'dashboard'){ ?>

	<script src='{{asset('public/admin/bower_components/js/Chart.bundle.js')}}'></script>
	<script src='{{asset('public/admin/bower_components/js/utils.js')}}'></script>
	<script>
		$('.Dashboard').parents('.treeview').addClass('menu-open');
		$('.Dashboard').parents('.treeview-menu').css({'display':'block'});
		$('.Dashboard').addClass('active');
		/*$('.user_management').removeClass('active');*/

		var ctx = document.getElementById('myChart1').getContext('2d');
		var sites = {!! json_encode($result_arr) !!};
		var myChart = new Chart(ctx, {
		    type: 'bar',
		    data: {
		        labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN','JUl','AUG','SEP','OCT','NOV','DEC'],
		        datasets: [{
		            label: '# of User SignUp in year',
		            data: sites,
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(255, 206, 86, 0.2)',
		                'rgba(75, 192, 192, 0.2)',
		                'rgba(153, 102, 255, 0.2)',
		                'rgba(255, 159, 64, 0.2)'
		            ],
		            borderColor: [
		                'rgba(255,99,132,1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(255, 206, 86, 1)',
		                'rgba(75, 192, 192, 1)',
		                'rgba(153, 102, 255, 1)',
		                'rgba(255, 159, 64, 1)'
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});

		var ctx = document.getElementById('myChart2').getContext('2d');
		var user_roles = {!! json_encode($usertype_arr) !!};
		var myChart = new Chart(ctx, {
		    type: 'line',
		    data: {
		        labels: ['Team', 'Player'],
		        datasets: [{
		            label: '# of Teams & Players',
		            data: user_roles ,
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		            ],
		            borderColor: [
		                'rgba(255,99,132,1)',
		                'rgba(54, 162, 235, 1)',
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});

		var ctx = document.getElementById('myChart3').getContext('2d');
		var login_type = {!! json_encode($signup_type_arr) !!}
		var myChart = new Chart(ctx, {
		    type: 'pie',
		    data: {
		        labels: ['Email', 'Facebook', 'Google', 'Linkedin'],
		        datasets: [{
		            label: '# of Votes',
		            data: login_type,
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(255, 206, 86, 0.2)',
		                'rgba(75, 192, 192, 0.2)',
		            ],
		            borderColor: [
		                'rgba(255, 99, 132, 1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(255, 206, 86, 1)',
		                'rgba(75, 192, 192, 1)',
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});

		var ctx = document.getElementById('myChart4').getContext('2d');
		var login_type = {!! json_encode($device_type_arr) !!}
		var myChart = new Chart(ctx, {
		    type: 'horizontalBar',
		    data: {
		        labels: ['android', 'ios'],
		        datasets: [{
		            label: '# of devices',
		            data: login_type,
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(255, 206, 86, 0.2)',
		                'rgba(75, 192, 192, 0.2)',
		                'rgba(153, 102, 255, 0.2)',
		                'rgba(255, 159, 64, 0.2)'
		            ],
		            borderColor: [
		                'rgba(255,99,132,1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(255, 206, 86, 1)',
		                'rgba(75, 192, 192, 1)',
		                'rgba(153, 102, 255, 1)',
		                'rgba(255, 159, 64, 1)'
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});
	</script>

<?php }elseif ($result == "dashboard_ads.php"){ ?>

	<script>
		$('.dashboard_ads').parents('.treeview').addClass('menu-open');
		$('.dashboard_ads').parents('.treeview-menu').css({'display':'block'});
		$('.dashboard_ads').addClass('active');
		$('.user_management').removeClass('active');
		var ctx = document.getElementById('myChart1').getContext('2d');
		var myChart = new Chart(ctx, {
		    type: 'bar',
		    data: {
		        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
		        datasets: [{
		            label: '# of Votes',
		            data: [12, 19, 3, 5, 2, 3],
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(255, 206, 86, 0.2)',
		                'rgba(75, 192, 192, 0.2)',
		                'rgba(153, 102, 255, 0.2)',
		                'rgba(255, 159, 64, 0.2)'
		            ],
		            borderColor: [
		                'rgba(255,99,132,1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(255, 206, 86, 1)',
		                'rgba(75, 192, 192, 1)',
		                'rgba(153, 102, 255, 1)',
		                'rgba(255, 159, 64, 1)'
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});

		var ctx = document.getElementById('myChart2').getContext('2d');
		var myChart = new Chart(ctx, {
		    type: 'line',
		    data: {
		        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
		        datasets: [{
		            label: '# of Votes',
		            data: [12, 19, 3, 5, 2, 3],
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(255, 206, 86, 0.2)',
		                'rgba(75, 192, 192, 0.2)',
		                'rgba(153, 102, 255, 0.2)',
		                'rgba(255, 159, 64, 0.2)'
		            ],
		            borderColor: [
		                'rgba(255,99,132,1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(255, 206, 86, 1)',
		                'rgba(75, 192, 192, 1)',
		                'rgba(153, 102, 255, 1)',
		                'rgba(255, 159, 64, 1)'
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});

		var ctx = document.getElementById('myChart3').getContext('2d');
		var myChart = new Chart(ctx, {
		    type: 'pie',
		    data: {
		        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
		        datasets: [{
		            label: '# of Votes',
		            data: [12, 19, 3, 5, 2, 3],
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(255, 206, 86, 0.2)',
		                'rgba(75, 192, 192, 0.2)',
		                'rgba(153, 102, 255, 0.2)',
		                'rgba(255, 159, 64, 0.2)'
		            ],
		            borderColor: [
		                'rgba(255,99,132,1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(255, 206, 86, 1)',
		                'rgba(75, 192, 192, 1)',
		                'rgba(153, 102, 255, 1)',
		                'rgba(255, 159, 64, 1)'
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});

		var ctx = document.getElementById('myChart4').getContext('2d');
		var myChart = new Chart(ctx, {
		    type: 'horizontalBar',
		    data: {
		        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
		        datasets: [{
		            label: '# of Votes',
		            data: [12, 19, 3, 5, 2, 3],
		            backgroundColor: [
		                'rgba(255, 99, 132, 0.2)',
		                'rgba(54, 162, 235, 0.2)',
		                'rgba(255, 206, 86, 0.2)',
		                'rgba(75, 192, 192, 0.2)',
		                'rgba(153, 102, 255, 0.2)',
		                'rgba(255, 159, 64, 0.2)'
		            ],
		            borderColor: [
		                'rgba(255,99,132,1)',
		                'rgba(54, 162, 235, 1)',
		                'rgba(255, 206, 86, 1)',
		                'rgba(75, 192, 192, 1)',
		                'rgba(153, 102, 255, 1)',
		                'rgba(255, 159, 64, 1)'
		            ],
		            borderWidth: 1
		        }]
		    },
		    options: {
		        scales: {
		            yAxes: [{
		                ticks: {
		                    beginAtZero:true
		                }
		            }]
		        }
		    }
		});
	</script>

<?php }elseif ($result == "artist"){ ?>

<!--     <script>
		$('.categories_management').parents('.treeview').addClass('menu-open');
		$('.categories_management').parents('.treeview-menu').css({'display':'block'});
		$('.categories_management').addClass('active');
		$('.user_management').removeClass('active');
	</script> -->

<?php }elseif ($result == "art_lover"){ ?>

	<script>
		$('.artist_management').parents('.treeview').addClass('menu-open');
		$('.artist_management').parents('.treeview-menu').css({'display':'block'});
		$('.artist_management').addClass('active');
	</script>

<?php }elseif ($result == "cm_term"){ ?>

	<script>
		$('.artLover_management').parents('.treeview').addClass('menu-open');
		$('.artLover_management').parents('.treeview-menu').css({'display':'block'});
		$('.artLover_management').addClass('active');
	</script>

<?php }elseif ($result == "cm_privacy"){ ?>

<!-- 	<script>
		$('.user_management').parents('.treeview').addClass('menu-open');
		$('.user_management').parents('.treeview-menu').css({'display':'block'});
		$('.user_management').addClass('active');
	</script>
	<script src='bower_components/js/rowDetails.js'></script>
	<script src='bower_components/js/imageGallery.js'></script> -->

<?php }elseif ($result == "cm_how"){ ?>
	
	<script>
		$('.cm_term').parents('.treeview').addClass('menu-open');
		$('.cm_term').parents('.treeview-menu').css({'display':'block'});
		$('.cm_term').addClass('active');
	</script>

<?php }elseif ($result == "cm_how.php"){ ?>
	
	<script>
		$('.cm_how').parents('.treeview').addClass('menu-open');
		$('.cm_how').parents('.treeview-menu').css({'display':'block'});
		$('.cm_how').addClass('active');
	</script>

<?php }elseif ($result == "cm_privacy.php"){ ?>
	
	<script>
		$('.cm_privacy').parents('.treeview').addClass('menu-open');
		$('.cm_privacy').parents('.treeview-menu').css({'display':'block'});
		$('.cm_privacy').addClass('active');
	</script>

<?php } else{} ?>
