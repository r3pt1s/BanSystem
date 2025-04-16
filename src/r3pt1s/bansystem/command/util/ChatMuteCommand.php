<?php

namespace r3pt1s\bansystem\command\util;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class ChatMuteCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("chatmute", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_MUTE_CHAT), "/chatmute", ["mutechat"]);
        $this->setPermission("bansystem.command.chat_mute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (BanSystem::getInstance()->isChatMuted()) {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::CHAT_MUTE_MUTED_SUCCESS));
                BanSystem::getInstance()->unmuteChat($sender->getName());
            } else {
                $sender->sendMessage(Language::get()->translate(LanguageKeys::CHAT_MUTE_UNMUTED_SUCCESS));
                BanSystem::getInstance()->muteChat($sender->getName());
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