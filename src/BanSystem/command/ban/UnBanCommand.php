<?php

namespace BanSystem\command\ban;

use BanSystem\BanSystem;
use BanSystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class UnBanCommand extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("unban.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0])) {
                if (BanManager::getInstance()->isBanned($args[0])) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7was unbanned!");
                    BanManager::getInstance()->unbanPlayer($args[0], $sender->getName());
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7isn't banned!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c/unban <player>");
            }
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }
}