<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register Tenant</title>
</head>
<body>

    @if($errors->any())
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    @endif
    <form method="post" action="{{ route('tenant.registerstore') }}">
        @csrf
         <input type="text" name="name" placeholder="Enter your name" required>
         <input type="email" name="email" placeholder="Enter your Email">
         <input type="password" name="password" placeholder="Enter your Password">
         <input type="password" name="password_confirmation" placeholder="Confirm password">
       <button type="submit">Register</button>

    </form>
</body>
</html>
