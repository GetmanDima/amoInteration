# Amocrm интеграция

## Установка
- Скачать репозиторий
- docker-compose up -d
- docker exec -it {container name} bash

Уже внутри docker контейнера:
- php artisan key:generate
- php artisan migrate
- php artisan queue:listen

Перейти: http://localhost:8080

## Дополнительно
Получение данных и сохранение их в БД реализовано в app/Jobs/AmoJob.php
