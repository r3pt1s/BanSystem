<?php

namespace r3pt1s\bansystem\event\warn;

use pocketmine\player\Player;
use r3pt1s\bansystem\event\WarnEvent;
use r3pt1s\bansystem\manager\warn\Warn;

class PlayerWarnRemoveEvent extends WarnEvent {

    public function __construct(
        Warn $warn,
        private readonly Player $moderator
    ) {
        parent::__construct($warn);
    }

    public function getModerator(): Player {
        return $this->moderator;
    }
}