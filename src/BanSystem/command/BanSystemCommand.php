<?php

namespace BanSystem\command;

use BanSystem\BanSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class BanSystemCommand extends Command {

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $sender->sendMessage(BanSystem::getPrefix() . "§7Plugin by §er3pt1s§7:");
        $sender->sendMessage(BanSystem::getPrefix() . "§7Version: §e" . BanSystem::$VERSION);
        $sender->sendMessage(BanSystem::getPrefix() . "§7Discord: §er3pt1s#8228");
        $sender->sendMessage(BanSystem::getPrefix() . "§7Download: §ehttps://github.com/r3pt1s/BanSystem");
        return true;
    }
}