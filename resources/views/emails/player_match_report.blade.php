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
        <img src="{{asset('public/email_images/logo.png')}}" class="img-responsive" />
      </div>
      <div class="banner">
        <img src="{{asset('public/email_images/banner.png')}}"  width="100%">

      </div>

      <p style="font-size: 20px;">Here is today's match summery between "{{$team_name}}" and "{{$opponent}}".</p>            
      <table class="table">
        <thead>
          <tr bgcolor="#043d6a"  style="color: #fff;">
            <th>Player</th>
            <th>Goal</th>
            <th>Yellow Card</th>
            <th>Red Card</th>
            <th>Own Goal</th>
            <th>Time</th>

          </tr>
        </thead>
        <tbody>
          <tr>
            <td>{{$game['first_name'].' '.$game['last_name']}}</td>
            <td>{{$game['goals']}}</td>
            <td>{{$game['yellow']}}</td>
            <td>{{$game['red']}}</td>
            <td>{{$game['own_goals']}}</td>
            <td>{{$game['time']}}</td>
          </tr>



        </tbody>
      </table>
      <div class="sign">
        <div class="thankyou">
      <img src="{{asset('public/email_images/thankyou.png')}}" class="img-responsive">
</div>

        <p>{{strtoupper($team_name)}}</p>



      </div>

    </div>
  </div>
</body>
</html>
