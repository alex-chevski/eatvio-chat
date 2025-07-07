<?php

namespace Eatvio\Chat\Traits;

use Eatvio\Chat\Exceptions\InvalidDirectMessageNumberOfParticipants;
use Eatvio\Chat\Models\Conversation;
use Eatvio\Chat\Models\Participation;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Messageable
{
    public function conversations()
    {
        return $this->participation->pluck('conversation');
    }

    public function participation(): MorphMany
    {
        return $this->morphMany(Participation::class, 'messageable');
    }

    public function joinConversation(Conversation $conversation)
    {
        if ($conversation->isDirectMessage() && $conversation->participants()->count() == 2) {
            throw new InvalidDirectMessageNumberOfParticipants;
        }

        $participation = new Participation;

        $participation->messageable_id = $this->getKey();
        $participation->messageable_type = $this->getMorphClass();
        $participation->conversation_id = $conversation->getKey();

        $this->participation()->save($participation);
    }

    public function leaveConversation($conversationId)
    {
        $this->participation()->where([
            'messageable_id' => $this->getKey(),
            'messageable_type' => $this->getMorphClass(),
            'conversation_id' => $conversationId,
        ])->delete();
    }
}
