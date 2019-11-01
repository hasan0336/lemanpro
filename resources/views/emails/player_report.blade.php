<!DOCTYPE html>
<html>
<head>
    <title>Player Match Report</title>
</head>

<body>
<h1>You Last match report</h1>
<h2>Date : <?php echo date("Y/m/d"); ?></h2>
<h2>Hi <b>{{$user['email']}}</b></h2>
<h2>Yellow Card <b>{{$user['yellow']}}</b></h2>
<h2>Red Card <b>{{$user['red']}}</b></h2>
<h2>Goals Scored <b>{{$user['goals']}}</b></h2>
<h2>Trophies <b>{{$user['trophies']}}</b></h2>
<h2>Playing Time <b>{{$user['time']}}</b></h2>
<br/>
</body>

</html>