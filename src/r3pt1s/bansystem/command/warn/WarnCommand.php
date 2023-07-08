<?php

namespace r3pt1s\bansystem\command\warn;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\warn\Warn;
use r3pt1s\bansystem\manager\warn\WarnManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class WarnCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("warn", "Warn a player", "/warn <player> [reason]");
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
                    $sender->sendMessage(BanSystem::getPrefix() . "§cYou can't warn yourself!");
                    return true;
                }

                if (($response = WarnManager::getInstance()->addWarn(new Warn($target, $sender->getName(), new \DateTime(), ($reason == "" ? null : $reason)))) == BanSystem::SUCCESS) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7You have warned §e" . $target->getName() . "§7.");
                    $target->sendMessage(BanSystem::getPrefix() . "§cYou have been §lwarned§r§c!");
                    $target->sendMessage(BanSystem::getPrefix() . "§7Reason: §e" . ($reason == "" ? "No reason provided." : $reason));
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c" . match ($response) {
                        BanSystem::FAILED_CANCELLED => "§cThe event was cancelled and the player cannot be warned.",
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