<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\Ban;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Utils;

class BanCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("ban", "Ban a player", "/ban <player> <banId>");
        $this->setPermission("bansystem.command.ban");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 2) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $banId = $args[1];

            if ($target == $sender->getName()) {
                $sender->sendMessage(BanSystem::getPrefix() . "§cYou can't ban yourself!");
                return true;
            }

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target, $banId): void {
                    if (!$exists) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cThis player doesn't exists!");
                        return;
                    }

                    if (($banId = Configuration::getInstance()->getBanId($banId)) !== null) {
                        if (($response = BanManager::getInstance()->addBan(new Ban($target, $sender->getName(), $banId->getReason(), new \DateTime(), ($banId->getDuration() === null ? null : Utils::convertStringToDateFormat($banId->getDuration()))))) == BanSystem::SUCCESS) {
                            $sender->sendMessage(BanSystem::getPrefix() . "§7You have banned §e" . $target . "§7.");
                        } else {
                            $sender->sendMessage(BanSystem::getPrefix() . "§c" . match ($response) {
                                BanSystem::FAILED_ALREADY => "§e" . $target . " §cis already banned!",
                                BanSystem::FAILED_CANCELLED => "§cThe event was cancelled and the player cannot be banned.",
                            });
                        }
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid banid!");
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