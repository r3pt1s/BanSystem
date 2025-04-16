<?php

namespace r3pt1s\bansystem\event\ban;

use pocketmine\command\CommandSender;
use r3pt1s\bansystem\event\BanEvent;
use r3pt1s\bansystem\manager\ban\Ban;

final class PlayerBanEditEvent extends BanEvent {

    public function __construct(
        Ban $ban,
        private readonly CommandSender $moderator,
        private readonly ?\DateTime $newTime
    ) {
        parent::__construct($ban);
    }

    public function getModerator(): CommandSender {
        return $this->moderator;
    }

    public function getNewTime(): ?\DateTime {
        return $this->newTime;
    }
}