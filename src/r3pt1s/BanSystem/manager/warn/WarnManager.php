<?php

namespace r3pt1s\BanSystem\manager\warn;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\ban\BanManager;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use r3pt1s\BanSystem\manager\notify\NotifyManager;
use pocketmine\player\Player;
use r3pt1s\BanSystem\provider\CurrentProvider;

class WarnManager {

    private static self $instance;

    public function __construct() {
        self::$instance = $this;
    }

    public function warnPlayer(Player|string $player, Player|string $moderator, string $reason) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        CurrentProvider::get()->pushWarn($player, $reason, $moderator, (new \DateTime("now"))->format("Y-m-d H:i:s"));

        $this->addWarnCount($player);

        NotifyManager::sendNotify(
            BanSystem::getPrefix() . "§e" . $player . " §7was warned by §c" . $moderator . "§7!\n" .
            BanSystem::getPrefix() . "§7Reason: §e" . $reason
        );

        $this->checkWarns($player);
    }

    public function checkWarns(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        if (BanSystem::getInstance()->getConfiguration()->getMaxWarns() > 0) {
            if ($this->getWarnCount($player) >= BanSystem::getInstance()->getConfiguration()->getMaxWarns()) {
                $this->removeWarnCount($player);
                if (BanSystem::getInstance()->getConfiguration()->getMaxWarnsAction() == "ban") {
                    BanManager::getInstance()->tempBanPlayer($player, "AUTOMATIC", BanSystem::getInstance()->getConfiguration()->getMaxWarnsActionReason(), "1d");
                } else if (BanSystem::getInstance()->getConfiguration()->getMaxWarnsAction() == "mute") {
                    MuteManager::getInstance()->tempMutePlayer($player, "AUTOMATIC", BanSystem::getInstance()->getConfiguration()->getMaxWarnsActionReason(), "1d");
                }
            }
        }
    }

    public function addWarnCount(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        CurrentProvider::get()->addWarnCount($player);
    }

    public function removeWarns(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        CurrentProvider::get()->removeWarns($player);

        $this->removeWarnCount($player);
    }

    public function removeWarnCount(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        CurrentProvider::get()->removeWarnCount($player);
    }

    public function getWarns(Player|string $player): array {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getWarns($player);
    }

    public function getWarnCount(Player|string $player): int {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getWarnCount($player);
    }

    public static function getInstance(): WarnManager {
        return self::$instance;
    }
}