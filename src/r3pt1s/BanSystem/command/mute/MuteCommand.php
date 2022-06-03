<?php

namespace r3pt1s\BanSystem\command\mute;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class MuteCommand extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("mute.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0]) && isset($args[1])) {
                if (BanSystem::getInstance()->isPlayerCreated($args[0])) {
                    if (!MuteManager::getInstance()->isMuted($args[0])) {
                        if (is_numeric($args[1])) {
                            if (MuteManager::getInstance()->isMuteId(intval($args[1]))) {
                                $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7was muted!");
                                MuteManager::getInstance()->mutePlayer($args[0], $sender, intval($args[1]));
                            } else {
                                $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid muteid!");
                            }
                        } else {
                            $sender->sendMessage(BanSystem::getPrefix() . "§c/mute <player> <muteId>");
                        }
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7is already muted!");
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player doesn't exists!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c/mute <player> <banId>");
            }
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }
}