## Installation

From the command line, run:

```
composer require eatvio/chat
```

Publish the assets:

```
php artisan vendor:publish --provider="Eatvio\Chat\ChatServiceProvider"
```

This will publish database migrations, models and a configuration file `eatvio_chat.php` in the Laravel config folder.

## Configuration

See `eatvio_chat.php` for configuration

Run the migrations:
```
php artisan migrate
```
