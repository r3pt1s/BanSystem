<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\Mute;
use r3pt1s\bansystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use r3pt1s\bansystem\util\Configuration;
use r3pt1s\bansystem\util\Utils;

class MuteCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("mute", "Mute a player", "/mute <player> <muteId>");
        $this->setPermission("bansystem.command.mute");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) < 2) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = $args[0];
            $muteId = $args[1];

            if ($target == $sender->getName()) {
                $sender->sendMessage(BanSystem::getPrefix() . "§cYou can't mute yourself!");
                return true;
            }

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target, $muteId): void {
                    if (!$exists) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cThis player doesn't exists!");
                        return;
                    }

                    if (($muteId = Configuration::getInstance()->getMuteId($muteId)) !== null) {
                        if (($response = MuteManager::getInstance()->addMute(new Mute($target, $sender->getName(), $muteId->getReason(), new \DateTime(), ($muteId->getDuration() === null ? null : Utils::convertStringToDateFormat($muteId->getDuration()))))) == BanSystem::SUCCESS) {
                            $sender->sendMessage(BanSystem::getPrefix() . "§7You have muted §e" . $target . "§7.");
                        } else {
                            $sender->sendMessage(BanSystem::getPrefix() . "§c" . match ($response) {
                                BanSystem::FAILED_ALREADY => "§e" . $target . " §cis already muted!",
                                BanSystem::FAILED_CANCELLED => "§cThe event was cancelled and the player cannot be muted.",
                            });
                        }
                    } else {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cPlease provide a valid muteid!");
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