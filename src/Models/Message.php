<?php

namespace Eatvio\Chat\Models;

use Eatvio\Chat\BaseModel;
use Eatvio\Chat\Chat;
use Eatvio\Chat\ConfigurationManager;
use Eatvio\Chat\Eventing\AllParticipantsDeletedMessage;
use Eatvio\Chat\Eventing\EventGenerator;
use Eatvio\Chat\Eventing\MessageWasSent;
use Illuminate\Database\Eloquent\Model;

class Message extends BaseModel
{
    use EventGenerator;

    protected $fillable = [
        'body',
        'participation_id',
        'type',
        'data',
    ];

    protected $table = ConfigurationManager::MESSAGES_TABLE;

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['conversation'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'flagged' => 'boolean',
        'data' => 'array',
    ];

    protected $appends = ['sender'];

    protected $attributes = [
        'body' => '',
    ];

    public function participation()
    {
        return $this->belongsTo(Participation::class, 'participation_id');
    }

    public function getSenderAttribute()
    {
        $participantModel = $this->participation->messageable;

        if (! isset($participantModel)) {
            return null;
        }

        if (method_exists($participantModel, 'getParticipantDetails')) {
            return $participantModel->getParticipantDetails();
        }

        $fields = Chat::senderFieldsWhitelist();

        return $fields ? $this->participation->messageable->only($fields) : $this->participation->messageable;
    }

    public function unreadCount(Model $participant)
    {
        return MessageNotification::where('messageable_id', $participant->getKey())
            ->where('is_seen', 0)
            ->where('messageable_type', $participant->getMorphClass())
            ->count();
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * Adds a message to a conversation.
     */
    public function send(Conversation $conversation, string $body, Participation $participant, string $type = 'text', array $data = []): Model
    {
        $message = $conversation->messages()->create([
            'body' => $body,
            'participation_id' => $participant->getKey(),
            'type' => $type,
            'data' => $data,
        ]);

        if (Chat::broadcasts()) {
            broadcast(new MessageWasSent($message))->toOthers();
        }

        $this->createNotifications($message);

        return $message;
    }

    /**
     * Creates an entry in the message_notification table for each participant
     * This will be used to determine if a message is read or deleted.
     *
     * @param  Message  $message
     */
    protected function createNotifications($message)
    {
        MessageNotification::make($message, $message->conversation);
    }

    /**
     * Deletes a message for the participant.
     */
    public function trash(Model $participant): void
    {
        MessageNotification::where('messageable_id', $participant->getKey())
            ->where('messageable_type', $participant->getMorphClass())
            ->where('message_id', $this->getKey())
            ->delete();

        if ($this->unDeletedCount() === 0) {
            event(new AllParticipantsDeletedMessage($this));
        }
    }

    public function unDeletedCount()
    {
        return MessageNotification::where('message_id', $this->getKey())
            ->count();
    }

    /**
     * Return user notification for specific message.
     */
    public function getNotification(Model $participant): MessageNotification
    {
        return MessageNotification::where('messageable_id', $participant->getKey())
            ->where('messageable_type', $participant->getMorphClass())
            ->where('message_id', $this->id)
            ->select([
                '*',
                'updated_at as read_at',
            ])
            ->first();
    }

    /**
     * Marks message as read.
     */
    public function markRead($participant): void
    {
        $this->getNotification($participant)->markAsRead();
    }

    public function flagged(Model $participant): bool
    {
        return (bool) MessageNotification::where('messageable_id', $participant->getKey())
            ->where('message_id', $this->id)
            ->where('messageable_type', $participant->getMorphClass())
            ->where('flagged', 1)
            ->first();
    }

    public function toggleFlag(Model $participant): self
    {
        MessageNotification::where('messageable_id', $participant->getKey())
            ->where('message_id', $this->id)
            ->where('messageable_type', $participant->getMorphClass())
            ->update(['flagged' => $this->flagged($participant) ? false : true]);

        return $this;
    }
}
