<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class BanInfoCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("baninfo", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_BAN_INFO), "/baninfo <player>");
        $this->setPermission("bansystem.command.baninfo");
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

                    BanSystem::getInstance()->getProvider()->getBanPoints($target)->onCompletion(
                        function(int $points) use($target, $sender): void {
                            if (($ban = BanManager::getInstance()->getBan($target)) !== null) {
                                $sender->sendMessage(Language::get()->translate(LanguageKeys::BAN_INFO_MESSAGE_BANNED, $target, $points, Language::get()->translate(LanguageKeys::RAW_YES), $ban->getReason(), $ban->getModerator(), $ban->getTime()->format("Y-m-d H:i:s"), ($ban->getExpire()?->format("Y-m-d H:i:s") ?? "§c§l" . Language::get()->translate(LanguageKeys::RAW_PERMANENTLY))));
                            } else {
                                $sender->sendMessage(Language::get()->translate(LanguageKeys::BAN_INFO_MESSAGE_NOT_BANNED, $target, $points, Language::get()->translate(LanguageKeys::RAW_NO)));
                            }
                        },
                        fn() => $sender->sendMessage(Language::get()->translate(LanguageKeys::CHECK_BAN_POINTS_FAILED, $target))
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