<?php

namespace r3pt1s\bansystem\command\util;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\util\Language;
use r3pt1s\bansystem\util\LanguageKeys;

final class SpectateCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("spectate", Language::get()->translate(LanguageKeys::COMMAND_DESCRIPTION_SPECTATE), "/spectate <player>", ["spec"]);
        $this->setPermission("bansystem.command.spectate");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermissionSilent($sender)) {
                $stoppedSpectating = false;
                if (BanSystem::getInstance()->isSpectating($sender)) {
                    BanSystem::getInstance()->stopSpectating($sender);
                    $stoppedSpectating = true;
                }

                if (count($args) == 0) {
                    if (!$stoppedSpectating) $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                    return true;
                }

                $target = implode(" ", $args);
                if (($target = Server::getInstance()->getPlayerExact($target)) !== null) {
                    BanSystem::getInstance()->spectatePlayer($target, $sender);
                } else {
                    $sender->sendMessage(Language::get()->translate(LanguageKeys::PLAYER_NOT_ONLINE));
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