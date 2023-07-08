<?php

namespace r3pt1s\bansystem\command\mute;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class MuteInfoCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("muteinfo", "See information about a player", "/muteinfo <player>");
        $this->setPermission("bansystem.command.muteinfo");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($this->testPermissionSilent($sender)) {
            if (count($args) == 0) {
                $sender->sendMessage(BanSystem::getPrefix() . "§c" . $this->getUsage());
                return true;
            }

            $target = implode(" ", $args);

            BanSystem::getInstance()->getProvider()->checkPlayer($target)->onCompletion(
                function(bool $exists) use($sender, $target): void {
                    if (!$exists) {
                        $sender->sendMessage(BanSystem::getPrefix() . "§cThis player doesn't exists!");
                        return;
                    }

                    BanSystem::getInstance()->getProvider()->getMutePoints($target)->onCompletion(
                        function(int $points) use($target, $sender): void {
                            $sender->sendMessage(BanSystem::getPrefix() . "§7Information about §e" . $target . "§8:");
                            $sender->sendMessage(BanSystem::getPrefix() . "§7MutePoints: §e" . $points);
                            $sender->sendMessage(BanSystem::getPrefix() . "§7Muted: §" . (MuteManager::getInstance()->isMuted($target) ? "aYes" : "cNo"));
                            if (($mute = MuteManager::getInstance()->getMute($target)) !== null) {
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Reason: §e" . $mute->getReason());
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Moderator: §e" . $mute->getModerator());
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Muted At: §e" . $mute->getTime()->format("Y-m-d H:i:s"));
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Until: §e" . ($mute->getExpire()?->format("Y-m-d H:i:s") ?? "§c§lPERMANENTLY"));
                            }
                        },
                        fn() => $sender->sendMessage(BanSystem::getPrefix() . "§4Failed to fetch mutepoints of §e" . $target)
                    );
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