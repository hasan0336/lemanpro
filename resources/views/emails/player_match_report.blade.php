<!DOCTYPE html>
<html>
<head>
    <title>Player Match Report</title>
</head>

<body>
<h1>You Last match report</h1>
<h2>Date : <?php echo date("Y/m/d"); ?></h2>
<h2>Hi <b>{{$game['email']}}</b></h2>
<h2>Yellow Card <b>{{$game['yellow']}}</b></h2>
<h2>Red Card <b>{{$game['red']}}</b></h2>
<h2>Goals Scored <b>{{$game['goals']}}</b></h2>
<h2>Trophies <b>{{$game['trophies']}}</b></h2>
<h2>Playing Time <b>{{$game['time']}}</b></h2>
<br/>
</body>

</html>