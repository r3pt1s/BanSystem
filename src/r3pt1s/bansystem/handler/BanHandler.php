<?php

namespace r3pt1s\bansystem\handler;

use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\util\Utils;

class BanHandler implements IHandler {

    public function handle(string $player): ?string {
        if (!BanManager::getInstance()->isBanned($player)) return null;
        if (($ban = BanManager::getInstance()->getBan($player)) !== null) {
            return "§8» §cYou have been §lbanned §r§8«\n§8» §7Reason: §e" . $ban->getReason() . "\n§8» §7Remaining time: §e" . ($ban->getExpire() === null ? "§c§lPERMANENTLY" : Utils::diffString(new \DateTime(), $ban->getExpire()));
        }
        return null;
    }
}