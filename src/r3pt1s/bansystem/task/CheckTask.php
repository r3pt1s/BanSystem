<?php

namespace r3pt1s\bansystem\task;

use pocketmine\scheduler\Task;
use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\manager\mute\MuteManager;

class CheckTask extends Task {

    public function onRun(): void {
        BanManager::getInstance()->check();
        MuteManager::getInstance()->check();
    }
}