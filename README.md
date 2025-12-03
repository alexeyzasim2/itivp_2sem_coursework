
## Учетные данные админа
По умолчанию создается администратор:
- Логин: `admin`
- Пароль: `admin_pass`

## Запуск
```bash
# Запуск
docker-compose up --build

# Остановка
docker-compose down

# Запуск тестов
docker-compose exec php-apache php /var/www/html/backend/tests/run_all_tests.php
```
## Доступ
- Приложение: http://localhost:8082
- phpMyAdmin: http://localhost:8083
- База данных: localhost:3308

## База данных
- Пользователь: `root`
- Пароль: `root`
- База данных: `dreamjournal`

## API Endpoints
- `POST /backend/api/auth.php?action=register` - Регистрация
- `POST /backend/api/auth.php?action=login` - Вход
- `POST /backend/api/auth.php?action=logout` - Выход
- `GET /backend/api/auth.php?action=check` - Проверка сессии
- `GET /backend/api/dreams.php` - Список снов пользователя
- `GET /backend/api/dreams.php/{id}` - Получить сон
- `POST /backend/api/dreams.php` - Создать сон
- `PUT /backend/api/dreams.php/{id}` - Обновить сон
- `DELETE /backend/api/dreams.php/{id}` - Удалить сон
- `GET /backend/api/analyze.php/{id}` - Анализ сна
- `GET /backend/api/stats.php` - Статистика пользователя
- `GET /backend/api/admin.php?users` - Список пользователей
- `GET /backend/api/admin.php?action=user&id={id}` - Получить пользователя
- `PUT /backend/api/admin.php?action=user&id={id}` - Обновить пользователя
- `DELETE /backend/api/admin.php?action=user&id={id}` - Удалить пользователя
- `GET /backend/api/admin.php?dreams` - Список всех снов
- `DELETE /backend/api/admin.php?action=dream&id={id}` - Удалить сон

