<?php

namespace Eatvio\Chat\Services;

use Eatvio\Chat\Commanding\CommandBus;
use Eatvio\Chat\Messages\SendMessageCommand;
use Eatvio\Chat\Models\Message;
use Eatvio\Chat\Traits\SetsParticipants;
use Exception;

class MessageService
{
    use SetsParticipants;

    protected $type = 'text';

    protected $data = [];

    protected $body;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Message
     */
    protected $message;

    public function __construct(CommandBus $commandBus, Message $message)
    {
        $this->commandBus = $commandBus;
        $this->message = $message;
    }

    public function setMessage(?string $message)
    {
        if (is_object($message)) {
            $this->message = $message;
        } else {
            $this->body = $message;
        }

        return $this;
    }

    /**
     * Set Message type.
     *
     * @param string type
     * @return $this
     */
    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function data(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function getById($id)
    {
        return $this->message->findOrFail($id);
    }

    /**
     * Mark a message as read.
     *
     * @return void
     */
    public function markRead()
    {
        $this->message->markRead($this->participant);
    }

    /**
     * Deletes message.
     *
     * @return void
     */
    public function delete()
    {
        $this->message->trash($this->participant);
    }

    /**
     * Get count for unread messages.
     *
     * @return void
     */
    public function unreadCount()
    {
        return $this->message->unreadCount($this->participant);
    }

    public function toggleFlag()
    {
        return $this->message->toggleFlag($this->participant);
    }

    public function flagged()
    {
        return $this->message->flagged($this->participant);
    }

    /**
     * Sends the message.
     *
     * @return Message
     *
     * @throws Exception
     */
    public function send()
    {
        if (! $this->sender) {
            throw new Exception('Message sender has not been set');
        }

        if (! $this->recipient) {
            throw new Exception('Message receiver has not been set');
        }

        $command = new SendMessageCommand($this->recipient, $this->body, $this->sender, $this->type, $this->data);

        return $this->commandBus->execute($command);
    }
}
