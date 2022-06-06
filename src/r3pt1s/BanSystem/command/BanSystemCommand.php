<?php

namespace r3pt1s\BanSystem\command;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class BanSystemCommand extends Command implements PluginOwned {

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $sender->sendMessage(BanSystem::getPrefix() . "§7Plugin by §er3pt1s§7:");
        $sender->sendMessage(BanSystem::getPrefix() . "§7Version: §e" . BanSystem::getInstance()->getDescription()->getVersion());
        $sender->sendMessage(BanSystem::getPrefix() . "§7Discord: §er3pt1s#8228");
        $sender->sendMessage(BanSystem::getPrefix() . "§7Download: §ehttps://poggit.pmmp.io/ci/r3pt1s/BanSystem");
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}