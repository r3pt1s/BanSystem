<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\manager\mute\MuteManager;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;
use r3pt1s\bansystem\util\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

final class TempMuteCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("tempmute", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_TEMP_MUTE), "/tempmute <player> <reason> <duration>");
        $this->setPermission("bansystem.command.tempmute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 3) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $reason = $args[1];
            $duration = $args[2];

            if ($target == $sender->getName()) {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::PUNISH_FAILED_YOURSELF));
                return true;
            }

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target, $reason, $duration): void {
                    if (!$exists) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_FOUND));
                        return;
                    }

                    if (Utils::convertStringToDateFormat($duration) === null) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::VALID_TIME_FORMAT));
                        return;
                    }

                    if (($response = MuteManager::getInstance()->addMute(new Mute($target, $sender->getName(), $reason, new \DateTime(), Utils::convertStringToDateFormat($duration)))) == BanSystem::SUCCESS) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::MUTE_SUCCESS, $target));
                    } else {
                        $sender->sendMessage(match ($response) {
                                BanSystem::FAILED_ALREADY => Language::get()->translate(LanguageKeys::MUTE_ALREADY_MUTED, $target),
                                BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::MUTE_EVENT_CANCELLED, $target),
                            });
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