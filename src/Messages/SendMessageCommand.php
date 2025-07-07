<?php

namespace Eatvio\Chat\Messages;

use Eatvio\Chat\Models\Conversation;
use Illuminate\Database\Eloquent\Model;

class SendMessageCommand
{
    public $body;

    public $conversation;

    public $type;

    public $data;

    public $participant;

    /**
     * @param  Conversation  $conversation  The conversation
     * @param  string  $body  The message body
     * @param  Model  $sender  The sender identifier
     * @param  string  $type  The message type
     */
    public function __construct(Conversation $conversation, $body, Model $sender, $type, $data)
    {
        $this->conversation = $conversation;
        $this->body = $body;
        $this->type = $type;
        $this->data = $data;
        $this->participant = $this->conversation->participantFromSender($sender);
    }
}
