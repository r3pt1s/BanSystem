<?php

namespace r3pt1s\BanSystem\provider;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\ban\BanManager;
use r3pt1s\BanSystem\manager\mute\MuteManager;

class JSONProvider extends Provider {

    public function createPlayer(string $player): void {
        $cfg = $this->getInfoConfig();
        $cfg->set($player, [
            "BanPoints" => 0,
            "MutePoints" => 0,
            "WarnCount" => 0,
            "Notify" => false
        ]);
        $cfg->save();
    }

    public function pushBan(string $player, int $type, string $moderator, int $id, string $time, string $bannedAt): void {
        $cfg = $this->getBansConfig();
        $cfg->set($player, [
            "Type" => BanManager::TYPE_BAN,
            "Moderator" => $moderator,
            "Id" => $id,
            "Time" => $time,
            "BannedAt" => $bannedAt
        ]);
        $cfg->save();
    }

    public function pushMute(string $player, int $type, string $moderator, int $id, string $time, string $mutedAt): void {
        $cfg = $this->getMutesConfig();
        $cfg->set($player, [
            "Type" => MuteManager::TYPE_MUTE,
            "Moderator" => $moderator,
            "Id" => $id,
            "Time" => $time,
            "MutedAt" => $mutedAt
        ]);
        $cfg->save();
    }

    public function pushTempBan(string $player, int $type, string $moderator, string $reason, string $time, string $bannedAt): void {
        $cfg = $this->getBansConfig();
        $cfg->set($player, [
            "Type" => BanManager::TYPE_TEMP_BAN,
            "Moderator" => $moderator,
            "Reason" => $reason,
            "Time" => $time,
            "BannedAt" => $bannedAt
        ]);
        $cfg->save();
    }

    public function pushTempMute(string $player, int $type, string $moderator, string $reason, string $time, string $mutedAt): void {
        $cfg = $this->getMutesConfig();
        $cfg->set($player, [
            "Type" => MuteManager::TYPE_TEMP_MUTE,
            "Moderator" => $moderator,
            "Reason" => $reason,
            "Time" => $time,
            "MutedAt" => (new \DateTime("now"))->format("Y-m-d H:i:s")
        ]);
        $cfg->save();
    }

    public function pushWarn(string $player, string $reason, string $moderator, string $warnedAt): void {
        $currentWarns = $this->getWarns($player);
        $currentWarns[] = ["Reason" => $reason, "Moderator" => $moderator, "WarnedAt" => (new \DateTime("now"))->format("Y-m-d H:i:s")];

        $cfg = $this->getWarnsConfig();
        $cfg->set($player, $currentWarns);
        $cfg->save();
    }

    public function removeBan(string $player): void {
        $cfg = $this->getBansConfig();
        $cfg->remove($player);
        $cfg->save();
    }

    public function removeMute(string $player): void {
        $cfg = $this->getMutesConfig();
        $cfg->remove($player);
        $cfg->save();
    }

    public function removeWarns(string $player): void {
        $cfg = $this->getWarnsConfig();
        $cfg->remove($player);
        $cfg->save();
    }

    public function isBanned(string $player): bool {
        return $this->getBansConfig()->exists($player);
    }

    public function isMuted(string $player): bool {
        return $this->getMutesConfig()->exists($player);
    }

    public function isPlayerCreated(string $player): bool {
        return $this->getInfoConfig()->exists($player);
    }

