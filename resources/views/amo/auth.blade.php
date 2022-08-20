<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Amo auth</title>
    <link href="{{ asset('css/amo-auth.css') }}" rel="stylesheet">
</head>
<body>
{{--<div>--}}
{{--    Errors:--}}
{{--    <div>--}}
{{--        {{$errors}}--}}
{{--    </div>--}}
{{--</div>--}}
<div class="container">
    <form action="/amo/auth" method="post" class="auth-form">
        @csrf
        <div class="input-container">
            <label for="" class="input-label">Секретный ключ</label>
            <input type="text" name="client_secret" class="input">
            @error('client_secret')
            <div class="input-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="input-container">
            <label for="" class="input-label">ID интеграции</label>
            <input type="text" name="client_id" class="input">
            @error('client_id')
            <div class="input-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="input-container">
            <label for="" class="input-label">Redirect URI</label>
            <input type="text" name="redirect_uri" class="input">
            @error('redirect_uri')
            <div class="input-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="input-container">
            <label for="" class="input-label">Код авторизации</label>
            <input type="text" name="auth_code" class="input">
            @error('auth_code')
            <div class="input-error">{{ $message }}</div>
            @enderror
        </div>
        <div class="input-container">
            <label for="" class="input-label">Домен</label>
            <input type="text" name="base_domain" class="input">
            @error('base_domain')
            <div class="input-error">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="submit-button">Вход</button>
    </form>
</div>
</body>
</html>
