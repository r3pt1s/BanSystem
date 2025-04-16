<?php

namespace r3pt1s\bansystem\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class KickCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("kick", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_KICK), "/kick <player> [reason]");
        $this->setPermission("bansystem.command.kick");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = array_shift($args);

            if ($target == $sender->getName()) {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::PUNISH_FAILED_YOURSELF));
                return true;
            }

            $reason = trim(implode(" ", $args));
            if (($target = Server::getInstance()->getPlayerExact($target)) !== null) {
                if (($response = BanSystem::getInstance()->kickPlayer($target, $sender, $reason)) == BanSystem::SUCCESS) {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::KICK_SUCCESS, $target->getName()));
                } else {
                    $sender->sendMessage(match ($response) {
                        BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::KICK_EVENT_CANCELLED, $target->getName()),
                        BanSystem::FAILED_CANT => Language::get()->translate(LanguageKeys::KICK_FAILED, $target->getName())
                    });
                }
            } else {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_ONLINE));
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