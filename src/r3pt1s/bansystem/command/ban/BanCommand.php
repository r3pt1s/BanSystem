<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;
use r3pt1s\bansystem\util\Utils;

final class BanCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("ban", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_BAN), "/ban <player> <banId>");
        $this->setPermission("bansystem.command.ban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 2) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $banId = $args[1];

            if ($target == $sender->getName()) {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::PUNISH_FAILED_YOURSELF));
                return true;
            }

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target, $banId): void {
                    if (!$exists) {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_FOUND));
                        return;
                    }

                    if (($banId = Configuration::getInstance()->getBanId($banId)) !== null) {
                        if (($response = BanManager::getInstance()->addBan(new Ban($target, $sender->getName(), $banId->getReason(), new \DateTime(), ($banId->getDuration() === null ? null : Utils::convertStringToDateFormat($banId->getDuration()))))) == BanSystem::SUCCESS) {
                            $sender->sendMessage(Language::get()->translate(LanguageKeys::BAN_SUCCESS, $target));
                        } else {
                            $sender->sendMessage("§c" . match ($response) {
                                BanSystem::FAILED_ALREADY => Language::get()->translate(LanguageKeys::BAN_ALREADY_BANNED, $target),
                                BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::BAN_EVENT_CANCELLED)
                            });
                        }
                    } else {
                        $sender->sendMessage(Language::get()->translate(LanguageKeys::BAN_VALID_BANID));
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