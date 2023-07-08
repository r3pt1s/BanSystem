<?php

namespace r3pt1s\bansystem\command\warn;

use pocketmine\plugin\PluginOwned;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\form\warn\WarnsForm;
use r3pt1s\bansystem\manager\warn\WarnManager;

class WarnsCommand extends Command implements PluginOwned {
    
    public function __construct() {
        parent::__construct("warns", "See all warns of a player", "/warns <player>");
        $this->setPermission("bansystem.command.warns");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                if (count($args) == 0) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                    return false;
                }

                $target = implode(" ", $args);

                if (($target = Server::getInstance()->getPlayerExact($target)) !== null) {
                    if (count(WarnManager::getInstance()->getWarns($target)) == 0) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§e" . $target->getName() . " §7has no warns.");
                    } else {
                        $sender->sendForm(new WarnsForm($target->getName()));
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§cThis player is not online!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§cYou don't have the permission to use this command.");
            }
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}