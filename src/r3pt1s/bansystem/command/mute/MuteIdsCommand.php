<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\util\Configuration;

class MuteIdsCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("muteids", "See all muteids", "/muteids");
        $this->setPermission("bansystem.command.muteids");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            $sender->sendMessage(BanSystem::getPrefix() . "§7MuteIds §8(§e" . count(Configuration::getInstance()->getMuteIds()) . "§8)§7:");
            foreach (Configuration::getInstance()->getMuteIds() as $id) {
                $sender->sendMessage("§8» §e" . $id->getId() . " §8- §e" . $id->getReason() . " §8- §e" . ($id->getDuration() === null ? "PERMANENTLY" : $id->getDuration()));
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