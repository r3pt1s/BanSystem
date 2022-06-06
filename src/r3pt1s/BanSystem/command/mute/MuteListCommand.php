<?php

namespace r3pt1s\BanSystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class MuteListCommand extends Command implements PluginOwned {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("bansystem.mutelist.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            $mutes = array_keys(MuteManager::getInstance()->getMutes());
            $sender->sendMessage(BanSystem::getPrefix() . "§7Mutes: §8(§e" . count($mutes) . "§8)");
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