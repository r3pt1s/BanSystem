<?php

namespace r3pt1s\bansystem\provider;

use pocketmine\player\Player;
use pocketmine\promise\Promise;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\mute\Mute;

interface Provider {

    public function addBan(Ban $ban): void;

    public function addBanLog(Ban $ban): void;

    public function removeBan(Ban $ban): void;

    public function editBan(Ban $ban, ?string $newTime): void;

    public function getBan(string $username): Promise;

    public function getBans(): Promise;

    public function addMute(Mute $mute): void;

    public function addMuteLog(Mute $mute): void;

    public function removeMute(Mute $mute): void;

    public function editMute(Mute $mute, ?string $newTime): void;

    public function getMute(string $username): Promise;

    public function getMutes(): Promise;

    public function createPlayer(Player $player): void;

    public function setNotifications(string $username, bool $value): void;

    public function addBanPoint(string $username): void;

    public function removeBanPoint(string $username): void;

    public function addMutePoint(string $username): void;

    public function removeMutePoint(string $username): void;

    public function getBanPoints(string $username): Promise;

    public function getMutePoints(string $username): Promise;

    public function getBanLogs(string $username): Promise;

    public function getMuteLogs(string $username): Promise;

    public function isBanned(string $username): Promise;

    public function isMuted(string $username): Promise;

    public function checkPlayer(string $username): Promise;

    public function hasNotifications(string $username): Promise;
}