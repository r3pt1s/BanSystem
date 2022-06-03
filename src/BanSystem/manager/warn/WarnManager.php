<?php

namespace BanSystem\manager\warn;

use BanSystem\BanSystem;
use BanSystem\manager\ban\BanManager;
use BanSystem\manager\mute\MuteManager;
use BanSystem\manager\notify\NotifyManager;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class WarnManager {

    private static self $instance;

    public function __construct() {
        self::$instance = $this;
    }

    public function warnPlayer(Player|string $player, Player|string $moderator, string $reason) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        $currentWarns = $this->getWarns($player);
        $currentWarns[] = ["Reason" => $reason, "Moderator" => $moderator, "WarnedAt" => (new \DateTime("now"))->format("Y-m-d H:i:s")];

        $cfg = $this->getConfig();
        $cfg->set($player, $currentWarns);
        $cfg->save();

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
                var_dump(BanSystem::getInstance()->getConfiguration()->getMaxWarnsAction());
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

        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".WarnCount", $this->getWarnCount($player) + 1);
        $cfg->save();
    }

    public function removeWarns(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getConfig();
        $cfg->remove($player);
        $cfg->save();

        $this->removeWarnCount($player);
    }

    public function removeWarnCount(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".WarnCount", 0);
        $cfg->save();
    }

    public function getWarns(Player|string $player): array {
        $player = $player instanceof Player ? $player->getName() : $player;

        $warns = [];
        foreach ($this->getConfig()->getAll() as $playerName => $playerWarns) {
            if ($playerName == $player) {
                foreach ($playerWarns as $warnData) {
                    $warns[] = [
                        "Reason" => $warnData["Reason"],
                        "Moderator" => $warnData["Moderator"],
                        "WarnedAt" => $warnData["WarnedAt"]
                    ];
                }
            }
        }
        return $warns;
    }

    public function getWarnCount(Player|string $player): int {
        $player = $player instanceof Player ? $player->getName() : $player;
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".WarnCount") ?? 0 : 0);
    }

    private function getConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getWarnPath() . "/warns.json", 1);
    }

    private function getInfoConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "/players.json", 1);
    }

    public static function getInstance(): WarnManager {
        return self::$instance;
    }
}