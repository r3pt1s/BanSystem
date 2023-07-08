<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class UnmuteCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("unmute", "Unmute a player", "/unmute <player> [mistake]");
        $this->setPermission("bansystem.command.unmute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = array_shift($args);
            $mistake = false;
            if (isset($args[0])) $mistake = (strtolower($args[0]) == "yes" || strtolower($args[0]) == "true");

            if (($mute = MuteManager::getInstance()->getMute($target)) !== null) {
                if (($response = MuteManager::getInstance()->removeMute($mute, $sender, $mistake)) == BanSystem::SUCCESS) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7You have unmuted §e" . $target . "§7.");
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§c" . match($response) {
                        BanSystem::FAILED_CANCELLED => "The event was cancelled and the mute of the player cannot be removed.",
                        BanSystem::FAILED_NOT => "§e" . $target . " §cis not muted!"
                    });
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§e" . $target . " §cis not muted!");
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