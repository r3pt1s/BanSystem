<?php

namespace r3pt1s\bansystem\command\util;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class VanishCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("vanish", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_VANISH), "/vanish", ["v"]);
        $this->setPermission("bansystem.command.vanish");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                if (BanSystem::getInstance()->isVanished($sender)) {
                    BanSystem::getInstance()->showPlayer($sender);
                } else {
                    BanSystem::getInstance()->vanishPlayer($sender);
                }
            } else {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::NO_PERMS));
            }
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}