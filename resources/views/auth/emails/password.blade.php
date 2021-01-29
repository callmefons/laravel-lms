Click here to reset your password: <br>
<a href="{{ $link = url('api/v1/password/reset', $token).'?email='.urldecode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a>
