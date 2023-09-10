<?php

namespace r3pt1s\bansystem\command\notify;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\notify\NotifyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class NotifyCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("notify", "Enable or disable notifications", "/notify");
        $this->setPermission("bansystem.command.notify");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                if (NotifyManager::getInstance()->hasNotifications($sender)) {
                    NotifyManager::getInstance()->setState($sender, false);
                    $sender->sendMessage(BanSystem::getPrefix() . "§7You will no longer receive notifications!");
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7You will now receive notifications!");
                    NotifyManager::getInstance()->setState($sender, true);
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