<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class BanListCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("banlist", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_BAN_LIST), "/banlist", ["bans"]);
        $this->setPermission("bansystem.command.banlist");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            $bans = array_keys(BanManager::getInstance()->getBans());
            $sender->sendMessage(BanSystem::getPrefix() . "§7Bans §8(§e" . count($bans) . "§8)§7:");
            $sender->sendMessage("§8» §e" . (count($bans) == 0 ? "§e0" : implode("§8, §e", $bans)));
        } else {
            $sender->sendMessage(Language::get()->translate(LanguageKeys::NO_PERMS));
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}