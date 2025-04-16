<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class UnbanCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("unban", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_UNBAN), "/unban <player> [mistake]", ["pardon"]);
        $this->setPermission("bansystem.command.unban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = array_shift($args);
            $mistake = false;
            if (isset($args[0])) $mistake = strtolower($args[0]) == "yes" || strtolower($args[0]) == "true";

            if (($ban = BanManager::getInstance()->getBan($target)) !== null) {
                if (($response = BanManager::getInstance()->removeBan($ban, $sender, $mistake)) == BanSystem::SUCCESS) {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::UNBAN_SUCCESS, $target));
                } else {
                    $sender->sendMessage(match($response) {
                        BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::UNBAN_EVENT_CANCELLED),
                        BanSystem::FAILED_NOT => Language::get()->translate(LanguageKeys::PLAYER_NOT_BANNED)
                    });
                }
            } else {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_BANNED));
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