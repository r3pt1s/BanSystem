<?php

namespace r3pt1s\BanSystem\manager\notify;

use r3pt1s\BanSystem\BanSystem;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

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

        $cfg = $this->getInfoConfig();
        $cfg->setNested($player . ".Notify", $v);
        $cfg->save();
    }

    public function receiveNotify(Player|string $player): bool {
        $player = $player instanceof Player ? $player->getName() : $player;
        return ($this->getInfoConfig()->exists($player) ? $this->getInfoConfig()->getNested($player . ".Notify") ?? false : false);
    }

    private function getInfoConfig(): Config {
        return new Config(BanSystem::getInstance()->getConfiguration()->getInfoPath() . "/players.json", 1);
    }

    public static function getInstance(): NotifyManager {
        return self::$instance;
    }

}