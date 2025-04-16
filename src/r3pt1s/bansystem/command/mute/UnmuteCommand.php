<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class UnmuteCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("unmute", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_UNMUTE), "/unmute <player> [mistake]");
        $this->setPermission("bansystem.command.unmute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = array_shift($args);
            $mistake = false;
            if (isset($args[0])) $mistake = (strtolower($args[0]) == "yes" || strtolower($args[0]) == "true");

            if (($mute = MuteManager::getInstance()->getMute($target)) !== null) {
                if (($response = MuteManager::getInstance()->removeMute($mute, $sender, $mistake)) == BanSystem::SUCCESS) {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::UNMUTE_SUCCESS, $target));
                } else {
                    $sender->sendMessage(match($response) {
                            BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::UNMUTE_EVENT_CANCELLED),
                            BanSystem::FAILED_NOT => Language::get()->translate(LanguageKeys::PLAYER_NOT_MUTED)
                        });
                }
            } else {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_MUTED));
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