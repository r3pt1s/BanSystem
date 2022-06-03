<?php

namespace r3pt1s\BanSystem\command\warn;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\warn\WarnManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

class ResetWarnsCommand extends Command implements PluginOwned {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("bansystem.resetwarns.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($sender->hasPermission($this->getPermission())) {
                if (isset($args[0])) {
                    if ($this->getOwningPlugin()->isPlayerCreated($args[0])) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7The warns of the player §e" . $args[0] . " §7were removed!");
                        WarnManager::getInstance()->removeWarns($args[0]);
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7doesn't exists!");
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c/resetwarns <player>");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
            }
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}