<?php

namespace r3pt1s\BanSystem\command\mute;

use r3pt1s\BanSystem\BanSystem;
use r3pt1s\BanSystem\manager\mute\MuteManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class MuteInfoCommand extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("muteinfo.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender->hasPermission($this->getPermission())) {
            if (isset($args[0])) {
                if (BanSystem::getInstance()->isPlayerCreated($args[0])) {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7Information about §e" . $args[0] . "§7:");
                    $sender->sendMessage(BanSystem::getPrefix() . "§7MutePoints: §e" . MuteManager::getInstance()->getMutesPoints($args[0]));
                    $sender->sendMessage(BanSystem::getPrefix() . "§7Muted: §e" . (MuteManager::getInstance()->isMuted($args[0]) ? "§cYES" : "§aNO"));
                    $info = MuteManager::getInstance()->getMuteInfo($args[0]);
                    if ($info !== false) {
                        $reason = (isset($info["Id"]) ? BanSystem::getInstance()->getConfiguration()->getMuteIds()[$info["Id"]]["reason"] ?? "Error" : $info["Reason"] ?? "Error");
                        $sender->sendMessage(BanSystem::getPrefix() . "§7Reason: §e" . $reason);
                        $sender->sendMessage(BanSystem::getPrefix() . "§7Moderator: §e" . $info["Moderator"]);
                        $sender->sendMessage(BanSystem::getPrefix() . "§7Time: §e" . ($info["Time"] == "-1" ? "PERMANENTLY" : $info["Time"]));
                        $sender->sendMessage(BanSystem::getPrefix() . "§7Muted at: §e" . $info["MutedAt"]);
                    }
                } else {
                    $sender->sendMessage(BanSystem::getPrefix() . "§7The player §e" . $args[0] . " §7doesn't exists!");
                }
            } else {
                $sender->sendMessage(BanSystem::getPrefix() . "§c/muteinfo <player>");
            }
        } else {
            $sender->sendMessage(BanSystem::getPrefix() . BanSystem::NO_PERMS);
        }
        return true;
    }
}