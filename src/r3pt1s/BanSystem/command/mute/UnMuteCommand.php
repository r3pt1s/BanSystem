<?php

namespace r3pt1s\BanSystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class UnMuteCommand extends Command implements PluginOwned {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("bansystem.unmute.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0])) {
                if (MuteManager::getInstance()->isMuted($args[0])) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7was unmuted!");
                    MuteManager::getInstance()->unmutePlayer($args[0], $sender->getName());
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7isn't muted!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c/unmute <player>");
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