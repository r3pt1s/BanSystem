<?php

namespace r3pt1s\bansystem\command\ban;

use pocketmine\plugin\PluginOwned;
use r3pt1s\bansystem\BanSystem;
use r3pt1s\bansystem\manager\ban\BanManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class BanInfoCommand extends Command implements PluginOwned {

    public function __construct() {
        parent::__construct("baninfo", "See information about a player", "/baninfo <player>");
        $this->setPermission("bansystem.command.baninfo");
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

                    BanSystem::getInstance()->getProvider()->getBanPoints($target)->onCompletion(
                        function(int $points) use($target, $sender): void {
                            $sender->sendMessage(BanSystem::getPrefix() . "§7Information about §e" . $target . "§8:");
                            $sender->sendMessage(BanSystem::getPrefix() . "§7BanPoints: §e" . $points);
                            $sender->sendMessage(BanSystem::getPrefix() . "§7Banned: §" . (BanManager::getInstance()->isBanned($target) ? "aYes" : "cNo"));
                            if (($ban = BanManager::getInstance()->getBan($target)) !== null) {
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Reason: §e" . $ban->getReason());
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Moderator: §e" . $ban->getModerator());
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Banned At: §e" . $ban->getTime()->format("Y-m-d H:i:s"));
                                $sender->sendMessage(BanSystem::getPrefix() . "§7Until: §e" . ($ban->getExpire()?->format("Y-m-d H:i:s") ?? "§c§lPERMANENTLY"));
                            }
                        },
                        fn() => $sender->sendMessage(BanSystem::getPrefix() . "§4Failed to fetch banpoints of §e" . $target)
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