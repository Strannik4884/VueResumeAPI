# VueResume - Задание №5 (API)

## Для развертывания проекта:

1. Установите Composer

2. Установите Symfony CLI

3. Установите MongoDB

В корне проекта выполните следующую команду:

```
composer install
```

### Укажите переменные окружения в файле .env
```
APP_ENV=
APP_SECRET=
APP_DB_URL=
APP_DB_NAME=
APP_DB_COLLECTION_NAME=
```

Приложение настроено и готово к работе. Для запуска можно использовать встроенный веб-сервер:
```
symfony server:start
```
В случае, если используется сторонний сервер (к примеру, Apache2), то для корректной работы домен нужно указать на папку /public

### REST API
| HTTP METHOD                | POST             | GET          |
| -------------------------- | ---------------- | ------------ |
| CRUD OP                    | CREATE/UPDATE    | READ         |
| /api/cv                    | Error            | List of CVs  |
| /api/cv{id}                | Error            | Get Cv by id |
| /api/cv/add                | Create new CV    | Error        |
| /api/cv/{id}/status/update | Update CV status | Error        |