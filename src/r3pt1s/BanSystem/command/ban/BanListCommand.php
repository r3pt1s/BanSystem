<?php

namespace r3pt1s\BanSystem\command\ban;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class BanListCommand extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("banlist.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            $bans = array_keys(BanManager::getInstance()->getBans());
            $sender->sendMessage(BanSystem::getPrefix() . "§7Bans: §8(§e" . count($bans) . "§8)");
            $sender->sendMessage("§8» §e" . (count($bans) == 0 ? "§cNo bans." : implode("§8, §e", $bans)));
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }
}