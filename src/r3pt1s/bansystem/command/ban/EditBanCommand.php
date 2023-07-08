<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\util\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class EditBanCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("editban", "Edit an existing ban", "/editban <player> <add|sub> <time>");
        $this->setPermission("bansystem.command.editban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 3) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $action = strtolower($args[1]);
            $time = $args[2];

            if ($action == "add" || $action == "sub") {
                if (Utils::convertStringToDateFormat($time) === null) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid time format! Example: §e1d");
                    return true;
                }

                if (($ban = BanManager::getInstance()->getBan($target)) !== null) {
                    if ($ban->getExpire() === null) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cYou can't edit the ban of §e" . $target . "§c, because it's §lPERMANENTLY§r§c.");
                        return true;
                    }

                    if (($response = BanManager::getInstance()->editBan($ban, $sender, Utils::convertStringToDateFormat($time, $ban->getExpire(), $action))) == BanSystem::SUCCESS) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7You have edited the ban of §e" . $target);
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§c" . match($response) {
                            BanSystem::FAILED_CANCELLED => "The event was cancelled and the ban of the player cannot be edited.",
                            BanSystem::FAILED_NOT => "§e" . $target . " §cis not banned!"
                        });
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§e" . $target . " §cis not banned!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
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