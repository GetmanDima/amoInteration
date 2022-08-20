<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DashBoard</title>
</head>
<body>
<form action="/amo/data" method="post">
    @csrf
    <button type="submit">Обновить данные</button>
    <div>Для работы необходимо запустить очередь: php artisan queue:listen</div>
</form>
</body>
</html>
