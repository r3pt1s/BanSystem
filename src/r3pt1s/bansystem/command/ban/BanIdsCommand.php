<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class BanIdsCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("banids", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_BAN_IDS), "/banids");
        $this->setPermission("bansystem.command.banids");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            $sender->sendMessage(BanSystem::getPrefix() . "§7BanIds §8(§e" . count(Configuration::getInstance()->getBanIds()) . "§8)§7:");
            foreach (Configuration::getInstance()->getBanIds() as $id) {
                $sender->sendMessage("§8» §e" . $id->getId() . " §8- §e" . $id->getReason() . " §8- §e" . ($id->getDuration() === null ? strtoupper(Language::get()->translate("raw.permanently")) : $id->getDuration()));
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