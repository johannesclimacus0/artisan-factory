# Artisan Factory

Пакет позволяет запускать фабрики Eloquent через Artisan без использования Tinker.

## Требования

- PHP 8.3+
- Laravel 13

## Установка

```bash
composer require johannesclimacus/artisan-factory --dev
```

## Примеры использования

Создать одну запись:

```bash
php artisan factory:create User
```

Создать несколько записей:

```bash
php artisan factory:create User --count=5
```

Применить состояние фабрики:

```bash
php artisan factory:create User --state=unverified
```

Переопределить атрибуты фабрики:

```bash
php artisan factory:create User --set="name=Test User" --set="email=test@example.com"
```

Специальные значения null, true и false автоматически преобразуются в соответствующие PHP-значения. Всё остальное остаётся строками.

Вывести все видимые атрибуты созданной модели:

```bash
php artisan factory:create User --details
```

### Связанные модели

Опция `--for` передаёт существующую родительскую модель в метод `Factory::for()`.

Для стандартного названия связи достаточно указать модель и её route key:

```bash
php artisan factory:create Post --for="User:1"
```

Если название связи отличается от названия модели, нужно явно указать:

```bash
php artisan factory:create HouseholdMessage --for="sender=User:1"
```

## Конфигурация

Опубликовать файл конфигурации:

```bash
php artisan vendor:publish --tag=factory-create-config
```

В конфигурации можно задать максимальное количество записей, создаваемых одной командой, и namespace моделей.

## Тестирование

```bash
composer test
```

## License

MIT
