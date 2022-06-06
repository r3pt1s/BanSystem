<?php

namespace r3pt1s\BanSystem\provider;

use r3pt1s\BanSystem\database\Database;
use r3pt1s\BanSystem\manager\ban\BanManager;
use r3pt1s\BanSystem\manager\mute\MuteManager;

class MySQLProvider extends Provider {

    public function createPlayer(string $player): void {
        Database::getInstance()->getDatabase()->insert("players", ["Player" => $player, "BanPoints" => 0, "MutePoints" => 0, "WarnCount" => 0, "Notify" => false]);
    }

    public function pushBan(string $player, int $type, string $moderator, int $id, string $time, string $bannedAt): void {
        Database::getInstance()->getDatabase()->insert("bans", ["Player" => $player, "Type" => $type, "Moderator" => $moderator, "Id" => $id, "Reason" => "", "Time" => $time, "BannedAt" => $bannedAt]);
    }

    public function pushMute(string $player, int $type, string $moderator, int $id, string $time, string $mutedAt): void {
        Database::getInstance()->getDatabase()->insert("mutes", ["Player" => $player, "Type" => $type, "Moderator" => $moderator, "Id" => $id, "Reason" => "", "Time" => $time, "MutedAt" => $mutedAt]);
    }

    public function pushTempBan(string $player, int $type, string $moderator, string $reason, string $time, string $bannedAt): void {
        Database::getInstance()->getDatabase()->insert("bans", ["Player" => $player, "Type" => $type, "Moderator" => $moderator, "Id" => 0, "Reason" => $reason, "Time" => $time, "BannedAt" => $bannedAt]);
    }

    public function pushTempMute(string $player, int $type, string $moderator, string $reason, string $time, string $mutedAt): void {
        Database::getInstance()->getDatabase()->insert("mutes", ["Player" => $player, "Type" => $type, "Moderator" => $moderator, "Id" => 0, "Reason" => $reason, "Time" => $time, "MutedAt" => $mutedAt]);
    }

    public function pushWarn(string $player, string $reason, string $moderator, string $warnedAt): void {
        Database::getInstance()->getDatabase()->insert("warns", ["Player" => $player, "Reason" => $reason, "Moderator" => $moderator, "WarnedAt" => $warnedAt]);
    }

    public function removeBan(string $player): void {
        Database::getInstance()->getDatabase()->delete("bans", ["Player" => $player]);
    }

    public function removeMute(string $player): void {
        Database::getInstance()->getDatabase()->delete("mutes", ["Player" => $player]);
    }

    public function removeWarns(string $player): void {
        Database::getInstance()->getDatabase()->delete("warns", ["Player" => $player]);
    }

    public function isBanned(string $player): bool {
        $result = Database::getInstance()->getDatabase()->get("bans", ["Player"], ["Player" => $player]);
        return is_array($result);
    }

    public function isMuted(string $player): bool {
        $result = Database::getInstance()->getDatabase()->get("mutes", ["Player"], ["Player" => $player]);
        return is_array($result);
    }

    public function isPlayerCreated(string $player): bool {
        $result = Database::getInstance()->getDatabase()->get("players", ["Player"], ["Player" => $player]);
        return is_array($result);
    }

    public function addBanPoint(string $player): void {
        Database::getInstance()->getDatabase()->update("players", ["BanPoints[+]" => 1], ["Player" => $player]);
    }

    public function addMutePoint(string $player): void {
        Database::getInstance()->getDatabase()->update("players", ["MutePoints[+]" => 1], ["Player" => $player]);
    }

    public function addWarnCount(string $player): void {
        Database::getInstance()->getDatabase()->update("players", ["WarnCount[+]" => 1], ["Player" => $player]);
    }

    public function removeWarnCount(string $player): void {
        Database::getInstance()->getDatabase()->update("players", ["WarnCount" => 0], ["Player" => $player]);
    }

    public function pushBanLog(string $player, string $moderator, string $reason, string $time, string $bannedAt): void {
        Database::getInstance()->getDatabase()->insert("banlogs", ["Player" => $player, "Moderator" => $moderator, "Reason" => $reason, "Time" => $time, "BannedAt" => $bannedAt]);
    }

    public function pushMuteLog(string $player, string $moderator, string $reason, string $time, string $mutedAt): void {
        Database::getInstance()->getDatabase()->insert("mutelogs", ["Player" => $player, "Moderator" => $moderator, "Reason" => $reason, "Time" => $time, "MutedAt" => $mutedAt]);
    }

    public function editBan(string $player, string $newTime): void {
        Database::getInstance()->getDatabase()->update("bans", ["Time" => $newTime], ["Player" => $player]);
    }

    public function editMute(string $player, string $newTime): void {
        Database::getInstance()->getDatabase()->update("mutes", ["Time" => $newTime], ["Player" => $player]);
    }

    public function getBanPoints(string $player): int {
        $result = Database::getInstance()->getDatabase()->get("players", ["BanPoints"], ["Player" => $player]) ?? ["BanPoints" => 0];
        return $result["BanPoints"] ?? 0;
    }

    public function getMutePoints(string $player): int {
        $result = Database::getInstance()->getDatabase()->get("players", ["MutePoints"], ["Player" => $player]) ?? ["MutePoints" => 0];
        return $result["MutePoints"] ?? 0;
    }

