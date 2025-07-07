<?php

namespace Eatvio\Chat\Commanding;

interface CommandHandler
{
    public function handle($command);
}
