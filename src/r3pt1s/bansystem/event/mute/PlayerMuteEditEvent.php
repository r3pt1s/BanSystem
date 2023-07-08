<?php

namespace r3pt1s\bansystem\event\mute;

use pocketmine\command\CommandSender;
use r3pt1s\bansystem\event\MuteEvent;
use r3pt1s\bansystem\manager\mute\Mute;

class PlayerMuteEditEvent extends MuteEvent {

    public function __construct(
        Mute $mute,
        private readonly CommandSender $moderator,
        private readonly ?\DateTime $newTime
    ) {
        parent::__construct($mute);
    }

    public function getModerator(): CommandSender {
        return $this->moderator;
    }

    public function getNewTime(): ?\DateTime {
        return $this->newTime;
    }
}