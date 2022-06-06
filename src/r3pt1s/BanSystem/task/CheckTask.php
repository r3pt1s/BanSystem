<?php

namespace r3pt1s\BanSystem\task;

use r3pt1s\BanSystem\manager\ban\BanManager;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use pocketmine\scheduler\Task;

class CheckTask extends Task {

    public function onRun(): void {
        BanManager::getInstance()->checkBans();
        MuteManager::getInstance()->checkMutes();
    }
}