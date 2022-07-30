<?php

namespace r3pt1s\BanSystem\manager\mute;

use pocketmine\command\CommandSender;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\handler\MuteHandler;
use r3pt1s\BanSystem\manager\notify\NotifyManager;
use r3pt1s\BanSystem\provider\CurrentProvider;
use r3pt1s\BanSystem\utils\Utils;
use pocketmine\player\Player;

class MuteManager {

    const TYPE_MUTE = 0;
    const TYPE_TEMP_MUTE = 1;

    private static self $instance;
    private MuteHandler $muteHandler;

    public function __construct() {
        self::$instance = $this;

        $this->muteHandler = new MuteHandler();
    }

    public function mutePlayer(Player|string $player, CommandSender|string $moderator, int $id) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof CommandSender ? $moderator->getName() : $moderator;

        $idData = BanSystem::getInstance()->getConfiguration()->getMuteIds()[$id];
        $mutedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($idData["duration"] == "-1" ? "-1" : Utils::convertStringToDateFormat($idData["duration"])->format("Y-m-d H:i:s"));

        $this->addMutePoint($player);

        CurrentProvider::get()->pushMute($player, self::TYPE_MUTE, $moderator, $id, $time, $mutedAt);

        $this->createMuteLog($player, $moderator, $idData["reason"], $time, $mutedAt);

        NotifyManager::sendNotify(
            BanSystem::getPrefix() . "§e" . $player . " §7was muted by §c" . $moderator . "§7!\n" .
            BanSystem::getPrefix() . "§7Reason: §e" . $idData["reason"] . "\n" .
            BanSystem::getPrefix() . "§7Time: §e" . str_replace("-1", "PERMANENTLY", $time)
        );
    }

    public function tempMutePlayer(Player|string $player, CommandSender|string $moderator, string $reason, string $duration) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof CommandSender ? $moderator->getName() : $moderator;

        $mutedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($duration== "-1" ? "-1" : Utils::convertStringToDateFormat($duration)->format("Y-m-d H:i:s"));

        $this->addMutePoint($player);

        CurrentProvider::get()->pushTempMute($player, self::TYPE_TEMP_MUTE, $moderator, $reason, $time, $mutedAt);

        $this->createMuteLog($player, $moderator, $reason, $time, $mutedAt);

        NotifyManager::sendNotify(
            BanSystem::getPrefix() . "§e" . $player . " §7was muted by §c" . $moderator . "§7!\n" .
            BanSystem::getPrefix() . "§7Reason: §e" . $reason . "\n" .
            BanSystem::getPrefix() . "§7Time: §e" . str_replace("-1", "PERMANENTLY", $time)
        );
    }

    public function unmutePlayer(Player|string $player, CommandSender|string $moderator = "automatic") {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof CommandSender ? $moderator->getName() : $moderator;

        CurrentProvider::get()->removeMute($player);

        NotifyManager::sendNotify(BanSystem::getPrefix() . "§e" . $player . " §7was unmuted by §c" . $moderator . "§7!");
    }

    public function isMuted(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->isMuted($player);
    }

    public function isMuteExpired(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        $muteInfo = $this->getMuteInfo($player);
        if (is_array($muteInfo)) {
            if ($muteInfo["Time"] == "-1") {
                return false;
            } else {
                if (new \DateTime($muteInfo["Time"]) > new \DateTime("now")) return false;
                else return true;
            }
        }
        return false;
    }

    public function addMutePoint(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        CurrentProvider::get()->addMutePoint($player);
    }

    public function createMuteLog(Player|string $player, CommandSender|string $moderator, string $reason, string $time, string $mutedAt) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof CommandSender ? $moderator->getName() : $moderator;

        if (BanSystem::getInstance()->getConfiguration()->isMakeBanMuteLogs()) {
            CurrentProvider::get()->pushMuteLog($player, $moderator, $reason, $time, $mutedAt);
        }
    }

    public function editMute(Player|string $player, string $time, string $type = "add", ?string &$errorMessage = null): bool {
        $player = $player instanceof Player ? $player->getName() : $player;

        $info = $this->getMuteInfo($player);
        if ($info !== false) {
            if ($info["Time"] !== "-1") {
                $newTime = Utils::convertStringToDateFormat($time, new \DateTime($info["Time"]), $type);
                CurrentProvider::get()->editMute($player, $newTime->format("Y-m-d H:i:s"));
                return true;
            } else $errorMessage = "§7The mute of the player §e" . $player . " §7can't be edited!";
        } else $errorMessage = "§7The player §e" . $player . " §7isn't muted!";
        return false;
    }

    public function getMutesPoints(Player|string $player): int {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getMutePoints($player);
    }

    public function getMuteInfo(Player|string $player): false|array {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getMuteInfo($player);
    }

    public function isMuteId(int $id): bool {
        return isset(BanSystem::getInstance()->getConfiguration()->getMuteIds()[$id]);
    }

    public function checkMutes() {
        foreach ($this->getMutes() as $player => $banInfo) {
            if ($this->isMuteExpired($player)) {
                $this->unmutePlayer($player);
            }
        }
    }

    public function getMuteLogs(Player|string $player): array {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getMuteLogs($player);
    }

    public function getMutes(): array {
        return CurrentProvider::get()->getMutes();
    }

    public function getMuteHandler(): MuteHandler {
        return $this->muteHandler;
    }

    public static function getInstance(): MuteManager {
        return self::$instance;
    }
}