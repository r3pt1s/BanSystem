<?php

namespace r3pt1s\BanSystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class BanIdsCommand extends Command implements PluginOwned {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("bansystem.banids.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(BanSystem::getPrefix() . "§7BanIds: §8(§e" . count($this->getOwningPlugin()->getConfiguration()->getBanIds()) . "§8)");
            foreach ($this->getOwningPlugin()->getConfiguration()->getBanIds() as $id => $data) {
                $sender->sendMessage("§8» §e" . $id . " §8- §e" . $data["reason"] . " §8- §e" . ($data["duration"] == "-1" ? "PERMANENTLY" : $data["duration"]));
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