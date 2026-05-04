
Для запуска контейнеров необходимо создать три .env файла, структура которых приведена ниже и в файле .env.template

```bash
### .env.mysql
MYSQL_DATABASE=db
MYSQL_USER=user
MYSQL_PASSWORD=pwd
MYSQL_ROOT_PASSWORD=rootpwd

### .env.pma
PMA_HOST=mysql
PMA_PORT=3306
PMA_USER=user
PMA_PASSWORD=rootpwd

### .env.wordpress
WORDPRESS_DB_HOST=mysql:3306
WORDPRESS_DB_USER=user
WORDPRESS_DB_PASSWORD=pwd
WORDPRESS_DB_NAME=db
BIS_DADATA_API_KEY=
BIS_DADATA_SECRET_KEY=
```

После настройки переменных окружения необходимо собрать контейнеры.

```bash
docker compose up
```

| Приложение | Адрес            |
| ---------- | ---------------- |
| Wordpress  | `localhost:8000` |
| phpMyAdmin | `localhost:8080` |
| MySQL      | `localhost:3307` |

Далее необходимо авторизоваться в WordPress `localhost:8000` и установить соответствующую тему `BIS-Theme`. Чтобы импортировать данные достаточно после авторизации удалить все таблицы из БД и импортировать файл дампа БД из phpMyAdmin. Важно не делать дамп, до установления Wordpress!

После дампа и установки темы нужно в настройках 'Постоянные ссылки' установить 'Название записи'.

Для настройки почты нужно создать её на TimeWeb (новый почтовый ящик) и добавить переменные SMTP в `.env.wordpress`. Для конфигурации SMTP сервера при необходимости можно установить плагин `WP Mail SMTP` и продублировать туда эти же данные.  

```php
define('BIS_SMTP_HOST', 'smtp.timeweb.ru');
define('BIS_SMTP_PORT', 465);
define('BIS_SMTP_SECURE', 'ssl');
define('BIS_SMTP_USER', ''); // почта SMTP timeweb
define('BIS_SMTP_PASS', ''); // пароль SMTP timeweb
define('BIS_SMTP_FROM_EMAIL', 'no-reply@bis-rf.ru');
define('BIS_SMTP_FROM_NAME', 'БИС');
define('BIS_SMTP_AUTH', 'true');
```

Для определения города через Dadata ключи тоже нужно хранить в `.env.wordpress`:

```bash
BIS_DADATA_API_KEY=ваш_api_ключ
BIS_DADATA_SECRET_KEY=ваш_secret_ключ
```

Их не нужно хардкодить в теме или класть в шаблоны.

На страницу «Комплексная чистка и дезинфекция системы вентиляции, удаление жировых отложений» нужно добавить HTML блок с содержимым из файла extra/blocks.html.

```html

```