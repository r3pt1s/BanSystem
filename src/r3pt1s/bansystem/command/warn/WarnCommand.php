<?php

namespace r3pt1s\bansystem\command\warn;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\warn\Warn;
use r3pt1s\bansystem\manager\warn\WarnManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class WarnCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("warn", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_WARN), "/warn <player> [reason]");
        $this->setPermission("bansystem.command.warn");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return false;
            }

            $target = array_shift($args);
            $reason = trim(implode(" ", $args));

            if (($target = Server::getInstance()->getPlayerExact($target)) !== null) {
                if ($target->getName() == $sender->getName()) {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::PUNISH_FAILED_YOURSELF));
                    return true;
                }

                if (($response = WarnManager::getInstance()->addWarn(new Warn($target, $sender->getName(), new \DateTime(), ($reason == "" ? null : $reason)))) == BanSystem::SUCCESS) {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::WARN_SUCCESS, $target->getName()));
                    $target->sendMessage(Language::get()->translate(LanguageKeys::SCREEN_WARN, ($reason == "" ? "/" : $reason)));
                } else {
                    $sender->sendMessage(match ($response) {
                        BanSystem::FAILED_CANCELLED => Language::get()->translate(LanguageKeys::WARN_EVENT_CANCELLED)
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