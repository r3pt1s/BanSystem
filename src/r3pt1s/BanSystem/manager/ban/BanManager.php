<?php

namespace r3pt1s\BanSystem\manager\ban;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\handler\BanHandler;
use r3pt1s\BanSystem\manager\notify\NotifyManager;
use r3pt1s\BanSystem\utils\Utils;
use pocketmine\player\Player;
use pocketmine\utils\Config;

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

        $cfg = $this->getConfig();
        $idData = BanSystem::getInstance()->getConfiguration()->getBanIds()[$id];
        $bannedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($idData["duration"] == "-1" ? "-1" : Utils::convertStringToDateFormat($idData["duration"])->format("Y-m-d H:i:s"));

        $this->addBanPoint($player);

        $cfg->set($player, [
            "Type" => self::TYPE_BAN,
            "Moderator" => $moderator,
            "Id" => $id,
            "Time" => $time,
            "BannedAt" => $bannedAt
        ]);
        $cfg->save();

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

        $cfg = $this->getConfig();
        $bannedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($duration== "-1" ? "-1" : Utils::convertStringToDateFormat($duration)->format("Y-m-d H:i:s"));

        $this->addBanPoint($player);

        $cfg->set($player, [
            "Type" => self::TYPE_TEMP_BAN,
            "Moderator" => $moderator,
            "Reason" => $reason,
            "Time" => $time,
            "BannedAt" => $bannedAt
        ]);
        $cfg->save();

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

        $cfg = $this->getConfig();
        $cfg->remove($player);
        $cfg->save();

        if ($moderator == "automatic") NotifyManager::sendNotify(BanSystem::getPrefix() . "§e" . $player . " §7was automatically unbanned!");
        else NotifyManager::sendNotify(BanSystem::getPrefix() . "§e" . $player . " §7was unbanned by §c" . $moderator . "§7!");
    }

    public function isBanned(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        return $this->getConfig()->exists($player);
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

        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".BanPoints", $this->getBanPoints($player) + 1);
        $cfg->save();
    }

    public function removeBanPoint(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".BanPoints", $this->getBanPoints($player) - 1);
        $cfg->save();
    }

    public function createBanLog(Player|string $player, Player|string $moderator, string $reason, string $time, string $bannedAt) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        if (BanSystem::getInstance()->getConfiguration()->isMakeBanMuteLogs()) {
            $cfg = $this->getBanLogsConfig($player);
            $cfg->set($bannedAt, [
                "Moderator" => $moderator,
                "Reason" => $reason,
                "Time" => $time,
                "BannedAt" => $bannedAt
            ]);
            $cfg->save();
        }
    }

    public function editBan(Player|string $player, string $time, string $type = "add", ?string &$errorMessage = null): bool {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getConfig();
        $info = $this->getBanInfo($player);
        if ($info !== false) {
            if ($info["Time"] !== "-1") {
                $newTime = Utils::convertStringToDateFormat($time, new \DateTime($info["Time"]), $type);
                $cfg->setNested($player . ".Time", $newTime->format("Y-m-d H:i:s"));
                $cfg->save();
                return true;
            } else $errorMessage = "§7The ban of the player §e" . $player . " §7can't be edited!";
        } else $errorMessage = "§7The player §e" . $player . " §7isn't banned!";
        return false;
    }

    public function getBanPoints(Player|string $player): int {
        $player = $player instanceof Player ? $player->getName() : $player;
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".BanPoints") ?? 0 : 0);
    }

    public function getBanInfo(Player|string $player): false|array {
        $player = $player instanceof Player ? $player->getName() : $player;

        if ($this->isBanned($player)) {
            $type = $this->getConfig()->getNested($player . ".Type");
            if ($type == 0) return [
                "Type" => $type,
                "Moderator" => $this->getConfig()->getNested($player . ".Moderator"),
                "Id" => $this->getConfig()->getNested($player . ".Id"),
                "Time" => $this->getConfig()->getNested($player . ".Time"),
                "BannedAt" => $this->getConfig()->getNested($player . ".BannedAt"),
            ];
            else if ($type == 1) return [
                "Type" => $type,
                "Moderator" => $this->getConfig()->getNested($player . ".Moderator"),
                "Reason" => $this->getConfig()->getNested($player . ".Reason"),
                "Time" => $this->getConfig()->getNested($player . ".Time"),
                "BannedAt" => $this->getConfig()->getNested($player . ".BannedAt"),
            ];
        }
        return false;
    }

    public function getBanLogInfo(Player|string $player, string $name): ?array {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getBanLogsConfig($player);
        if ($cfg->exists($name)) {
            return [
                "Moderator" => $cfg->getNested($name . ".Moderator"),
                "Reason" => $cfg->getNested($name . ".Reason"),
                "Time" => $cfg->getNested($name . ".Time"),
                "BannedAt" => $cfg->getNested($name . ".BannedAt")
            ];
        }
        return null;
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
        return $this->getBanLogsConfig($player)->getAll();
    }

    public function getBans(): array {
        return $this->getConfig()->getAll();
    }

    public function getBanHandler(): BanHandler {
        return $this->banHandler;
    }

    private function getBanLogsConfig(Player|string $player): Config {
        $player = $player instanceof Player ? $player->getName() : $player;
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "banlogs/" . $player . ".json", 1);
    }

    private function getConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getBanPath() . "/bans.json", 1);
    }

    private function getInfoConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "/players.json", 1);
    }

    public static function getInstance(): BanManager {
        return self::$instance;
    }
}