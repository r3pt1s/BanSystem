<?php

namespace r3pt1s\BanSystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use r3pt1s\BanSystem\provider\CurrentProvider;
use r3pt1s\BanSystem\utils\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class TempMuteCommand extends Command implements PluginOwned {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("bansystem.tempmute.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0]) && isset($args[1]) && isset($args[2])) {
                if (CurrentProvider::get()->isPlayerCreated($args[0])) {
                    if (!MuteManager::getInstance()->isMuted($args[0])) {
                        if (Utils::convertStringToDateFormat($args[2]) === null) {
                            $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid duration format! Example: 1d");
                        } else {
                            $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7was muted!");
                            MuteManager::getInstance()->tempMutePlayer($args[0], $sender->getName(), $args[1], $args[2]);
                        }
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7is already muted!");
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player doesn't exists!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c/tempmute <player> <reason> <duration>");
            }
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}