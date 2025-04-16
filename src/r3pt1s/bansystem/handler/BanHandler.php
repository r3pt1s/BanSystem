<?php

namespace r3pt1s\bansystem\handler;

use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;
use r3pt1s\bansystem\util\Utils;

final class BanHandler implements IHandler {

    public function handle(string $player): ?string {
        if (!BanManager::getInstance()->isBanned($player)) return null;
        if (($ban = BanManager::getInstance()->getBan($player)) !== null) {
            return Language::get()->translate(
                LanguageKeys::SCREEN_BAN,
                $ban->getReason(),
                ($ban->getExpire() === null ? "§c§l" . Language::get()->translate("raw.permanently") : Utils::diffString(new \DateTime(), $ban->getExpire()))
            );
        }
        return null;
    }
}