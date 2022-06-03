<?php

namespace BanSystem\task;

use BanSystem\manager\ban\BanManager;
use BanSystem\manager\mute\MuteManager;
use pocketmine\scheduler\Task;

class CheckTask extends Task {

    public function onRun(): void {
        BanManager::getInstance()->checkBans();
        MuteManager::getInstance()->checkMutes();
    }
}