<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class MuteInfoCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("muteinfo", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_MUTE_INFO), "/muteinfo <player>");
        $this->setPermission("bansystem.command.muteinfo");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = implode(" ", $args);

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target): void {
                    if (!$exists) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_FOUND));
                        return;
                    }

                    BanSystem::getInstance()->getProvider()->getMutePoints($target)->onCompletion(
                        function(int $points) use($target, $sender): void {
                            if (($mute = MuteManager::getInstance()->getMute($target)) !== null) {
                                $sender->sendMessage(Language::get()->translate(LanguageKeys::MUTE_INFO_MESSAGE_MUTED, $target, $points, Language::get()->translate(LanguageKeys::RAW_YES), $mute->getReason(), $mute->getModerator(), $mute->getTime()->format("Y-m-d H:i:s"), ($mute->getExpire()?->format("Y-m-d H:i:s") ?? "§c§l" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))));
                            } else {
                                $sender->sendMessage(Language::get()->translate(LanguageKeys::MUTE_INFO_MESSAGE_NOT_MUTED, $target, $points, Language::get()->translate(LanguageKeys::RAW_NO)));
                            }
                        },
                        fn() => $sender->sendMessage(Language::get()->translate(LanguageKeys::CHECK_MUTE_POINTS_FAILED, $target))
                    );
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