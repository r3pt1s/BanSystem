<?php

namespace r3pt1s\bansystem\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use r3pt1s\bansystem\BanSystem;

class KickCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("kick", "Kick a player from the server", "/kick <player> [reason]");
        $this->setPermission("bansystem.command.kick");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = array_shift($args);
            $reason = trim(implode(" ", $args));
            if (($target = Server::getInstance()->getPlayerExact($target)) !== null) {
                if (($response = BanSystem::getInstance()->kickPlayer($target, $sender, $reason)) == BanSystem::SUCCESS) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7You have kicked §e" . $target->getName() . " §7from the server.");
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . match ($response) {
                        BanSystem::FAILED_CANCELLED => "§cThe event was cancelled and the player cannot be kicked.",
                        BanSystem::FAILED_CANT => "§cYou can't kick the player."
                    });
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§cThis player is not online!");
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