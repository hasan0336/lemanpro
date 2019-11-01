<!DOCTYPE html>
<html>
<head>
    <title>Player Match Report</title>
</head>

<body>
<h1>You Last match report</h1>
<h2>Date : <?php echo date("Y/m/d"); ?></h2>
@foreach($game as $key => $player)
	<h1> {{$key+1}}</h1>
	<h2>Hi <b>{{$player['email']}}</b></h2>
	<h2>Yellow Card <b>{{$player['yellow']}}</b></h2>
	<h2>Red Card <b>{{$player['red']}}</b></h2>
	<h2>Goals Scored <b>{{$player['goals']}}</b></h2>
	<h2>Trophies <b>{{$player['trophies']}}</b></h2>
	<h2>Playing Time <b>{{$player['time']}}</b></h2>
	<?php $key++; ?>
@endforeach

<br/>
</body>

</html>