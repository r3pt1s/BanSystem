<?php

namespace r3pt1s\bansystem\event\ban;

use pocketmine\command\CommandSender;
use r3pt1s\bansystem\event\BanEvent;
use r3pt1s\bansystem\manager\ban\Ban;

final class PlayerUnbanEvent extends BanEvent {

    public function __construct(
        Ban $ban,
        private readonly ?CommandSender $moderator,
        private readonly bool $mistake
    ) {
        parent::__construct($ban);
    }

    public function getModerator(): ?CommandSender {
        return $this->moderator;
    }

    public function isMistake(): bool {
        return $this->mistake;
    }
}