<?php

namespace BanSystem\task;

use BanSystem\handler\BanHandler;
use BanSystem\manager\ban\BanManager;
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