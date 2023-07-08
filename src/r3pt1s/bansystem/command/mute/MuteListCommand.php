<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\manager\mute\MuteManager;

class MuteListCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("mutelist", "See a list of all muted players", "/mutelist", ["mutes"]);
        $this->setPermission("bansystem.command.mutelist");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            $mutes = array_keys(MuteManager::getInstance()->getMutes());
            $sender->sendMessage(BanSystem::getPrefix() . "§7Mutes §8(§e" . count($mutes) . "§8)§7:");
            $sender->sendMessage("§8» §e" . (count($mutes) == 0 ? "§cNo mutes." : implode("§8, §e", $mutes)));
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}