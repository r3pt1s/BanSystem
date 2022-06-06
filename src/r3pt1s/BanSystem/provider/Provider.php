<?php

namespace r3pt1s\BanSystem\provider;

abstract class Provider {

    abstract public function createPlayer(string $player): void;

    abstract public function pushBan(string $player, int $type, string $moderator, int $id, string $time, string $bannedAt): void;

    abstract public function pushMute(string $player, int $type, string $moderator, int $id, string $time, string $mutedAt): void;

    abstract public function pushTempBan(string $player, int $type, string $moderator, string $reason, string $time, string $bannedAt): void;

    abstract public function pushTempMute(string $player, int $type, string $moderator, string $reason, string $time, string $mutedAt): void;

    abstract public function pushWarn(string $player, string $reason, string $moderator, string $warnedAt): void;

    abstract public function removeBan(string $player): void;

    abstract public function removeMute(string $player): void;

    abstract public function removeWarns(string $player): void;

    abstract public function isBanned(string $player): bool;

    abstract public function isMuted(string $player): bool;

    abstract public function isPlayerCreated(string $player): bool;

    abstract public function addBanPoint(string $player): void;

    abstract public function addMutePoint(string $player): void;

    abstract public function addWarnCount(string $player): void;

    abstract public function removeWarnCount(string $player): void;

    abstract public function pushBanLog(string $player, string $moderator, string $reason, string $time, string $bannedAt): void;

    abstract public function pushMuteLog(string $player, string $moderator, string $reason, string $time, string $mutedAt): void;

    abstract public function editBan(string $player, string $newTime): void;

    abstract public function editMute(string $player, string $newTime): void;

    abstract public function getBanPoints(string $player): int;

    abstract public function getMutePoints(string $player): int;

    abstract public function getWarnCount(string $player): int;

    abstract public function getBanInfo(string $player): false|array;

    abstract public function getMuteInfo(string $player): false|array;

    abstract public function getBanLogs(string $player): array;

    abstract public function getMuteLogs(string $player): array;

    abstract public function getBans(): array;

    abstract public function getMutes(): array;

    abstract public function getWarns(string $player): array;

    abstract public function changeNotifyState(string $player, bool $v): void;

    abstract public function getNotifyState(string $player): bool;
}