<?php

namespace BanSystem\handler;

use BanSystem\BanSystem;
use BanSystem\manager\mute\MuteManager;
use BanSystem\utils\Utils;
use pocketmine\player\Player;

class MuteHandler {

    private static self $instance;

    public function __construct() {
        self::$instance = $this;
    }

    public function handle(Player $player, ?string &$muteScreen = null): bool {
        if (MuteManager::getInstance()->isMuted($player)) {
            if (!$player->hasPermission("mute.bypass")) {
                if (!MuteManager::getInstance()->isMuteExpired($player)) {
                    $info = MuteManager::getInstance()->getMuteInfo($player);
                    if ($info !== false) {
                        $muteScreen = BanSystem::getPrefix() . "§cYou were muted!\n";
                        $muteScreen .= BanSystem::getPrefix() . "§cReason: §r§e" . ($info["Type"] == MuteManager::TYPE_MUTE ?
                                (
                                isset(BanSystem::getInstance()->getConfiguration()->getMuteIds()[$info["Id"]]) ? BanSystem::getInstance()->getConfiguration()->getMuteIds()[$info["Id"]]["reason"] : "Error"
                                ) : $info["Reason"]) . "\n";
                        $muteScreen .= BanSystem::getPrefix() . "§cRemaining Time: §r§e" . ($info["Time"] == "-1" ? "PERMANENTLY" : Utils::diffString(new \DateTime("now"), new \DateTime($info["Time"])));
                        return true;
                    }
                } else {
                    MuteManager::getInstance()->unmutePlayer($player);
                }
            }
        }
        return false;
    }

    public static function getInstance(): MuteHandler {
        return self::$instance;
    }
}