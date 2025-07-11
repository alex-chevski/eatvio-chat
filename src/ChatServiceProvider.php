<?php

namespace Eatvio\Chat;

use Eatvio\Chat\Models\Conversation;
use Eatvio\Chat\Models\Message;
use Eatvio\Chat\Models\MessageNotification;
use Eatvio\Chat\Models\Participation;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishMigrations();
        $this->publishConfig();
        $this->publishModels();

        $this->registerModelBindings();

        if (config('eatvio_chat.should_load_routes')) {
            require __DIR__.'/Http/routes.php';
        }
    }

    public function registerModelBindings(): void
    {
        $models = [
            'conversation' => Conversation::class,
            'message' => Message::class,
            'message_notification' => MessageNotification::class,
            'participation' => Participation::class,
        ];

        foreach ($models as $key => $defaultClass) {
            $customClass = config("eatvio_chat.models.{$key}");

            if ($customClass && class_exists($customClass)) {
                $this->app->bind($defaultClass, fn () => $this->app->make($customClass));

                $this->app->alias($customClass, $defaultClass);
            }
        }
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerChat();
    }

    /**
     * Registers Chat.
     *
     * @return void
     */
    private function registerChat()
    {
        $this->app->bind('\Eatvio\Chat\Chat', function () {
            return $this->app->make(Chat::class);
        });
    }

    /**
     * Publish package's migrations.
     *
     * @return void
     */
    public function publishMigrations()
    {
        $timestamp = date('Y_m_d_His', time());
        $stub = __DIR__.'/../database/migrations/create_chat_tables.php';
        $target = $this->app->databasePath().'/migrations/'.$timestamp.'_create_chat_tables.php';

        $this->publishes([$stub => $target], 'chat.migrations');
    }

    /**
     * Publish package's config file.
     *
     * @return void
     */
    public function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../config' => config_path(),
        ], 'chat.config');
    }

    /**
     * Publish package's Models file.
     *
     * @return void
     */
    public function publishModels()
    {
        $this->publishes([
            __DIR__.'/../src/Models' => app_path('Models'),
        ], 'chat.models');
    }
}
