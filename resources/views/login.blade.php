<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PNL Login</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}" />
    <style>
        .login {
            background: #f8f9fa;
        }

        .container-login {
            width: 400px;
            padding: 60px 22px;
            border-radius: 5px;
        }

        .btn-login {
            padding: 15px 0;
            min-width: 135px;
        }
    </style>
</head>

<body class="login">
    <div class="wrapper wrapper-login">
        <div class="container container-login">
            <h3 class="text-center mb-4">Dashboard Login</h3>
            @include('layouts.alert')
            <form action="{{ route('login.post') }}" method="POST">
                @method('POST')
                @csrf
                <div class="form-group mb-3">
                    <input type="email" class="form-control" name="email" id="floatingInput"
                        placeholder="Email address / Username">
                </div>
                <div class="form-group mb-3">
                    <input type="password" class="form-control" name="password" id="floatingPassword"
                        placeholder="Password">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value=""
                        id="rememberPasswordCheck" name="remember">
                    <label class="form-check-label" for="rememberPasswordCheck">
                        Remember password
                    </label>
                </div>
                <div class="form-action">
                    <button class="btn btn-primary btn-login w-100" type="submit">Login</button>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
</body>

</html>