    public function getWarnCount(string $player): int {
        $result = Database::getInstance()->getDatabase()->get("players", ["WarnCount"], ["Player" => $player]) ?? ["WarnCount" => 0];
        return $result["WarnCount"] ?? 0;
    }

    public function getBanInfo(string $player): false|array {
        $result = Database::getInstance()->getDatabase()->get("bans", ["Player", "Type", "Moderator", "Id", "Reason", "Time", "BannedAt"], ["Player" => $player]) ?? false;
        if (!$result) return false;
        if (intval($result["Type"]) == BanManager::TYPE_BAN) {
            return [
                "Type" => intval($result["Type"]),
                "Moderator" => $result["Moderator"],
                "Id" => intval($result["Id"]),
                "Time" => $result["Time"],
                "BannedAt" => $result["BannedAt"]
            ];
        } else if (intval($result["Type"]) == BanManager::TYPE_TEMP_BAN) {
            return [
                "Type" => intval($result["Type"]),
                "Moderator" => $result["Moderator"],
                "Reason" => $result["Reason"],
                "Time" => $result["Time"],
                "BannedAt" => $result["BannedAt"]
            ];
        }
        return false;
    }

    public function getMuteInfo(string $player): false|array {
        $result = Database::getInstance()->getDatabase()->get("mutes", ["Player", "Type", "Moderator", "Id", "Reason", "Time", "MutedAt"], ["Player" => $player]) ?? false;
        if (!$result) return false;
        if (intval($result["Type"]) == MuteManager::TYPE_MUTE) {
            return [
                "Type" => intval($result["Type"]),
                "Moderator" => $result["Moderator"],
                "Id" => intval($result["Id"]),
                "Time" => $result["Time"],
                "MutedAt" => $result["MutedAt"]
            ];
        } else if (intval($result["Type"]) == MuteManager::TYPE_TEMP_MUTE) {
            return [
                "Type" => intval($result["Type"]),
                "Moderator" => $result["Moderator"],
                "Reason" => $result["Reason"],
                "Time" => $result["Time"],
                "MutedAt" => $result["MutedAt"]
            ];
        }
        return false;
    }

    public function getBanLogs(string $player): array {
        $banlogs = [];
        $result = Database::getInstance()->getDatabase()->select("banlogs", ["Player", "Moderator", "Reason", "Time", "BannedAt"], ["Player" => $player]) ?? [];
        foreach ($result as $log) {
            $banlogs[$log["BannedAt"]] = [
                "Moderator" => $log["Moderator"],
                "Reason" => $log["Reason"],
                "Time" => $log["Time"],
                "BannedAt" => $log["BannedAt"],
            ];
        }
        return $banlogs;
    }

    public function getMuteLogs(string $player): array {
        $mutelogs = [];
        $result = Database::getInstance()->getDatabase()->select("mutelogs", ["Player", "Moderator", "Reason", "Time", "MutedAt"], ["Player" => $player]) ?? [];
        foreach ($result as $log) {
            $mutelogs[$log["MutedAt"]] = [
                "Moderator" => $log["Moderator"],
                "Reason" => $log["Reason"],
                "Time" => $log["Time"],
                "MutedAt" => $log["MutedAt"],
            ];
        }
        return $mutelogs;
    }

    public function getBans(): array {
        $bans = [];
        $result = Database::getInstance()->getDatabase()->select("bans", "*") ?? [];
        foreach ($result as $ban) {
            $bans[$ban["Player"]] = [
                "Type" => $ban["Type"],
                "Moderator" => $ban["Moderator"],
                "Id" => $ban["Id"],
                "Reason" => $ban["Reason"],
                "Time" => $ban["Time"],
                "BannedAt" => $ban["BannedAt"]
            ];
        }
        return $bans;
    }

    public function getMutes(): array {
        $mutes = [];
        $result = Database::getInstance()->getDatabase()->select("mutes", "*") ?? [];
        foreach ($result as $mute) {
            $mutes[$mute["Player"]] = [
                "Type" => $mute["Type"],
                "Moderator" => $mute["Moderator"],
                "Id" => $mute["Id"],
                "Reason" => $mute["Reason"],
                "Time" => $mute["Time"],
                "MutedAt" => $mute["MutedAt"]
            ];
        }
        return $mutes;
    }

    public function getWarns(string $player): array {
        $warns = [];
        $result = Database::getInstance()->getDatabase()->select("warns", ["Player", "Moderator", "Reason", "WarnedAt"], ["Player" => $player]) ?? [];
        foreach ($result as $warn) {
            $warns[] = [
                "Reason" => $warn["Reason"],
                "Moderator" => $warn["Moderator"],
                "WarnedAt" => $warn["WarnedAt"]
            ];
        }
        return $warns;
    }

    public function changeNotifyState(string $player, bool $v): void {
        Database::getInstance()->getDatabase()->update("players", ["Notify" => $v], ["Player" => $player]);
    }

    public function getNotifyState(string $player): bool {
        $result = Database::getInstance()->getDatabase()->get("players", ["Notify"], ["Player" => $player]) ?? ["Notify" => "0"];
        return intval($result["Notify"]) == 1;
    }
}