<?php

namespace r3pt1s\BanSystem\task;

use r3pt1s\BanSystem\handler\BanHandler;
use r3pt1s\BanSystem\manager\ban\BanManager;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PlayerKickTask extends Task {

    public function onRun(): void {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (BanManager::getInstance()->isBanned($player)) {
                if (BanHandler::getInstance()->handle($player, $kickScreen)) {
                    $player->kick($kickScreen);
                }
            }
        }
    }
}