<!DOCTYPE html>
<html>
<head>
    <title>Help & Feedback</title>
</head>

<body>
<h1>Help & Feedback</h1>
<h2>Date : <?php echo date("Y/m/d"); ?></h2>
<h2>Title : <?php echo $help_feedback['subject']; ?></h2>
<h2>Description : <?php echo $help_feedback['description']; ?> </h2>
@foreach($help_feedback['help_feedback_image'] as $type => $result)
   <h4><img src="{{url('public/help_feedback_images/'.$result)}}"></h4>
@endforeach




<br/>
</body>

</html>