<?php

namespace Eatvio\Chat\Eventing;

use Eatvio\Chat\Models\Message;

class AllParticipantsDeletedMessage extends Event
{
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }
}
