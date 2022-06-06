<?php

namespace r3pt1s\BanSystem\manager\ban;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\handler\BanHandler;
use r3pt1s\BanSystem\manager\notify\NotifyManager;
use r3pt1s\BanSystem\provider\CurrentProvider;
use r3pt1s\BanSystem\utils\Utils;
use pocketmine\player\Player;

class BanManager {

    const TYPE_BAN = 0;
    const TYPE_TEMP_BAN = 1;

    private static self $instance;
    private BanHandler $banHandler;

    public function __construct() {
        self::$instance = $this;

        $this->banHandler = new BanHandler();
    }

    public function banPlayer(Player|string $player, Player|string $moderator, int $id) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        $idData = BanSystem::getInstance()->getConfiguration()->getBanIds()[$id];
        $bannedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($idData["duration"] == "-1" ? "-1" : Utils::convertStringToDateFormat($idData["duration"])->format("Y-m-d H:i:s"));

        $this->addBanPoint($player);

        CurrentProvider::get()->pushBan($player, self::TYPE_BAN, $moderator, $id, $time, $bannedAt);

        $this->createBanLog($player, $moderator, $idData["reason"], $time, $bannedAt);

        NotifyManager::sendNotify(
            BanSystem::getPrefix() . "§e" . $player . " §7was banned by §c" . $moderator . "§7!\n" .
            BanSystem::getPrefix() . "§7Reason: §e" . $idData["reason"] . "\n" .
            BanSystem::getPrefix() . "§7Time: §e" . str_replace("-1", "PERMANENTLY", $time)
        );
    }

    public function tempBanPlayer(Player|string $player, Player|string $moderator, string $reason, string $duration) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        $bannedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($duration== "-1" ? "-1" : Utils::convertStringToDateFormat($duration)->format("Y-m-d H:i:s"));

        $this->addBanPoint($player);

        CurrentProvider::get()->pushTempBan($player, self::TYPE_TEMP_BAN, $moderator, $reason, $time, $bannedAt);

        $this->createBanLog($player, $moderator, $reason, $time, $bannedAt);

        NotifyManager::sendNotify(
            BanSystem::getPrefix() . "§e" . $player . " §7was banned by §c" . $moderator . "§7!\n" .
            BanSystem::getPrefix() . "§7Reason: §e" . $reason . "\n" .
            BanSystem::getPrefix() . "§7Time: §e" . str_replace("-1", "PERMANENTLY", $time)
        );
    }

    public function unbanPlayer(Player|string $player, Player|string $moderator = "automatic") {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        CurrentProvider::get()->removeBan($player);

        if ($moderator == "automatic") NotifyManager::sendNotify(BanSystem::getPrefix() . "§e" . $player . " §7was automatically unbanned!");
        else NotifyManager::sendNotify(BanSystem::getPrefix() . "§e" . $player . " §7was unbanned by §c" . $moderator . "§7!");
    }

    public function isBanned(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->isBanned($player);
    }

    public function isBanExpired(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        $banInfo = $this->getBanInfo($player);
        if (is_array($banInfo)) {
            if ($banInfo["Time"] == "-1") {
                return false;
            } else {
                if (new \DateTime($banInfo["Time"]) > new \DateTime("now")) return false;
                else return true;
            }
        }
        return false;
    }

    public function addBanPoint(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        CurrentProvider::get()->addBanPoint($player);
    }

    public function createBanLog(Player|string $player, Player|string $moderator, string $reason, string $time, string $bannedAt) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        if (BanSystem::getInstance()->getConfiguration()->isMakeBanMuteLogs()) {
            CurrentProvider::get()->pushBanLog($player, $moderator, $reason, $time, $bannedAt);
        }
    }

    public function editBan(Player|string $player, string $time, string $type = "add", ?string &$errorMessage = null): bool {
        $player = $player instanceof Player ? $player->getName() : $player;

        $info = $this->getBanInfo($player);
        if ($info !== false) {
            if ($info["Time"] !== "-1") {
                $newTime = Utils::convertStringToDateFormat($time, new \DateTime($info["Time"]), $type);
                CurrentProvider::get()->editBan($player, $newTime->format("Y-m-d H:i:s"));
                return true;
            } else $errorMessage = "§7The ban of the player §e" . $player . " §7can't be edited!";
        } else $errorMessage = "§7The player §e" . $player . " §7isn't banned!";
        return false;
    }

    public function getBanPoints(Player|string $player): int {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getBanPoints($player);
    }

    public function getBanInfo(Player|string $player): false|array {
        $player = $player instanceof Player ? $player->getName() : $player;

        return CurrentProvider::get()->getBanInfo($player);
    }

    public function isBanId(int $id): bool {
        return isset(BanSystem::getInstance()->getConfiguration()->getBanIds()[$id]);
    }

    public function checkBans() {
        foreach ($this->getBans() as $player => $banInfo) {
            if ($this->isBanExpired($player)) {
                $this->unbanPlayer($player);
            }
        }
    }

    public function getBanLogs(Player|string $player): array {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getBanLogs($player);
    }

    public function getBans(): array {
        return CurrentProvider::get()->getBans();
    }

    public function getBanHandler(): BanHandler {
        return $this->banHandler;
    }

    public static function getInstance(): BanManager {
        return self::$instance;
    }
}