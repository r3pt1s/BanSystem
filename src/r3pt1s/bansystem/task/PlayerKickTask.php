<?php

namespace r3pt1s\bansystem\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use r3pt1s\bansystem\manager\ban\BanManager;

class PlayerKickTask extends Task {

    public function onRun(): void {
        foreach (array_filter(Server::getInstance()->getOnlinePlayers(), fn(Player $player) => !$player->hasPermission("bansystem.bypass.ban") && BanManager::getInstance()->isBanned($player)) as $player) {
            $player->kick(BanManager::getInstance()->getBanHandler()->handle($player));
        }
    }
}