    public function addBanPoint(string $player): void {
        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".BanPoints", $this->getBanPoints($player) + 1);
        $cfg->save();
    }

    public function addMutePoint(string $player): void {
        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".MutePoints", $this->getMutePoints($player) + 1);
        $cfg->save();
    }

    public function addWarnCount(string $player): void {
        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".WarnCount", $this->getWarnCount($player) + 1);
        $cfg->save();
    }

    public function removeWarnCount(string $player): void {
        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".WarnCount", 0);
        $cfg->save();
    }

    public function pushBanLog(string $player, string $moderator, string $reason, string $time, string $bannedAt): void {
        $cfg = $this->getBanLogsConfig($player);
        $cfg->set($bannedAt, [
            "Moderator" => $moderator,
            "Reason" => $reason,
            "Time" => $time,
            "BannedAt" => $bannedAt
        ]);
        $cfg->save();
    }

    public function pushMuteLog(string $player, string $moderator, string $reason, string $time, string $mutedAt): void {
        $cfg = $this->getMuteLogsConfig($player);
        $cfg->set($mutedAt, [
            "Moderator" => $moderator,
            "Reason" => $reason,
            "Time" => $time,
            "MutedAt" => $mutedAt
        ]);
        $cfg->save();
    }

    public function editBan(string $player, string $newTime): void {
        $cfg = $this->getBansConfig();
        $cfg->setNested($player . ".Time", $newTime);
        $cfg->save();
    }

    public function editMute(string $player, string $newTime): void {
        $cfg = $this->getMutesConfig();
        $cfg->setNested($player . ".Time", $newTime);
        $cfg->save();
    }

    public function getBanPoints(string $player): int {
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".BanPoints") ?? 0 : 0);
    }

    public function getMutePoints(string $player): int {
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".MutePoints") ?? 0 : 0);
    }

    public function getWarnCount(string $player): int {
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".WarnCount") ?? 0 : 0);
    }

    public function getBanInfo(string $player): false|array {
        if ($this->isBanned($player)) {
            $type = $this->getBansConfig()->getNested($player . ".Type");
            if ($type == 0) return [
                "Type" => $type,
                "Moderator" => $this->getBansConfig()->getNested($player . ".Moderator"),
                "Id" => $this->getBansConfig()->getNested($player . ".Id"),
                "Time" => $this->getBansConfig()->getNested($player . ".Time"),
                "BannedAt" => $this->getBansConfig()->getNested($player . ".BannedAt"),
            ];
            else if ($type == 1) return [
                "Type" => $type,
                "Moderator" => $this->getBansConfig()->getNested($player . ".Moderator"),
                "Reason" => $this->getBansConfig()->getNested($player . ".Reason"),
                "Time" => $this->getBansConfig()->getNested($player . ".Time"),
                "BannedAt" => $this->getBansConfig()->getNested($player . ".BannedAt"),
            ];
        }
        return false;
    }

    public function getMuteInfo(string $player): false|array {
        if ($this->isMuted($player)) {
            $type = $this->getMutesConfig()->getNested($player . ".Type");
            if ($type == 0) return [
                "Type" => $type,
                "Moderator" => $this->getMutesConfig()->getNested($player . ".Moderator"),
                "Id" => $this->getMutesConfig()->getNested($player . ".Id"),
                "Time" => $this->getMutesConfig()->getNested($player . ".Time"),
                "MutedAt" => $this->getMutesConfig()->getNested($player . ".MutedAt"),
            ];
            else if ($type == 1) return [
                "Type" => $type,
                "Moderator" => $this->getMutesConfig()->getNested($player . ".Moderator"),
                "Reason" => $this->getMutesConfig()->getNested($player . ".Reason"),
                "Time" => $this->getMutesConfig()->getNested($player . ".Time"),
                "MutedAt" => $this->getMutesConfig()->getNested($player . ".MutedAt"),
            ];
        }
        return false;
    }

    public function getBanLogs(string $player): array {
        return $this->getBanLogsConfig($player)->getAll();
    }

    public function getMuteLogs(string $player): array {
        return $this->getMuteLogsConfig($player)->getAll();
    }

    public function getBans(): array {
        return $this->getBansConfig()->getAll();
    }

    public function getMutes(): array {
        return $this->getMutesConfig()->getAll();
    }

    public function getWarns(string $player): array {
        $warns = [];
        foreach ($this->getWarnsConfig()->getAll() as $playerName => $playerWarns) {
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

    public function changeNotifyState(string $player, bool $v): void {
        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".Notify", $v);
        $cfg->save();
    }

    public function getNotifyState(string $player): bool {
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".Notify") ?? false : false);
    }


    private function getBansConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getBanPath() . "/bans.json", 1);
    }

    private function getMutesConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getMutePath() . "/mutes.json", 1);
    }

    private function getWarnsConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getWarnPath() . "/warns.json", 1);
    }

    private function getBanLogsConfig(Player|string $player): Config {
        $player = $player instanceof Player ? $player->getName() : $player;
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "banlogs/" . $player . ".json", 1);
    }

    private function getMuteLogsConfig(Player|string $player): Config {
        $player = $player instanceof Player ? $player->getName() : $player;
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "mutelogs/" . $player . ".json", 1);
    }

    private function getInfoConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "/players.json", 1);
    }
}