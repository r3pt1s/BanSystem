<?php

namespace r3pt1s\bansystem\event\mute;

use pocketmine\command\CommandSender;
use r3pt1s\bansystem\event\MuteEvent;
use r3pt1s\bansystem\manager\mute\Mute;

class PlayerUnmuteEvent extends MuteEvent {

    public function __construct(
        Mute $mute,
        private readonly ?CommandSender $moderator,
        private readonly bool $mistake
    ) {
        parent::__construct($mute);
    }

    public function getModerator(): ?CommandSender {
        return $this->moderator;
    }

    public function isMistake(): bool {
        return $this->mistake;
    }
}