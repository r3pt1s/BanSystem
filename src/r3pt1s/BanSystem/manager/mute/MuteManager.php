<?php

namespace r3pt1s\BanSystem\manager\mute;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\handler\MuteHandler;
use r3pt1s\BanSystem\manager\notify\NotifyManager;
use r3pt1s\BanSystem\utils\Utils;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class MuteManager {

    const TYPE_MUTE = 0;
    const TYPE_TEMP_MUTE = 1;

    private static self $instance;
    private MuteHandler $muteHandler;

    public function __construct() {
        self::$instance = $this;

        $this->muteHandler = new MuteHandler();
    }

    public function mutePlayer(Player|string $player, Player|string $moderator, int $id) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        $cfg = $this->getConfig();
        $idData = BanSystem::getInstance()->getConfiguration()->getMuteIds()[$id];
        $mutedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($idData["duration"] == "-1" ? "-1" : Utils::convertStringToDateFormat($idData["duration"])->format("Y-m-d H:i:s"));

        $this->addMutePoint($player);

        $cfg->set($player, [
            "Type" => self::TYPE_MUTE,
            "Moderator" => $moderator,
            "Id" => $id,
            "Time" => $time,
            "MutedAt" => $mutedAt
        ]);
        $cfg->save();

        $this->createMuteLog($player, $moderator, $idData["reason"], $time, $mutedAt);

        NotifyManager::sendNotify(
            BanSystem::getPrefix() . "§e" . $player . " §7was muted by §c" . $moderator . "§7!\n" .
            BanSystem::getPrefix() . "§7Reason: §e" . $idData["reason"] . "\n" .
            BanSystem::getPrefix() . "§7Time: §e" . str_replace("-1", "PERMANENTLY", $time)
        );
    }

    public function tempMutePlayer(Player|string $player, Player|string $moderator, string $reason, string $duration) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        $cfg = $this->getConfig();
        $mutedAt = (new \DateTime("now"))->format("Y-m-d H:i:s");
        $time = ($duration== "-1" ? "-1" : Utils::convertStringToDateFormat($duration)->format("Y-m-d H:i:s"));

        $this->addMutePoint($player);

        $cfg->set($player, [
            "Type" => self::TYPE_TEMP_MUTE,
            "Moderator" => $moderator,
            "Reason" => $reason,
            "Time" => $time,
            "MutedAt" => (new \DateTime("now"))->format("Y-m-d H:i:s")
        ]);
        $cfg->save();

        $this->createMuteLog($player, $moderator, $reason, $time, $mutedAt);

        NotifyManager::sendNotify(
            BanSystem::getPrefix() . "§e" . $player . " §7was muted by §c" . $moderator . "§7!\n" .
            BanSystem::getPrefix() . "§7Reason: §e" . $reason . "\n" .
            BanSystem::getPrefix() . "§7Time: §e" . str_replace("-1", "PERMANENTLY", $time)
        );
    }

    public function unmutePlayer(Player|string $player, Player|string $moderator = "automatic") {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        $cfg = $this->getConfig();
        $cfg->remove($player);
        $cfg->save();

        NotifyManager::sendNotify(BanSystem::getPrefix() . "§e" . $player . " §7was unmuted by §c" . $moderator . "§7!");
    }

    public function isMuted(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        return $this->getConfig()->exists($player);
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

        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".MutePoints", $this->getMutesPoints($player) + 1);
        $cfg->save();
    }

    public function removeMutePoint(Player|string $player) {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".MutePoints", $this->getMutesPoints($player) - 1);
        $cfg->save();
    }

    public function createMuteLog(Player|string $player, Player|string $moderator, string $reason, string $time, string $mutedAt) {
        $player = $player instanceof Player ? $player->getName() : $player;
        $moderator = $moderator instanceof Player ? $moderator->getName() : $moderator;

        if (BanSystem::getInstance()->getConfiguration()->isMakeBanMuteLogs()) {
            $cfg = $this->getMuteLogsConfig($player);
            $cfg->set($mutedAt, [
                "Moderator" => $moderator,
                "Reason" => $reason,
                "Time" => $time,
                "MutedAt" => $mutedAt
            ]);
            $cfg->save();
        }
    }

    public function editMute(Player|string $player, string $time, string $type = "add", ?string &$errorMessage = null): bool {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getConfig();
        $info = $this->getMuteInfo($player);
        if ($info !== false) {
            if ($info["Time"] !== "-1") {
                $newTime = Utils::convertStringToDateFormat($time, new \DateTime($info["Time"]), $type);
                $cfg->setNested($player . ".Time", $newTime->format("Y-m-d H:i:s"));
                $cfg->save();
                return true;
            } else $errorMessage = "§7The mute of the player §e" . $player . " §7can't be edited!";
        } else $errorMessage = "§7The player §e" . $player . " §7isn't muted!";
        return false;
    }

    public function getMutesPoints(Player|string $player): int {
        $player = $player instanceof Player ? $player->getName() : $player;
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".MutePoints") ?? 0 : 0);
    }

    public function getMuteInfo(Player|string $player): false|array {
        $player = $player instanceof Player ? $player->getName() : $player;

        if ($this->isMuted($player)) {
            $type = $this->getConfig()->getNested($player . ".Type");
            if ($type == 0) return [
                "Type" => $type,
                "Moderator" => $this->getConfig()->getNested($player . ".Moderator"),
                "Id" => $this->getConfig()->getNested($player . ".Id"),
                "Time" => $this->getConfig()->getNested($player . ".Time"),
                "MutedAt" => $this->getConfig()->getNested($player . ".MutedAt"),
            ];
            else if ($type == 1) return [
                "Type" => $type,
                "Moderator" => $this->getConfig()->getNested($player . ".Moderator"),
                "Reason" => $this->getConfig()->getNested($player . ".Reason"),
                "Time" => $this->getConfig()->getNested($player . ".Time"),
                "MutedAt" => $this->getConfig()->getNested($player . ".MutedAt"),
            ];
        }
        return false;
    }

    public function getMuteLogInfo(Player|string $player, string $name): ?array {
        $player = $player instanceof Player ? $player->getName() : $player;

        $cfg = $this->getMuteLogsConfig($player);
        if ($cfg->exists($name)) {
            return [
                "Moderator" => $cfg->getNested($name . ".Moderator"),
                "Reason" => $cfg->getNested($name . ".Reason"),
                "Time" => $cfg->getNested($name . ".Time"),
                "MutedAt" => $cfg->getNested($name . ".MutedAt")
            ];
        }
        return null;
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
        return $this->getMuteLogsConfig($player)->getAll();
    }

    public function getMutes(): array {
        return $this->getConfig()->getAll();
    }

    public function getMuteHandler(): MuteHandler {
        return $this->muteHandler;
    }

    private function getMuteLogsConfig(Player|string $player): Config {
        $player = $player instanceof Player ? $player->getName() : $player;
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "mutelogs/" . $player . ".json", 1);
    }

    private function getConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getMutePath() . "/mutes.json", 1);
    }

    private function getInfoConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "/players.json", 1);
    }

    public static function getInstance(): MuteManager {
        return self::$instance;
    }
}