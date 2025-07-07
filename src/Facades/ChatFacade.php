<?php

namespace Eatvio\Chat\Facades;

use Eatvio\Chat\Chat;
use Illuminate\Support\Facades\Facade;

class ChatFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    protected static function getFacadeAccessor()
    {
        self::clearResolvedInstance(Chat::class);

        return Chat::class;
    }
}
