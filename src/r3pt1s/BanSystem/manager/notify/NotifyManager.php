<?php

namespace r3pt1s\BanSystem\manager\notify;

use pocketmine\player\Player;
use pocketmine\Server;
use r3pt1s\BanSystem\provider\CurrentProvider;

class NotifyManager {

    public static function sendNotify(string $message) {
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->hasPermission("notify.receive") && self::getInstance()->receiveNotify($player)) {
                $player->sendMessage($message);
            }
        }
    }

    private static self $instance;

    public function __construct() {
        self::$instance = $this;
    }

    public function setNotify(Player|string $player, bool $v) {
        $player = $player instanceof Player ? $player->getName() : $player;

        CurrentProvider::get()->changeNotifyState($player, $v);
    }

    public function receiveNotify(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        return CurrentProvider::get()->getNotifyState($player);
    }

    public static function getInstance(): NotifyManager {
        return self::$instance;
    }

}