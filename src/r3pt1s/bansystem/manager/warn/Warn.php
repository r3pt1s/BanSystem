<?php

namespace r3pt1s\bansystem\manager\warn;

use pocketmine\player\Player;

class Warn {

    public function __construct(
        private readonly Player $player,
        private readonly string $moderator,
        private readonly \DateTime $time,
        private readonly ?string $reason = null
    ) {}

    public function getPlayer(): Player {
        return $this->player;
    }

    public function getModerator(): string {
        return $this->moderator;
    }

    public function getTime(): \DateTime {
        return $this->time;
    }

    public function getReason(): ?string {
        return $this->reason;
    }
}