<?php

namespace r3pt1s\bansystem\handler;

use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\util\Utils;

class MuteHandler implements IHandler {

    public function handle(string $player): ?string {
        if (!MuteManager::getInstance()->isMuted($player)) return null;
        if (($mute = MuteManager::getInstance()->getMute($player)) !== null) {
            $screen = BanSystem::getPrefix() . "§cYou have been §lmuted§r§c!\n";
            $screen .= BanSystem::getPrefix() . "§7Reason: §e" . $mute->getReason() . "\n";
            $screen .= BanSystem::getPrefix() . "§7Remaining time: §e" . ($mute->getExpire() === null ? "§c§lPERMANENTLY" : Utils::diffString(new \DateTime(), $mute->getExpire()));
            return $screen;
        }
        return null;
    }
}