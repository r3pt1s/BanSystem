<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class MuteListCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("mutelist", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_MUTE_LIST), "/mutelist", ["mutes"]);
        $this->setPermission("bansystem.command.mutelist");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            $mutes = array_keys(MuteManager::getInstance()->getMutes());
            $sender->sendMessage(BanSystem::getPrefix() . "§7Mutes §8(§e" . count($mutes) . "§8)§7:");
            $sender->sendMessage("§8» §e" . (count($mutes) == 0 ? "§cNo mutes." : implode("§8, §e", $mutes)));
        } else {
            $sender->sendMessage(Language::get()->translate(LanguageKeys::NO_PERMS));
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}