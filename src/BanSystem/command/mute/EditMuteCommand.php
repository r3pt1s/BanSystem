<?php

namespace BanSystem\command\mute;

use BanSystem\BanSystem;
use BanSystem\manager\mute\MuteManager;
use BanSystem\utils\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class EditMuteCommand extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("editmute.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0]) && isset($args[1]) && isset($args[2])) {
                if (MuteManager::getInstance()->isMuted($args[0])) {
                    if (Utils::convertStringToDateFormat($args[2]) === null) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid time format! Example: 1d");
                    } else {
                        if (strtolower($args[1]) == "add") {
                            if (!MuteManager::getInstance()->editMute($args[0], $args[2], "add", $errorMessage)) {
                                $sender->sendMessage(BanSystem::getPrefix() . $errorMessage);
                            } else {
                                $sender->sendMessage(BanSystem::getPrefix() . "§7The mute of the player §e" . $args[0] . " §7was edited!");
                            }
                        } else if (strtolower($args[1]) == "sub") {
                            if (!MuteManager::getInstance()->editMute($args[0], $args[2], "sub", $errorMessage)) {
                                $sender->sendMessage(BanSystem::getPrefix() . $errorMessage);
                            } else {
                                $sender->sendMessage(BanSystem::getPrefix() . "§7The mute of the player §e" . $args[0] . " §7was edited!");
                            }
                        } else {
                            $sender->sendMessage(BanSystem::getPrefix() . "§c/editmute <player> <add | sub> <time>");
                        }
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7isn't muted!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c/editmute <player> <add | sub> <time>");
            }
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }
}