<?php

namespace r3pt1s\bansystem\event\kick;

use pocketmine\command\CommandSender;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

final class PlayerKickEvent extends PlayerEvent implements Cancellable {
    use CancellableTrait;

    public function __construct(
        Player $player,
        private readonly CommandSender $moderator,
        private string $reason
    ) {}

    public function setReason(string $reason): void {
        $this->reason = $reason;
    }

    public function getModerator(): CommandSender {
        return $this->moderator;
    }

    public function getReason(): string {
        return $this->reason;
    }
}