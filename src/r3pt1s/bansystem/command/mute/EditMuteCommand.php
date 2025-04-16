<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;
use r3pt1s\bansystem\util\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

final class EditMuteCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("editmute", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_EDIT_MUTE), "/editmute <player> <add|sub> <time>");
        $this->setPermission("bansystem.command.editmute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 3) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $action = strtolower($args[1]);
            $time = $args[2];

            if ($action == "add" || $action == "sub") {
                if (Utils::convertStringToDateFormat($time) === null) {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::VALID_TIME_FORMAT));
                    return true;
                }

                if (($mute = MuteManager::getInstance()->getMute($target)) !== null) {
                    if ($mute->getExpire() === null) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::EDIT_MUTE_EDIT_FAILED, $target));
                        return true;
                    }

                    if (($response = MuteManager::getInstance()->editMute($mute, $sender, Utils::convertStringToDateFormat($time, $mute->getExpire(), $action))) == BanSystem::SUCCESS) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::EDIT_MUTE_SUCCESS, $target));
                    } else {
                        $sender->sendMessage(match($response) {
                                BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::EDIT_MUTE_EVENT_CANCELLED),
                                BanSystem::FAILED_NOT => Language::get()->translate(LanguageKeys::PLAYER_NOT_MUTED, $target)
                            });
                    }
                } else {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_MUTED, $target));
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
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