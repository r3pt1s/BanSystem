<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;
use r3pt1s\bansystem\util\Utils;

final class MuteCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("mute", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_MUTE), "/mute <player> <muteId>");
        $this->setPermission("bansystem.command.mute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 2) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $muteId = $args[1];

            if ($target == $sender->getName()) {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::PUNISH_FAILED_YOURSELF));
                return true;
            }

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target, $muteId): void {
                    if (!$exists) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_FOUND));
                        return;
                    }

                    if (($muteId = Configuration::getInstance()->getMuteId($muteId)) !== null) {
                        if (($response = MuteManager::getInstance()->addMute(new Mute($target, $sender->getName(), $muteId->getReason(), new \DateTime(), ($muteId->getDuration() === null ? null : Utils::convertStringToDateFormat($muteId->getDuration()))))) == BanSystem::SUCCESS) {
                            $sender->sendMessage(Language::get()->translate(LanguageKeys::MUTE_SUCCESS, $target));
                        } else {
                            $sender->sendMessage("§c" . match ($response) {
                                    BanSystem::FAILED_ALREADY => Language::get()->translate(LanguageKeys::MUTE_ALREADY_MUTED, $target),
                                    BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::MUTE_EVENT_CANCELLED)
                                });
                        }
                    } else {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::MUTE_VALID_MUTEID));
                    }
                },
                fn() => $sender->sendMessage(Language::get()->translate(LanguageKeys::CHECK_EXISTS_FAILED, $target))
            );
        } else {
            $sender->sendMessage(Language::get()->translate(LanguageKeys::NO_PERMS));
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}