<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>

<body>
<h2>Hi <b>{{$user['email']}}</b></h2>
<br/>
By verifying your email address {{$user['email']}} help us secure your Releasur account.
<a href="{{route('verified_email',['email_token' => $user['email_token']])}}">Verify email address</a>
</body>

</html>