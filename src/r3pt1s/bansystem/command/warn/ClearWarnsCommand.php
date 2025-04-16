<?php

namespace r3pt1s\bansystem\command\warn;

use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\warn\WarnManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class ClearWarnsCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("clearwarns", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_CLEAR_WARNS), "/clearwarns <player>");
        $this->setPermission("bansystem.command.clearwarns");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = implode(" ", $args);

            if (($target = Server::getInstance()->getPlayerExact($target)) !== null) {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::WARNS_CLEARED, $target->getName()));
                WarnManager::getInstance()->clearWarns($target, $sender);
            } else {
                
                $sender->sendMessage(Language::get()->translate(LanguageKeys::WARNS_CLEARED, $target->getName()));
            }
        } else {
            $sender->sendMessage(Language::get()->translate(LanguageKeys::NO_PERMS));
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}