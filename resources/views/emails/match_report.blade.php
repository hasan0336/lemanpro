<!-- <!DOCTYPE html>
<html>
<head>
    <title>Player Match Report</title>
</head>

<body>
<h1>You Last match report</h1>
<h2>Date : <?php //echo date("Y/m/d"); ?></h2>
@foreach($game as $key => $player)
	<h1> {{$key+1}}</h1>
	<h2>Hi <b>{{$player['email']}}</b></h2>
	<h2>Yellow Card <b>{{$player['yellow']}}</b></h2>
	<h2>Red Card <b>{{$player['red']}}</b></h2>
	<h2>Goals Scored <b>{{$player['goals']}}</b></h2>
	<h2>Trophies <b>{{$player['trophies']}}</b></h2>
	<h2>Playing Time <b>{{$player['time']}}</b></h2>
	<?php $key//++; ?>
@endforeach

<br/>
</body>

</html> -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

  <style type="text/css">

    table.table {
      border-bottom: 1px solid #043d6a;
      margin-top: 20px;
    }
    .logo{
      padding: 30px 0 17px;

    }
    .logo img {
      width: 208px;
      padding-top: 109px;
      padding-bottom: 0px;
    }
    .tamp::before {
      position: absolute;
      content: "";
      height: 204px;
      width: 266px;
      right: 0px;
      background: url(images/topo.png);
    }

    .tamp::after {
      position: absolute;
      content: "";
      height: 150px;
      background-repeat: no-repeat;
      width: 237px;
      left: 0;
      bottom: 0;
      background: url(images/bottom.png);
    }
    .tamp {
      width: 750px;
      padding: 0px 80px;
      margin: 0 auto;
      position: relative;
    }
    .sign H1 {
      color: #ec7b39;
    }
    .sign p {
      color: #000;
      font-weight: bold;
    }
    .sign {
      width: 50%;
      margin-top: 175px;
      background: white;
      display: grid;
      flex-wrap: wrap;
      justify-content: left;
      padding-bottom: 70px;
    }
  </style>




  <div class="tamp">
    <div class="content">
      <div class="logo" style="padding: 30px 0;">
        <img src="images/logo.png" class="img-responsive" />
      </div>
      <div class="banner">
        <img src="images/banner.png"  width="100%">

      </div>

      <h2>Hi</h2>
      <p style="font-size: 20px;">Here is today's match summery between "White Falcon and "NYK Club".</p>            
      <table class="table">
        <thead>
          <tr bgcolor="#043d6a"  style="color: #fff;">
            <th></th>

            <th>Player</th>
            <th>Goal</th>
            <th>Yellow Card</th>
            <th>Red Card</th>
            <th>Own Goal</th>

          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>John K </td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>

          </tr>
          



        </tbody>
      </table>
      <div class="sign">
        <div class="thankyou">
      <img src="images/thankyou.png" class="img-responsive">
</div>

        <p>TEAM WHITE FALCON</p>



      </div>

    </div>
  </div>
</body>
</html>
