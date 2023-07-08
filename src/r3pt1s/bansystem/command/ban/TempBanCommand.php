<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\ban\BanManager;
use r3pt1s\bansystem\util\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class TempBanCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("tempban", "Ban a player temporarily", "/tempban <player> <reason> <duration>");
        $this->setPermission("bansystem.command.tempban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 3) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $reason = $args[1];
            $duration = $args[2];

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target, $reason, $duration): void {
                    if (!$exists) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cThis player doesn't exists!");
                        return;
                    }

                    if (Utils::convertStringToDateFormat($duration) === null) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid duration format! Example: §e1d");
                        return;
                    }

                    if (($response = BanManager::getInstance()->addBan(new Ban($target, $sender->getName(), $reason, new \DateTime(), Utils::convertStringToDateFormat($duration)))) == BanSystem::SUCCESS) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§7You have banned §e" . $target . "§7.");
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§c" . match ($response) {
                            BanSystem::FAILED_ALREADY => "§e" . $target . " §cis already banned!",
                            BanSystem::FAILED_CANCELLED => "§cThe event was cancelled and the player cannot be banned.",
                        });
                    }
                },
                fn() => $sender->sendMessage(BanSystem::getPrefix() . "§4Failed to check if §e" . $target . " §4exists")
            );
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }

    public function getOwningPlugin(): BanSystem {
        return BanSystem::getInstance();
    }
}