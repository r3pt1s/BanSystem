<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class BanListCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("banlist", "See a list of all banned players", "/banlist", ["bans"]);
        $this->setPermission("bansystem.command.banlist");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            $bans = array_keys(BanManager::getInstance()->getBans());
            $sender->sendMessage(BanSystem::getPrefix() . "§7Bans §8(§e" . count($bans) . "§8)§7:");
            $sender->sendMessage("§8» §e" . (count($bans) == 0 ? "§cNo bans." : implode("§8, §e", $bans)));
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}