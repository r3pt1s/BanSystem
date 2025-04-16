<?php

namespace r3pt1s\bansystem\handler;

use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;
use r3pt1s\bansystem\util\Utils;

final class MuteHandler implements IHandler {

    public function handle(string $player): ?string {
        if (!MuteManager::getInstance()->isMuted($player)) return null;
        if (($mute = MuteManager::getInstance()->getMute($player)) !== null) {
            return Language::get()->translate(
                LanguageKeys::SCREEN_MUTE,
                $mute->getReason(),
                ($mute->getExpire() === null ? "§c§l" . Language::get()->translate("raw.permanently") : Utils::diffString(new \DateTime(), $mute->getExpire()))
            );
        }
        return null;
    }
}