<?php

namespace r3pt1s\bansystem\command\notify;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\notify\NotifyManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class NotifyCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("notify", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_NOTIFY), "/notify");
        $this->setPermission("bansystem.command.notify");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                if (NotifyManager::getInstance()->hasNotifications($sender)) {
                    NotifyManager::getInstance()->setState($sender, false);
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::NOTIFICATIONS_DISABLED));
                } else {
                    NotifyManager::getInstance()->setState($sender, true);
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::NOTIFICATIONS_ENABLED));
                }
            } else {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::NO_PERMS));
            }
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}