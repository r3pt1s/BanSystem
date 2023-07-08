<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class UnbanCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("unban", "Unban a player", "/unban <player> [mistake]");
        $this->setPermission("bansystem.command.unban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = array_shift($args);
            $mistake = false;
            if (isset($args[0])) $mistake = strtolower($args[0]) == "yes" || strtolower($args[0]) == "true";

            if (($ban = BanManager::getInstance()->getBan($target)) !== null) {
                if (($response = BanManager::getInstance()->removeBan($ban, $sender, $mistake)) == BanSystem::SUCCESS) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7You have unbanned §e" . $target . "§7.");
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c" . match($response) {
                        BanSystem::FAILED_CANCELLED => "The event was cancelled and the ban of the player cannot be removed.",
                        BanSystem::FAILED_NOT => "§e" . $target . " §cis not banned!"
                    });
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§e" . $target . " §cis not banned!");
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