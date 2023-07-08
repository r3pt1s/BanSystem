<?php

namespace r3pt1s\bansystem\command\warn;

use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\warn\WarnManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class ClearWarnsCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("clearwarns", "Clear the warns of a player", "/clearwarns <player>");
        $this->setPermission("bansystem.command.clearwarns");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = implode(" ", $args);

            if (($target = Server::getInstance()->getPlayerExact($target)) !== null) {
                $sender->sendMessage(BanSystem::getPrefix() . "§7The warns of §e" . $target->getName() . " §7were cleared!");
                WarnManager::getInstance()->clearWarns($target, $sender);
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§cThis player is not online!");
            }
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}